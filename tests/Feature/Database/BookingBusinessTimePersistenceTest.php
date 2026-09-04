<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingBusinessTimePersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_preferred_date_and_time_persist_without_timezone_shifting(): void
    {
        $booking = Booking::factory()->create([
            'preferred_date' => '2026-08-27',
            'preferred_time' => '19:00:00',
        ]);

        $fresh = $booking->fresh();

        $this->assertSame('2026-08-27', $fresh->preferred_date->format('Y-m-d'));
        $this->assertStringStartsWith('19:00', (string) $fresh->preferred_time);
    }
}
