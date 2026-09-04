<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Pages;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Services\Media\MediaUploadService;
use App\Services\Pages\PageWriter;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PageMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_media_association_and_duplicate_prevention(): void
    {
        $writer = app(PageWriter::class);
        $media = Media::factory()->create(['disk' => 'public']);

        $page = $writer->create([
            'key' => 'gallery-test',
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Trang Ảnh'],
            'page_media_ids' => [$media->id, $media->id], // Duplicate IDs passed
        ]);

        $count = DB::table('page_media')
            ->where('page_id', $page->id)
            ->where('media_id', $media->id)
            ->count();

        $this->assertSame(1, $count);
        $this->assertCount(1, $page->media);
    }

    public function test_page_media_sort_order_persists_on_reorder(): void
    {
        $writer = app(PageWriter::class);
        $mediaA = Media::factory()->create(['disk' => 'public']);
        $mediaB = Media::factory()->create(['disk' => 'public']);

        // 1. Initial Order: [A, B]
        $page = $writer->create([
            'key' => 'reorder-test',
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Trang Sắp Xếp'],
            'page_media_ids' => [$mediaA->id, $mediaB->id],
        ]);

        $pivotA = DB::table('page_media')->where('page_id', $page->id)->where('media_id', $mediaA->id)->first();
        $pivotB = DB::table('page_media')->where('page_id', $page->id)->where('media_id', $mediaB->id)->first();
        $this->assertSame(0, $pivotA->sort_order);
        $this->assertSame(1, $pivotB->sort_order);

        // 2. Reorder: [B, A]
        $writer->update($page, [
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Trang Sắp Xếp'],
            'page_media_ids' => [$mediaB->id, $mediaA->id],
        ]);

        $pivotAAfter = DB::table('page_media')->where('page_id', $page->id)->where('media_id', $mediaA->id)->first();
        $pivotBAfter = DB::table('page_media')->where('page_id', $page->id)->where('media_id', $mediaB->id)->first();
        $this->assertSame(1, $pivotAAfter->sort_order);
        $this->assertSame(0, $pivotBAfter->sort_order);
    }

    public function test_detaching_page_media_removes_pivot_only_and_preserves_physical_media(): void
    {
        $writer = app(PageWriter::class);
        $media = Media::factory()->create(['disk' => 'public']);

        $page = $writer->create([
            'key' => 'detach-test',
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Trang Gỡ Ảnh'],
            'page_media_ids' => [$media->id],
        ]);

        // Detach
        $writer->update($page, [
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Trang Gỡ Ảnh'],
            'page_media_ids' => [],
        ]);

        $this->assertCount(0, $page->fresh()->media);
        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }

    public function test_media_referenced_by_page_cannot_be_deleted(): void
    {
        $writer = app(PageWriter::class);
        $media = Media::factory()->create(['disk' => 'public']);

        $writer->create([
            'key' => 'ref-delete-test',
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['title' => 'Trang Giữ Ảnh'],
            'page_media_ids' => [$media->id],
        ]);

        $mediaService = app(MediaUploadService::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot delete media because it is currently referenced');

        $mediaService->delete($media);
    }
}
