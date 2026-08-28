<?php

declare(strict_types=1);

namespace App\Services\Settings;

use App\Enums\SiteSettingType;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class SiteSettings
{
    public const CACHE_PREFIX = 'site_settings:';

    public const PUBLIC_ALL_CACHE_KEY = 'site_settings:public_all';

    /**
     * Retrieve typed setting value by key with cache-first lookup.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $normalizedKey = strtolower(trim($key));
        $cacheKey = self::CACHE_PREFIX.$normalizedKey;

        return Cache::remember($cacheKey, now()->addDay(), function () use ($normalizedKey, $default) {
            $setting = SiteSetting::where('key', $normalizedKey)->first();
            if (! $setting) {
                return $default;
            }

            return $this->deserialize($setting->value, $setting->type);
        });
    }

    /**
     * Retrieve setting value only if is_public = true.
     */
    public function getPublic(string $key, mixed $default = null): mixed
    {
        $normalizedKey = strtolower(trim($key));

        $setting = SiteSetting::where('key', $normalizedKey)->first();
        if (! $setting || ! $setting->is_public) {
            return $default;
        }

        return $this->get($normalizedKey, $default);
    }

    /**
     * Retrieve all public settings as an associative key-value map.
     *
     * @return array<string, mixed>
     */
    public function publicSettings(): array
    {
        return Cache::remember(self::PUBLIC_ALL_CACHE_KEY, now()->addDay(), function () {
            $settings = SiteSetting::where('is_public', true)->get();
            $result = [];

            foreach ($settings as $setting) {
                $result[$setting->key] = $this->deserialize($setting->value, $setting->type);
            }

            return $result;
        });
    }

    /**
     * Deserialize raw database string value to native PHP type.
     */
    public function deserialize(?string $rawValue, SiteSettingType $type): mixed
    {
        if ($rawValue === null) {
            return null;
        }

        return match ($type) {
            SiteSettingType::STRING, SiteSettingType::TEXT => $rawValue,
            SiteSettingType::BOOLEAN => in_array(strtolower(trim($rawValue)), ['1', 'true', 'on', 'yes'], true),
            SiteSettingType::JSON => json_decode($rawValue, true),
        };
    }

    /**
     * Invalidate cached keys for a setting.
     */
    public function clearCache(string $key): void
    {
        $normalizedKey = strtolower(trim($key));
        Cache::forget(self::CACHE_PREFIX.$normalizedKey);
        Cache::forget(self::PUBLIC_ALL_CACHE_KEY);
    }
}
