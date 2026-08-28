<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Tables;

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Services\Pages\PageWriter;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Page::query()->with('translations')->withCount('media'))
            ->columns([
                TextColumn::make('key')
                    ->label('Mã trang (Key)')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('title')
                    ->label('Tiêu đề (Tiếng Việt)')
                    ->getStateUsing(fn (Page $record) => $record->translationFor('vi')?->title ?? '—')
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('translations', function ($q) use ($search) {
                            $q->where('title', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge(),

                TextColumn::make('media_count')
                    ->label('Số ảnh')
                    ->counts('media')
                    ->badge()
                    ->color('info'),

                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options(ContentStatus::class),
            ])
            ->actions([
                EditAction::make(),

                Action::make('archive')
                    ->label('Lưu trữ')
                    ->icon(Heroicon::OutlinedArchiveBox)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Page $record) => $record->status !== ContentStatus::ARCHIVED)
                    ->action(fn (Page $record, PageWriter $writer) => $writer->archive($record)),

                Action::make('restore')
                    ->label('Khôi phục nháp')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Page $record) => $record->status === ContentStatus::ARCHIVED)
                    ->action(fn (Page $record, PageWriter $writer) => $writer->restore($record)),

                DeleteAction::make()
                    ->visible(fn (Page $record) => $record->status === ContentStatus::DRAFT)
                    ->action(fn (Page $record, PageWriter $writer) => $writer->delete($record)),
            ]);
    }
}
