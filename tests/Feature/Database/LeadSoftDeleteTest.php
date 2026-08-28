<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\Booking;
use App\Models\TrainingInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_uses_soft_deletes(): void
    {
        $booking = Booking::factory()->create();

        $booking->delete();

        $this->assertNotNull($booking->deleted_at);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
        $this->assertCount(0, Booking::all());
        $this->assertCount(1, Booking::withTrashed()->get());

        $booking->restore();
        $this->assertNull($booking->deleted_at);
        $this->assertCount(1, Booking::all());
    }

    public function test_training_inquiry_uses_soft_deletes(): void
    {
        $inquiry = TrainingInquiry::factory()->create();

        $inquiry->delete();

        $this->assertNotNull($inquiry->deleted_at);
        $this->assertDatabaseHas('training_inquiries', ['id' => $inquiry->id]);
        $this->assertCount(0, TrainingInquiry::all());
        $this->assertCount(1, TrainingInquiry::withTrashed()->get());

        $inquiry->restore();
        $this->assertNull($inquiry->deleted_at);
        $this->assertCount(1, TrainingInquiry::all());
    }
}
