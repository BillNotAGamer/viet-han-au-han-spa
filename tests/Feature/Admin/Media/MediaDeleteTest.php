<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Media;

use App\Models\Media;
use App\Models\MediaTranslation;
use App\Services\Media\MediaUploadService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        config(['media.disk' => 'public']);
    }

    public function test_unreferenced_media_is_deleted_with_translations_and_physical_file(): void
    {
        $service = app(MediaUploadService::class);

        $storedPath = 'media/2026/08/clean_me.jpg';
        Storage::disk('public')->put($storedPath, 'fake_image_content');

        $media = Media::factory()->create([
            'disk' => 'public',
            'path' => $storedPath,
        ]);

        MediaTranslation::factory()->create([
            'media_id' => $media->id,
            'locale' => 'vi',
            'alt_text' => 'Ảnh sắp xóa',
        ]);

        MediaTranslation::factory()->create([
            'media_id' => $media->id,
            'locale' => 'en',
            'alt_text' => 'Image to be deleted',
        ]);

        $id = $media->id;

        $result = $service->delete($media);

        // 1. Result confirms both DB and physical cleanup succeeded
        $this->assertTrue($result['database_deleted']);
        $this->assertTrue($result['physical_deleted']);
        $this->assertNull($result['error']);

        // 2. DB media record removed
        $this->assertDatabaseMissing('media', ['id' => $id]);

        // 3. DB translations cascade removed
        $this->assertDatabaseMissing('media_translations', ['media_id' => $id]);

        // 4. Physical file removed
        Storage::disk('public')->assertMissing($storedPath);
    }

    public function test_physical_delete_failure_keeps_database_deleted_and_reports_cleanup_failure(): void
    {
        $service = app(MediaUploadService::class);

        $storedPath = 'media/2026/08/locked_file.jpg';
        Storage::disk('public')->put($storedPath, 'fake_image_content');

        $media = Media::factory()->create([
            'disk' => 'public',
            'path' => $storedPath,
        ]);

        MediaTranslation::factory()->create([
            'media_id' => $media->id,
            'locale' => 'vi',
            'alt_text' => 'Ảnh lỗi xóa tệp',
        ]);

        $id = $media->id;

        // Mock Storage::disk('public') delete to throw an exception
        Storage::shouldReceive('disk')->with('public')->andReturnSelf();
        Storage::shouldReceive('exists')->with($storedPath)->andReturn(true);
        Storage::shouldReceive('delete')->with($storedPath)->andThrow(new Exception('Filesystem permission denied'));

        $result = $service->delete($media);

        // 1. DB deletion is authoritative and was NOT rolled back
        $this->assertTrue($result['database_deleted']);
        $this->assertDatabaseMissing('media', ['id' => $id]);
        $this->assertDatabaseMissing('media_translations', ['media_id' => $id]);

        // 2. Service explicitly reports physical cleanup failed
        $this->assertFalse($result['physical_deleted']);
        $this->assertNotNull($result['error']);
        $this->assertSame('Filesystem permission denied', $result['error']);
    }
}
