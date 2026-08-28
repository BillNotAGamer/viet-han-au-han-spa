<?php

declare(strict_types=1);

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\ServiceCategory;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
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
use Illuminate\Support\Str;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin cốt lõi (Core Settings)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('service_category_id')
                                    ->label('Danh mục dịch vụ')
                                    ->options(function () {
                                        return ServiceCategory::with('translations')->get()
                                            ->mapWithKeys(fn ($cat) => [
                                                $cat->id => $cat->translationFor('vi')?->name ?? 'Category #'.$cat->id,
                                            ]);
                                    })
                                    ->searchable()
                                    ->required(),

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

                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_featured')
                                    ->label('Dịch vụ nổi bật')
                                    ->default(false),

                                Select::make('hero_media_id')
                                    ->label('Ảnh đại diện (Hero Media)')
                                    ->options(fn () => Media::pluck('file_name', 'id'))
                                    ->searchable()
                                    ->nullable(),
                            ]),

                        Select::make('gallery_media_ids')
                            ->label('Thư viện ảnh kèm theo (Gallery Media)')
                            ->multiple()
                            ->options(fn () => Media::pluck('file_name', 'id'))
                            ->searchable(),
                    ]),

                Tabs::make('Bilingual Translations')
                    ->tabs([
                        Tab::make('Tiếng Việt (Mặc định)')
                            ->schema([
                                TextInput::make('vi.name')
                                    ->label('Tên dịch vụ (Tiếng Việt)')
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

                                Textarea::make('vi.excerpt')
                                    ->label('Tóm tắt ngắn (Excerpt)')
                                    ->rows(2),

                                RichEditor::make('vi.content')
                                    ->label('Nội dung chi tiết'),

                                Repeater::make('vi.benefits')
                                    ->label('Lợi ích dịch vụ (Benefits)')
                                    ->schema([
                                        TextInput::make('title')->label('Tiêu đề')->required(),
                                        Textarea::make('description')->label('Mô tả')->rows(2),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),

                                Repeater::make('vi.process_steps')
                                    ->label('Quy trình thực hiện (Process Steps)')
                                    ->schema([
                                        TextInput::make('title')->label('Bước thực hiện')->required(),
                                        Textarea::make('description')->label('Mô tả chi tiết')->rows(2),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),

                                Repeater::make('vi.faqs')
                                    ->label('Câu hỏi thường gặp (FAQs)')
                                    ->schema([
                                        TextInput::make('question')->label('Câu hỏi')->required(),
                                        Textarea::make('answer')->label('Câu trả lời')->rows(2)->required(),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),

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
                                    ->label('Service Name (English)')
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

                                Textarea::make('en.excerpt')
                                    ->label('Short Excerpt')
                                    ->rows(2),

                                RichEditor::make('en.content')
                                    ->label('Detailed Content'),

                                Repeater::make('en.benefits')
                                    ->label('Benefits')
                                    ->schema([
                                        TextInput::make('title')->label('Title')->required(),
                                        Textarea::make('description')->label('Description')->rows(2),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),

                                Repeater::make('en.process_steps')
                                    ->label('Process Steps')
                                    ->schema([
                                        TextInput::make('title')->label('Step Title')->required(),
                                        Textarea::make('description')->label('Description')->rows(2),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),

                                Repeater::make('en.faqs')
                                    ->label('Frequently Asked Questions (FAQs)')
                                    ->schema([
                                        TextInput::make('question')->label('Question')->required(),
                                        Textarea::make('answer')->label('Answer')->rows(2)->required(),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),

                                TextInput::make('en.seo_title')
                                    ->label('SEO Title')
                                    ->maxLength(255),

                                Textarea::make('en.seo_description')
                                    ->label('SEO Description')
                                    ->rows(2)
                                    ->maxLength(500),
                            ]),
                    ]),

                Section::make('Bảng giá dịch vụ (Price Tiers)')
                    ->schema([
                        Repeater::make('prices')
                            ->label('Các gói thời lượng & giá tiền')
                            ->schema([
                                Hidden::make('id'),

                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('duration_minutes')
                                            ->label('Thời lượng (Phút)')
                                            ->numeric()
                                            ->minValue(1)
                                            ->required(),

                                        TextInput::make('price_amount')
                                            ->label('Giá (VND)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->required(),

                                        TextInput::make('sort_order')
                                            ->label('Thứ tự')
                                            ->numeric()
                                            ->default(0),

                                        Toggle::make('is_active')
                                            ->label('Đang áp dụng')
                                            ->default(true),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('label_vi')
                                            ->label('Tên gói (Tiếng Việt, ví dụ: Gói Cơ Bản)')
                                            ->maxLength(255),

                                        TextInput::make('label_en')
                                            ->label('Package Label (English, e.g.: Standard)')
                                            ->maxLength(255),
                                    ]),
                            ])
                            ->defaultItems(1),
                    ]),
            ]);
    }
}
