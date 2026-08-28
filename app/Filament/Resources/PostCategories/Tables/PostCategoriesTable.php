<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostCategories\Tables;

use App\Enums\ContentStatus;
use App\Models\PostCategory;
use App\Services\Blog\PostCategoryWriter;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(PostCategory::query()->with('translations')->withCount('posts'))
            ->columns([
                TextColumn::make('name')
                    ->label('Tên danh mục (Tiếng Việt)')
                    ->getStateUsing(fn (PostCategory $record) => $record->translationFor('vi')?->name ?? '—')
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('translations', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge(),

                TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->sortable(),

                TextColumn::make('posts_count')
                    ->label('Số bài viết')
                    ->counts('posts')
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
                DeleteAction::make()
                    ->visible(fn (PostCategory $record) => $record->posts()->count() === 0)
                    ->action(fn (PostCategory $record, PostCategoryWriter $writer) => $writer->delete($record)),
            ]);
    }
}
