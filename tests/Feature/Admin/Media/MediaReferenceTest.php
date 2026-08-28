<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Media;

use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\TrainingCourse;
use App\Services\Media\MediaReferenceInspector;
use App\Services\Media\MediaUploadService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaReferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_breakdown_reports_all_seven_reference_types(): void
    {
        $inspector = app(MediaReferenceInspector::class);
        $media = Media::factory()->create(['disk' => 'public']);

        $initialBreakdown = $inspector->getReferenceBreakdown($media);
        $this->assertSame([
            'service_hero' => 0,
            'training_hero' => 0,
            'post_hero' => 0,
            'service_gallery' => 0,
            'training_gallery' => 0,
            'post_gallery' => 0,
            'page_gallery' => 0,
        ], $initialBreakdown);

        $this->assertFalse($inspector->hasReferences($media));
    }

    public function test_deletion_denied_when_referenced_as_service_hero(): void
    {
        $media = Media::factory()->create(['disk' => 'public']);
        $service = Service::factory()->create(['hero_media_id' => $media->id]);

        $uploadService = app(MediaUploadService::class);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot delete media because it is currently referenced');

        $uploadService->delete($media);

        $this->assertDatabaseHas('media', ['id' => $media->id]);
        $this->assertSame($media->id, $service->fresh()->hero_media_id);
    }

    public function test_deletion_denied_when_referenced_as_training_hero(): void
    {
        $media = Media::factory()->create(['disk' => 'public']);
        $course = TrainingCourse::factory()->create(['hero_media_id' => $media->id]);

        $uploadService = app(MediaUploadService::class);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot delete media because it is currently referenced');

        $uploadService->delete($media);

        $this->assertDatabaseHas('media', ['id' => $media->id]);
        $this->assertSame($media->id, $course->fresh()->hero_media_id);
    }

    public function test_deletion_denied_when_referenced_as_post_hero(): void
    {
        $media = Media::factory()->create(['disk' => 'public']);
        $post = Post::factory()->create(['hero_media_id' => $media->id]);

        $uploadService = app(MediaUploadService::class);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot delete media because it is currently referenced');

        $uploadService->delete($media);

        $this->assertDatabaseHas('media', ['id' => $media->id]);
        $this->assertSame($media->id, $post->fresh()->hero_media_id);
    }

    public function test_deletion_denied_when_referenced_in_service_gallery(): void
    {
        $media = Media::factory()->create(['disk' => 'public']);
        $service = Service::factory()->create();
        $service->media()->attach($media->id, ['sort_order' => 0]);

        $uploadService = app(MediaUploadService::class);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot delete media because it is currently referenced');

        $uploadService->delete($media);

        $this->assertDatabaseHas('media', ['id' => $media->id]);
        $this->assertDatabaseHas('service_media', ['service_id' => $service->id, 'media_id' => $media->id]);
    }

    public function test_deletion_denied_when_referenced_in_training_gallery(): void
    {
        $media = Media::factory()->create(['disk' => 'public']);
        $course = TrainingCourse::factory()->create();
        $course->media()->attach($media->id, ['sort_order' => 0]);

        $uploadService = app(MediaUploadService::class);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot delete media because it is currently referenced');

        $uploadService->delete($media);

        $this->assertDatabaseHas('media', ['id' => $media->id]);
        $this->assertDatabaseHas('training_course_media', ['training_course_id' => $course->id, 'media_id' => $media->id]);
    }

    public function test_deletion_denied_when_referenced_in_post_gallery(): void
    {
        $media = Media::factory()->create(['disk' => 'public']);
        $post = Post::factory()->create();
        $post->media()->attach($media->id, ['sort_order' => 0]);

        $uploadService = app(MediaUploadService::class);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot delete media because it is currently referenced');

        $uploadService->delete($media);

        $this->assertDatabaseHas('media', ['id' => $media->id]);
        $this->assertDatabaseHas('post_media', ['post_id' => $post->id, 'media_id' => $media->id]);
    }

    public function test_deletion_denied_when_referenced_in_page_gallery(): void
    {
        $media = Media::factory()->create(['disk' => 'public']);
        $page = Page::factory()->create();
        $page->media()->attach($media->id, ['sort_order' => 0]);

        $uploadService = app(MediaUploadService::class);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot delete media because it is currently referenced');

        $uploadService->delete($media);

        $this->assertDatabaseHas('media', ['id' => $media->id]);
        $this->assertDatabaseHas('page_media', ['page_id' => $page->id, 'media_id' => $media->id]);
    }
}
