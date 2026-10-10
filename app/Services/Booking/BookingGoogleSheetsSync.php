<?php

declare(strict_types=1);

namespace App\Services\Booking;

use App\Enums\BookingGoogleSheetsSyncStatus;
use App\Models\Booking;
use App\Models\BookingGoogleSheetsSyncRecord;
use Illuminate\Support\Facades\Log;
use Throwable;

class BookingGoogleSheetsSync
{
    public function __construct(protected BookingGoogleSheetsSyncProcessor $processor) {}

    public function sync(Booking $booking): bool
    {
        if (! (bool) config('booking.google_sheets.enabled', false)) {
            return false;
        }

        try {
            $record = BookingGoogleSheetsSyncRecord::query()
                ->where('booking_id', $booking->getKey())
                ->where('destination', BookingGoogleSheetsSyncRecord::DESTINATION)
                ->first();

            if ($record === null) {
                return false;
            }

            return $this->processor->process($record)->status === BookingGoogleSheetsSyncStatus::SYNCED;
        } catch (Throwable $exception) {
            Log::error('Booking Google Sheets synchronization failed unexpectedly.', [
                'reference' => (string) $booking->reference,
                'exception' => $exception::class,
            ]);

            return false;
        }
    }
}
