<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\Booking;
use App\Models\ServicePrice;
use App\Models\TrainingCourse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoneyIntegerPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_money_fields_persist_and_roundtrip_as_exact_integers(): void
    {
        $price = ServicePrice::factory()->create([
            'price_amount' => 390000,
        ]);

        $course = TrainingCourse::factory()->create([
            'tuition_fee' => 25000000,
        ]);

        $booking = Booking::factory()->create([
            'price_amount_snapshot' => 490000,
        ]);

        $this->assertSame(390000, $price->fresh()->price_amount);
        $this->assertSame(25000000, $course->fresh()->tuition_fee);
        $this->assertSame(490000, $booking->fresh()->price_amount_snapshot);
    }
}
