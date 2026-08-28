<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrainingCourses\Tables;

use App\Enums\ContentStatus;
use App\Models\TrainingCourse;
use App\Services\Training\TrainingCourseWriter;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TrainingCoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(TrainingCourse::query()->with(['translations', 'heroMedia'])->withCount('inquiries'))
            ->columns([
                TextColumn::make('title')
                    ->label('Tên khóa học (Tiếng Việt)')
                    ->getStateUsing(fn (TrainingCourse $record) => $record->translationFor('vi')?->title ?? '—')
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('translations', function ($q) use ($search) {
                            $q->where('title', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('tuition_fee')
                    ->label('Học phí')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((int) $state, 0, ',', '.').' ₫' : 'Liên hệ')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge(),

                IconColumn::make('is_featured')
                    ->label('Nổi bật')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->sortable(),

                TextColumn::make('inquiries_count')
                    ->label('Yêu cầu TV')
                    ->counts('inquiries')
                    ->badge()
                    ->color('info'),

                TextColumn::make('published_at')
                    ->label('Xuất bản')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options(ContentStatus::class),

                TernaryFilter::make('is_featured')
                    ->label('Khóa học nổi bật'),
            ])
            ->actions([
                EditAction::make(),

                Action::make('archive')
                    ->label('Lưu trữ')
                    ->icon(Heroicon::OutlinedArchiveBox)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (TrainingCourse $record) => $record->status !== ContentStatus::ARCHIVED)
                    ->action(fn (TrainingCourse $record, TrainingCourseWriter $writer) => $writer->archive($record)),

                Action::make('restore')
                    ->label('Khôi phục nháp')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (TrainingCourse $record) => $record->status === ContentStatus::ARCHIVED)
                    ->action(fn (TrainingCourse $record, TrainingCourseWriter $writer) => $writer->restore($record)),

                DeleteAction::make()
                    ->visible(fn (TrainingCourse $record) => $record->status === ContentStatus::DRAFT && $record->inquiries()->count() === 0)
                    ->action(fn (TrainingCourse $record, TrainingCourseWriter $writer) => $writer->delete($record)),
            ]);
    }
}
