<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SiteSettingType: string implements HasColor, HasLabel
{
    case STRING = 'string';
    case TEXT = 'text';
    case BOOLEAN = 'boolean';
    case JSON = 'json';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::STRING => 'Chuỗi ngắn (String)',
            self::TEXT => 'Văn bản dài (Text)',
            self::BOOLEAN => 'Đúng / Sai (Boolean)',
            self::JSON => 'Cấu trúc JSON (JSON)',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::STRING => 'primary',
            self::TEXT => 'info',
            self::BOOLEAN => 'success',
            self::JSON => 'warning',
        };
    }
}
