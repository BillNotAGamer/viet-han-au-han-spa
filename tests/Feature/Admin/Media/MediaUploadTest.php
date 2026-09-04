<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Media;

use App\Models\Media;
use App\Models\User;
use App\Services\Media\MediaUploadService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        config(['media.disk' => 'public']);
    }

    public function test_valid_raster_images_are_accepted_and_stored_with_trusted_metadata(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $service = app(MediaUploadService::class);

        // 1. Test JPEG
        $jpegFile = UploadedFile::fake()->image('banner_spa.jpg', 1200, 800)->size(1500);
        $mediaJpeg = $service->upload($jpegFile, [
            'vi' => ['alt_text' => 'Ảnh bìa spa', 'caption' => 'Không gian thư giãn chuẩn Hàn'],
            'en' => ['alt_text' => 'Spa Banner', 'caption' => 'Relaxing Korean spa space'],
        ], $admin->id);

        $this->assertInstanceOf(Media::class, $mediaJpeg);
        $this->assertSame('banner_spa.jpg', $mediaJpeg->file_name);
        $this->assertSame('jpg', $mediaJpeg->extension);
        $this->assertSame('image/jpeg', $mediaJpeg->mime_type);
        $this->assertSame(1200, $mediaJpeg->width);
        $this->assertSame(800, $mediaJpeg->height);
        $this->assertSame($admin->id, $mediaJpeg->uploaded_by);

        Storage::disk('public')->assertExists($mediaJpeg->path);

        $this->assertDatabaseHas('media_translations', [
            'media_id' => $mediaJpeg->id,
            'locale' => 'vi',
            'alt_text' => 'Ảnh bìa spa',
            'caption' => 'Không gian thư giãn chuẩn Hàn',
        ]);

        $this->assertDatabaseHas('media_translations', [
            'media_id' => $mediaJpeg->id,
            'locale' => 'en',
            'alt_text' => 'Spa Banner',
            'caption' => 'Relaxing Korean spa space',
        ]);

        // 2. Test PNG
        $pngFile = UploadedFile::fake()->image('treatment.png', 800, 600)->size(800);
        $mediaPng = $service->upload($pngFile, [], $admin->id);
        $this->assertSame('png', $mediaPng->extension);
        $this->assertSame('image/png', $mediaPng->mime_type);
        Storage::disk('public')->assertExists($mediaPng->path);

        // 3. Test WebP
        $webpFile = UploadedFile::fake()->image('facial.webp', 640, 480)->size(400);
        $mediaWebp = $service->upload($webpFile, [], $admin->id);
        $this->assertSame('webp', $mediaWebp->extension);
        $this->assertSame('image/webp', $mediaWebp->mime_type);
        Storage::disk('public')->assertExists($mediaWebp->path);
    }

    public function test_authoritative_content_detection_stores_canonical_extension_regardless_of_client_extension(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $service = app(MediaUploadService::class);

        // 1. Real JPEG bytes given a misleading .png filename
        $jpegWithPngName = UploadedFile::fake()->image('misleading.png', 400, 300);
        // Ensure it has JPEG bytes
        $tempPath = tempnam(sys_get_temp_dir(), 'img_test_');
        $img = imagecreatetruecolor(100, 100);
        imagejpeg($img, $tempPath);
        imagedestroy($img);
        $realJpegNamedPng = new UploadedFile($tempPath, 'fake_name.png', 'image/png', null, true);

        $media = $service->upload($realJpegNamedPng, [], $admin->id);
        $this->assertSame('jpg', $media->extension);
        $this->assertSame('image/jpeg', $media->mime_type);
        $this->assertStringEndsWith('.jpg', $media->path);
        @unlink($tempPath);

        // 2. Real PNG bytes given a misleading .jpg filename
        $tempPathPng = tempnam(sys_get_temp_dir(), 'img_png_');
        $imgPng = imagecreatetruecolor(100, 100);
        imagepng($imgPng, $tempPathPng);
        imagedestroy($imgPng);
        $realPngNamedJpg = new UploadedFile($tempPathPng, 'fake_name.jpg', 'image/jpeg', null, true);

        $mediaPng = $service->upload($realPngNamedJpg, [], $admin->id);
        $this->assertSame('png', $mediaPng->extension);
        $this->assertSame('image/png', $mediaPng->mime_type);
        $this->assertStringEndsWith('.png', $mediaPng->path);
        @unlink($tempPathPng);
    }

    public function test_rejection_of_fake_images_and_scripts(): void
    {
        $service = app(MediaUploadService::class);

        // 1. Plain text disguised as .jpg
        $fakeTxt = UploadedFile::fake()->createWithContent('malicious.jpg', 'Plain text disguised as image');
        try {
            $service->inspectAndValidateImage($fakeTxt);
            $this->fail('Expected InvalidArgumentException for text disguised as image');
        } catch (InvalidArgumentException $e) {
            $this->assertNotEmpty($e->getMessage());
        }

        // 2. PHP file disguised as .png
        $fakePhp = UploadedFile::fake()->createWithContent('shell.png', "<?php echo 'malicious'; ?>");
        try {
            $service->inspectAndValidateImage($fakePhp);
            $this->fail('Expected InvalidArgumentException for PHP disguised as image');
        } catch (InvalidArgumentException $e) {
            $this->assertNotEmpty($e->getMessage());
        }

        // 3. HTML disguised as .jpg
        $fakeHtml = UploadedFile::fake()->createWithContent('page.jpg', '<html><script>alert(1)</script></html>');
        try {
            $service->inspectAndValidateImage($fakeHtml);
            $this->fail('Expected InvalidArgumentException for HTML disguised as image');
        } catch (InvalidArgumentException $e) {
            $this->assertNotEmpty($e->getMessage());
        }

        // 4. SVG file
        $svgFile = UploadedFile::fake()->createWithContent('vector.svg', '<svg xmlns="http://www.w3.org/2000/svg"><circle r="10"/></svg>');
        try {
            $service->inspectAndValidateImage($svgFile);
            $this->fail('Expected InvalidArgumentException for SVG');
        } catch (InvalidArgumentException $e) {
            $this->assertNotEmpty($e->getMessage());
        }
    }

    public function test_stored_path_safety_prevents_path_traversal(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $service = app(MediaUploadService::class);

        // File with traversal in client name
        $file1 = UploadedFile::fake()->image('../../evil.jpg', 200, 200);
        $media1 = $service->upload($file1, [], $admin->id);

        $this->assertStringStartsWith('media/'.date('Y/m').'/', $media1->path);
        $this->assertStringNotContainsString('..', $media1->path);
        $this->assertSame('evil.jpg', $media1->file_name);

        $file2 = UploadedFile::fake()->image('folder/sub/image.png', 200, 200);
        $media2 = $service->upload($file2, [], $admin->id);

        $this->assertStringStartsWith('media/'.date('Y/m').'/', $media2->path);
        $this->assertStringNotContainsString('sub', $media2->path);
        $this->assertSame('image.png', $media2->file_name);
    }

    public function test_normal_edit_does_not_replace_physical_file_or_technical_metadata(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $service = app(MediaUploadService::class);

        $file = UploadedFile::fake()->image('original.jpg', 600, 400);
        $media = $service->upload($file, [
            'vi' => ['alt_text' => 'Gốc'],
        ], $admin->id);

        $originalPath = $media->path;
        $originalSize = $media->size_bytes;
        $originalWidth = $media->width;
        $originalHeight = $media->height;
        $originalMime = $media->mime_type;
        $originalExt = $media->extension;
        $originalFileName = $media->file_name;

        // Perform edit
        $updated = $service->updateTranslations($media, [
            'vi' => ['alt_text' => 'Đã sửa', 'caption' => 'Mô tả mới'],
            'en' => ['alt_text' => 'Updated Alt'],
        ]);

        $fresh = $updated->fresh();
        $this->assertSame($originalPath, $fresh->path);
        $this->assertSame($originalSize, $fresh->size_bytes);
        $this->assertSame($originalWidth, $fresh->width);
        $this->assertSame($originalHeight, $fresh->height);
        $this->assertSame($originalMime, $fresh->mime_type);
        $this->assertSame($originalExt, $fresh->extension);
        $this->assertSame($originalFileName, $fresh->file_name);

        $this->assertSame('Đã sửa', $fresh->translationFor('vi')?->alt_text);
        $this->assertSame('Mô tả mới', $fresh->translationFor('vi')?->caption);
        $this->assertSame('Updated Alt', $fresh->translationFor('en')?->alt_text);
    }

    public function test_compensation_cleans_up_stored_file_if_database_write_fails(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $service = app(MediaUploadService::class);
        $file = UploadedFile::fake()->image('rollback_test.jpg', 500, 500);

        Media::creating(function () {
            throw new Exception('Simulated database deadlock during Media creation.');
        });

        try {
            $service->upload($file, [], $admin->id);
            $this->fail('Expected Exception on simulated failure');
        } catch (Exception $e) {
            $this->assertSame('Simulated database deadlock during Media creation.', $e->getMessage());
        }

        $filesOnDisk = Storage::disk('public')->allFiles('media');
        $this->assertEmpty($filesOnDisk, 'Expected compensating cleanup to remove physical file on DB failure.');
    }
}
