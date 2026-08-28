<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->randomElement(['home', 'about', 'contact', 'terms', 'privacy']).'-'.$this->faker->unique()->numberBetween(1000, 99999),
            'status' => ContentStatus::PUBLISHED,
        ];
    }
}
