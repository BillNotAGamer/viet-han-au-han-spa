<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Blog;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\PostCategory;
use App\Services\Blog\PostWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_hero_and_gallery_media_association(): void
    {
        $writer = app(PostWriter::class);
        $category = PostCategory::factory()->create();
        $hero = Media::factory()->create();
        $gallery = Media::factory()->count(3)->create();

        $post = $writer->create([
            'post_category_id' => $category->id,
            'hero_media_id' => $hero->id,
            'post_media_ids' => $gallery->pluck('id')->toArray(),
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Bài Test Ảnh', 'slug' => 'bai-test-anh'],
        ]);

        $this->assertSame($hero->id, $post->hero_media_id);
        $this->assertCount(3, $post->media);

        // Detach removes pivot but preserves physical Media records
        $writer->update($post, [
            'post_category_id' => $category->id,
            'hero_media_id' => null,
            'post_media_ids' => [],
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Bài Test Ảnh', 'slug' => 'bai-test-anh'],
        ]);

        $this->assertCount(0, $post->fresh()->media);
        $this->assertDatabaseHas('media', ['id' => $hero->id]);
        foreach ($gallery as $m) {
            $this->assertDatabaseHas('media', ['id' => $m->id]);
        }
    }

    public function test_duplicate_gallery_media_selection_is_prevented(): void
    {
        $writer = app(PostWriter::class);
        $category = PostCategory::factory()->create();
        $media = Media::factory()->create();

        $post = $writer->create([
            'post_category_id' => $category->id,
            'post_media_ids' => [$media->id, $media->id],
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Bài Trùng Ảnh', 'slug' => 'bai-trung-anh'],
        ]);

        $count = DB::table('post_media')
            ->where('post_id', $post->id)
            ->where('media_id', $media->id)
            ->count();

        $this->assertSame(1, $count);
    }

    public function test_gallery_media_reordering_persists_sort_order(): void
    {
        $writer = app(PostWriter::class);
        $category = PostCategory::factory()->create();
        $mediaA = Media::factory()->create();
        $mediaB = Media::factory()->create();

        // 1. Initial Order: [A, B]
        $post = $writer->create([
            'post_category_id' => $category->id,
            'post_media_ids' => [$mediaA->id, $mediaB->id],
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Bài Sắp Xếp Ảnh', 'slug' => 'bai-sap-xep-anh'],
        ]);

        $pivotA = DB::table('post_media')->where('post_id', $post->id)->where('media_id', $mediaA->id)->first();
        $pivotB = DB::table('post_media')->where('post_id', $post->id)->where('media_id', $mediaB->id)->first();

        $this->assertSame(0, $pivotA->sort_order);
        $this->assertSame(1, $pivotB->sort_order);

        // 2. Reorder: [B, A]
        $writer->update($post, [
            'post_category_id' => $category->id,
            'post_media_ids' => [$mediaB->id, $mediaA->id],
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Bài Sắp Xếp Ảnh', 'slug' => 'bai-sap-xep-anh'],
        ]);

        $pivotAAfter = DB::table('post_media')->where('post_id', $post->id)->where('media_id', $mediaA->id)->first();
        $pivotBAfter = DB::table('post_media')->where('post_id', $post->id)->where('media_id', $mediaB->id)->first();

        $this->assertSame(1, $pivotAAfter->sort_order);
        $this->assertSame(0, $pivotBAfter->sort_order);
    }
}
