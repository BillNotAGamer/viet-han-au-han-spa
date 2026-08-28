<?php

declare(strict_types=1);

namespace App\Filament\Resources\Media\Tables;

use App\Models\Media;
use App\Services\Media\MediaReferenceInspector;
use App\Services\Media\MediaUploadService;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Media::query()->with(['translations', 'uploader']))
            ->columns([
                ImageColumn::make('path')
                    ->label('Xem trước')
                    ->disk(fn (Media $record) => $record->disk)
                    ->square(),

                TextColumn::make('file_name')
                    ->label('Tên tệp')
                    ->searchable()
                    ->weight('bold')
                    ->limit(30),

                TextColumn::make('mime_type')
                    ->label('Định dạng')
                    ->badge(),

                TextColumn::make('size_bytes')
                    ->label('Dung lượng')
                    ->formatStateUsing(fn ($state) => number_format(((int) $state) / 1024, 1).' KB')
                    ->sortable(),

                TextColumn::make('dimensions')
                    ->label('Kích thước')
                    ->getStateUsing(fn (Media $record) => ($record->width && $record->height) ? "{$record->width}×{$record->height}" : '—'),

                TextColumn::make('references_count')
                    ->label('Liên kết')
                    ->getStateUsing(fn (Media $record, MediaReferenceInspector $inspector) => $inspector->getTotalReferencesCount($record))
                    ->badge()
                    ->color(fn (int $state) => $state > 0 ? 'warning' : 'success'),

                TextColumn::make('uploader.name')
                    ->label('Người tải lên')
                    ->placeholder('Hệ thống')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('mime_type')
                    ->label('Định dạng tệp')
                    ->options([
                        'image/jpeg' => 'JPEG (image/jpeg)',
                        'image/png' => 'PNG (image/png)',
                        'image/webp' => 'WebP (image/webp)',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->visible(fn (Media $record, MediaReferenceInspector $inspector) => ! $inspector->hasReferences($record))
                    ->action(function (Media $record, MediaUploadService $service) {
                        $res = $service->delete($record);
                        if (! $res['physical_deleted']) {
                            Notification::make()
                                ->warning()
                                ->title('Đã xóa dữ liệu, cần dọn dẹp tệp vật lý')
                                ->body('Bản ghi cơ sở dữ liệu đã được xóa an toàn, nhưng tệp tin vật lý trên ổ đĩa lưu trữ chưa thể xóa tự động.')
                                ->send();
                        } else {
                            Notification::make()
                                ->success()
                                ->title('Đã xóa tệp media thành công')
                                ->send();
                        }
                    }),
            ]);
    }
}
