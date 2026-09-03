<?php

declare(strict_types=1);

namespace App\Services\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;
use DomainException;
use Illuminate\Support\Facades\DB;

class BookingWorkflow
{
    public function markAsContacted(Booking $booking, ?string $adminNote = null): void
    {
        if ($booking->status !== BookingStatus::NEW) {
            throw new DomainException("Cannot transition from {$booking->status->value} to CONTACTED. Only NEW bookings can be marked as contacted.");
        }

        $this->updateStatus($booking, BookingStatus::CONTACTED, ['contacted_at' => $booking->contacted_at ?? now()], $adminNote);
    }

    public function markAsConfirmed(Booking $booking, ?string $adminNote = null): void
    {
        if (! in_array($booking->status, [BookingStatus::NEW, BookingStatus::CONTACTED], true)) {
            throw new DomainException("Cannot transition from {$booking->status->value} to CONFIRMED.");
        }

        $this->updateStatus($booking, BookingStatus::CONFIRMED, ['confirmed_at' => now()], $adminNote);
    }

    public function markAsCancelled(Booking $booking, ?string $adminNote = null): void
    {
        if (in_array($booking->status, [BookingStatus::COMPLETED, BookingStatus::CANCELLED, BookingStatus::NO_SHOW], true)) {
            throw new DomainException("Cannot cancel a terminal booking in {$booking->status->value} status.");
        }

        $this->updateStatus($booking, BookingStatus::CANCELLED, ['cancelled_at' => now()], $adminNote);
    }

    public function updateAdminNote(Booking $booking, string $note): void
    {
        $booking->update(['admin_note' => $note]);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    protected function updateStatus(Booking $booking, BookingStatus $status, array $extra = [], ?string $adminNote = null): void
    {
        DB::transaction(function () use ($booking, $status, $extra, $adminNote): void {
            $payload = array_merge(['status' => $status], $extra);

            if ($adminNote !== null) {
                $payload['admin_note'] = $adminNote;
            }

            $booking->update($payload);
        });
    }
}
