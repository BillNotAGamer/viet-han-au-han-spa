<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Training;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Services\Training\TrainingCourseWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TrainingCourseMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_hero_and_gallery_media_association(): void
    {
        $writer = app(TrainingCourseWriter::class);
        $hero = Media::factory()->create();
        $gallery = Media::factory()->count(2)->create();

        $course = $writer->create([
            'status' => ContentStatus::DRAFT,
            'hero_media_id' => $hero->id,
            'gallery_media_ids' => $gallery->pluck('id')->toArray(),
            'vi' => ['title' => 'Khóa Test Ảnh', 'slug' => 'khoa-test-anh'],
        ]);

        $this->assertSame($hero->id, $course->hero_media_id);
        $this->assertCount(2, $course->media);

        // Detaching does not delete physical Media records
        $writer->update($course, [
            'status' => ContentStatus::DRAFT,
            'hero_media_id' => null,
            'gallery_media_ids' => [],
            'vi' => ['title' => 'Khóa Test Ảnh', 'slug' => 'khoa-test-anh'],
        ]);

        $this->assertCount(0, $course->fresh()->media);
        $this->assertDatabaseHas('media', ['id' => $hero->id]);
        foreach ($gallery as $m) {
            $this->assertDatabaseHas('media', ['id' => $m->id]);
        }
    }

    public function test_duplicate_gallery_media_selection_is_prevented(): void
    {
        $writer = app(TrainingCourseWriter::class);
        $media = Media::factory()->create();

        $course = $writer->create([
            'status' => ContentStatus::DRAFT,
            'gallery_media_ids' => [$media->id, $media->id],
            'vi' => ['title' => 'Khóa Trùng Ảnh', 'slug' => 'khoa-trung-anh'],
        ]);

        $count = DB::table('training_course_media')
            ->where('training_course_id', $course->id)
            ->where('media_id', $media->id)
            ->count();

        $this->assertSame(1, $count);
    }

    public function test_gallery_media_reordering_persists_sort_order(): void
    {
        $writer = app(TrainingCourseWriter::class);
        $mediaA = Media::factory()->create();
        $mediaB = Media::factory()->create();

        // 1. Initial Order: [A, B]
        $course = $writer->create([
            'status' => ContentStatus::DRAFT,
            'gallery_media_ids' => [$mediaA->id, $mediaB->id],
            'vi' => ['title' => 'Khóa Sắp Xếp Ảnh', 'slug' => 'khoa-sap-xep-anh'],
        ]);

        $pivotA = DB::table('training_course_media')->where('training_course_id', $course->id)->where('media_id', $mediaA->id)->first();
        $pivotB = DB::table('training_course_media')->where('training_course_id', $course->id)->where('media_id', $mediaB->id)->first();

        $this->assertSame(0, $pivotA->sort_order);
        $this->assertSame(1, $pivotB->sort_order);

        // 2. Reorder: [B, A]
        $writer->update($course, [
            'status' => ContentStatus::DRAFT,
            'gallery_media_ids' => [$mediaB->id, $mediaA->id],
            'vi' => ['title' => 'Khóa Sắp Xếp Ảnh', 'slug' => 'khoa-sap-xep-anh'],
        ]);

        $pivotAAfter = DB::table('training_course_media')->where('training_course_id', $course->id)->where('media_id', $mediaA->id)->first();
        $pivotBAfter = DB::table('training_course_media')->where('training_course_id', $course->id)->where('media_id', $mediaB->id)->first();

        $this->assertSame(1, $pivotAAfter->sort_order);
        $this->assertSame(0, $pivotBAfter->sort_order);
    }
}
