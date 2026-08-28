<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrainingCourses\Schemas;

use App\Enums\ContentStatus;
use App\Models\Media;
use Filament\Forms\Components\DateTimePicker;
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

class TrainingCourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin chung (General)')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('status')
                            ->label('Trạng thái (Status)')
                            ->options(ContentStatus::class)
                            ->default(ContentStatus::DRAFT)
                            ->required(),

                        TextInput::make('tuition_fee')
                            ->label('Học phí (Tuition Fee VND)')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('Ví dụ: 8000000'),

                        TextInput::make('sort_order')
                            ->label('Thứ tự sắp xếp (Sort Order)')
                            ->numeric()
                            ->default(0),
                    ]),

                    Grid::make(2)->schema([
                        DateTimePicker::make('published_at')
                            ->label('Thời điểm xuất bản (Published At UTC)')
                            ->helperText('Tự động ghi nhận nếu để trống khi xuất bản.'),

                        Toggle::make('is_featured')
                            ->label('Khóa học tiêu biểu / nổi bật (Featured)')
                            ->inline(false),
                    ]),

                    Grid::make(2)->schema([
                        Select::make('hero_media_id')
                            ->label('Hình ảnh đại diện (Hero Media)')
                            ->options(fn () => Media::pluck('file_name', 'id'))
                            ->searchable()
                            ->placeholder('Chọn ảnh đại diện có sẵn'),

                        Select::make('gallery_media_ids')
                            ->label('Thư viện ảnh khóa học (Gallery Media)')
                            ->options(fn () => Media::pluck('file_name', 'id'))
                            ->multiple()
                            ->searchable()
                            ->placeholder('Chọn các ảnh thư viện có sẵn'),
                    ]),
                ]),

            Tabs::make('Bản dịch (Translations)')
                ->tabs([
                    Tab::make('Tiếng Việt (Canonical)')
                        ->schema([
                            TextInput::make('vi.title')
                                ->label('Tên khóa học (VI)')
                                ->required(),

                            TextInput::make('vi.slug')
                                ->label('Đường dẫn tĩnh / Slug (VI)')
                                ->helperText('Tự động sinh từ tên khóa học nếu để trống.'),

                            Grid::make(3)->schema([
                                TextInput::make('vi.duration_display')
                                    ->label('Thời lượng hiển thị (Duration)')
                                    ->placeholder('Ví dụ: 3 tháng (120 giờ)'),

                                TextInput::make('vi.schedule_display')
                                    ->label('Lịch học (Schedule)')
                                    ->placeholder('Ví dụ: T2-T4-T6 hoặc Cuối tuần'),

                                TextInput::make('vi.target_audience')
                                    ->label('Đối tượng phù hợp (Target Audience)')
                                    ->placeholder('Ví dụ: Học viên mới bắt đầu, kỹ thuật viên spa'),
                            ]),

                            Textarea::make('vi.excerpt')
                                ->label('Tóm tắt ngắn (Excerpt)')
                                ->rows(2),

                            RichEditor::make('vi.content')
                                ->label('Nội dung chi tiết khóa học (Content)'),

                            Section::make('Chương trình đào tạo (Curriculum Modules)')
                                ->collapsed()
                                ->schema([
                                    Repeater::make('vi.curriculum_modules')
                                        ->schema([
                                            TextInput::make('title')->label('Tên chuyên đề / học phần')->required(),
                                            Textarea::make('description')->label('Nội dung chuyên đề')->rows(2),
                                        ])
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                                ]),

                            Section::make('Quyền lợi học viên (Benefits)')
                                ->collapsed()
                                ->schema([
                                    Repeater::make('vi.benefits')
                                        ->schema([
                                            TextInput::make('title')->label('Tiêu đề quyền lợi')->required(),
                                            Textarea::make('description')->label('Chi tiết quyền lợi')->rows(2),
                                        ])
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                                ]),

                            Section::make('Câu hỏi thường gặp (FAQs)')
                                ->collapsed()
                                ->schema([
                                    Repeater::make('vi.faqs')
                                        ->schema([
                                            TextInput::make('question')->label('Câu hỏi')->required(),
                                            Textarea::make('answer')->label('Câu trả lời')->rows(2)->required(),
                                        ])
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => $state['question'] ?? null),
                                ]),

                            Section::make('Cấu hình SEO (VI)')
                                ->collapsed()
                                ->schema([
                                    TextInput::make('vi.seo_title')->label('SEO Title'),
                                    Textarea::make('vi.seo_description')->label('SEO Description')->rows(2),
                                ]),
                        ]),

                    Tab::make('English (Secondary)')
                        ->schema([
                            TextInput::make('en.title')
                                ->label('Course Title (EN)'),

                            TextInput::make('en.slug')
                                ->label('Slug (EN)'),

                            Grid::make(3)->schema([
                                TextInput::make('en.duration_display')
                                    ->label('Duration Display (EN)'),

                                TextInput::make('en.schedule_display')
                                    ->label('Schedule Display (EN)'),

                                TextInput::make('en.target_audience')
                                    ->label('Target Audience (EN)'),
                            ]),

                            Textarea::make('en.excerpt')
                                ->label('Excerpt (EN)')
                                ->rows(2),

                            RichEditor::make('en.content')
                                ->label('Detailed Content (EN)'),

                            Section::make('Curriculum Modules (EN)')
                                ->collapsed()
                                ->schema([
                                    Repeater::make('en.curriculum_modules')
                                        ->schema([
                                            TextInput::make('title')->label('Module Title'),
                                            Textarea::make('description')->label('Module Description')->rows(2),
                                        ])
                                        ->collapsible(),
                                ]),

                            Section::make('Student Benefits (EN)')
                                ->collapsed()
                                ->schema([
                                    Repeater::make('en.benefits')
                                        ->schema([
                                            TextInput::make('title')->label('Benefit Title'),
                                            Textarea::make('description')->label('Benefit Description')->rows(2),
                                        ])
                                        ->collapsible(),
                                ]),

                            Section::make('FAQs (EN)')
                                ->collapsed()
                                ->schema([
                                    Repeater::make('en.faqs')
                                        ->schema([
                                            TextInput::make('question')->label('Question'),
                                            Textarea::make('answer')->label('Answer')->rows(2),
                                        ])
                                        ->collapsible(),
                                ]),

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
