<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference',
        'service_id',
        'service_price_id',
        'service_name_snapshot',
        'service_price_label_snapshot',
        'duration_minutes_snapshot',
        'price_amount_snapshot',
        'customer_name',
        'phone',
        'phone_normalized',
        'email',
        'preferred_date',
        'preferred_time',
        'guest_count',
        'customer_note',
        'admin_note',
        'status',
        'locale',
        'contacted_at',
        'confirmed_at',
        'completed_at',
        'cancelled_at',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'gclid',
        'gbraid',
        'wbraid',
        'fbclid',
        'fbp',
        'fbc',
        'landing_page',
        'referrer',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'preferred_date' => 'date',
            'guest_count' => 'integer',
            'duration_minutes_snapshot' => 'integer',
            'price_amount_snapshot' => 'integer',
            'contacted_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function servicePrice(): BelongsTo
    {
        return $this->belongsTo(ServicePrice::class, 'service_price_id');
    }
}
