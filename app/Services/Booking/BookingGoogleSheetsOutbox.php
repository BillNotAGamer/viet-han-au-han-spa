<?php

declare(strict_types=1);

namespace App\Services\Booking;

use App\Enums\BookingGoogleSheetsSyncStatus;
use App\Models\Booking;
use App\Models\BookingGoogleSheetsSyncRecord;

class BookingGoogleSheetsOutbox
{
    public function createFor(Booking $booking): ?BookingGoogleSheetsSyncRecord
    {
        if (! (bool) config('booking.google_sheets.enabled', false)) {
            return null;
        }

        return BookingGoogleSheetsSyncRecord::query()->create([
            'booking_id' => $booking->getKey(),
            'booking_reference' => (string) $booking->reference,
            'destination' => BookingGoogleSheetsSyncRecord::DESTINATION,
            'status' => BookingGoogleSheetsSyncStatus::PENDING,
            'append_safe' => true,
        ]);
    }
}
