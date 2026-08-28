<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostCategories\Schemas;

use App\Enums\ContentStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PostCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Cấu hình chung (General)')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('status')
                            ->label('Trạng thái (Status)')
                            ->options(ContentStatus::class)
                            ->default(ContentStatus::PUBLISHED)
                            ->required(),

                        TextInput::make('sort_order')
                            ->label('Thứ tự sắp xếp (Sort Order)')
                            ->numeric()
                            ->default(0),
                    ]),
                ]),

            Tabs::make('Bản dịch (Translations)')
                ->tabs([
                    Tab::make('Tiếng Việt (Canonical)')
                        ->schema([
                            TextInput::make('vi.name')
                                ->label('Tên danh mục (VI)')
                                ->required(),

                            TextInput::make('vi.slug')
                                ->label('Đường dẫn tĩnh / Slug (VI)')
                                ->helperText('Tự động sinh từ tên nếu để trống.'),

                            Textarea::make('vi.description')
                                ->label('Mô tả danh mục (VI)')
                                ->rows(3),

                            Section::make('SEO Cấu hình (VI)')
                                ->collapsed()
                                ->schema([
                                    TextInput::make('vi.seo_title')->label('SEO Title (VI)'),
                                    Textarea::make('vi.seo_description')->label('SEO Description (VI)')->rows(2),
                                ]),
                        ]),

                    Tab::make('English (Secondary)')
                        ->schema([
                            TextInput::make('en.name')
                                ->label('Category Name (EN)'),

                            TextInput::make('en.slug')
                                ->label('Slug (EN)'),

                            Textarea::make('en.description')
                                ->label('Description (EN)')
                                ->rows(3),

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
