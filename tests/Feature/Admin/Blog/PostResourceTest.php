<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Blog;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use App\Services\Blog\PostWriter;
use DomainException;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_non_admin_cannot_access_posts(): void
    {
        $this->get('/admin/posts')->assertStatus(302);

        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->get('/admin/posts')->assertStatus(403);
    }

    public function test_admin_can_access_post_list_and_create_pages(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/posts')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/posts/create')->assertStatus(200);
    }

    public function test_post_writer_creates_post_atomically(): void
    {
        $writer = app(PostWriter::class);
        $admin = User::factory()->admin()->create();
        $category = PostCategory::factory()->create();
        $hero = Media::factory()->create();
        $gallery = Media::factory()->count(2)->create();

        $this->actingAs($admin);

        $post = $writer->create([
            'post_category_id' => $category->id,
            'status' => ContentStatus::PUBLISHED,
            'is_featured' => true,
            'hero_media_id' => $hero->id,
            'vi' => [
                'title' => 'Top 5 Cách Chăm Sóc Da Mùa Hè Chuẩn Hàn Quốc',
                'slug' => 'top-5-cach-cham-soc-da-mua-he-chuan-han-quoc',
                'excerpt' => 'Bí quyết giữ làn da căng mọng, không bóng dầu trong ngày hè oi bức',
                'content' => '<p>Chi tiết các bước dưỡng da mùa hè...</p>',
                'seo_title' => 'Chăm Sóc Da Mùa Hè Chuẩn Hàn',
                'seo_description' => 'Hướng dẫn 5 bước chăm sóc da mùa hè hiệu quả',
            ],
            'en' => [
                'title' => 'Top 5 Summer Skincare Tips Korean Standard',
                'slug' => 'top-5-summer-skincare-tips-korean-standard',
                'excerpt' => 'Keep your skin hydrated and oil-free',
                'content' => '<p>Detailed summer skincare steps...</p>',
            ],
            'post_media_ids' => $gallery->pluck('id')->toArray(),
        ]);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertSame($category->id, $post->post_category_id);
        $this->assertSame($admin->id, $post->author_id); // Defaulted to current admin
        $this->assertSame(ContentStatus::PUBLISHED, $post->status);
        $this->assertTrue($post->is_featured);
        $this->assertNotNull($post->published_at);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'author_id' => $admin->id,
            'status' => 'PUBLISHED',
            'is_featured' => 1,
        ]);

        $this->assertDatabaseHas('post_translations', [
            'post_id' => $post->id,
            'locale' => 'vi',
            'title' => 'Top 5 Cách Chăm Sóc Da Mùa Hè Chuẩn Hàn Quốc',
            'slug' => 'top-5-cach-cham-soc-da-mua-he-chuan-han-quoc',
        ]);

        $this->assertDatabaseHas('post_translations', [
            'post_id' => $post->id,
            'locale' => 'en',
            'title' => 'Top 5 Summer Skincare Tips Korean Standard',
            'slug' => 'top-5-summer-skincare-tips-korean-standard',
        ]);

        $this->assertCount(2, $post->media);
    }

    public function test_post_update_preserves_translation_id(): void
    {
        $writer = app(PostWriter::class);
        $category = PostCategory::factory()->create();

        $post = $writer->create([
            'post_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Tiêu Đề Gốc', 'slug' => 'tieu-de-goc'],
            'en' => ['title' => 'Original Title', 'slug' => 'original-title'],
        ]);

        $originalViId = $post->translationFor('vi')?->id;
        $originalEnId = $post->translationFor('en')?->id;

        $updated = $writer->update($post, [
            'post_category_id' => $category->id,
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['title' => 'Tiêu Đề Cập Nhật', 'slug' => 'tieu-de-cap-nhat'],
            'en' => ['title' => 'Updated Title', 'slug' => 'updated-title'],
        ]);

        $this->assertSame($originalViId, $updated->translationFor('vi')?->id);
        $this->assertSame($originalEnId, $updated->translationFor('en')?->id);
        $this->assertSame('Tiêu Đề Cập Nhật', $updated->translationFor('vi')?->title);
    }

    public function test_post_omitted_en_translation_is_not_fabricated(): void
    {
        $writer = app(PostWriter::class);
        $category = PostCategory::factory()->create();

        $post = $writer->create([
            'post_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Bài Viết Chỉ Tiếng Việt', 'slug' => 'bai-viet-chi-tieng-viet'],
        ]);

        $this->assertNotNull($post->translationFor('vi'));
        $this->assertNull($post->translationFor('en'));
        $this->assertDatabaseMissing('post_translations', [
            'post_id' => $post->id,
            'locale' => 'en',
        ]);
    }

    public function test_post_slug_rules(): void
    {
        $writer = app(PostWriter::class);
        $category = PostCategory::factory()->create();

        // 1. Same-locale collision rejected
        $writer->create([
            'post_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Bài 1', 'slug' => 'bai-viet-slug'],
        ]);

        try {
            $writer->create([
                'post_category_id' => $category->id,
                'status' => ContentStatus::DRAFT,
                'vi' => ['title' => 'Bài 2', 'slug' => 'bai-viet-slug'],
            ]);
            $this->fail('Expected slug collision exception');
        } catch (Exception $e) {
            // Expected
        }

        // 2. Cross-locale same slug allowed
        $postCross = $writer->create([
            'post_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Bí Quyết Trị Mụn', 'slug' => 'acne-care'],
            'en' => ['title' => 'Acne Care Tips', 'slug' => 'acne-care'],
        ]);
        $this->assertDatabaseHas('post_translations', ['post_id' => $postCross->id, 'locale' => 'vi', 'slug' => 'acne-care']);
        $this->assertDatabaseHas('post_translations', ['post_id' => $postCross->id, 'locale' => 'en', 'slug' => 'acne-care']);

        // 3. Self-update succeeds
        $updated = $writer->update($postCross, [
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['title' => 'Bí Quyết Trị Mụn Chuyên Sâu', 'slug' => 'acne-care'],
        ]);
        $this->assertSame('acne-care', $updated->translationFor('vi')?->slug);

        // 4. Blank slug auto-generates
        $postBlank = $writer->create([
            'post_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Tự Động Sinh Slug', 'slug' => ''],
        ]);
        $this->assertSame('tu-dong-sinh-slug', $postBlank->translationFor('vi')?->slug);

        // 5. Manual slug stays stable
        $postManual = $writer->create([
            'post_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Tiêu Đề Ban Đầu', 'slug' => 'slug-co-dinh'],
        ]);
        $postManualUpdated = $writer->update($postManual, [
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Tiêu Đề Đã Sửa', 'slug' => 'slug-co-dinh'],
        ]);
        $this->assertSame('slug-co-dinh', $postManualUpdated->translationFor('vi')?->slug);
    }

    public function test_post_archive_and_restore_workflow(): void
    {
        $writer = app(PostWriter::class);
        $category = PostCategory::factory()->create();

        $post = $writer->create([
            'post_category_id' => $category->id,
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['title' => 'Bài Lưu Trữ', 'slug' => 'bai-luu-tru'],
        ]);

        $publishedAt = $post->published_at;
        $this->assertNotNull($publishedAt);

        // Archive
        $writer->archive($post);
        $this->assertSame(ContentStatus::ARCHIVED, $post->fresh()->status);
        $this->assertEquals($publishedAt, $post->fresh()->published_at); // Preserved

        // Restore
        $writer->restore($post);
        $this->assertSame(ContentStatus::DRAFT, $post->fresh()->status);
        $this->assertNull($post->fresh()->published_at); // Cleared on restore
    }

    public function test_post_hard_delete_policy(): void
    {
        $writer = app(PostWriter::class);
        $category = PostCategory::factory()->create();

        // 1. DRAFT may be deleted
        $draftPost = $writer->create([
            'post_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Bài Nháp Xóa', 'slug' => 'bai-nhap-xoa'],
        ]);
        $id = $draftPost->id;
        $writer->delete($draftPost);
        $this->assertDatabaseMissing('posts', ['id' => $id]);

        // 2. PUBLISHED cannot be hard deleted
        $publishedPost = $writer->create([
            'post_category_id' => $category->id,
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['title' => 'Bài Đã Xuất Bản', 'slug' => 'bai-da-xuat-ban'],
        ]);
        try {
            $writer->delete($publishedPost);
            $this->fail('Expected exception for deleting published post');
        } catch (DomainException $e) {
            $this->assertSame('Only draft posts can be deleted.', $e->getMessage());
        }

        // 3. ARCHIVED cannot be hard deleted
        $archivedPost = $writer->create([
            'post_category_id' => $category->id,
            'status' => ContentStatus::ARCHIVED,
            'vi' => ['title' => 'Bài Đã Lưu Trữ', 'slug' => 'bai-da-luu-tru'],
        ]);
        try {
            $writer->delete($archivedPost);
            $this->fail('Expected exception for deleting archived post');
        } catch (DomainException $e) {
            $this->assertSame('Only draft posts can be deleted.', $e->getMessage());
        }
    }

    public function test_transaction_rollback_leaves_no_partial_state(): void
    {
        $writer = app(PostWriter::class);
        $category = PostCategory::factory()->create();

        $initialCount = Post::count();

        $writer->create([
            'post_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Bài Chuẩn', 'slug' => 'slug-chuan'],
        ]);

        $this->assertSame($initialCount + 1, Post::count());

        try {
            // Attempt to create second post with duplicate localized slug
            $writer->create([
                'post_category_id' => $category->id,
                'status' => ContentStatus::DRAFT,
                'vi' => ['title' => 'Bài Trùng Slug', 'slug' => 'slug-chuan'],
            ]);
        } catch (Exception $e) {
            // Expected
        }

        // Count remains exactly initialCount + 1 (no partial records)
        $this->assertSame($initialCount + 1, Post::count());
    }
}
