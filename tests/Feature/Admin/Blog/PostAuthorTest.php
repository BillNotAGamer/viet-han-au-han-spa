<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Blog;

use App\Enums\ContentStatus;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use App\Services\Blog\PostWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostAuthorTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_id_defaults_to_authenticated_admin_on_create(): void
    {
        $writer = app(PostWriter::class);
        $admin = User::factory()->admin()->create();
        $category = PostCategory::factory()->create();

        $this->actingAs($admin);

        $post = $writer->create([
            'post_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Bài Viết Test Tác Giả', 'slug' => 'bai-viet-test-tac-gia'],
        ]);

        $this->assertSame($admin->id, $post->author_id);
    }

    public function test_explicit_existing_user_may_be_selected_as_author(): void
    {
        $writer = app(PostWriter::class);
        $admin = User::factory()->admin()->create();
        $otherAuthor = User::factory()->create(['name' => 'Bác sĩ Da liễu Kim']);
        $category = PostCategory::factory()->create();

        $this->actingAs($admin);

        $post = $writer->create([
            'post_category_id' => $category->id,
            'author_id' => $otherAuthor->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Bài Viết Chuyên Môn', 'slug' => 'bai-viet-chuyen-mon'],
        ]);

        $this->assertSame($otherAuthor->id, $post->author_id);
    }

    public function test_deleting_author_user_sets_post_author_id_to_null_and_post_remains(): void
    {
        $writer = app(PostWriter::class);
        $author = User::factory()->create();
        $category = PostCategory::factory()->create();

        $post = $writer->create([
            'post_category_id' => $category->id,
            'author_id' => $author->id,
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['title' => 'Bài Tác Giả Sắp Xóa', 'slug' => 'bai-tac-gia-sap-xoa'],
        ]);

        $this->assertSame($author->id, $post->author_id);

        // Delete user
        $author->delete();

        // Post must still exist, with author_id = null (ON DELETE SET NULL)
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'author_id' => null,
        ]);
    }
}
