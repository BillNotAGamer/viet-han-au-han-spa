<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePriceTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_price_id',
        'locale',
        'label',
    ];

    public function servicePrice(): BelongsTo
    {
        return $this->belongsTo(ServicePrice::class, 'service_price_id');
    }
}
