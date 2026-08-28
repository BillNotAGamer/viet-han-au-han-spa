<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TrainingInquiryStatus: string implements HasColor, HasLabel
{
    case NEW = 'NEW';
    case CONTACTED = 'CONTACTED';
    case ENROLLED = 'ENROLLED';
    case CLOSED = 'CLOSED';

    public function getLabel(): string
    {
        return match ($this) {
            self::NEW => 'Mới (New)',
            self::CONTACTED => 'Đã liên hệ (Contacted)',
            self::ENROLLED => 'Đã đăng ký (Enrolled)',
            self::CLOSED => 'Đã đóng (Closed)',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::NEW => 'info',
            self::CONTACTED => 'warning',
            self::ENROLLED => 'success',
            self::CLOSED => 'gray',
        };
    }
}
