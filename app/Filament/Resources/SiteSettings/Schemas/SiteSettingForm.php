<?php

declare(strict_types=1);

namespace App\Filament\Resources\SiteSettings\Schemas;

use App\Enums\SiteSettingType;
use App\Models\SiteSetting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin cài đặt (Configuration)')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('key')
                            ->label('Khóa cài đặt / Key (Machine Identifier)')
                            ->required()
                            ->disabled(fn (?SiteSetting $record) => $record !== null)
                            ->helperText('Chữ thường, số, dấu chấm/gạch nối (vd: contact.phone). Không thể thay đổi sau khi tạo. TUYỆT ĐỐI KHÔNG LƯU MẬT KHẨU / API SECRET VÀO ĐÂY.'),

                        Select::make('type')
                            ->label('Định dạng dữ liệu (Type)')
                            ->options(SiteSettingType::class)
                            ->default(SiteSettingType::STRING)
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('group')
                            ->label('Nhóm cấu hình (Group)')
                            ->default('general')
                            ->helperText('Ví dụ: contact, social, tracking, business.'),

                        Toggle::make('is_public')
                            ->label('Hiển thị công khai (Public Safe)')
                            ->default(false)
                            ->helperText('Bật nếu giá trị này an toàn để hiển thị trên website công khai.'),
                    ]),

                    Textarea::make('value')
                        ->label('Giá trị cài đặt (Value)')
                        ->rows(5)
                        ->helperText('Chuỗi văn bản, số 1/0 cho Boolean, hoặc cấu trúc JSON hợp lệ.'),
                ]),
        ]);
    }
}
