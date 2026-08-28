<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

class Localization
{
    /**
     * @return array<string, array{name: string, prefix: string, regional: string, dir: string}>
     */
    public static function supportedLocales(): array
    {
        return config('localization.supported_locales', [
            'vi' => ['name' => 'Tiếng Việt', 'prefix' => '', 'regional' => 'vi_VN', 'dir' => 'ltr'],
            'en' => ['name' => 'English', 'prefix' => 'en', 'regional' => 'en_US', 'dir' => 'ltr'],
        ]);
    }

    /**
     * @return list<string>
     */
    public static function supportedLocaleKeys(): array
    {
        return array_keys(static::supportedLocales());
    }

    public static function isSupported(?string $locale): bool
    {
        if ($locale === null || $locale === '') {
            return false;
        }

        return in_array($locale, static::supportedLocaleKeys(), true);
    }

    public static function defaultLocale(): string
    {
        return (string) config('localization.default_locale', 'vi');
    }

    public static function fallbackLocale(): string
    {
        return (string) config('localization.fallback_locale', 'vi');
    }

    public static function currentLocale(): string
    {
        return App::getLocale();
    }

    public static function getPrefixForLocale(string $locale): string
    {
        $locales = static::supportedLocales();

        return $locales[$locale]['prefix'] ?? '';
    }

    /**
     * Generate the corresponding URL for switching language.
     *
     * @param  array<string, mixed>  $parameters
     */
    public static function switchLocaleUrl(string $targetLocale, ?string $currentRouteName = null, array $parameters = []): string
    {
        if (! static::isSupported($targetLocale)) {
            $targetLocale = static::defaultLocale();
        }

        $routeName = $currentRouteName ?? Route::currentRouteName();

        if ($routeName !== null && $routeName !== '') {
            // Check if route name has a locale prefix (e.g. 'vi.home', 'en.home')
            $parts = explode('.', $routeName, 2);
            if (count($parts) === 2 && static::isSupported($parts[0])) {
                $targetRouteName = $targetLocale.'.'.$parts[1];
                if (Route::has($targetRouteName)) {
                    return route($targetRouteName, $parameters);
                }
            }
        }

        // Default fallback to target locale's home page
        $homeRoute = $targetLocale.'.home';
        if (Route::has($homeRoute)) {
            return route($homeRoute);
        }

        return $targetLocale === 'en' ? url('/en') : url('/');
    }
}
