<?php

declare(strict_types=1);

namespace App\Services\Booking;

use App\Models\Booking;

class BookingGoogleSheetsRowMapper
{
    /**
     * Map only the current client-owned A:F schema.
     *
     * @return array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string}
     */
    public function map(Booking $booking, string $bookingReference): array
    {
        return [
            $booking->preferred_date?->format('d/m/Y') ?? '',
            (string) $booking->service_name_snapshot,
            (string) $booking->customer_name,
            (string) $booking->phone,
            substr((string) $booking->preferred_time, 0, 5),
            $bookingReference,
        ];
    }
}
