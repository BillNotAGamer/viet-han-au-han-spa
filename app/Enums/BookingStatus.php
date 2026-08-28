<?php

declare(strict_types=1);

namespace App\Enums;

enum BookingStatus: string
{
    case NEW = 'NEW';
    case CONTACTED = 'CONTACTED';
    case CONFIRMED = 'CONFIRMED';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';
    case NO_SHOW = 'NO_SHOW';
}
