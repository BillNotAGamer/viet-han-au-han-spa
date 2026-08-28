<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PostCategory;
use App\Models\PostCategoryTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PostCategoryTranslation>
 */
class PostCategoryTranslationFactory extends Factory
{
    protected $model = PostCategoryTranslation::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'post_category_id' => PostCategory::factory(),
            'locale' => 'vi',
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1000, 99999),
            'description' => $this->faker->sentence(12),
            'seo_title' => ucfirst($name).' - Blog Spa Việt Hàn',
            'seo_description' => $this->faker->sentence(15),
        ];
    }
}
