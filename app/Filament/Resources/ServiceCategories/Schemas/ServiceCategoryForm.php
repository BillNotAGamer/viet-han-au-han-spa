<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServiceCategories\Schemas;

use App\Enums\ContentStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin chính (Core Fields)')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label('Trạng thái')
                                    ->options(ContentStatus::class)
                                    ->default(ContentStatus::DRAFT)
                                    ->required(),

                                TextInput::make('sort_order')
                                    ->label('Thứ tự sắp xếp')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                            ]),
                    ]),

                Tabs::make('Bilingual Translations')
                    ->tabs([
                        Tab::make('Tiếng Việt (Mặc định)')
                            ->schema([
                                TextInput::make('vi.name')
                                    ->label('Tên danh mục (Tiếng Việt)')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if (blank($get('vi.slug')) && filled($state)) {
                                            $set('vi.slug', Str::slug($state));
                                        }
                                    }),

                                TextInput::make('vi.slug')
                                    ->label('Đường dẫn tĩnh (Slug)')
                                    ->required()
                                    ->maxLength(255),

                                Textarea::make('vi.description')
                                    ->label('Mô tả danh mục')
                                    ->rows(3),

                                TextInput::make('vi.seo_title')
                                    ->label('SEO Title')
                                    ->maxLength(255),

                                Textarea::make('vi.seo_description')
                                    ->label('SEO Description')
                                    ->rows(2)
                                    ->maxLength(500),
                            ]),

                        Tab::make('English (Secondary)')
                            ->schema([
                                TextInput::make('en.name')
                                    ->label('Category Name (English)')
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if (blank($get('en.slug')) && filled($state)) {
                                            $set('en.slug', Str::slug($state));
                                        }
                                    }),

                                TextInput::make('en.slug')
                                    ->label('Slug (English)')
                                    ->maxLength(255),

                                Textarea::make('en.description')
                                    ->label('Description')
                                    ->rows(3),

                                TextInput::make('en.seo_title')
                                    ->label('SEO Title')
                                    ->maxLength(255),

                                Textarea::make('en.seo_description')
                                    ->label('SEO Description')
                                    ->rows(2)
                                    ->maxLength(500),
                            ]),
                    ]),
            ]);
    }
}
