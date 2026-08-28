<?php

declare(strict_types=1);

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Services\Media\MediaUploadService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $service = app(MediaUploadService::class);
        $tempPath = (string) ($data['file_upload'] ?? '');

        return $service->createFromFilamentTemp(
            $tempPath,
            [
                'vi' => $data['vi'] ?? [],
                'en' => $data['en'] ?? [],
            ],
            auth()->id()
        );
    }
}
