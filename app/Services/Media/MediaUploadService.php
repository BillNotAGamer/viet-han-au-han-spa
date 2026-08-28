<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Media;
use App\Models\MediaTranslation;
use DomainException;
use Exception;
use finfo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MediaUploadService
{
    /**
     * Map of supported PHP image types to canonical MIME and extension.
     *
     * @var array<int, array{mime: string, ext: string}>
     */
    protected const SUPPORTED_TYPES = [
        IMAGETYPE_JPEG => ['mime' => 'image/jpeg', 'ext' => 'jpg'],
        IMAGETYPE_PNG => ['mime' => 'image/png', 'ext' => 'png'],
        IMAGETYPE_WEBP => ['mime' => 'image/webp', 'ext' => 'webp'],
    ];

    public function __construct(
        protected MediaReferenceInspector $referenceInspector
    ) {}

    /**
     * Inspect and validate raster image file from actual byte content.
     *
     * @return array{mime: string, ext: string, width: int, height: int}
     */
    public function inspectAndValidateImage(UploadedFile $file): array
    {
        if (! $file->isValid()) {
            throw new InvalidArgumentException('Uploaded file is corrupted or upload failed.');
        }

        $maxBytes = (int) config('media.max_file_size_kb', 10240) * 1024;
        if ($file->getSize() > $maxBytes) {
            throw new InvalidArgumentException("File size exceeds maximum allowed limit of {$maxBytes} bytes.");
        }

        $realPath = $file->getRealPath();
        if (! $realPath || ! file_exists($realPath)) {
            throw new InvalidArgumentException('Uploaded file does not exist on disk.');
        }

        // Quick check for SVG or scriptable content
        $sample = (string) file_get_contents($realPath, false, null, 0, 2048);
        if (str_contains($sample, '<svg') || str_contains($sample, '<?xml') || str_contains($sample, '<?php')) {
            throw new InvalidArgumentException('SVG and executable/scriptable files are strictly prohibited.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $detectedFinfoMime = $finfo->file($realPath);
        if ($detectedFinfoMime === 'image/svg+xml' || str_starts_with((string) $detectedFinfoMime, 'text/')) {
            throw new InvalidArgumentException('File is not a valid raster image.');
        }

        // Deep raster header inspection via getimagesize
        $imageInfo = @getimagesize($realPath);
        if ($imageInfo === false) {
            throw new InvalidArgumentException('File is not a valid or readable raster image.');
        }

        $imageType = (int) $imageInfo[2];
        if (! isset(self::SUPPORTED_TYPES[$imageType])) {
            throw new InvalidArgumentException('Image format is not supported. Only JPEG, PNG, and WebP raster images are allowed.');
        }

        $canonicalMime = self::SUPPORTED_TYPES[$imageType]['mime'];
        $canonicalExt = self::SUPPORTED_TYPES[$imageType]['ext'];
        $width = (int) $imageInfo[0];
        $height = (int) $imageInfo[1];

        $maxDimension = (int) config('media.max_dimension', 10000);
        if ($width > $maxDimension || $height > $maxDimension) {
            throw new InvalidArgumentException("Image dimensions ({$width}x{$height}) exceed maximum allowed limit of {$maxDimension}px.");
        }

        return [
            'mime' => $canonicalMime,
            'ext' => $canonicalExt,
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * Backward-compatible validation wrapper.
     */
    public function validateImageFile(UploadedFile $file): void
    {
        $this->inspectAndValidateImage($file);
    }

    /**
     * Upload an UploadedFile instance with atomicity and compensation.
     *
     * @param  array<string, mixed>  $translations
     */
    public function upload(UploadedFile $file, array $translations = [], ?int $uploaderId = null): Media
    {
        $validated = $this->inspectAndValidateImage($file);

        $disk = (string) config('media.disk', 'public');
        $canonicalExt = $validated['ext'];
        $canonicalMime = $validated['mime'];
        $width = $validated['width'];
        $height = $validated['height'];
        $sizeBytes = $file->getSize();

        // Sanitize client original name for display, preventing path traversal
        $rawClientName = $file->getClientOriginalName();
        $safeClientName = basename(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $rawClientName));
        $safeClientName = preg_replace('/[^a-zA-Z0-9._\-\s]/u', '', $safeClientName) ?: 'image.'.$canonicalExt;

        $directory = 'media/'.date('Y/m');
        $safeFileName = Str::ulid().'.'.$canonicalExt;
        $storedPath = $directory.'/'.$safeFileName;

        // 1. Write file to configured filesystem disk
        $stored = Storage::disk($disk)->putFileAs($directory, $file, $safeFileName);
        if ($stored === false) {
            throw new DomainException("Failed to write media file to disk '{$disk}'.");
        }

        // 2. Persist database record inside transaction with compensation
        try {
            return DB::transaction(function () use (
                $disk,
                $storedPath,
                $safeClientName,
                $canonicalMime,
                $canonicalExt,
                $sizeBytes,
                $width,
                $height,
                $uploaderId,
                $translations
            ) {
                $media = Media::create([
                    'disk' => $disk,
                    'path' => $storedPath,
                    'file_name' => $safeClientName,
                    'mime_type' => $canonicalMime,
                    'extension' => $canonicalExt,
                    'size_bytes' => $sizeBytes,
                    'width' => $width,
                    'height' => $height,
                    'uploaded_by' => $uploaderId ?? auth()->id(),
                ]);

                // Save Vietnamese Translation
                $viAlt = trim((string) ($translations['vi']['alt_text'] ?? ''));
                $viCap = trim((string) ($translations['vi']['caption'] ?? ''));
                if ($viAlt !== '' || $viCap !== '') {
                    MediaTranslation::create([
                        'media_id' => $media->id,
                        'locale' => 'vi',
                        'alt_text' => $viAlt !== '' ? $viAlt : null,
                        'caption' => $viCap !== '' ? $viCap : null,
                    ]);
                }

                // Save English Translation
                $enAlt = trim((string) ($translations['en']['alt_text'] ?? ''));
                $enCap = trim((string) ($translations['en']['caption'] ?? ''));
                if ($enAlt !== '' || $enCap !== '') {
                    MediaTranslation::create([
                        'media_id' => $media->id,
                        'locale' => 'en',
                        'alt_text' => $enAlt !== '' ? $enAlt : null,
                        'caption' => $enCap !== '' ? $enCap : null,
                    ]);
                }

                return $media->fresh('translations');
            });
        } catch (Exception $e) {
            // Compensating cleanup on DB failure: delete newly created physical file
            Storage::disk($disk)->delete($storedPath);
            throw $e;
        }
    }

    /**
     * Process a file previously staged by Filament FileUpload component.
     *
     * @param  array<string, mixed>  $translations
     */
    public function createFromFilamentTemp(string $tempPath, array $translations = [], ?int $uploaderId = null): Media
    {
        $disk = (string) config('media.disk', 'public');
        if (! Storage::disk($disk)->exists($tempPath)) {
            throw new InvalidArgumentException("Staged file '{$tempPath}' does not exist on disk '{$disk}'.");
        }

        $fullPath = Storage::disk($disk)->path($tempPath);
        $file = new UploadedFile($fullPath, basename($tempPath), null, null, true);

        $media = $this->upload($file, $translations, $uploaderId);

        // Delete temporary staged file
        if ($tempPath !== $media->path) {
            Storage::disk($disk)->delete($tempPath);
        }

        return $media;
    }

    /**
     * Update editorial translations in place without changing physical file.
     *
     * @param  array<string, mixed>  $translations
     */
    public function updateTranslations(Media $media, array $translations): Media
    {
        return DB::transaction(function () use ($media, $translations) {
            // Update Vietnamese translation
            $viAlt = trim((string) ($translations['vi']['alt_text'] ?? ''));
            $viCap = trim((string) ($translations['vi']['caption'] ?? ''));
            MediaTranslation::updateOrCreate(
                ['media_id' => $media->id, 'locale' => 'vi'],
                [
                    'alt_text' => $viAlt !== '' ? $viAlt : null,
                    'caption' => $viCap !== '' ? $viCap : null,
                ]
            );

            // Update English translation
            $enAlt = trim((string) ($translations['en']['alt_text'] ?? ''));
            $enCap = trim((string) ($translations['en']['caption'] ?? ''));
            if ($enAlt !== '' || $enCap !== '') {
                MediaTranslation::updateOrCreate(
                    ['media_id' => $media->id, 'locale' => 'en'],
                    [
                        'alt_text' => $enAlt !== '' ? $enAlt : null,
                        'caption' => $enCap !== '' ? $enCap : null,
                    ]
                );
            }

            return $media->fresh('translations');
        });
    }

    /**
     * Delete an unreferenced media item.
     * Database deletion is authoritative. Physical cleanup occurs after DB commit.
     *
     * @return array{database_deleted: bool, physical_deleted: bool, error: ?string}
     */
    public function delete(Media $media): array
    {
        if ($this->referenceInspector->hasReferences($media)) {
            $count = $this->referenceInspector->getTotalReferencesCount($media);
            throw new DomainException("Cannot delete media because it is currently referenced across {$count} content relationship(s). Remove references first.");
        }

        $disk = $media->disk;
        $path = $media->path;
        $mediaId = $media->id;

        // 1. Authoritative DB deletion
        DB::transaction(function () use ($media) {
            $media->translations()->delete();
            $media->delete();
        });

        // 2. Physical file cleanup after commit
        $physicalDeleted = false;
        $cleanupError = null;

        try {
            if ($disk && $path && Storage::disk($disk)->exists($path)) {
                $deleted = Storage::disk($disk)->delete($path);
                if (! $deleted) {
                    $cleanupError = "Storage delete returned false for disk '{$disk}', path '{$path}'.";
                    Log::warning("Media [{$mediaId}] database record deleted, but physical file could not be deleted from storage.", [
                        'media_id' => $mediaId,
                        'disk' => $disk,
                        'path' => $path,
                    ]);
                } else {
                    $physicalDeleted = true;
                }
            } else {
                $physicalDeleted = true; // No file on disk to delete
            }
        } catch (Exception $e) {
            $cleanupError = $e->getMessage();
            Log::error("Media [{$mediaId}] physical cleanup encountered an exception after database deletion.", [
                'media_id' => $mediaId,
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }

        return [
            'database_deleted' => true,
            'physical_deleted' => $physicalDeleted,
            'error' => $cleanupError,
        ];
    }
}
