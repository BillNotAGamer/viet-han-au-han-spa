<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Throwable;

class Media extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'media';

    protected $fillable = [
        'disk',
        'path',
        'file_name',
        'mime_type',
        'extension',
        'size_bytes',
        'width',
        'height',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function getUrl(): ?string
    {
        try {
            return Storage::disk($this->disk)->url($this->path);
        } catch (Throwable) {
            return null;
        }
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(MediaTranslation::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_media')
            ->withPivot('sort_order', 'created_at');
    }

    public function trainingCourses(): BelongsToMany
    {
        return $this->belongsToMany(TrainingCourse::class, 'training_course_media')
            ->withPivot('sort_order', 'created_at');
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_media')
            ->withPivot('sort_order', 'created_at');
    }

    public function pages(): BelongsToMany
    {
        return $this->belongsToMany(Page::class, 'page_media')
            ->withPivot('sort_order', 'created_at');
    }
}
