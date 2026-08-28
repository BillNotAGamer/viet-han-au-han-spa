<?php

declare(strict_types=1);

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Models\SiteSetting;
use App\Services\Settings\SiteSettingWriter;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSiteSetting extends EditRecord
{
    protected static string $resource = SiteSettingResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var SiteSetting $record */
        $record = $this->getRecord();
        $data['key'] = $record->key;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var SiteSetting $record */
        return app(SiteSettingWriter::class)->update($record, $data);
    }
}
