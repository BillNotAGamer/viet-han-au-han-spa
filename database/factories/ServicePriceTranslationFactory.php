<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ServicePrice;
use App\Models\ServicePriceTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServicePriceTranslation>
 */
class ServicePriceTranslationFactory extends Factory
{
    protected $model = ServicePriceTranslation::class;

    public function definition(): array
    {
        return [
            'service_price_id' => ServicePrice::factory(),
            'locale' => 'vi',
            'label' => $this->faker->randomElement(['Gói Tiêu Chuẩn', 'Gói Chuyên Sâu', 'Gói Cao Cấp', 'Gói VIP']),
        ];
    }
}
