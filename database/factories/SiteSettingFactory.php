<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SiteSettingType;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteSetting>
 */
class SiteSettingFactory extends Factory
{
    protected $model = SiteSetting::class;

    public function definition(): array
    {
        return [
            'key' => 'setting_'.$this->faker->unique()->slug(2),
            'value' => $this->faker->sentence(4),
            'type' => SiteSettingType::STRING,
            'group' => 'general',
            'is_public' => false,
        ];
    }
}
