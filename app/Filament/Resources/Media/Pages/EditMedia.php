<?php

declare(strict_types=1);

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Models\Media;
use App\Services\Media\MediaUploadService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditMedia extends EditRecord
{
    protected static string $resource = MediaResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Media $record */
        $record = $this->getRecord();
        $record->loadMissing('translations');

        $vi = $record->translationFor('vi');
        $en = $record->translationFor('en');

        if ($vi) {
            $data['vi'] = [
                'alt_text' => $vi->alt_text,
                'caption' => $vi->caption,
            ];
        }

        if ($en) {
            $data['en'] = [
                'alt_text' => $en->alt_text,
                'caption' => $en->caption,
            ];
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Media $record */
        return app(MediaUploadService::class)->updateTranslations($record, [
            'vi' => $data['vi'] ?? [],
            'en' => $data['en'] ?? [],
        ]);
    }
}
