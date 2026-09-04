<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Blog;

use App\Enums\ContentStatus;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use App\Services\Blog\PostCategoryWriter;
use DomainException;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_non_admin_cannot_access_post_categories(): void
    {
        $this->get('/admin/post-categories')->assertStatus(302);

        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->get('/admin/post-categories')->assertStatus(403);
    }

    public function test_admin_can_access_category_list_and_create_pages(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/post-categories')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/post-categories/create')->assertStatus(200);
    }

    public function test_category_writer_creates_core_and_bilingual_translations(): void
    {
        $writer = app(PostCategoryWriter::class);

        $category = $writer->create([
            'status' => ContentStatus::PUBLISHED,
            'sort_order' => 1,
            'vi' => [
                'name' => 'Chăm Sóc Sắc Đẹp',
                'slug' => 'cham-soc-sac-dep',
                'description' => 'Bí quyết và xu hướng làm đẹp chuẩn Hàn',
                'seo_title' => 'Chuyên Mục Chăm Sóc Sắc Đẹp',
                'seo_description' => 'Tổng hợp các bài viết làm đẹp hữu ích',
            ],
            'en' => [
                'name' => 'Beauty Care',
                'slug' => 'beauty-care',
                'description' => 'Korean beauty tips and care',
            ],
        ]);

        $this->assertInstanceOf(PostCategory::class, $category);
        $this->assertSame(ContentStatus::PUBLISHED, $category->status);
        $this->assertSame(1, $category->sort_order);

        $this->assertDatabaseHas('post_categories', [
            'id' => $category->id,
            'status' => 'PUBLISHED',
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('post_category_translations', [
            'post_category_id' => $category->id,
            'locale' => 'vi',
            'name' => 'Chăm Sóc Sắc Đẹp',
            'slug' => 'cham-soc-sac-dep',
        ]);

        $this->assertDatabaseHas('post_category_translations', [
            'post_category_id' => $category->id,
            'locale' => 'en',
            'name' => 'Beauty Care',
            'slug' => 'beauty-care',
        ]);
    }

    public function test_category_update_preserves_translation_id(): void
    {
        $writer = app(PostCategoryWriter::class);

        $category = $writer->create([
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Danh Mục Gốc', 'slug' => 'danh-muc-goc'],
            'en' => ['name' => 'Original Category', 'slug' => 'original-category'],
        ]);

        $originalViId = $category->translationFor('vi')?->id;
        $originalEnId = $category->translationFor('en')?->id;

        $updated = $writer->update($category, [
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['name' => 'Danh Mục Cập Nhật', 'slug' => 'danh-muc-cap-nhat'],
            'en' => ['name' => 'Updated Category', 'slug' => 'updated-category'],
        ]);

        $this->assertSame($originalViId, $updated->translationFor('vi')?->id);
        $this->assertSame($originalEnId, $updated->translationFor('en')?->id);
        $this->assertSame('Danh Mục Cập Nhật', $updated->translationFor('vi')?->name);
    }

    public function test_category_omitted_en_translation_is_not_fabricated(): void
    {
        $writer = app(PostCategoryWriter::class);

        $category = $writer->create([
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['name' => 'Danh Mục Tiếng Việt', 'slug' => 'danh-muc-tieng-viet'],
        ]);

        $this->assertNotNull($category->translationFor('vi'));
        $this->assertNull($category->translationFor('en'));
        $this->assertDatabaseMissing('post_category_translations', [
            'post_category_id' => $category->id,
            'locale' => 'en',
        ]);
    }

    public function test_category_slug_rules(): void
    {
        $writer = app(PostCategoryWriter::class);

        // 1. Same-locale collision rejected
        $writer->create([
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['name' => 'Kinh Nghiệm Spa', 'slug' => 'kinh-nghiem-spa'],
        ]);

        try {
            $writer->create([
                'status' => ContentStatus::PUBLISHED,
                'vi' => ['name' => 'Kinh Nghiệm Spa 2', 'slug' => 'kinh-nghiem-spa'],
            ]);
            $this->fail('Expected collision exception');
        } catch (Exception $e) {
            // Expected
        }

        // 2. Cross-locale same slug allowed
        $catCross = $writer->create([
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['name' => 'Tin Tức', 'slug' => 'news'],
            'en' => ['name' => 'News', 'slug' => 'news'],
        ]);
        $this->assertDatabaseHas('post_category_translations', ['post_category_id' => $catCross->id, 'locale' => 'vi', 'slug' => 'news']);
        $this->assertDatabaseHas('post_category_translations', ['post_category_id' => $catCross->id, 'locale' => 'en', 'slug' => 'news']);

        // 3. Self-update succeeds
        $updated = $writer->update($catCross, [
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['name' => 'Tin Tức Mới', 'slug' => 'news'],
        ]);
        $this->assertSame('news', $updated->translationFor('vi')?->slug);

        // 4. Blank slug auto-generates
        $catBlank = $writer->create([
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['name' => 'Kiến Thức Làm Đẹp', 'slug' => ''],
        ]);
        $this->assertSame('kien-thuc-lam-dep', $catBlank->translationFor('vi')?->slug);

        // 5. Manual slug remains stable on later updates
        $catManual = $writer->create([
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['name' => 'Tên Ban Đầu', 'slug' => 'slug-tuy-chinh'],
        ]);
        $catManualUpdated = $writer->update($catManual, [
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['name' => 'Tên Thay Đổi', 'slug' => 'slug-tuy-chinh'],
        ]);
        $this->assertSame('slug-tuy-chinh', $catManualUpdated->translationFor('vi')?->slug);
    }

    public function test_category_containing_posts_cannot_be_deleted(): void
    {
        $writer = app(PostCategoryWriter::class);

        $category = $writer->create([
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['name' => 'Danh Mục Có Bài', 'slug' => 'danh-muc-co-bai'],
        ]);

        Post::factory()->create(['post_category_id' => $category->id]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot delete category because it contains associated posts.');

        $writer->delete($category);
    }

    public function test_empty_category_can_be_deleted(): void
    {
        $writer = app(PostCategoryWriter::class);

        $category = $writer->create([
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Danh Mục Trống', 'slug' => 'danh-muc-trong'],
        ]);

        $id = $category->id;
        $writer->delete($category);

        $this->assertDatabaseMissing('post_categories', ['id' => $id]);
        $this->assertDatabaseMissing('post_category_translations', ['post_category_id' => $id]);
    }
}
