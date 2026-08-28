<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrainingInquiries\Pages;

use App\Filament\Resources\TrainingInquiries\TrainingInquiryResource;
use Filament\Resources\Pages\ListRecords;

class ListTrainingInquiries extends ListRecords
{
    protected static string $resource = TrainingInquiryResource::class;

    protected function getHeaderActions(): array
    {
        // For lead safety, zero manual creation action is exposed
        return [];
    }
}
