<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrainingCourses;

use App\Filament\Resources\TrainingCourses\Pages\CreateTrainingCourse;
use App\Filament\Resources\TrainingCourses\Pages\EditTrainingCourse;
use App\Filament\Resources\TrainingCourses\Pages\ListTrainingCourses;
use App\Filament\Resources\TrainingCourses\Schemas\TrainingCourseForm;
use App\Filament\Resources\TrainingCourses\Tables\TrainingCoursesTable;
use App\Models\TrainingCourse;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TrainingCourseResource extends Resource
{
    protected static ?string $model = TrainingCourse::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|UnitEnum|null $navigationGroup = 'Đào tạo';

    protected static ?string $navigationLabel = 'Khóa học';

    protected static ?string $modelLabel = 'Khóa học';

    protected static ?string $pluralModelLabel = 'Khóa học';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return TrainingCourseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingCoursesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrainingCourses::route('/'),
            'create' => CreateTrainingCourse::route('/create'),
            'edit' => EditTrainingCourse::route('/{record}/edit'),
        ];
    }
}
