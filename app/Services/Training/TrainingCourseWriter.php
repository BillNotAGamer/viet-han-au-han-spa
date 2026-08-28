<?php

declare(strict_types=1);

namespace App\Services\Training;

use App\Enums\ContentStatus;
use App\Models\TrainingCourse;
use App\Models\TrainingCourseTranslation;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrainingCourseWriter
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): TrainingCourse
    {
        return DB::transaction(function () use ($data) {
            $status = $data['status'] instanceof ContentStatus
                ? $data['status']
                : ContentStatus::from((string) ($data['status'] ?? ContentStatus::DRAFT->value));

            $publishedAt = ! empty($data['published_at']) ? Carbon::parse($data['published_at']) : null;
            if ($status === ContentStatus::PUBLISHED && $publishedAt === null) {
                $publishedAt = now();
            }

            $course = TrainingCourse::create([
                'hero_media_id' => ! empty($data['hero_media_id']) ? (int) $data['hero_media_id'] : null,
                'tuition_fee' => isset($data['tuition_fee']) && $data['tuition_fee'] !== '' ? (int) $data['tuition_fee'] : null,
                'status' => $status,
                'is_featured' => (bool) ($data['is_featured'] ?? false),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'published_at' => $publishedAt,
            ]);

            // Vietnamese Translation (Required)
            $viTitle = trim((string) ($data['vi']['title'] ?? ''));
            $viSlug = trim((string) ($data['vi']['slug'] ?? ''));
            if ($viSlug === '') {
                $viSlug = Str::slug($viTitle);
            }

            TrainingCourseTranslation::create([
                'training_course_id' => $course->id,
                'locale' => 'vi',
                'title' => $viTitle,
                'slug' => $viSlug,
                'excerpt' => $data['vi']['excerpt'] ?? null,
                'content' => $data['vi']['content'] ?? null,
                'duration_display' => $data['vi']['duration_display'] ?? null,
                'schedule_display' => $data['vi']['schedule_display'] ?? null,
                'target_audience' => $data['vi']['target_audience'] ?? null,
                'curriculum_modules' => $this->sanitizeJsonRepeater($data['vi']['curriculum_modules'] ?? null),
                'benefits' => $this->sanitizeJsonRepeater($data['vi']['benefits'] ?? null),
                'faqs' => $this->sanitizeJsonRepeater($data['vi']['faqs'] ?? null),
                'seo_title' => $data['vi']['seo_title'] ?? null,
                'seo_description' => $data['vi']['seo_description'] ?? null,
            ]);

            // English Translation (Optional)
            $enTitle = trim((string) ($data['en']['title'] ?? ''));
            if ($enTitle !== '') {
                $enSlug = trim((string) ($data['en']['slug'] ?? ''));
                if ($enSlug === '') {
                    $enSlug = Str::slug($enTitle);
                }

                TrainingCourseTranslation::create([
                    'training_course_id' => $course->id,
                    'locale' => 'en',
                    'title' => $enTitle,
                    'slug' => $enSlug,
                    'excerpt' => $data['en']['excerpt'] ?? null,
                    'content' => $data['en']['content'] ?? null,
                    'duration_display' => $data['en']['duration_display'] ?? null,
                    'schedule_display' => $data['en']['schedule_display'] ?? null,
                    'target_audience' => $data['en']['target_audience'] ?? null,
                    'curriculum_modules' => $this->sanitizeJsonRepeater($data['en']['curriculum_modules'] ?? null),
                    'benefits' => $this->sanitizeJsonRepeater($data['en']['benefits'] ?? null),
                    'faqs' => $this->sanitizeJsonRepeater($data['en']['faqs'] ?? null),
                    'seo_title' => $data['en']['seo_title'] ?? null,
                    'seo_description' => $data['en']['seo_description'] ?? null,
                ]);
            }

            // Sync Gallery Media
            $this->syncGalleryMedia($course, $data['gallery_media_ids'] ?? []);

            return $course->fresh(['translations', 'media', 'heroMedia']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(TrainingCourse $course, array $data): TrainingCourse
    {
        return DB::transaction(function () use ($course, $data) {
            $status = $data['status'] instanceof ContentStatus
                ? $data['status']
                : ContentStatus::from((string) ($data['status'] ?? $course->status->value));

            $publishedAt = $course->published_at;
            if (isset($data['published_at'])) {
                $publishedAt = ! empty($data['published_at']) ? Carbon::parse($data['published_at']) : null;
            } elseif ($status === ContentStatus::PUBLISHED && $publishedAt === null) {
                $publishedAt = now();
            }

            $course->update([
                'hero_media_id' => ! empty($data['hero_media_id']) ? (int) $data['hero_media_id'] : null,
                'tuition_fee' => isset($data['tuition_fee']) && $data['tuition_fee'] !== '' ? (int) $data['tuition_fee'] : null,
                'status' => $status,
                'is_featured' => (bool) ($data['is_featured'] ?? $course->is_featured),
                'sort_order' => (int) ($data['sort_order'] ?? $course->sort_order),
                'published_at' => $publishedAt,
            ]);

            // Update Vietnamese Translation in place
            $viTitle = trim((string) ($data['vi']['title'] ?? ''));
            $viSlug = trim((string) ($data['vi']['slug'] ?? ''));
            if ($viSlug === '') {
                $viSlug = Str::slug($viTitle);
            }

            TrainingCourseTranslation::updateOrCreate(
                [
                    'training_course_id' => $course->id,
                    'locale' => 'vi',
                ],
                [
                    'title' => $viTitle,
                    'slug' => $viSlug,
                    'excerpt' => $data['vi']['excerpt'] ?? null,
                    'content' => $data['vi']['content'] ?? null,
                    'duration_display' => $data['vi']['duration_display'] ?? null,
                    'schedule_display' => $data['vi']['schedule_display'] ?? null,
                    'target_audience' => $data['vi']['target_audience'] ?? null,
                    'curriculum_modules' => $this->sanitizeJsonRepeater($data['vi']['curriculum_modules'] ?? null),
                    'benefits' => $this->sanitizeJsonRepeater($data['vi']['benefits'] ?? null),
                    'faqs' => $this->sanitizeJsonRepeater($data['vi']['faqs'] ?? null),
                    'seo_title' => $data['vi']['seo_title'] ?? null,
                    'seo_description' => $data['vi']['seo_description'] ?? null,
                ]
            );

            // Update English Translation in place
            $enTitle = trim((string) ($data['en']['title'] ?? ''));
            if ($enTitle !== '') {
                $enSlug = trim((string) ($data['en']['slug'] ?? ''));
                if ($enSlug === '') {
                    $enSlug = Str::slug($enTitle);
                }

                TrainingCourseTranslation::updateOrCreate(
                    [
                        'training_course_id' => $course->id,
                        'locale' => 'en',
                    ],
                    [
                        'title' => $enTitle,
                        'slug' => $enSlug,
                        'excerpt' => $data['en']['excerpt'] ?? null,
                        'content' => $data['en']['content'] ?? null,
                        'duration_display' => $data['en']['duration_display'] ?? null,
                        'schedule_display' => $data['en']['schedule_display'] ?? null,
                        'target_audience' => $data['en']['target_audience'] ?? null,
                        'curriculum_modules' => $this->sanitizeJsonRepeater($data['en']['curriculum_modules'] ?? null),
                        'benefits' => $this->sanitizeJsonRepeater($data['en']['benefits'] ?? null),
                        'faqs' => $this->sanitizeJsonRepeater($data['en']['faqs'] ?? null),
                        'seo_title' => $data['en']['seo_title'] ?? null,
                        'seo_description' => $data['en']['seo_description'] ?? null,
                    ]
                );
            }

            // Sync Gallery Media
            $this->syncGalleryMedia($course, $data['gallery_media_ids'] ?? []);

            return $course->fresh(['translations', 'media', 'heroMedia']);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $items
     * @return array<int, array<string, mixed>>|null
     */
    protected function sanitizeJsonRepeater(?array $items): ?array
    {
        if (empty($items)) {
            return null;
        }

        $filtered = array_filter($items, function ($item) {
            if (! is_array($item)) {
                return false;
            }
            foreach ($item as $val) {
                if (filled($val)) {
                    return true;
                }
            }

            return false;
        });

        return ! empty($filtered) ? array_values($filtered) : null;
    }

    /**
     * @param  array<int, int|string>  $mediaIds
     */
    protected function syncGalleryMedia(TrainingCourse $course, array $mediaIds): void
    {
        $syncData = [];
        $order = 0;
        foreach (array_unique($mediaIds) as $mediaId) {
            if (! empty($mediaId)) {
                $syncData[(int) $mediaId] = [
                    'sort_order' => $order++,
                    'created_at' => now(),
                ];
            }
        }

        $course->media()->sync($syncData);
    }

    public function archive(TrainingCourse $course): void
    {
        $course->update(['status' => ContentStatus::ARCHIVED]);
    }

    public function restore(TrainingCourse $course): void
    {
        $course->update([
            'status' => ContentStatus::DRAFT,
            'published_at' => null, // Cleared on restore to prevent immediate republishing
        ]);
    }

    public function delete(TrainingCourse $course): void
    {
        if ($course->status !== ContentStatus::DRAFT) {
            throw new DomainException('Only draft courses can be deleted.');
        }

        if ($course->inquiries()->exists()) {
            throw new DomainException('Cannot delete course because it has associated inquiries.');
        }

        DB::transaction(function () use ($course) {
            $course->media()->detach();
            $course->translations()->delete();
            $course->delete();
        });
    }
}
