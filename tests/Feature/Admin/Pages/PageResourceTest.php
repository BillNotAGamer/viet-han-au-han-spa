<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Pages;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\Page;
use App\Models\User;
use App\Services\Pages\PageWriter;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_non_admin_cannot_access_page_resource(): void
    {
        $this->get('/admin/pages')->assertStatus(302);

        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->get('/admin/pages')->assertStatus(403);
    }

    public function test_admin_can_access_page_list_create_and_edit(): void
    {
        $admin = User::factory()->admin()->create();
        $page = Page::factory()->create(['key' => 'home']);

        $this->actingAs($admin)->get('/admin/pages')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/pages/create')->assertStatus(200);
        $this->actingAs($admin)->get("/admin/pages/{$page->id}/edit")->assertStatus(200);
    }

    public function test_page_writer_creates_page_with_translations_and_media(): void
    {
        $writer = app(PageWriter::class);
        $media = Media::factory()->create(['disk' => 'public']);

        $page = $writer->create([
            'key' => 'about',
            'status' => ContentStatus::PUBLISHED,
            'vi' => [
                'title' => 'Về Chúng Tôi',
                'slug' => 'gioi-thieu',
                'content' => '<p>Giới thiệu về viện thẩm mỹ Việt Hàn...</p>',
                'seo_title' => 'Giới Thiệu - Việt Hàn Âu Hàn Spa',
                'seo_description' => 'Tìm hiểu về sứ mệnh và đội ngũ chuyên gia',
            ],
            'en' => [
                'title' => 'About Us',
                'slug' => 'about',
                'content' => '<p>About Viet Han Au Han Spa...</p>',
            ],
            'page_media_ids' => [$media->id],
        ]);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame('about', $page->key);
        $this->assertSame(ContentStatus::PUBLISHED, $page->status);

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'key' => 'about',
            'status' => 'PUBLISHED',
        ]);

        $this->assertDatabaseHas('page_translations', [
            'page_id' => $page->id,
            'locale' => 'vi',
            'title' => 'Về Chúng Tôi',
            'slug' => 'gioi-thieu',
        ]);

        $this->assertDatabaseHas('page_translations', [
            'page_id' => $page->id,
            'locale' => 'en',
            'title' => 'About Us',
            'slug' => 'about',
        ]);

        $this->assertCount(1, $page->media);
    }

    public function test_missing_english_translation_does_not_fabricate_en_row(): void
    {
        $writer = app(PageWriter::class);

        $page = $writer->create([
            'key' => 'contact',
            'status' => ContentStatus::PUBLISHED,
            'vi' => [
                'title' => 'Liên Hệ',
                'slug' => 'lien-he',
            ],
        ]);

        $this->assertNotNull($page->translationFor('vi'));
        $this->assertNull($page->translationFor('en'));
        $this->assertDatabaseMissing('page_translations', [
            'page_id' => $page->id,
            'locale' => 'en',
        ]);
    }

    public function test_page_update_preserves_translation_id(): void
    {
        $writer = app(PageWriter::class);

        $page = $writer->create([
            'key' => 'terms',
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Điều Khoản Ban Đầu', 'slug' => 'dieu-khoan'],
            'en' => ['title' => 'Initial Terms', 'slug' => 'terms'],
        ]);

        $originalViId = $page->translationFor('vi')?->id;
        $originalEnId = $page->translationFor('en')?->id;

        $updated = $writer->update($page, [
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['title' => 'Điều Khoản Cập Nhật', 'slug' => 'dieu-khoan'],
            'en' => ['title' => 'Updated Terms', 'slug' => 'terms'],
        ]);

        $this->assertSame($originalViId, $updated->translationFor('vi')?->id);
        $this->assertSame($originalEnId, $updated->translationFor('en')?->id);
        $this->assertSame('Điều Khoản Cập Nhật', $updated->translationFor('vi')?->title);
    }

    public function test_page_archive_and_restore_lifecycle(): void
    {
        $writer = app(PageWriter::class);

        $page = $writer->create([
            'key' => 'promo',
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['title' => 'Trang Khuyến Mãi'],
        ]);

        // Archive
        $writer->archive($page);
        $this->assertSame(ContentStatus::ARCHIVED, $page->fresh()->status);

        // Restore
        $writer->restore($page);
        $this->assertSame(ContentStatus::DRAFT, $page->fresh()->status);
    }

    public function test_page_hard_delete_policy(): void
    {
        $writer = app(PageWriter::class);

        // 1. DRAFT page can be deleted
        $draftPage = $writer->create([
            'key' => 'draft-page',
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Trang Nháp Xóa'],
        ]);
        $draftId = $draftPage->id;
        $writer->delete($draftPage);
        $this->assertDatabaseMissing('pages', ['id' => $draftId]);

        // 2. PUBLISHED page cannot be hard-deleted
        $publishedPage = $writer->create([
            'key' => 'published-page',
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['title' => 'Trang Đã Xuất Bản'],
        ]);
        try {
            $writer->delete($publishedPage);
            $this->fail('Expected DomainException on deleting published page');
        } catch (DomainException $e) {
            $this->assertSame('Only draft pages can be deleted.', $e->getMessage());
        }

        // 3. ARCHIVED page cannot be hard-deleted
        $archivedPage = $writer->create([
            'key' => 'archived-page',
            'status' => ContentStatus::ARCHIVED,
            'vi' => ['title' => 'Trang Đã Lưu Trữ'],
        ]);
        try {
            $writer->delete($archivedPage);
            $this->fail('Expected DomainException on deleting archived page');
        } catch (DomainException $e) {
            $this->assertSame('Only draft pages can be deleted.', $e->getMessage());
        }
    }
}
