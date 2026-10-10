<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BookingGoogleSheetsSyncStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class BookingGoogleSheetsSyncRecord extends Model
{
    public const DESTINATION = 'booking_google_sheet';

    protected $table = 'booking_google_sheets_syncs';

    protected $fillable = [
        'booking_id',
        'booking_reference',
        'destination',
        'status',
        'attempts',
        'last_attempted_at',
        'next_attempt_at',
        'synced_at',
        'last_error_code',
        'last_error_summary',
        'append_safe',
        'retryable',
        'requires_manual_intervention',
        'lease_token',
        'lease_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingGoogleSheetsSyncStatus::class,
            'attempts' => 'integer',
            'last_attempted_at' => 'datetime',
            'next_attempt_at' => 'datetime',
            'synced_at' => 'datetime',
            'append_safe' => 'boolean',
            'retryable' => 'boolean',
            'requires_manual_intervention' => 'boolean',
            'lease_expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (self $record): void {
            if ($record->isDirty(['booking_id', 'booking_reference', 'destination'])) {
                throw new LogicException('Booking Google Sheets synchronization identity is immutable.');
            }
        });
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function canRetrySafely(): bool
    {
        return $this->status === BookingGoogleSheetsSyncStatus::PENDING
            || ($this->status === BookingGoogleSheetsSyncStatus::FAILED && $this->append_safe);
    }
}
