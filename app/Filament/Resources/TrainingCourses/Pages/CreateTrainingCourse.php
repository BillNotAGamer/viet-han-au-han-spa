<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrainingCourses\Pages;

use App\Filament\Resources\TrainingCourses\TrainingCourseResource;
use App\Services\Training\TrainingCourseWriter;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTrainingCourse extends CreateRecord
{
    protected static string $resource = TrainingCourseResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(TrainingCourseWriter::class)->create($data);
    }
}
