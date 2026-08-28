<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Models\Page;
use App\Services\Pages\PageWriter;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Page $page */
        $page = $this->getRecord();
        $page->loadMissing(['translations', 'media']);

        $data['key'] = $page->key;

        $vi = $page->translationFor('vi');
        $en = $page->translationFor('en');

        if ($vi) {
            $data['vi'] = [
                'title' => $vi->title,
                'slug' => $vi->slug,
                'content' => $vi->content,
                'seo_title' => $vi->seo_title,
                'seo_description' => $vi->seo_description,
            ];
        }

        if ($en) {
            $data['en'] = [
                'title' => $en->title,
                'slug' => $en->slug,
                'content' => $en->content,
                'seo_title' => $en->seo_title,
                'seo_description' => $en->seo_description,
            ];
        }

        $data['page_media_ids'] = $page->media()->pluck('media.id')->toArray();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Page $record */
        return app(PageWriter::class)->update($record, $data);
    }
}
