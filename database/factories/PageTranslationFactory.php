<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PageTranslation>
 */
class PageTranslationFactory extends Factory
{
    protected $model = PageTranslation::class;

    public function definition(): array
    {
        $title = $this->faker->words(3, true);

        return [
            'page_id' => Page::factory(),
            'locale' => 'vi',
            'title' => ucfirst($title),
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1000, 99999),
            'content' => '<p>'.$this->faker->paragraphs(3, true).'</p>',
            'seo_title' => ucfirst($title).' - Việt Hàn Âu Hàn Spa',
            'seo_description' => $this->faker->sentence(15),
        ];
    }
}
