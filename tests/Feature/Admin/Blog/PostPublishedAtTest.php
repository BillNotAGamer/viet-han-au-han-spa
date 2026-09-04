<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Blog;

use App\Enums\ContentStatus;
use App\Models\PostCategory;
use App\Services\Blog\PostWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PostPublishedAtTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishing_with_null_published_at_receives_current_timestamp(): void
    {
        $writer = app(PostWriter::class);
        $category = PostCategory::factory()->create();

        $post = $writer->create([
            'post_category_id' => $category->id,
            'status' => ContentStatus::PUBLISHED,
            'published_at' => null,
            'vi' => ['title' => 'Bài Xuất Bản Liền', 'slug' => 'bai-xuat-ban-lien'],
        ]);

        $this->assertNotNull($post->published_at);
        $this->assertTrue($post->published_at->isPast() || $post->published_at->isCurrentSecond());
    }

    public function test_publishing_with_explicit_future_timestamp_is_preserved(): void
    {
        $writer = app(PostWriter::class);
        $category = PostCategory::factory()->create();

        $futureDate = Carbon::parse('2026-12-25 09:00:00');
        $post = $writer->create([
            'post_category_id' => $category->id,
            'status' => ContentStatus::PUBLISHED,
            'published_at' => $futureDate,
            'vi' => ['title' => 'Bài Giáng Sinh', 'slug' => 'bai-giang-sinh'],
        ]);

        $this->assertEquals($futureDate, $post->published_at);
    }

    public function test_editing_already_published_post_preserves_published_at(): void
    {
        $writer = app(PostWriter::class);
        $category = PostCategory::factory()->create();

        $historicalDate = Carbon::parse('2026-06-15 14:30:00');
        $post = $writer->create([
            'post_category_id' => $category->id,
            'status' => ContentStatus::PUBLISHED,
            'published_at' => $historicalDate,
            'vi' => ['title' => 'Bài Quá Khứ', 'slug' => 'bai-qua-khu'],
        ]);

        $updated = $writer->update($post, [
            'post_category_id' => $category->id,
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['title' => 'Bài Quá Khứ Đổi Tên', 'slug' => 'bai-qua-khu'],
        ]);

        $this->assertEquals($historicalDate, $updated->published_at);
    }
}
