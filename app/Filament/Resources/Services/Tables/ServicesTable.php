<?php

declare(strict_types=1);

namespace App\Filament\Resources\Services\Tables;

use App\Enums\ContentStatus;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\ServiceCatalog\ServiceWriter;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn () => Service::with(['translations', 'category.translations', 'prices']))
            ->columns([
                TextColumn::make('name')
                    ->label('Tên dịch vụ (Tiếng Việt)')
                    ->getStateUsing(fn (Service $record) => $record->translationFor('vi')?->name ?? '—')
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('translations', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('category')
                    ->label('Danh mục')
                    ->getStateUsing(fn (Service $record) => $record->category?->translationFor('vi')?->name ?? '—'),

                TextColumn::make('pricing')
                    ->label('Mức giá (VND)')
                    ->getStateUsing(function (Service $record) {
                        $activePrices = $record->prices->where('is_active', true);
                        if ($activePrices->isEmpty()) {
                            return 'Liên hệ';
                        }
                        $min = $activePrices->min('price_amount');
                        $max = $activePrices->max('price_amount');
                        if ($min === $max) {
                            return number_format((float) $min).' đ';
                        }

                        return number_format((float) $min).' - '.number_format((float) $max).' đ';
                    }),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->label('Nổi bật')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Cập nhật lúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options(ContentStatus::class),

                SelectFilter::make('service_category_id')
                    ->label('Danh mục')
                    ->options(function () {
                        return ServiceCategory::with('translations')->get()
                            ->mapWithKeys(fn ($c) => [$c->id => $c->translationFor('vi')?->name ?? 'Category #'.$c->id]);
                    }),

                TernaryFilter::make('is_featured')
                    ->label('Nổi bật'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('archive')
                    ->label('Lưu trữ')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Service $record) => $record->status !== ContentStatus::ARCHIVED)
                    ->action(function (Service $record) {
                        app(ServiceWriter::class)->archive($record);
                        Notification::make()->success()->title('Dịch vụ đã được chuyển vào lưu trữ.')->send();
                    }),
                Action::make('restore')
                    ->label('Khôi phục nháp')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->visible(fn (Service $record) => $record->status === ContentStatus::ARCHIVED)
                    ->action(function (Service $record) {
                        app(ServiceWriter::class)->restore($record);
                        Notification::make()->success()->title('Dịch vụ đã được khôi phục về bản nháp.')->send();
                    }),
                DeleteAction::make()
                    ->visible(fn (Service $record) => $record->status === ContentStatus::DRAFT && $record->bookings()->count() === 0)
                    ->before(function (DeleteAction $action, Service $record) {
                        if ($record->bookings()->count() > 0) {
                            Notification::make()
                                ->danger()
                                ->title('Không thể xóa')
                                ->body('Dịch vụ đã có dữ liệu đặt lịch, không thể xóa.')
                                ->send();

                            $action->halt();
                        }
                    })
                    ->using(function (Service $record) {
                        app(ServiceWriter::class)->delete($record);
                    }),
            ]);
    }
}
