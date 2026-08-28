<?php

declare(strict_types=1);

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Services\Settings\SiteSettingWriter;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSiteSetting extends CreateRecord
{
    protected static string $resource = SiteSettingResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(SiteSettingWriter::class)->create($data);
    }
}
