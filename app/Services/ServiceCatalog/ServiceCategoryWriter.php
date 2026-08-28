<?php

declare(strict_types=1);

namespace App\Services\ServiceCatalog;

use App\Enums\ContentStatus;
use App\Models\ServiceCategory;
use App\Models\ServiceCategoryTranslation;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceCategoryWriter
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ServiceCategory
    {
        return DB::transaction(function () use ($data) {
            $status = $data['status'] instanceof ContentStatus
                ? $data['status']
                : ContentStatus::from((string) ($data['status'] ?? ContentStatus::DRAFT->value));

            $category = ServiceCategory::create([
                'status' => $status,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
            ]);

            // Save Vietnamese Translation (Required)
            $viName = trim((string) ($data['vi']['name'] ?? ''));
            $viSlug = trim((string) ($data['vi']['slug'] ?? ''));
            if ($viSlug === '') {
                $viSlug = Str::slug($viName);
            }

            ServiceCategoryTranslation::create([
                'service_category_id' => $category->id,
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

                ServiceCategoryTranslation::create([
                    'service_category_id' => $category->id,
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
    public function update(ServiceCategory $category, array $data): ServiceCategory
    {
        return DB::transaction(function () use ($category, $data) {
            $status = $data['status'] instanceof ContentStatus
                ? $data['status']
                : ContentStatus::from((string) ($data['status'] ?? $category->status->value));

            $category->update([
                'status' => $status,
                'sort_order' => (int) ($data['sort_order'] ?? $category->sort_order),
            ]);

            // Update Vietnamese Translation
            $viName = trim((string) ($data['vi']['name'] ?? ''));
            $viSlug = trim((string) ($data['vi']['slug'] ?? ''));
            if ($viSlug === '') {
                $viSlug = Str::slug($viName);
            }

            ServiceCategoryTranslation::updateOrCreate(
                [
                    'service_category_id' => $category->id,
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

            // Update English Translation
            $enName = trim((string) ($data['en']['name'] ?? ''));
            if ($enName !== '') {
                $enSlug = trim((string) ($data['en']['slug'] ?? ''));
                if ($enSlug === '') {
                    $enSlug = Str::slug($enName);
                }

                ServiceCategoryTranslation::updateOrCreate(
                    [
                        'service_category_id' => $category->id,
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

    public function delete(ServiceCategory $category): void
    {
        if ($category->services()->count() > 0) {
            throw new DomainException('Cannot delete category because it contains associated services.');
        }

        DB::transaction(function () use ($category) {
            $category->translations()->delete();
            $category->delete();
        });
    }
}
