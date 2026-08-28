<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrainingInquiries\Schemas;

use App\Models\TrainingInquiry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TrainingInquiryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin học viên (Lead Details)')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('reference')->label('Mã tham chiếu')->weight('bold'),
                        TextEntry::make('customer_name')->label('Họ và tên'),
                        TextEntry::make('phone')->label('Số điện thoại')->copyable(),
                    ]),
                    Grid::make(3)->schema([
                        TextEntry::make('email')->label('Email')->placeholder('—'),
                        TextEntry::make('course')->label('Khóa học quan tâm')->getStateUsing(fn (TrainingInquiry $record) => $record->course?->translationFor('vi')?->title ?? '—'),
                        TextEntry::make('locale')->label('Ngôn ngữ'),
                    ]),
                    TextEntry::make('message')->label('Lời nhắn của học viên')->columnSpanFull()->placeholder('Không có lời nhắn'),
                    TextEntry::make('created_at')->label('Thời gian gửi')->dateTime('d/m/Y H:i:s'),
                ]),

            Section::make('Trạng thái & Tiến trình xử lý (Lifecycle)')
                ->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('status')->label('Trạng thái hiện tại')->badge(),
                        TextEntry::make('contacted_at')->label('Thời điểm liên hệ')->dateTime('d/m/Y H:i:s')->placeholder('Chưa liên hệ'),
                        TextEntry::make('enrolled_at')->label('Thời điểm đăng ký')->dateTime('d/m/Y H:i:s')->placeholder('Chưa đăng ký'),
                        TextEntry::make('closed_at')->label('Thời điểm đóng')->dateTime('d/m/Y H:i:s')->placeholder('Chưa đóng'),
                    ]),
                    TextEntry::make('admin_note')->label('Ghi chú nội bộ (Admin Note)')->columnSpanFull()->placeholder('Chưa có ghi chú'),
                ]),

            Section::make('Dữ liệu nguồn & Tiếp thị (Attribution Read-Only)')
                ->collapsed()
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('utm_source')->label('UTM Source')->placeholder('—'),
                        TextEntry::make('utm_medium')->label('UTM Medium')->placeholder('—'),
                        TextEntry::make('utm_campaign')->label('UTM Campaign')->placeholder('—'),
                    ]),
                    Grid::make(2)->schema([
                        TextEntry::make('utm_content')->label('UTM Content')->placeholder('—'),
                        TextEntry::make('utm_term')->label('UTM Term')->placeholder('—'),
                    ]),
                    Grid::make(3)->schema([
                        TextEntry::make('gclid')->label('Google Click ID (gclid)')->placeholder('—'),
                        TextEntry::make('gbraid')->label('gbraid')->placeholder('—'),
                        TextEntry::make('wbraid')->label('wbraid')->placeholder('—'),
                    ]),
                    Grid::make(3)->schema([
                        TextEntry::make('fbclid')->label('Meta Click ID (fbclid)')->placeholder('—'),
                        TextEntry::make('fbp')->label('Meta Browser ID (fbp)')->placeholder('—'),
                        TextEntry::make('fbc')->label('Meta Click Context (fbc)')->placeholder('—'),
                    ]),
                    Grid::make(2)->schema([
                        TextEntry::make('landing_page')->label('Landing Page')->placeholder('—')->columnSpan(1),
                        TextEntry::make('referrer')->label('Referrer')->placeholder('—')->columnSpan(1),
                    ]),
                ]),
        ]);
    }
}
