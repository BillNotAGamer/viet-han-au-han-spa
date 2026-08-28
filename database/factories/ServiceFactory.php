<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'service_category_id' => ServiceCategory::factory(),
            'hero_media_id' => Media::factory(),
            'status' => ContentStatus::PUBLISHED,
            'is_featured' => $this->faker->boolean(30),
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }
}
