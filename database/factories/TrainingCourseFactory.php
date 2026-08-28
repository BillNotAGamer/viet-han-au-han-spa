<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\TrainingCourse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingCourse>
 */
class TrainingCourseFactory extends Factory
{
    protected $model = TrainingCourse::class;

    public function definition(): array
    {
        return [
            'hero_media_id' => Media::factory(),
            'tuition_fee' => $this->faker->randomElement([15000000, 20000000, 25000000, 35000000]),
            'status' => ContentStatus::PUBLISHED,
            'is_featured' => $this->faker->boolean(40),
            'sort_order' => $this->faker->numberBetween(0, 50),
            'published_at' => now(),
        ];
    }
}
