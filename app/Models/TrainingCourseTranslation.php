<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingCourseTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_course_id',
        'locale',
        'title',
        'slug',
        'excerpt',
        'content',
        'duration_display',
        'schedule_display',
        'target_audience',
        'curriculum_modules',
        'benefits',
        'faqs',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'curriculum_modules' => 'array',
            'benefits' => 'array',
            'faqs' => 'array',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(TrainingCourse::class, 'training_course_id');
    }
}
