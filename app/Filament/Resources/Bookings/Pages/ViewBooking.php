<?php

declare(strict_types=1);

namespace App\Filament\Resources\Bookings\Pages;

use App\Enums\BookingGoogleSheetsSyncStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Services\Booking\BookingGoogleSheetsSyncProcessor;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reconcileGoogleSheets')
                ->label('Kiểm tra đồng bộ')
                ->visible(fn (): bool => $this->hasActionableSyncRecord())
                ->action(function (BookingGoogleSheetsSyncProcessor $processor): void {
                    Gate::authorize('reconcileGoogleSheets', $this->record);
                    $sync = $this->record->googleSheetsSync()->firstOrFail();

                    try {
                        $result = $processor->reconcile($sync);

                        Log::notice('Administrator reconciled a Booking Google Sheets sync record.', [
                            'user_id' => auth()->id(),
                            'reference' => $sync->booking_reference,
                            'result' => $result->status->value,
                        ]);

                        $this->sendSyncNotification($result->status, 'Đã kiểm tra Google Sheets.');
                        $this->record->unsetRelation('googleSheetsSync');
                    } catch (\Throwable $exception) {
                        Log::error('Administrator Google Sheets reconciliation failed unexpectedly.', [
                            'user_id' => auth()->id(),
                            'reference' => $sync->booking_reference,
                            'exception' => $exception::class,
                        ]);
                        Notification::make()
                            ->title('Không thể kiểm tra Google Sheets.')
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('retryGoogleSheets')
                ->label('Thử đồng bộ lại')
                ->requiresConfirmation()
                ->modalDescription('Chỉ thực hiện khi trạng thái cho phép thử lại an toàn. Bản ghi không chắc chắn phải được kiểm tra trước và không thể nối lại từ thao tác này.')
                ->visible(fn (): bool => $this->hasSafelyRetryableSyncRecord())
                ->action(function (BookingGoogleSheetsSyncProcessor $processor): void {
                    Gate::authorize('retryGoogleSheets', $this->record);
                    $sync = $this->record->googleSheetsSync()->firstOrFail();

                    try {
                        $result = $processor->retry($sync);

                        Log::notice('Administrator retried a Booking Google Sheets sync record.', [
                            'user_id' => auth()->id(),
                            'reference' => $sync->booking_reference,
                            'result' => $result->status->value,
                        ]);

                        $this->sendSyncNotification($result->status, 'Đã xử lý yêu cầu thử lại.');
                        $this->record->unsetRelation('googleSheetsSync');
                    } catch (\Throwable $exception) {
                        Log::error('Administrator Google Sheets retry failed unexpectedly.', [
                            'user_id' => auth()->id(),
                            'reference' => $sync->booking_reference,
                            'exception' => $exception::class,
                        ]);
                        Notification::make()
                            ->title('Không thể thử đồng bộ lại.')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    protected function hasActionableSyncRecord(): bool
    {
        return (bool) config('booking.google_sheets.enabled', false)
            && Schema::hasTable('booking_google_sheets_syncs')
            && $this->record->googleSheetsSync !== null
            && $this->record->googleSheetsSync->status !== BookingGoogleSheetsSyncStatus::PROCESSING;
    }

    protected function hasSafelyRetryableSyncRecord(): bool
    {
        return (bool) config('booking.google_sheets.enabled', false)
            && Schema::hasTable('booking_google_sheets_syncs')
            && $this->record->googleSheetsSync?->canRetrySafely() === true;
    }

    protected function sendSyncNotification(BookingGoogleSheetsSyncStatus $status, string $title): void
    {
        $notification = Notification::make()
            ->title($title)
            ->body('Trạng thái hiện tại: '.$status->value);

        (match ($status) {
            BookingGoogleSheetsSyncStatus::SYNCED => $notification->success(),
            BookingGoogleSheetsSyncStatus::FAILED => $notification->danger(),
            default => $notification->warning(),
        })->send();
    }
}
