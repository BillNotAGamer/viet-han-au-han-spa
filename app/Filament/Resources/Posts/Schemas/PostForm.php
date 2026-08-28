<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\PostCategory;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Cấu hình bài viết (Post Settings)')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('post_category_id')
                            ->label('Danh mục bài viết (Category)')
                            ->relationship('category', 'id')
                            ->getOptionLabelFromRecordUsing(fn (PostCategory $cat) => $cat->translationFor('vi')?->name ?? "Category #{$cat->id}")
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('author_id')
                            ->label('Tác giả (Author)')
                            ->relationship('author', 'name')
                            ->default(fn () => auth()->id())
                            ->searchable()
                            ->preload()
                            ->placeholder('Chọn tác giả hoặc để mặc định'),

                        Select::make('status')
                            ->label('Trạng thái (Status)')
                            ->options(ContentStatus::class)
                            ->default(ContentStatus::DRAFT)
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        DateTimePicker::make('published_at')
                            ->label('Thời gian xuất bản (Published At UTC)')
                            ->helperText('Tự động ghi nhận thời điểm hiện tại nếu để trống khi xuất bản.'),

                        Toggle::make('is_featured')
                            ->label('Bài viết nổi bật (Featured)')
                            ->inline(false),
                    ]),

                    Grid::make(2)->schema([
                        Select::make('hero_media_id')
                            ->label('Hình ảnh đại diện (Hero Media)')
                            ->options(fn () => Media::pluck('file_name', 'id'))
                            ->searchable()
                            ->placeholder('Chọn ảnh đại diện có sẵn'),

                        Select::make('post_media_ids')
                            ->label('Thư viện ảnh bài viết (Post Media)')
                            ->options(fn () => Media::pluck('file_name', 'id'))
                            ->multiple()
                            ->searchable()
                            ->placeholder('Chọn các ảnh bài viết có sẵn'),
                    ]),
                ]),

            Tabs::make('Bản dịch (Translations)')
                ->tabs([
                    Tab::make('Tiếng Việt (Canonical)')
                        ->schema([
                            TextInput::make('vi.title')
                                ->label('Tiêu đề bài viết (VI)')
                                ->required(),

                            TextInput::make('vi.slug')
                                ->label('Đường dẫn tĩnh / Slug (VI)')
                                ->helperText('Tự động sinh từ tiêu đề nếu để trống.'),

                            Textarea::make('vi.excerpt')
                                ->label('Tóm tắt bài viết (Excerpt)')
                                ->rows(3),

                            RichEditor::make('vi.content')
                                ->label('Nội dung bài viết (Content)'),

                            Section::make('Cấu hình SEO (VI)')
                                ->collapsed()
                                ->schema([
                                    TextInput::make('vi.seo_title')->label('SEO Title (VI)'),
                                    Textarea::make('vi.seo_description')->label('SEO Description (VI)')->rows(2),
                                ]),
                        ]),

                    Tab::make('English (Secondary)')
                        ->schema([
                            TextInput::make('en.title')
                                ->label('Article Title (EN)'),

                            TextInput::make('en.slug')
                                ->label('Slug (EN)'),

                            Textarea::make('en.excerpt')
                                ->label('Excerpt (EN)')
                                ->rows(3),

                            RichEditor::make('en.content')
                                ->label('Detailed Content (EN)'),

                            Section::make('SEO Configuration (EN)')
                                ->collapsed()
                                ->schema([
                                    TextInput::make('en.seo_title')->label('SEO Title (EN)'),
                                    Textarea::make('en.seo_description')->label('SEO Description (EN)')->rows(2),
                                ]),
                        ]),
                ]),
        ]);
    }
}
