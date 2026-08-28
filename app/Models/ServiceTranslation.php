<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'locale',
        'name',
        'slug',
        'excerpt',
        'content',
        'benefits',
        'process_steps',
        'faqs',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'benefits' => 'array',
            'process_steps' => 'array',
            'faqs' => 'array',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
