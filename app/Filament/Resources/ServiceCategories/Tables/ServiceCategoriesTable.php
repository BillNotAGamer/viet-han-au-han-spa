<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServiceCategories\Tables;

use App\Enums\ContentStatus;
use App\Models\ServiceCategory;
use App\Services\ServiceCatalog\ServiceCategoryWriter;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ServiceCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn () => ServiceCategory::with('translations')->withCount('services'))
            ->columns([
                TextColumn::make('name')
                    ->label('Tên danh mục (Tiếng Việt)')
                    ->getStateUsing(fn (ServiceCategory $record) => $record->translationFor('vi')?->name ?? '—')
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('translations', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->sortable(),

                TextColumn::make('services_count')
                    ->label('Số dịch vụ')
                    ->counts('services')
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
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, ServiceCategory $record) {
                        if ($record->services()->count() > 0) {
                            Notification::make()
                                ->danger()
                                ->title('Không thể xóa')
                                ->body('Danh mục đang chứa dịch vụ, không thể xóa.')
                                ->send();

                            $action->halt();
                        }
                    })
                    ->using(function (ServiceCategory $record) {
                        app(ServiceCategoryWriter::class)->delete($record);
                    }),
            ]);
    }
}
