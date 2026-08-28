<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Services;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\ServiceCategory;
use App\Services\ServiceCatalog\ServiceWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MediaAssociationTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_media_can_be_assigned_as_hero_and_gallery(): void
    {
        $writer = app(ServiceWriter::class);
        $category = ServiceCategory::factory()->create();
        $hero = Media::factory()->create(['file_name' => 'hero.jpg']);
        $gallery = Media::factory()->count(3)->create();

        $service = $writer->create([
            'service_category_id' => $category->id,
            'hero_media_id' => $hero->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Dịch Vụ Test Media', 'slug' => 'dich-vu-test-media'],
            'gallery_media_ids' => $gallery->pluck('id')->toArray(),
        ]);

        $this->assertSame($hero->id, $service->hero_media_id);
        $this->assertCount(3, $service->media);

        // Removing gallery media removes pivot but preserves physical Media records
        $writer->update($service, [
            'service_category_id' => $category->id,
            'hero_media_id' => null,
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Dịch Vụ Test Media', 'slug' => 'dich-vu-test-media'],
            'gallery_media_ids' => [],
        ]);

        $this->assertCount(0, $service->fresh()->media);

        // Media records MUST NOT be deleted
        $this->assertDatabaseHas('media', ['id' => $hero->id]);
        foreach ($gallery as $m) {
            $this->assertDatabaseHas('media', ['id' => $m->id]);
        }
    }

    public function test_duplicate_gallery_media_selection_is_prevented(): void
    {
        $writer = app(ServiceWriter::class);
        $category = ServiceCategory::factory()->create();
        $media = Media::factory()->create();

        // Pass the same media id twice in gallery_media_ids
        $service = $writer->create([
            'service_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Test Trùng Media', 'slug' => 'test-trung-media'],
            'gallery_media_ids' => [$media->id, $media->id],
        ]);

        // Database must have exactly 1 pivot record for this service and media
        $pivotCount = DB::table('service_media')
            ->where('service_id', $service->id)
            ->where('media_id', $media->id)
            ->count();

        $this->assertSame(1, $pivotCount);
    }

    public function test_gallery_media_reordering_persists_sort_order(): void
    {
        $writer = app(ServiceWriter::class);
        $category = ServiceCategory::factory()->create();
        $mediaA = Media::factory()->create(['file_name' => 'a.jpg']);
        $mediaB = Media::factory()->create(['file_name' => 'b.jpg']);

        // 1. Initial Order: [A, B]
        $service = $writer->create([
            'service_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Test Sắp Xếp Media', 'slug' => 'test-sap-xep-media'],
            'gallery_media_ids' => [$mediaA->id, $mediaB->id],
        ]);

        $pivotA = DB::table('service_media')->where('service_id', $service->id)->where('media_id', $mediaA->id)->first();
        $pivotB = DB::table('service_media')->where('service_id', $service->id)->where('media_id', $mediaB->id)->first();

        $this->assertSame(0, $pivotA->sort_order);
        $this->assertSame(1, $pivotB->sort_order);

        // 2. Reorder: [B, A]
        $writer->update($service, [
            'service_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Test Sắp Xếp Media', 'slug' => 'test-sap-xep-media'],
            'gallery_media_ids' => [$mediaB->id, $mediaA->id],
        ]);

        $pivotAAfter = DB::table('service_media')->where('service_id', $service->id)->where('media_id', $mediaA->id)->first();
        $pivotBAfter = DB::table('service_media')->where('service_id', $service->id)->where('media_id', $mediaB->id)->first();

        $this->assertSame(1, $pivotAAfter->sort_order);
        $this->assertSame(0, $pivotBAfter->sort_order);
    }
}
