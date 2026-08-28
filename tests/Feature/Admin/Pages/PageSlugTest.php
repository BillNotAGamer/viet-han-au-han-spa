<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Pages;

use App\Enums\ContentStatus;
use App\Services\Pages\PageWriter;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_translation_with_null_slug_persists_successfully(): void
    {
        $writer = app(PageWriter::class);

        // Homepage legitimately has null slug
        $homePage = $writer->create([
            'key' => 'home',
            'status' => ContentStatus::PUBLISHED,
            'vi' => [
                'title' => 'Trang Chủ',
                'slug' => '', // Blank input
            ],
            'en' => [
                'title' => 'Homepage',
                'slug' => null, // Explicit null
            ],
        ]);

        $this->assertNull($homePage->translationFor('vi')?->slug);
        $this->assertNull($homePage->translationFor('en')?->slug);

        $this->assertDatabaseHas('page_translations', [
            'page_id' => $homePage->id,
            'locale' => 'vi',
            'slug' => null,
        ]);
    }

    public function test_multiple_pages_in_same_locale_with_null_slug_are_permitted(): void
    {
        $writer = app(PageWriter::class);

        $page1 = $writer->create([
            'key' => 'page-one',
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Trang Không Slug 1', 'slug' => ''],
        ]);

        $page2 = $writer->create([
            'key' => 'page-two',
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Trang Không Slug 2', 'slug' => null],
        ]);

        $this->assertNull($page1->translationFor('vi')?->slug);
        $this->assertNull($page2->translationFor('vi')?->slug);
        $this->assertNotSame($page1->id, $page2->id);
    }

    public function test_blank_page_slug_is_not_converted_to_title_derived_slug(): void
    {
        $writer = app(PageWriter::class);

        $page = $writer->create([
            'key' => 'brand-story',
            'status' => ContentStatus::PUBLISHED,
            'vi' => [
                'title' => 'Câu Chuyện Thương Hiệu',
                'slug' => '   ', // Whitespace
            ],
        ]);

        $this->assertNull($page->translationFor('vi')?->slug);
    }

    public function test_same_locale_non_null_slug_collision_is_rejected(): void
    {
        $writer = app(PageWriter::class);

        $writer->create([
            'key' => 'page-a',
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Trang A', 'slug' => 'slug-trung'],
        ]);

        try {
            $writer->create([
                'key' => 'page-b',
                'status' => ContentStatus::DRAFT,
                'vi' => ['title' => 'Trang B', 'slug' => 'slug-trung'],
            ]);
            $this->fail('Expected exception for duplicate non-null slug in same locale');
        } catch (Exception $e) {
            $this->assertNotEmpty($e->getMessage());
        }
    }

    public function test_cross_locale_same_non_null_slug_is_allowed(): void
    {
        $writer = app(PageWriter::class);

        $page = $writer->create([
            'key' => 'about-cross',
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['title' => 'Giới Thiệu', 'slug' => 'about'],
            'en' => ['title' => 'About', 'slug' => 'about'],
        ]);

        $this->assertSame('about', $page->translationFor('vi')?->slug);
        $this->assertSame('about', $page->translationFor('en')?->slug);
    }

    public function test_self_update_with_unchanged_slug_succeeds(): void
    {
        $writer = app(PageWriter::class);

        $page = $writer->create([
            'key' => 'self-update-slug',
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Trang Test', 'slug' => 'slug-rieng'],
        ]);

        $updated = $writer->update($page, [
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['title' => 'Trang Test Đã Đổi Tên', 'slug' => 'slug-rieng'],
        ]);

        $this->assertSame('slug-rieng', $updated->translationFor('vi')?->slug);
    }
}
