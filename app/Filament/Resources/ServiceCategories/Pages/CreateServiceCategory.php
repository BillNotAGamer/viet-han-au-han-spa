<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServiceCategories\Pages;

use App\Filament\Resources\ServiceCategories\ServiceCategoryResource;
use App\Services\ServiceCatalog\ServiceCategoryWriter;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateServiceCategory extends CreateRecord
{
    protected static string $resource = ServiceCategoryResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(ServiceCategoryWriter::class)->create($data);
    }
}
