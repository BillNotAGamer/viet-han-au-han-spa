<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Services\Booking\GoogleBookingSheetsClient;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Tests\TestCase;

class GoogleBookingSheetsCredentialTest extends TestCase
{
    /** @var list<string> */
    protected array $temporaryFiles = [];

    protected string|false $originalLegacyCredentials;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalLegacyCredentials = getenv('GOOGLE_SHEETS_CREDENTIALS_JSON');
        putenv('GOOGLE_SHEETS_CREDENTIALS_JSON');

        config(['booking.google_sheets.credentials_file' => null]);
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        if ($this->originalLegacyCredentials === false) {
            putenv('GOOGLE_SHEETS_CREDENTIALS_JSON');
        } else {
            putenv('GOOGLE_SHEETS_CREDENTIALS_JSON='.$this->originalLegacyCredentials);
        }

        parent::tearDown();
    }

    public function test_valid_absolute_credentials_file_is_loaded_at_runtime(): void
    {
        $path = $this->credentialFile([
            'type' => 'service_account',
            'project_id' => 'file-project',
        ]);
        config(['booking.google_sheets.credentials_file' => $path]);

        $credentials = $this->client()->resolveCredentialsForTest();

        $this->assertSame('service_account', $credentials['type']);
        $this->assertSame('file-project', $credentials['project_id']);
    }

    public function test_relative_credentials_file_path_is_rejected(): void
    {
        config(['booking.google_sheets.credentials_file' => 'private/google-booking.json']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must be absolute');

        $this->client()->resolveCredentialsForTest();
    }

    public function test_missing_credentials_file_is_rejected(): void
    {
        config([
            'booking.google_sheets.credentials_file' => storage_path('framework/testing/missing-google-credentials.json'),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('does not exist or is not a regular file');

        $this->client()->resolveCredentialsForTest();
    }

    public function test_unreadable_credentials_file_is_rejected(): void
    {
        $path = $this->credentialFile(['type' => 'service_account']);
        $resolvedPath = realpath($path);
        $this->assertIsString($resolvedPath);

        /** @var Filesystem&MockObject $files */
        $files = $this->createMock(Filesystem::class);
        $files->expects($this->once())->method('isFile')->with($resolvedPath)->willReturn(true);
        $files->expects($this->once())->method('isReadable')->with($resolvedPath)->willReturn(false);
        $files->expects($this->never())->method('get');

        config(['booking.google_sheets.credentials_file' => $path]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('is not readable');

        $this->client($files)->resolveCredentialsForTest();
    }

    public function test_invalid_json_credentials_file_is_rejected(): void
    {
        $path = $this->temporaryFile('{invalid-json');
        config(['booking.google_sheets.credentials_file' => $path]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('are not valid JSON');

        $this->client()->resolveCredentialsForTest();
    }

    public function test_non_service_account_credentials_file_is_rejected(): void
    {
        $path = $this->credentialFile(['type' => 'authorized_user']);
        config(['booking.google_sheets.credentials_file' => $path]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must describe a service account');

        $this->client()->resolveCredentialsForTest();
    }

    public function test_credentials_file_inside_public_directory_is_rejected_before_reading(): void
    {
        config(['booking.google_sheets.credentials_file' => public_path('index.php')]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must be outside the public web root');

        $this->client()->resolveCredentialsForTest();
    }

    public function test_raw_json_environment_value_remains_a_backward_compatible_fallback(): void
    {
        putenv('GOOGLE_SHEETS_CREDENTIALS_JSON={"type":"service_account","project_id":"legacy-project"}');

        $credentials = $this->client()->resolveCredentialsForTest();

        $this->assertSame('service_account', $credentials['type']);
        $this->assertSame('legacy-project', $credentials['project_id']);
    }

    public function test_credentials_file_takes_precedence_over_legacy_raw_json(): void
    {
        $path = $this->credentialFile([
            'type' => 'service_account',
            'project_id' => 'preferred-file-project',
        ]);
        config(['booking.google_sheets.credentials_file' => $path]);
        putenv('GOOGLE_SHEETS_CREDENTIALS_JSON={"type":"service_account","project_id":"legacy-project"}');

        $credentials = $this->client()->resolveCredentialsForTest();

        $this->assertSame('preferred-file-project', $credentials['project_id']);
    }

    public function test_private_key_material_is_not_part_of_cached_booking_configuration(): void
    {
        $this->assertArrayHasKey('credentials_file', config('booking.google_sheets'));
        $this->assertArrayNotHasKey('credentials_json', config('booking.google_sheets'));
    }

    public function test_google_sheets_integration_is_disabled_by_default(): void
    {
        $this->assertFalse((bool) config('booking.google_sheets.enabled'));
    }

    /**
     * @param  array<string, string>  $credentials
     */
    protected function credentialFile(array $credentials): string
    {
        return $this->temporaryFile(json_encode($credentials, JSON_THROW_ON_ERROR));
    }

    protected function temporaryFile(string $contents): string
    {
        $path = tempnam(storage_path('framework/testing'), 'google-credentials-');

        if ($path === false) {
            throw new RuntimeException('Unable to create temporary credentials fixture.');
        }

        file_put_contents($path, $contents);
        $this->temporaryFiles[] = $path;

        return $path;
    }

    protected function client(?Filesystem $files = null): TestableGoogleBookingSheetsClient
    {
        return new TestableGoogleBookingSheetsClient($files ?? new Filesystem);
    }
}

class TestableGoogleBookingSheetsClient extends GoogleBookingSheetsClient
{
    /**
     * @return array<string, mixed>
     */
    public function resolveCredentialsForTest(): array
    {
        return $this->credentials();
    }
}
