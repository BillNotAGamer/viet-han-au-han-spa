<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TrainingInquiryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingInquiry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference',
        'training_course_id',
        'customer_name',
        'phone',
        'phone_normalized',
        'email',
        'message',
        'status',
        'locale',
        'admin_note',
        'contacted_at',
        'enrolled_at',
        'closed_at',
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
            'status' => TrainingInquiryStatus::class,
            'contacted_at' => 'datetime',
            'enrolled_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(TrainingCourse::class, 'training_course_id');
    }
}
