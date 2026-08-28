<?php

declare(strict_types=1);

namespace App\Services\Settings;

use App\Enums\SiteSettingType;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SiteSettingWriter
{
    /**
     * Sensitive key fragments prohibited from site_settings.
     *
     * @var array<int, string>
     */
    public const FORBIDDEN_KEY_PATTERNS = [
        'password',
        'passwd',
        'secret',
        'private_key',
        'client_secret',
        'access_token',
        'app_key',
        'smtp_password',
        'database_password',
        'db_password',
    ];

    public function __construct(
        protected SiteSettings $reader
    ) {}

    /**
     * Validate key against secret patterns and grammar.
     */
    public function validateKey(string $key): string
    {
        $normalized = strtolower(trim($key));

        // 1. Secret guard check
        foreach (self::FORBIDDEN_KEY_PATTERNS as $forbidden) {
            if (str_contains($normalized, $forbidden)) {
                throw new InvalidArgumentException("Setting key '{$key}' contains forbidden keyword '{$forbidden}'. Secrets must be stored in environment variables or hosting configuration, never in site_settings.");
            }
        }

        // 2. Grammar check: alphanumeric with dots, hyphens, underscores
        if (! preg_match('/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/', $normalized)) {
            throw new InvalidArgumentException("Invalid setting key '{$key}'. Key must be lowercase alphanumeric with dots, hyphens, or underscores.");
        }

        return $normalized;
    }

    /**
     * Normalize and serialize value according to SiteSettingType.
     */
    public function serializeValue(mixed $value, SiteSettingType $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            SiteSettingType::STRING => trim((string) $value),
            SiteSettingType::TEXT => (string) $value,
            SiteSettingType::BOOLEAN => in_array(
                is_string($value) ? strtolower(trim($value)) : $value,
                [true, 1, '1', 'true', 'on', 'yes'],
                true
            ) ? '1' : '0',
            SiteSettingType::JSON => $this->validateAndEncodeJson($value),
        };
    }

    /**
     * Validate JSON input and return deterministic compact string.
     */
    protected function validateAndEncodeJson(mixed $value): string
    {
        if (is_array($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($encoded === false) {
                throw new InvalidArgumentException('Failed to encode JSON value: '.json_last_error_msg());
            }

            return $encoded;
        }

        $strValue = trim((string) $value);
        if ($strValue === '') {
            return '{}';
        }

        $decoded = json_decode($strValue, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON provided: '.json_last_error_msg());
        }

        return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    /**
     * Create a new SiteSetting.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): SiteSetting
    {
        $key = $this->validateKey((string) ($data['key'] ?? ''));

        if (SiteSetting::where('key', $key)->exists()) {
            throw new InvalidArgumentException("Setting with key '{$key}' already exists.");
        }

        $type = $data['type'] instanceof SiteSettingType
            ? $data['type']
            : SiteSettingType::from((string) ($data['type'] ?? SiteSettingType::STRING->value));

        $serializedValue = $this->serializeValue($data['value'] ?? null, $type);
        $group = strtolower(trim((string) ($data['group'] ?? 'general'))) ?: 'general';
        $isPublic = (bool) ($data['is_public'] ?? false);

        $setting = DB::transaction(function () use ($key, $serializedValue, $type, $group, $isPublic) {
            return SiteSetting::create([
                'key' => $key,
                'value' => $serializedValue,
                'type' => $type,
                'group' => $group,
                'is_public' => $isPublic,
            ]);
        });

        $this->reader->clearCache($key);

        return $setting;
    }

    /**
     * Update an existing SiteSetting. Key is strictly immutable.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(SiteSetting $setting, array $data): SiteSetting
    {
        $type = isset($data['type'])
            ? ($data['type'] instanceof SiteSettingType ? $data['type'] : SiteSettingType::from((string) $data['type']))
            : $setting->type;

        $rawValue = array_key_exists('value', $data) ? $data['value'] : $setting->value;
        $serializedValue = $this->serializeValue($rawValue, $type);

        $group = isset($data['group'])
            ? (strtolower(trim((string) $data['group'])) ?: 'general')
            : $setting->group;

        $isPublic = array_key_exists('is_public', $data)
            ? (bool) $data['is_public']
            : $setting->is_public;

        // Key remains unchanged
        DB::transaction(function () use ($setting, $serializedValue, $type, $group, $isPublic) {
            $setting->update([
                'value' => $serializedValue,
                'type' => $type,
                'group' => $group,
                'is_public' => $isPublic,
            ]);
        });

        $this->reader->clearCache($setting->key);

        return $setting->fresh();
    }

    /**
     * Delete a SiteSetting and invalidate cache.
     */
    public function delete(SiteSetting $setting): void
    {
        $key = $setting->key;

        DB::transaction(function () use ($setting) {
            $setting->delete();
        });

        $this->reader->clearCache($key);
    }
}
