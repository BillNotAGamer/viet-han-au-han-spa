<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\Page;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Cấu hình trang (Page Core)')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('key')
                            ->label('Mã định danh trang / Key (Machine ID)')
                            ->required()
                            ->disabled(fn (?Page $record) => $record !== null)
                            ->regex('/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/')
                            ->helperText('Chỉ dùng chữ cái thường, số và dấu gạch nối (ví dụ: home, about, contact). Không thể thay đổi sau khi tạo.'),

                        Select::make('status')
                            ->label('Trạng thái (Status)')
                            ->options(ContentStatus::class)
                            ->default(ContentStatus::PUBLISHED)
                            ->required(),
                    ]),

                    Select::make('page_media_ids')
                        ->label('Hình ảnh liên kết (Associated Media)')
                        ->options(fn () => Media::pluck('file_name', 'id'))
                        ->multiple()
                        ->searchable()
                        ->placeholder('Chọn hình ảnh có sẵn từ thư viện Media'),
                ]),

            Tabs::make('Bản dịch nội dung (Translations)')
                ->tabs([
                    Tab::make('Tiếng Việt (Canonical)')
                        ->schema([
                            TextInput::make('vi.title')
                                ->label('Tiêu đề trang (VI)')
                                ->required(),

                            TextInput::make('vi.slug')
                                ->label('Đường dẫn tĩnh / Slug (VI)')
                                ->helperText('Để trống cho trang chủ (slug = null), hoặc nhập slug tùy chỉnh.'),

                            RichEditor::make('vi.content')
                                ->label('Nội dung trang (VI)'),

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
                                ->label('Page Title (EN)'),

                            TextInput::make('en.slug')
                                ->label('Slug (EN)')
                                ->helperText('Optional slug for English version.'),

                            RichEditor::make('en.content')
                                ->label('Page Content (EN)'),

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
