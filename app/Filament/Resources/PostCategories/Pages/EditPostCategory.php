<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostCategories\Pages;

use App\Filament\Resources\PostCategories\PostCategoryResource;
use App\Models\PostCategory;
use App\Services\Blog\PostCategoryWriter;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPostCategory extends EditRecord
{
    protected static string $resource = PostCategoryResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var PostCategory $category */
        $category = $this->getRecord();
        $category->loadMissing('translations');

        $vi = $category->translationFor('vi');
        $en = $category->translationFor('en');

        if ($vi) {
            $data['vi'] = [
                'name' => $vi->name,
                'slug' => $vi->slug,
                'description' => $vi->description,
                'seo_title' => $vi->seo_title,
                'seo_description' => $vi->seo_description,
            ];
        }

        if ($en) {
            $data['en'] = [
                'name' => $en->name,
                'slug' => $en->slug,
                'description' => $en->description,
                'seo_title' => $en->seo_title,
                'seo_description' => $en->seo_description,
            ];
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var PostCategory $record */
        return app(PostCategoryWriter::class)->update($record, $data);
    }
}
