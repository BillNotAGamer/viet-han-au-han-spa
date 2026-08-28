<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Services;

use App\Enums\ContentStatus;
use App\Models\ServiceCategory;
use App\Services\ServiceCatalog\ServiceCategoryWriter;
use App\Services\ServiceCatalog\ServiceWriter;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizedSlugValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_locale_slug_collision_is_rejected(): void
    {
        $writer = app(ServiceWriter::class);
        $category = ServiceCategory::factory()->create();

        $writer->create([
            'service_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Massage Thư Giãn', 'slug' => 'massage-thu-gian'],
        ]);

        $this->expectException(Exception::class);

        // Attempting to create duplicate slug in same locale (vi) must be rejected
        $writer->create([
            'service_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Massage Thư Giãn 2', 'slug' => 'massage-thu-gian'],
        ]);
    }

    public function test_different_locale_identical_slug_is_allowed(): void
    {
        $writer = app(ServiceWriter::class);
        $category = ServiceCategory::factory()->create();

        // Same slug 'spa' in vi and en is valid across UNIQUE(locale, slug)
        $service = $writer->create([
            'service_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Dịch Vụ Spa', 'slug' => 'spa'],
            'en' => ['name' => 'Spa Service', 'slug' => 'spa'],
        ]);

        $this->assertDatabaseHas('service_translations', [
            'service_id' => $service->id,
            'locale' => 'vi',
            'slug' => 'spa',
        ]);

        $this->assertDatabaseHas('service_translations', [
            'service_id' => $service->id,
            'locale' => 'en',
            'slug' => 'spa',
        ]);
    }

    public function test_editing_existing_translation_without_changing_slug_succeeds(): void
    {
        $writer = app(ServiceCategoryWriter::class);

        $category = $writer->create([
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Tên Ban Đầu', 'slug' => 'ten-ban-dau'],
        ]);

        // Update name and description while retaining same slug
        $updated = $writer->update($category, [
            'status' => ContentStatus::PUBLISHED,
            'vi' => [
                'name' => 'Tên Mới Hoàn Toàn',
                'slug' => 'ten-ban-dau', // Same slug must not collide with itself
                'description' => 'Mô tả thêm',
            ],
        ]);

        $this->assertSame('ten-ban-dau', $updated->translationFor('vi')?->slug);
        $this->assertSame('Tên Mới Hoàn Toàn', $updated->translationFor('vi')?->name);
    }

    public function test_slug_auto_generates_when_blank_and_preserves_manual_entry(): void
    {
        $writer = app(ServiceCategoryWriter::class);

        // 1. Blank slug generates from name
        $cat1 = $writer->create([
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Chăm Sóc Toàn Thân', 'slug' => ''],
        ]);
        $this->assertSame('cham-soc-toan-than', $cat1->translationFor('vi')?->slug);

        // 2. Manually entered slug is preserved and not overwritten on name change
        $cat2 = $writer->create([
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Tên Ban Đầu', 'slug' => 'slug-tuy-chinh-rieng'],
        ]);
        $this->assertSame('slug-tuy-chinh-rieng', $cat2->translationFor('vi')?->slug);

        $updatedCat2 = $writer->update($cat2, [
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Tên Sau Khi Sửa', 'slug' => 'slug-tuy-chinh-rieng'],
        ]);
        $this->assertSame('slug-tuy-chinh-rieng', $updatedCat2->translationFor('vi')?->slug);
    }
}
