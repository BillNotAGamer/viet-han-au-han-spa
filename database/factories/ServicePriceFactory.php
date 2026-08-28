<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServicePrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServicePrice>
 */
class ServicePriceFactory extends Factory
{
    protected $model = ServicePrice::class;

    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'duration_minutes' => $this->faker->randomElement([60, 75, 90, 120]),
            'price_amount' => $this->faker->randomElement([390000, 490000, 590000, 790000, 990000]),
            'sort_order' => $this->faker->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
