<?php

declare(strict_types=1);

namespace App\Services\Booking;

use App\Services\Booking\Contracts\BookingSheetsClient;
use App\Services\Booking\Exceptions\GoogleSheetsConfigurationException;
use Dotenv\Dotenv;
use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Env;
use JsonException;
use Throwable;

class GoogleBookingSheetsClient implements BookingSheetsClient
{
    protected ?Sheets $service = null;

    protected ?string $credentialsFingerprint = null;

    /** @var array<string, mixed>|null */
    protected ?array $resolvedCredentials = null;

    public function __construct(protected Filesystem $files) {}

    /**
     * @return list<string>
     */
    public function readHeader(string $spreadsheetId, string $range): array
    {
        $values = $this->sheets()
            ->spreadsheets_values
            ->get($spreadsheetId, $range)
            ->getValues();

        if (! isset($values[0]) || ! is_array($values[0])) {
            return [];
        }

        return array_values(array_map(
            static fn (mixed $value): string => (string) $value,
            $values[0]
        ));
    }

    /**
     * Read only the reference column used for durable duplicate detection.
     *
     * @return list<string>
     */
    public function readReferences(string $spreadsheetId, string $range): array
    {
        $values = $this->sheets()
            ->spreadsheets_values
            ->get($spreadsheetId, $range)
            ->getValues();

        if (! is_array($values)) {
            return [];
        }

        $references = [];

        foreach ($values as $row) {
            if (is_array($row) && array_key_exists(0, $row)) {
                $references[] = (string) $row[0];
            }
        }

        return $references;
    }

    /**
     * @param  list<string>  $row
     */
    public function appendRow(string $spreadsheetId, string $range, array $row): void
    {
        $body = new ValueRange([
            'majorDimension' => 'ROWS',
            'values' => [$row],
        ]);

        $this->sheets()->spreadsheets_values->append(
            $spreadsheetId,
            $range,
            $body,
            [
                'insertDataOption' => 'INSERT_ROWS',
                'valueInputOption' => 'RAW',
            ]
        );
    }

    protected function sheets(): Sheets
    {
        $credentials = $this->credentials();
        $credentialsFingerprint = hash(
            'sha256',
            json_encode($credentials, JSON_THROW_ON_ERROR),
        );

        if ($this->service !== null && $this->credentialsFingerprint === $credentialsFingerprint) {
            return $this->service;
        }

        // Explicitly disable API retries. An append that succeeds remotely but
        // times out locally must not be replayed within the same HTTP request.
        $client = new Client([
            'retry' => ['retries' => 0],
        ]);
        $client->setApplicationName((string) config('app.name'));
        $client->setAuthConfig($credentials);
        $client->setScopes([Sheets::SPREADSHEETS]);
        $client->setHttpClient(new HttpClient([
            'connect_timeout' => 5,
            'timeout' => 10,
        ]));

        $this->credentialsFingerprint = $credentialsFingerprint;

        return $this->service = new Sheets($client);
    }

    /**
     * @return array<string, mixed>
     */
    protected function credentials(): array
    {
        if ($this->resolvedCredentials !== null) {
            return $this->resolvedCredentials;
        }

        try {
            $credentials = json_decode($this->credentialsJson(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new GoogleSheetsConfigurationException('Google Sheets service-account credentials are not valid JSON.', 0, $exception);
        }

        if (! is_array($credentials) || ($credentials['type'] ?? null) !== 'service_account') {
            throw new GoogleSheetsConfigurationException('Google Sheets credentials must describe a service account.');
        }

        return $this->resolvedCredentials = $credentials;
    }

    /**
     * Resolve credentials at runtime so private key material is never stored in
     * Laravel's cached configuration. A configured file always takes precedence
     * over the legacy raw-JSON environment fallback.
     */
    protected function credentialsJson(): string
    {
        $credentialsFile = trim((string) config('booking.google_sheets.credentials_file'));

        if ($credentialsFile !== '') {
            return $this->readCredentialsFile($credentialsFile);
        }

        $credentialsJson = $this->legacyCredentialsJson();

        if ($credentialsJson === '') {
            throw new GoogleSheetsConfigurationException('Google Sheets service-account credentials are not configured.');
        }

        return $credentialsJson;
    }

    protected function readCredentialsFile(string $path): string
    {
        if (! $this->isAbsolutePath($path)) {
            throw new GoogleSheetsConfigurationException('Google Sheets credentials file path must be absolute.');
        }

        if ($this->isInsidePublicPath($path)) {
            throw new GoogleSheetsConfigurationException('Google Sheets credentials file must be outside the public web root.');
        }

        $resolvedPath = realpath($path);

        if ($resolvedPath === false || ! $this->files->isFile($resolvedPath)) {
            throw new GoogleSheetsConfigurationException('Google Sheets credentials file does not exist or is not a regular file.');
        }

        if (! $this->files->isReadable($resolvedPath)) {
            throw new GoogleSheetsConfigurationException('Google Sheets credentials file is not readable.');
        }

        if ($this->isInsidePublicPath($resolvedPath)) {
            throw new GoogleSheetsConfigurationException('Google Sheets credentials file must be outside the public web root.');
        }

        return $this->files->get($resolvedPath);
    }

    protected function legacyCredentialsJson(): string
    {
        $credentialsJson = Env::get('GOOGLE_SHEETS_CREDENTIALS_JSON');

        if (is_string($credentialsJson) && trim($credentialsJson) !== '') {
            return trim($credentialsJson);
        }

        $environmentFile = base_path('.env');

        if (! $this->files->isFile($environmentFile) || ! $this->files->isReadable($environmentFile)) {
            return '';
        }

        try {
            $variables = Dotenv::parse($this->files->get($environmentFile));
        } catch (Throwable $exception) {
            throw new GoogleSheetsConfigurationException('Unable to read legacy Google Sheets credentials from the environment file.', 0, $exception);
        }

        $credentialsJson = $variables['GOOGLE_SHEETS_CREDENTIALS_JSON'] ?? null;

        return is_string($credentialsJson) ? trim($credentialsJson) : '';
    }

    protected function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/')
            || str_starts_with($path, '\\\\')
            || preg_match('/\A[A-Za-z]:[\\\\\/]/', $path) === 1;
    }

    protected function isInsidePublicPath(string $path): bool
    {
        $publicPath = realpath(public_path()) ?: public_path();
        $normalizedPath = $this->normalizePath($path);
        $normalizedPublicPath = $this->normalizePath($publicPath);

        return $normalizedPath === $normalizedPublicPath
            || str_starts_with($normalizedPath, $normalizedPublicPath.'/');
    }

    protected function normalizePath(string $path): string
    {
        $normalized = rtrim(str_replace('\\', '/', $path), '/');

        return PHP_OS_FAMILY === 'Windows' ? strtolower($normalized) : $normalized;
    }
}
