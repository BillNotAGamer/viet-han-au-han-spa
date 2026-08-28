<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrainingCourses\Pages;

use App\Filament\Resources\TrainingCourses\TrainingCourseResource;
use App\Models\TrainingCourse;
use App\Services\Training\TrainingCourseWriter;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTrainingCourse extends EditRecord
{
    protected static string $resource = TrainingCourseResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var TrainingCourse $course */
        $course = $this->getRecord();
        $course->loadMissing(['translations', 'media']);

        $viTranslation = $course->translationFor('vi');
        $enTranslation = $course->translationFor('en');

        if ($viTranslation) {
            $data['vi'] = [
                'title' => $viTranslation->title,
                'slug' => $viTranslation->slug,
                'excerpt' => $viTranslation->excerpt,
                'content' => $viTranslation->content,
                'duration_display' => $viTranslation->duration_display,
                'schedule_display' => $viTranslation->schedule_display,
                'target_audience' => $viTranslation->target_audience,
                'curriculum_modules' => $viTranslation->curriculum_modules,
                'benefits' => $viTranslation->benefits,
                'faqs' => $viTranslation->faqs,
                'seo_title' => $viTranslation->seo_title,
                'seo_description' => $viTranslation->seo_description,
            ];
        }

        if ($enTranslation) {
            $data['en'] = [
                'title' => $enTranslation->title,
                'slug' => $enTranslation->slug,
                'excerpt' => $enTranslation->excerpt,
                'content' => $enTranslation->content,
                'duration_display' => $enTranslation->duration_display,
                'schedule_display' => $enTranslation->schedule_display,
                'target_audience' => $enTranslation->target_audience,
                'curriculum_modules' => $enTranslation->curriculum_modules,
                'benefits' => $enTranslation->benefits,
                'faqs' => $enTranslation->faqs,
                'seo_title' => $enTranslation->seo_title,
                'seo_description' => $enTranslation->seo_description,
            ];
        }

        $data['gallery_media_ids'] = $course->media()->pluck('media.id')->toArray();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var TrainingCourse $record */
        return app(TrainingCourseWriter::class)->update($record, $data);
    }
}
