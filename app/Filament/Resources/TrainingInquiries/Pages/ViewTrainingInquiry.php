<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrainingInquiries\Pages;

use App\Filament\Resources\TrainingInquiries\TrainingInquiryResource;
use Filament\Resources\Pages\ViewRecord;

class ViewTrainingInquiry extends ViewRecord
{
    protected static string $resource = TrainingInquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
