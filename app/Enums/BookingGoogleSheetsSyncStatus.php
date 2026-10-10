<?php

declare(strict_types=1);

namespace App\Enums;

enum BookingGoogleSheetsSyncStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SYNCED = 'synced';
    case FAILED = 'failed';
    case UNCERTAIN = 'uncertain';
}
