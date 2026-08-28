<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostCategories\Pages;

use App\Filament\Resources\PostCategories\PostCategoryResource;
use App\Services\Blog\PostCategoryWriter;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePostCategory extends CreateRecord
{
    protected static string $resource = PostCategoryResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(PostCategoryWriter::class)->create($data);
    }
}
