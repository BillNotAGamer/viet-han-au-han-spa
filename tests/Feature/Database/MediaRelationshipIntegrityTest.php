<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\TrainingCourse;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaRelationshipIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_media_pivot_prevents_duplicates(): void
    {
        $service = Service::factory()->create();
        $media = Media::factory()->create();

        $service->media()->attach($media->id, ['sort_order' => 0]);

        $this->expectException(QueryException::class);
        $service->media()->attach($media->id, ['sort_order' => 1]);
    }

    public function test_deleting_service_removes_service_media_pivot_row(): void
    {
        $service = Service::factory()->create();
        $media = Media::factory()->create();
        $service->media()->attach($media->id, ['sort_order' => 0]);

        $service->delete();

        $this->assertDatabaseMissing('service_media', ['service_id' => $service->id, 'media_id' => $media->id]);
        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }

    public function test_deleting_media_attached_to_service_is_restricted(): void
    {
        $service = Service::factory()->create();
        $media = Media::factory()->create();
        $service->media()->attach($media->id, ['sort_order' => 0]);

        $this->expectException(QueryException::class);
        $media->delete();
    }

    public function test_training_course_media_pivot_prevents_duplicates(): void
    {
        $course = TrainingCourse::factory()->create();
        $media = Media::factory()->create();

        $course->media()->attach($media->id, ['sort_order' => 0]);

        $this->expectException(QueryException::class);
        $course->media()->attach($media->id, ['sort_order' => 1]);
    }

    public function test_post_media_pivot_prevents_duplicates(): void
    {
        $post = Post::factory()->create();
        $media = Media::factory()->create();

        $post->media()->attach($media->id, ['sort_order' => 0]);

        $this->expectException(QueryException::class);
        $post->media()->attach($media->id, ['sort_order' => 1]);
    }

    public function test_page_media_pivot_prevents_duplicates(): void
    {
        $page = Page::factory()->create();
        $media = Media::factory()->create();

        $page->media()->attach($media->id, ['sort_order' => 0]);

        $this->expectException(QueryException::class);
        $page->media()->attach($media->id, ['sort_order' => 1]);
    }
}
