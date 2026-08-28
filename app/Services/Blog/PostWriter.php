<?php

declare(strict_types=1);

namespace App\Services\Blog;

use App\Enums\ContentStatus;
use App\Models\Post;
use App\Models\PostTranslation;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostWriter
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Post
    {
        return DB::transaction(function () use ($data) {
            $status = $data['status'] instanceof ContentStatus
                ? $data['status']
                : ContentStatus::from((string) ($data['status'] ?? ContentStatus::DRAFT->value));

            $publishedAt = ! empty($data['published_at']) ? Carbon::parse($data['published_at']) : null;
            if ($status === ContentStatus::PUBLISHED && $publishedAt === null) {
                $publishedAt = now();
            }

            $authorId = isset($data['author_id']) && $data['author_id'] !== ''
                ? (int) $data['author_id']
                : auth()->id();

            $post = Post::create([
                'post_category_id' => (int) $data['post_category_id'],
                'author_id' => $authorId !== null ? (int) $authorId : null,
                'hero_media_id' => ! empty($data['hero_media_id']) ? (int) $data['hero_media_id'] : null,
                'status' => $status,
                'is_featured' => (bool) ($data['is_featured'] ?? false),
                'published_at' => $publishedAt,
            ]);

            // Save Vietnamese Translation (Required)
            $viTitle = trim((string) ($data['vi']['title'] ?? ''));
            $viSlug = trim((string) ($data['vi']['slug'] ?? ''));
            if ($viSlug === '') {
                $viSlug = Str::slug($viTitle);
            }

            PostTranslation::create([
                'post_id' => $post->id,
                'locale' => 'vi',
                'title' => $viTitle,
                'slug' => $viSlug,
                'excerpt' => $data['vi']['excerpt'] ?? null,
                'content' => $data['vi']['content'] ?? null,
                'seo_title' => $data['vi']['seo_title'] ?? null,
                'seo_description' => $data['vi']['seo_description'] ?? null,
            ]);

            // Save English Translation (Optional)
            $enTitle = trim((string) ($data['en']['title'] ?? ''));
            if ($enTitle !== '') {
                $enSlug = trim((string) ($data['en']['slug'] ?? ''));
                if ($enSlug === '') {
                    $enSlug = Str::slug($enTitle);
                }

                PostTranslation::create([
                    'post_id' => $post->id,
                    'locale' => 'en',
                    'title' => $enTitle,
                    'slug' => $enSlug,
                    'excerpt' => $data['en']['excerpt'] ?? null,
                    'content' => $data['en']['content'] ?? null,
                    'seo_title' => $data['en']['seo_title'] ?? null,
                    'seo_description' => $data['en']['seo_description'] ?? null,
                ]);
            }

            // Synchronize Post Media
            $this->syncPostMedia($post, $data['post_media_ids'] ?? []);

            return $post->fresh(['translations', 'media', 'heroMedia', 'author', 'category']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data) {
            $status = $data['status'] instanceof ContentStatus
                ? $data['status']
                : ContentStatus::from((string) ($data['status'] ?? $post->status->value));

            $publishedAt = $post->published_at;
            if (isset($data['published_at'])) {
                $publishedAt = ! empty($data['published_at']) ? Carbon::parse($data['published_at']) : null;
            } elseif ($status === ContentStatus::PUBLISHED && $publishedAt === null) {
                $publishedAt = now();
            }

            $authorId = array_key_exists('author_id', $data)
                ? (! empty($data['author_id']) ? (int) $data['author_id'] : null)
                : $post->author_id;

            $post->update([
                'post_category_id' => (int) ($data['post_category_id'] ?? $post->post_category_id),
                'author_id' => $authorId,
                'hero_media_id' => ! empty($data['hero_media_id']) ? (int) $data['hero_media_id'] : null,
                'status' => $status,
                'is_featured' => (bool) ($data['is_featured'] ?? $post->is_featured),
                'published_at' => $publishedAt,
            ]);

            // Update Vietnamese Translation in place
            $viTitle = trim((string) ($data['vi']['title'] ?? ''));
            $viSlug = trim((string) ($data['vi']['slug'] ?? ''));
            if ($viSlug === '') {
                $viSlug = Str::slug($viTitle);
            }

            PostTranslation::updateOrCreate(
                [
                    'post_id' => $post->id,
                    'locale' => 'vi',
                ],
                [
                    'title' => $viTitle,
                    'slug' => $viSlug,
                    'excerpt' => $data['vi']['excerpt'] ?? null,
                    'content' => $data['vi']['content'] ?? null,
                    'seo_title' => $data['vi']['seo_title'] ?? null,
                    'seo_description' => $data['vi']['seo_description'] ?? null,
                ]
            );

            // Update English Translation in place
            $enTitle = trim((string) ($data['en']['title'] ?? ''));
            if ($enTitle !== '') {
                $enSlug = trim((string) ($data['en']['slug'] ?? ''));
                if ($enSlug === '') {
                    $enSlug = Str::slug($enTitle);
                }

                PostTranslation::updateOrCreate(
                    [
                        'post_id' => $post->id,
                        'locale' => 'en',
                    ],
                    [
                        'title' => $enTitle,
                        'slug' => $enSlug,
                        'excerpt' => $data['en']['excerpt'] ?? null,
                        'content' => $data['en']['content'] ?? null,
                        'seo_title' => $data['en']['seo_title'] ?? null,
                        'seo_description' => $data['en']['seo_description'] ?? null,
                    ]
                );
            }

            // Synchronize Post Media
            $this->syncPostMedia($post, $data['post_media_ids'] ?? []);

            return $post->fresh(['translations', 'media', 'heroMedia', 'author', 'category']);
        });
    }

    /**
     * @param  array<int, int|string>  $mediaIds
     */
    protected function syncPostMedia(Post $post, array $mediaIds): void
    {
        $syncData = [];
        $order = 0;
        foreach (array_unique($mediaIds) as $mediaId) {
            if (! empty($mediaId)) {
                $syncData[(int) $mediaId] = [
                    'sort_order' => $order++,
                    'created_at' => now(),
                ];
            }
        }

        $post->media()->sync($syncData);
    }

    public function archive(Post $post): void
    {
        $post->update(['status' => ContentStatus::ARCHIVED]);
    }

    public function restore(Post $post): void
    {
        $post->update([
            'status' => ContentStatus::DRAFT,
            'published_at' => null, // Clear on restore to prevent immediate republishing
        ]);
    }

    public function delete(Post $post): void
    {
        if ($post->status !== ContentStatus::DRAFT) {
            throw new DomainException('Only draft posts can be deleted.');
        }

        DB::transaction(function () use ($post) {
            $post->media()->detach();
            $post->translations()->delete();
            $post->delete();
        });
    }
}
