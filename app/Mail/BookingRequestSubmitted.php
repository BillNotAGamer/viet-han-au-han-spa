<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingRequestSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('[Việt Hàn Âu Hàn Spa] Yêu cầu đặt lịch %s', $this->booking->reference),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking.submitted',
        );
    }
}
