<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Media;
use App\Models\MediaTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaTranslation>
 */
class MediaTranslationFactory extends Factory
{
    protected $model = MediaTranslation::class;

    public function definition(): array
    {
        return [
            'media_id' => Media::factory(),
            'locale' => 'vi',
            'alt_text' => $this->faker->sentence(4),
            'caption' => $this->faker->sentence(8),
        ];
    }
}
