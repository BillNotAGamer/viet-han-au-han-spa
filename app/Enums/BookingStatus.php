<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BookingStatus: string implements HasColor, HasLabel
{
    case NEW = 'NEW';
    case CONTACTED = 'CONTACTED';
    case CONFIRMED = 'CONFIRMED';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';
    case NO_SHOW = 'NO_SHOW';

    public function getLabel(): string
    {
        return match ($this) {
            self::NEW => 'Mới (New)',
            self::CONTACTED => 'Đã liên hệ (Contacted)',
            self::CONFIRMED => 'Đã xác nhận (Confirmed)',
            self::COMPLETED => 'Hoàn tất (Completed)',
            self::CANCELLED => 'Đã hủy (Cancelled)',
            self::NO_SHOW => 'Không đến (No-show)',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::NEW => 'info',
            self::CONTACTED => 'warning',
            self::CONFIRMED => 'success',
            self::COMPLETED => 'success',
            self::CANCELLED => 'gray',
            self::NO_SHOW => 'danger',
        };
    }
}
