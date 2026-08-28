<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ServiceCategory;
use App\Models\ServiceCategoryTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceCategoryTranslation>
 */
class ServiceCategoryTranslationFactory extends Factory
{
    protected $model = ServiceCategoryTranslation::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'service_category_id' => ServiceCategory::factory(),
            'locale' => 'vi',
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1000, 99999),
            'description' => $this->faker->paragraph(),
            'seo_title' => ucfirst($name).' - Việt Hàn Âu Hàn Spa',
            'seo_description' => $this->faker->sentence(15),
        ];
    }
}
