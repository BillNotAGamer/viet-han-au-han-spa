<?php

declare(strict_types=1);

namespace App\Filament\Resources\Media\Schemas;

use App\Models\Media;
use App\Services\Media\MediaReferenceInspector;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tệp hình ảnh (Image File)')
                ->schema([
                    FileUpload::make('file_upload')
                        ->label('Chọn tệp hình ảnh tải lên')
                        ->image()
                        ->disk(fn () => config('media.disk', 'public'))
                        ->directory('temp-uploads')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(10240)
                        ->required(fn (?Media $record) => $record === null)
                        ->visible(fn (?Media $record) => $record === null)
                        ->helperText('Hỗ trợ: JPEG, PNG, WebP. Tối đa 10 MB, kích thước tối đa 10.000x10.000 px.'),

                    Grid::make(3)
                        ->visible(fn (?Media $record) => $record !== null)
                        ->schema([
                            Placeholder::make('file_name_display')
                                ->label('Tên tệp gốc')
                                ->content(fn (?Media $record) => $record?->file_name ?? '—'),

                            Placeholder::make('mime_type_display')
                                ->label('Định dạng / MIME')
                                ->content(fn (?Media $record) => "{$record?->mime_type} (.{$record?->extension})"),

                            Placeholder::make('size_display')
                                ->label('Dung lượng')
                                ->content(fn (?Media $record) => $record ? number_format($record->size_bytes / 1024, 1).' KB' : '—'),

                            Placeholder::make('dimensions_display')
                                ->label('Kích thước ảnh')
                                ->content(fn (?Media $record) => ($record?->width && $record?->height) ? "{$record->width} × {$record->height} px" : '—'),

                            Placeholder::make('uploader_display')
                                ->label('Người tải lên')
                                ->content(fn (?Media $record) => $record?->uploader?->name ?? 'Hệ thống'),

                            Placeholder::make('created_at_display')
                                ->label('Thời gian tải lên')
                                ->content(fn (?Media $record) => $record?->created_at?->format('d/m/Y H:i:s') ?? '—'),
                        ]),

                    Section::make('Liên kết trong hệ thống (References)')
                        ->visible(fn (?Media $record) => $record !== null)
                        ->collapsed()
                        ->schema([
                            Placeholder::make('references_breakdown')
                                ->label('Chi tiết liên kết')
                                ->content(function (?Media $record, MediaReferenceInspector $inspector) {
                                    if (! $record) {
                                        return '—';
                                    }
                                    $b = $inspector->getReferenceBreakdown($record);

                                    return "Hero Dịch vụ: {$b['service_hero']} | Hero Đào tạo: {$b['training_hero']} | Hero Blog: {$b['post_hero']} | Gallery Dịch vụ: {$b['service_gallery']} | Gallery Đào tạo: {$b['training_gallery']} | Gallery Blog: {$b['post_gallery']} | Gallery Trang: {$b['page_gallery']}";
                                }),
                        ]),
                ]),

            Tabs::make('Bản dịch & Chú thích (Translations)')
                ->tabs([
                    Tab::make('Tiếng Việt (Canonical)')
                        ->schema([
                            TextInput::make('vi.alt_text')
                                ->label('Văn bản thay thế / Alt Text (VI)')
                                ->helperText('Mô tả nội dung hình ảnh hỗ trợ SEO & tiếp cận. Để trống nếu là ảnh trang trí.'),

                            Textarea::make('vi.caption')
                                ->label('Chú thích ảnh / Caption (VI)')
                                ->rows(2),
                        ]),

                    Tab::make('English (Secondary)')
                        ->schema([
                            TextInput::make('en.alt_text')
                                ->label('Alt Text (EN)'),

                            Textarea::make('en.caption')
                                ->label('Caption (EN)')
                                ->rows(2),
                        ]),
                ]),
        ]);
    }
}
