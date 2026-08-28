<?php

declare(strict_types=1);

namespace App\Services\Pages;

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\PageTranslation;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PageWriter
{
    /**
     * Normalize and validate machine key format.
     */
    public function normalizeKey(string $key): string
    {
        $normalized = strtolower(trim($key));

        if (! preg_match('/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/', $normalized)) {
            throw new InvalidArgumentException("Invalid page key '{$key}'. Must be lowercase alphanumeric with hyphens or underscores.");
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Page
    {
        return DB::transaction(function () use ($data) {
            $key = $this->normalizeKey((string) ($data['key'] ?? ''));

            if (Page::where('key', $key)->exists()) {
                throw new InvalidArgumentException("Page with key '{$key}' already exists.");
            }

            $status = $data['status'] instanceof ContentStatus
                ? $data['status']
                : ContentStatus::from((string) ($data['status'] ?? ContentStatus::PUBLISHED->value));

            $page = Page::create([
                'key' => $key,
                'status' => $status,
            ]);

            // Save Vietnamese translation (Required)
            $viTitle = trim((string) ($data['vi']['title'] ?? ''));
            if ($viTitle === '') {
                throw new InvalidArgumentException('Vietnamese page title is required.');
            }

            $rawViSlug = isset($data['vi']['slug']) ? trim((string) $data['vi']['slug']) : '';
            $viSlug = $rawViSlug !== '' ? Str::slug($rawViSlug) : null;

            PageTranslation::create([
                'page_id' => $page->id,
                'locale' => 'vi',
                'title' => $viTitle,
                'slug' => $viSlug,
                'content' => $data['vi']['content'] ?? null,
                'seo_title' => $data['vi']['seo_title'] ?? null,
                'seo_description' => $data['vi']['seo_description'] ?? null,
            ]);

            // Save English translation (Optional)
            $enTitle = trim((string) ($data['en']['title'] ?? ''));
            if ($enTitle !== '') {
                $rawEnSlug = isset($data['en']['slug']) ? trim((string) $data['en']['slug']) : '';
                $enSlug = $rawEnSlug !== '' ? Str::slug($rawEnSlug) : null;

                PageTranslation::create([
                    'page_id' => $page->id,
                    'locale' => 'en',
                    'title' => $enTitle,
                    'slug' => $enSlug,
                    'content' => $data['en']['content'] ?? null,
                    'seo_title' => $data['en']['seo_title'] ?? null,
                    'seo_description' => $data['en']['seo_description'] ?? null,
                ]);
            }

            // Sync page media associations
            $this->syncPageMedia($page, $data['page_media_ids'] ?? []);

            return $page->fresh(['translations', 'media']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Page $page, array $data): Page
    {
        return DB::transaction(function () use ($page, $data) {
            $status = $data['status'] instanceof ContentStatus
                ? $data['status']
                : ContentStatus::from((string) ($data['status'] ?? $page->status->value));

            // Key is strictly immutable during normal edits
            $page->update([
                'status' => $status,
            ]);

            // Update Vietnamese translation in-place (preserve ID)
            $viTitle = trim((string) ($data['vi']['title'] ?? ''));
            if ($viTitle === '') {
                throw new InvalidArgumentException('Vietnamese page title is required.');
            }

            $rawViSlug = isset($data['vi']['slug']) ? trim((string) $data['vi']['slug']) : '';
            $viSlug = $rawViSlug !== '' ? Str::slug($rawViSlug) : null;

            PageTranslation::updateOrCreate(
                [
                    'page_id' => $page->id,
                    'locale' => 'vi',
                ],
                [
                    'title' => $viTitle,
                    'slug' => $viSlug,
                    'content' => $data['vi']['content'] ?? null,
                    'seo_title' => $data['vi']['seo_title'] ?? null,
                    'seo_description' => $data['vi']['seo_description'] ?? null,
                ]
            );

            // Update English translation in-place (preserve ID)
            $enTitle = trim((string) ($data['en']['title'] ?? ''));
            if ($enTitle !== '') {
                $rawEnSlug = isset($data['en']['slug']) ? trim((string) $data['en']['slug']) : '';
                $enSlug = $rawEnSlug !== '' ? Str::slug($rawEnSlug) : null;

                PageTranslation::updateOrCreate(
                    [
                        'page_id' => $page->id,
                        'locale' => 'en',
                    ],
                    [
                        'title' => $enTitle,
                        'slug' => $enSlug,
                        'content' => $data['en']['content'] ?? null,
                        'seo_title' => $data['en']['seo_title'] ?? null,
                        'seo_description' => $data['en']['seo_description'] ?? null,
                    ]
                );
            }

            // Sync page media associations
            $this->syncPageMedia($page, $data['page_media_ids'] ?? []);

            return $page->fresh(['translations', 'media']);
        });
    }

    /**
     * @param  array<int, int|string>  $mediaIds
     */
    protected function syncPageMedia(Page $page, array $mediaIds): void
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

        $page->media()->sync($syncData);
    }

    public function archive(Page $page): void
    {
        $page->update(['status' => ContentStatus::ARCHIVED]);
    }

    public function restore(Page $page): void
    {
        $page->update(['status' => ContentStatus::DRAFT]);
    }

    public function delete(Page $page): void
    {
        if ($page->status !== ContentStatus::DRAFT) {
            throw new DomainException('Only draft pages can be deleted.');
        }

        DB::transaction(function () use ($page) {
            $page->media()->detach();
            $page->translations()->delete();
            $page->delete();
        });
    }
}
