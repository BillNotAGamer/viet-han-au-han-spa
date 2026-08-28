<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentStatus;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingCourse extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'hero_media_id',
        'tuition_fee',
        'status',
        'is_featured',
        'sort_order',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'tuition_fee' => 'integer',
            'status' => ContentStatus::class,
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function heroMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'hero_media_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TrainingCourseTranslation::class);
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'training_course_media')
            ->withPivot('sort_order', 'created_at');
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(TrainingInquiry::class);
    }
}
