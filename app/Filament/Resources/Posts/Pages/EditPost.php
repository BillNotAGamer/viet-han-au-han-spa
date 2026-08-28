<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Services\Blog\PostWriter;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Post $post */
        $post = $this->getRecord();
        $post->loadMissing(['translations', 'media']);

        $vi = $post->translationFor('vi');
        $en = $post->translationFor('en');

        if ($vi) {
            $data['vi'] = [
                'title' => $vi->title,
                'slug' => $vi->slug,
                'excerpt' => $vi->excerpt,
                'content' => $vi->content,
                'seo_title' => $vi->seo_title,
                'seo_description' => $vi->seo_description,
            ];
        }

        if ($en) {
            $data['en'] = [
                'title' => $en->title,
                'slug' => $en->slug,
                'excerpt' => $en->excerpt,
                'content' => $en->content,
                'seo_title' => $en->seo_title,
                'seo_description' => $en->seo_description,
            ];
        }

        $data['post_media_ids'] = $post->media()->pluck('media.id')->toArray();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Post $record */
        return app(PostWriter::class)->update($record, $data);
    }
}
