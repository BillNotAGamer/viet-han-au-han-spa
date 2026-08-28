<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SiteSettingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'type' => SiteSettingType::class,
            'is_public' => 'boolean',
        ];
    }
}
