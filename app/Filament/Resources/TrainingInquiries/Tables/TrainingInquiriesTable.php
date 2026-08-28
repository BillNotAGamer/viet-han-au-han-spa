<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrainingInquiries\Tables;

use App\Enums\TrainingInquiryStatus;
use App\Models\TrainingCourse;
use App\Models\TrainingInquiry;
use App\Services\Training\TrainingInquiryWorkflow;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TrainingInquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(TrainingInquiry::query()->with(['course.translations']))
            ->columns([
                TextColumn::make('reference')
                    ->label('Mã tham chiếu')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('customer_name')
                    ->label('Học viên')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Số điện thoại')
                    ->searchable(),

                TextColumn::make('course')
                    ->label('Khóa học quan tâm')
                    ->getStateUsing(fn (TrainingInquiry $record) => $record->course?->translationFor('vi')?->title ?? '—'),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge(),

                TextColumn::make('locale')
                    ->label('Ngôn ngữ')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Ngày gửi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('contacted_at')
                    ->label('Đã liên hệ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options(TrainingInquiryStatus::class),

                SelectFilter::make('training_course_id')
                    ->label('Khóa học')
                    ->relationship('course', 'id')
                    ->getOptionLabelFromRecordUsing(fn (TrainingCourse $record) => $record->translationFor('vi')?->title ?? "Course #{$record->id}"),

                SelectFilter::make('locale')
                    ->label('Ngôn ngữ')
                    ->options([
                        'vi' => 'Tiếng Việt (vi)',
                        'en' => 'English (en)',
                    ]),

                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),

                Action::make('markContacted')
                    ->label('Đã liên hệ')
                    ->icon(Heroicon::OutlinedPhone)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (TrainingInquiry $record) => $record->status === TrainingInquiryStatus::NEW)
                    ->form([
                        TextInput::make('admin_note')->label('Ghi chú cuộc gọi (Admin Note)'),
                    ])
                    ->action(function (TrainingInquiry $record, array $data, TrainingInquiryWorkflow $workflow) {
                        $workflow->markAsContacted($record, $data['admin_note'] ?? null);
                    }),

                Action::make('markEnrolled')
                    ->label('Đã đăng ký')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (TrainingInquiry $record) => $record->status === TrainingInquiryStatus::CONTACTED)
                    ->form([
                        TextInput::make('admin_note')->label('Ghi chú nhập học (Admin Note)'),
                    ])
                    ->action(function (TrainingInquiry $record, array $data, TrainingInquiryWorkflow $workflow) {
                        $workflow->markAsEnrolled($record, $data['admin_note'] ?? null);
                    }),

                Action::make('markClosed')
                    ->label('Đóng')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (TrainingInquiry $record) => in_array($record->status, [TrainingInquiryStatus::NEW, TrainingInquiryStatus::CONTACTED], true))
                    ->form([
                        TextInput::make('admin_note')->label('Lý do đóng (Admin Note)'),
                    ])
                    ->action(function (TrainingInquiry $record, array $data, TrainingInquiryWorkflow $workflow) {
                        $workflow->markAsClosed($record, $data['admin_note'] ?? null);
                    }),

                Action::make('editNote')
                    ->label('Ghi chú')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->color('info')
                    ->fillForm(fn (TrainingInquiry $record) => ['admin_note' => $record->admin_note])
                    ->form([
                        Textarea::make('admin_note')->label('Ghi chú nội bộ')->required(),
                    ])
                    ->action(function (TrainingInquiry $record, array $data, TrainingInquiryWorkflow $workflow) {
                        $workflow->updateAdminNote($record, (string) $data['admin_note']);
                    }),

                DeleteAction::make(),
                RestoreAction::make(),
            ]);
    }
}
