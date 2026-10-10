<?php

declare(strict_types=1);

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\Booking;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Schema as DatabaseSchema;

class BookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin khách hàng (Request Details)')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('reference')->label('Mã tham chiếu')->weight('bold'),
                        TextEntry::make('customer_name')->label('Họ và tên'),
                        TextEntry::make('phone')->label('Số điện thoại')->copyable(),
                    ]),
                    Grid::make(3)->schema([
                        TextEntry::make('email')->label('Email (dữ liệu cũ)')->visible(fn (Booking $record): bool => filled($record->email)),
                        TextEntry::make('service')->label('Dịch vụ quan tâm')->getStateUsing(fn (Booking $record) => $record->service_name_snapshot ?: $record->service?->translationFor('vi')?->name ?? '—'),
                        TextEntry::make('locale')->label('Ngôn ngữ'),
                    ]),
                    Grid::make(3)->schema([
                        TextEntry::make('preferred_date')->label('Ngày mong muốn')->date('d/m/Y'),
                        TextEntry::make('preferred_time')->label('Giờ mong muốn'),
                        TextEntry::make('guest_count')->label('Số khách'),
                    ]),
                    TextEntry::make('customer_note')->label('Ghi chú khách hàng (dữ liệu cũ)')->columnSpanFull()->visible(fn (Booking $record): bool => filled($record->customer_note)),
                    TextEntry::make('created_at')->label('Thời gian gửi')->dateTime('d/m/Y H:i:s'),
                ]),

            Section::make('Dịch vụ & giá tại thời điểm gửi (Snapshot)')
                ->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('service_name_snapshot')->label('Tên dịch vụ')->placeholder('—'),
                        TextEntry::make('service_price_label_snapshot')->label('Gói giá')->placeholder('—'),
                        TextEntry::make('duration_minutes_snapshot')
                            ->label('Thời lượng')
                            ->formatStateUsing(fn (?int $state): ?string => $state === null ? null : $state.' phút')
                            ->placeholder('—'),
                        TextEntry::make('price_amount_snapshot')->label('Giá')->money('VND', divideBy: 1)->placeholder('—'),
                    ]),
                ]),

            Section::make('Trạng thái & xử lý (Lifecycle)')
                ->schema([
                    Grid::make(5)->schema([
                        TextEntry::make('status')->label('Trạng thái')->badge(),
                        TextEntry::make('contacted_at')->label('Đã liên hệ')->dateTime('d/m/Y H:i:s')->placeholder('Chưa liên hệ'),
                        TextEntry::make('confirmed_at')->label('Đã xác nhận')->dateTime('d/m/Y H:i:s')->placeholder('Chưa xác nhận'),
                        TextEntry::make('completed_at')->label('Hoàn tất')->dateTime('d/m/Y H:i:s')->placeholder('Chưa hoàn tất'),
                        TextEntry::make('cancelled_at')->label('Đã hủy')->dateTime('d/m/Y H:i:s')->placeholder('Chưa hủy'),
                    ]),
                    TextEntry::make('admin_note')->label('Ghi chú nội bộ')->columnSpanFull()->placeholder('Chưa có ghi chú'),
                ]),

            Section::make('Đồng bộ Google Sheets')
                ->visible(fn (): bool => DatabaseSchema::hasTable('booking_google_sheets_syncs'))
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('googleSheetsSync.status')
                            ->label('Trạng thái đồng bộ')
                            ->formatStateUsing(fn (mixed $state): string => $state instanceof \BackedEnum ? (string) $state->value : (string) ($state ?? 'not_queued'))
                            ->badge(),
                        TextEntry::make('googleSheetsSync.attempts')
                            ->label('Số lần xử lý')
                            ->placeholder('0'),
                        TextEntry::make('googleSheetsSync.last_attempted_at')
                            ->label('Lần xử lý gần nhất')
                            ->dateTime('d/m/Y H:i:s')
                            ->placeholder('Chưa xử lý'),
                    ]),
                    Grid::make(3)->schema([
                        TextEntry::make('googleSheetsSync.synced_at')
                            ->label('Đồng bộ lúc')
                            ->dateTime('d/m/Y H:i:s')
                            ->placeholder('Chưa đồng bộ'),
                        TextEntry::make('googleSheetsSync.last_error_code')
                            ->label('Mã lỗi gần nhất')
                            ->placeholder('—'),
                        TextEntry::make('googleSheetsSync.requires_manual_intervention')
                            ->label('Cần can thiệp thủ công')
                            ->formatStateUsing(fn (mixed $state): string => $state ? 'Có' : 'Không')
                            ->placeholder('Không'),
                    ]),
                    TextEntry::make('googleSheetsSync.last_error_summary')
                        ->label('Lỗi đã được lược bỏ dữ liệu nhạy cảm')
                        ->placeholder('Không có lỗi')
                        ->columnSpanFull(),
                    TextEntry::make('googleSheetsSync.append_safe')
                        ->label('Có thể nối thêm an toàn sau khi xác nhận')
                        ->formatStateUsing(fn (mixed $state): string => $state ? 'Có' : 'Không')
                        ->placeholder('Không'),
                ]),

            Section::make('Dữ liệu nguồn & tiếp thị (Attribution Read-Only)')
                ->collapsed()
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('utm_source')->label('UTM Source')->placeholder('—'),
                        TextEntry::make('utm_medium')->label('UTM Medium')->placeholder('—'),
                        TextEntry::make('utm_campaign')->label('UTM Campaign')->placeholder('—'),
                    ]),
                    Grid::make(3)->schema([
                        TextEntry::make('utm_content')->label('UTM Content')->placeholder('—'),
                        TextEntry::make('utm_term')->label('UTM Term')->placeholder('—'),
                        TextEntry::make('fbclid')->label('Facebook Click ID')->placeholder('—'),
                    ]),
                    Grid::make(3)->schema([
                        TextEntry::make('gclid')->label('Google Click ID')->placeholder('—'),
                        TextEntry::make('gbraid')->label('GBRAID')->placeholder('—'),
                        TextEntry::make('wbraid')->label('WBRAID')->placeholder('—'),
                    ]),
                    Grid::make(2)->schema([
                        TextEntry::make('fbp')->label('Meta Browser ID (_fbp)')->placeholder('—'),
                        TextEntry::make('fbc')->label('Meta Click ID (_fbc)')->placeholder('—'),
                    ]),
                    Grid::make(2)->schema([
                        TextEntry::make('landing_page')->label('Landing Page')->placeholder('—')->wrap(),
                        TextEntry::make('referrer')->label('Referrer')->placeholder('—')->wrap(),
                    ]),
                ]),
        ]);
    }
}
