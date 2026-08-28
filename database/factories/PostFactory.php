<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'post_category_id' => PostCategory::factory(),
            'author_id' => User::factory(),
            'hero_media_id' => Media::factory(),
            'status' => ContentStatus::PUBLISHED,
            'is_featured' => $this->faker->boolean(25),
            'published_at' => now(),
        ];
    }
}
