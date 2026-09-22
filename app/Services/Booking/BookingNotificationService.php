<?php

declare(strict_types=1);

namespace App\Services\Booking;

use App\Mail\BookingRequestSubmitted;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class BookingNotificationService
{
    public function notifySubmitted(Booking $booking): bool
    {
        if (! (bool) config('booking.notifications.email.enabled', false)) {
            return false;
        }

        $recipient = config('booking.notifications.email.recipient');

        if (empty($recipient) || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Booking notification skipped: invalid or missing recipient.', [
                'reference' => $booking->reference,
            ]);

            return false;
        }

        try {
            Mail::to((string) $recipient)->send(new BookingRequestSubmitted($booking));

            return true;
        } catch (Throwable $e) {
            // Notification failure isolation: log operational error without logging customer PII (phone, email, notes)
            Log::error('Booking notification email failed to send.', [
                'reference' => $booking->reference,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
