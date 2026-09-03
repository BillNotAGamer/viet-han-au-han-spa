<?php

declare(strict_types=1);

namespace App\Filament\Resources\Bookings\Tables;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Services\Booking\BookingWorkflow;
use Filament\Actions\Action;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Booking::query()->with(['service.translations', 'servicePrice.translations']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->label('Mã')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('created_at')
                    ->label('Ngày gửi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Khách hàng')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Số điện thoại')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('service')
                    ->label('Dịch vụ')
                    ->getStateUsing(fn (Booking $record) => $record->service_name_snapshot ?: $record->service?->translationFor('vi')?->name ?? '—'),

                TextColumn::make('preferred_date')
                    ->label('Ngày mong muốn')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('preferred_time')
                    ->label('Giờ')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options(BookingStatus::class),

                SelectFilter::make('service_id')
                    ->label('Dịch vụ')
                    ->relationship('service', 'id')
                    ->getOptionLabelFromRecordUsing(fn (Service $record) => $record->translationFor('vi')?->name ?? "Service #{$record->id}"),

                Filter::make('preferred_date')
                    ->label('Ngày mong muốn')
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['date'] ?? null, fn (Builder $query, string $date) => $query->whereDate('preferred_date', $date)))
                    ->schema([
                        TextInput::make('date')
                            ->label('Ngày')
                            ->placeholder('YYYY-MM-DD'),
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
                    ->visible(fn (Booking $record) => $record->status === BookingStatus::NEW)
                    ->form([
                        TextInput::make('admin_note')->label('Ghi chú cuộc gọi (Admin Note)'),
                    ])
                    ->action(fn (Booking $record, array $data, BookingWorkflow $workflow) => $workflow->markAsContacted($record, $data['admin_note'] ?? null)),

                Action::make('markConfirmed')
                    ->label('Xác nhận lịch')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Booking $record) => in_array($record->status, [BookingStatus::NEW, BookingStatus::CONTACTED], true))
                    ->form([
                        TextInput::make('admin_note')->label('Ghi chú xác nhận (Admin Note)'),
                    ])
                    ->action(fn (Booking $record, array $data, BookingWorkflow $workflow) => $workflow->markAsConfirmed($record, $data['admin_note'] ?? null)),

                Action::make('markCancelled')
                    ->label('Hủy yêu cầu')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Booking $record) => ! in_array($record->status, [BookingStatus::COMPLETED, BookingStatus::CANCELLED, BookingStatus::NO_SHOW], true))
                    ->form([
                        TextInput::make('admin_note')->label('Lý do hủy (Admin Note)'),
                    ])
                    ->action(fn (Booking $record, array $data, BookingWorkflow $workflow) => $workflow->markAsCancelled($record, $data['admin_note'] ?? null)),

                Action::make('editNote')
                    ->label('Ghi chú')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->color('info')
                    ->fillForm(fn (Booking $record) => ['admin_note' => $record->admin_note])
                    ->form([
                        Textarea::make('admin_note')->label('Ghi chú nội bộ')->required(),
                    ])
                    ->action(fn (Booking $record, array $data, BookingWorkflow $workflow) => $workflow->updateAdminNote($record, (string) $data['admin_note'])),

                RestoreAction::make(),
            ]);
    }
}
