<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\ContentStatus;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\Blog\PostWriter;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Post::query()->with(['translations', 'category.translations', 'author', 'heroMedia']))
            ->columns([
                TextColumn::make('title')
                    ->label('Tiêu đề bài viết (Tiếng Việt)')
                    ->getStateUsing(fn (Post $record) => $record->translationFor('vi')?->title ?? '—')
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('translations', function ($q) use ($search) {
                            $q->where('title', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('category')
                    ->label('Danh mục')
                    ->getStateUsing(fn (Post $record) => $record->category?->translationFor('vi')?->name ?? '—'),

                TextColumn::make('author.name')
                    ->label('Tác giả')
                    ->placeholder('Chưa có tác giả'),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge(),

                IconColumn::make('is_featured')
                    ->label('Nổi bật')
                    ->boolean(),

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

                SelectFilter::make('post_category_id')
                    ->label('Danh mục')
                    ->relationship('category', 'id')
                    ->getOptionLabelFromRecordUsing(fn (PostCategory $cat) => $cat->translationFor('vi')?->name ?? "Category #{$cat->id}"),

                SelectFilter::make('author_id')
                    ->label('Tác giả')
                    ->relationship('author', 'name'),

                TernaryFilter::make('is_featured')
                    ->label('Bài viết nổi bật'),
            ])
            ->actions([
                EditAction::make(),

                Action::make('archive')
                    ->label('Lưu trữ')
                    ->icon(Heroicon::OutlinedArchiveBox)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Post $record) => $record->status !== ContentStatus::ARCHIVED)
                    ->action(fn (Post $record, PostWriter $writer) => $writer->archive($record)),

                Action::make('restore')
                    ->label('Khôi phục nháp')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Post $record) => $record->status === ContentStatus::ARCHIVED)
                    ->action(fn (Post $record, PostWriter $writer) => $writer->restore($record)),

                DeleteAction::make()
                    ->visible(fn (Post $record) => $record->status === ContentStatus::DRAFT)
                    ->action(fn (Post $record, PostWriter $writer) => $writer->delete($record)),
            ]);
    }
}
