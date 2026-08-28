<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Support\Facades\DB;

class MediaReferenceInspector
{
    /**
     * Inspect all 7 approved reference categories.
     *
     * @return array<string, int>
     */
    public function getReferenceBreakdown(Media $media): array
    {
        return [
            'service_hero' => DB::table('services')->where('hero_media_id', $media->id)->count(),
            'training_hero' => DB::table('training_courses')->where('hero_media_id', $media->id)->count(),
            'post_hero' => DB::table('posts')->where('hero_media_id', $media->id)->count(),
            'service_gallery' => DB::table('service_media')->where('media_id', $media->id)->count(),
            'training_gallery' => DB::table('training_course_media')->where('media_id', $media->id)->count(),
            'post_gallery' => DB::table('post_media')->where('media_id', $media->id)->count(),
            'page_gallery' => DB::table('page_media')->where('media_id', $media->id)->count(),
        ];
    }

    public function getTotalReferencesCount(Media $media): int
    {
        return array_sum($this->getReferenceBreakdown($media));
    }

    public function hasReferences(Media $media): bool
    {
        return $this->getTotalReferencesCount($media) > 0;
    }
}
