<?php

declare(strict_types=1);

namespace App\Services\Blog;

use App\Enums\ContentStatus;
use App\Models\PostCategory;
use App\Models\PostCategoryTranslation;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostCategoryWriter
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PostCategory
    {
        return DB::transaction(function () use ($data) {
            $status = $data['status'] instanceof ContentStatus
                ? $data['status']
                : ContentStatus::from((string) ($data['status'] ?? ContentStatus::PUBLISHED->value));

            $category = PostCategory::create([
                'status' => $status,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
            ]);

            // Save Vietnamese Translation (Required)
            $viName = trim((string) ($data['vi']['name'] ?? ''));
            $viSlug = trim((string) ($data['vi']['slug'] ?? ''));
            if ($viSlug === '') {
                $viSlug = Str::slug($viName);
            }

            PostCategoryTranslation::create([
                'post_category_id' => $category->id,
                'locale' => 'vi',
                'name' => $viName,
                'slug' => $viSlug,
                'description' => $data['vi']['description'] ?? null,
                'seo_title' => $data['vi']['seo_title'] ?? null,
                'seo_description' => $data['vi']['seo_description'] ?? null,
            ]);

            // Save English Translation (Optional)
            $enName = trim((string) ($data['en']['name'] ?? ''));
            if ($enName !== '') {
                $enSlug = trim((string) ($data['en']['slug'] ?? ''));
                if ($enSlug === '') {
                    $enSlug = Str::slug($enName);
                }

                PostCategoryTranslation::create([
                    'post_category_id' => $category->id,
                    'locale' => 'en',
                    'name' => $enName,
                    'slug' => $enSlug,
                    'description' => $data['en']['description'] ?? null,
                    'seo_title' => $data['en']['seo_title'] ?? null,
                    'seo_description' => $data['en']['seo_description'] ?? null,
                ]);
            }

            return $category->fresh(['translations']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PostCategory $category, array $data): PostCategory
    {
        return DB::transaction(function () use ($category, $data) {
            $status = $data['status'] instanceof ContentStatus
                ? $data['status']
                : ContentStatus::from((string) ($data['status'] ?? $category->status->value));

            $category->update([
                'status' => $status,
                'sort_order' => (int) ($data['sort_order'] ?? $category->sort_order),
            ]);

            // Update Vietnamese Translation (In-place ID preservation)
            $viName = trim((string) ($data['vi']['name'] ?? ''));
            $viSlug = trim((string) ($data['vi']['slug'] ?? ''));
            if ($viSlug === '') {
                $viSlug = Str::slug($viName);
            }

            PostCategoryTranslation::updateOrCreate(
                [
                    'post_category_id' => $category->id,
                    'locale' => 'vi',
                ],
                [
                    'name' => $viName,
                    'slug' => $viSlug,
                    'description' => $data['vi']['description'] ?? null,
                    'seo_title' => $data['vi']['seo_title'] ?? null,
                    'seo_description' => $data['vi']['seo_description'] ?? null,
                ]
            );

            // Update English Translation (In-place ID preservation)
            $enName = trim((string) ($data['en']['name'] ?? ''));
            if ($enName !== '') {
                $enSlug = trim((string) ($data['en']['slug'] ?? ''));
                if ($enSlug === '') {
                    $enSlug = Str::slug($enName);
                }

                PostCategoryTranslation::updateOrCreate(
                    [
                        'post_category_id' => $category->id,
                        'locale' => 'en',
                    ],
                    [
                        'name' => $enName,
                        'slug' => $enSlug,
                        'description' => $data['en']['description'] ?? null,
                        'seo_title' => $data['en']['seo_title'] ?? null,
                        'seo_description' => $data['en']['seo_description'] ?? null,
                    ]
                );
            }

            return $category->fresh(['translations']);
        });
    }

    public function delete(PostCategory $category): void
    {
        if ($category->posts()->exists()) {
            throw new DomainException('Cannot delete category because it contains associated posts.');
        }

        DB::transaction(function () use ($category) {
            $category->translations()->delete();
            $category->delete();
        });
    }
}
