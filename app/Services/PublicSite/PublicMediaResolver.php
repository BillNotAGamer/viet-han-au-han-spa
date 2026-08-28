<?php

declare(strict_types=1);

namespace App\Services\PublicSite;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class PublicMediaResolver
{
    /**
     * Resolve a Media model to public presentation data for a specific locale.
     *
     * @return array{url: string|null, alt: string, caption: string|null, title: string|null}|null
     */
    public function resolve(?Media $media, string $locale): ?array
    {
        if (! $media || empty($media->path)) {
            return null;
        }

        $diskName = $media->disk ?: config('filesystems.media_disk', 'public');
        $disk = Storage::disk($diskName);

        // Graceful handling of missing physical files
        try {
            if (! $disk->exists($media->path)) {
                return null;
            }
            $url = $disk->url($media->path);
        } catch (\Throwable) {
            return null;
        }

        // Exact-locale translation resolution
        $translation = $media->translations->firstWhere('locale', $locale);

        $alt = $translation?->alt_text ? trim($translation->alt_text) : '';
        $caption = $translation?->caption ? trim($translation->caption) : null;
        $title = $translation?->title ? trim($translation->title) : null;

        return [
            'url' => $url,
            'alt' => $alt,
            'caption' => $caption,
            'title' => $title,
        ];
    }
}
