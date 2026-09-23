<?php

declare(strict_types=1);

namespace App\Enums;

enum HeaderServiceGroup: string
{
    case SKIN_CARE = 'cham-soc-da';
    case ADVANCED_HAIR_REMOVAL = 'triet-long-cong-nghe-cao';
    case ACNE_SCAR_TREATMENT = 'dieu-tri-mun-seo';
    case HERBAL_HAIR_SCALP_CARE = 'goi-dau-duong-sinh-dong-y';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_reduce(self::cases(), function (array $options, self $group): array {
            $options[$group->value] = $group->label('vi');

            return $options;
        }, []);
    }

    public function label(string $locale): string
    {
        return (string) __('services.groups.'.$this->value.'.label', [], $locale);
    }

    public function routeSlug(string $locale): string
    {
        return (string) __('services.groups.'.$this->value.'.route_slug', [], $locale);
    }

    public static function fromRouteSlug(string $locale, string $slug): ?self
    {
        foreach (self::cases() as $group) {
            if ($group->routeSlug($locale) === $slug) {
                return $group;
            }
        }

        return null;
    }
}
