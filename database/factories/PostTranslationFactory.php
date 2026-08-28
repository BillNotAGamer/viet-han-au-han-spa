<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PostTranslation>
 */
class PostTranslationFactory extends Factory
{
    protected $model = PostTranslation::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(6);

        return [
            'post_id' => Post::factory(),
            'locale' => 'vi',
            'title' => $title,
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1000, 99999),
            'excerpt' => $this->faker->sentence(15),
            'content' => '<p>'.$this->faker->paragraphs(4, true).'</p>',
            'seo_title' => $title.' | Việt Hàn Âu Hàn Spa',
            'seo_description' => $this->faker->sentence(15),
        ];
    }
}
