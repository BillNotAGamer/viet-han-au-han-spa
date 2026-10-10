<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\BookingGoogleSheetsSyncStatus;
use App\Enums\ContentStatus;
use App\Models\Booking;
use App\Models\BookingGoogleSheetsSyncRecord;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceTranslation;
use App\Models\User;
use App\Services\Booking\BookingGoogleSheetsRowMapper;
use App\Services\Booking\BookingGoogleSheetsSync;
use App\Services\Booking\BookingGoogleSheetsSyncProcessor;
use App\Services\Booking\Contracts\BookingSheetsClient;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Tests\TestCase;

class BookingGoogleSheetsSyncTest extends TestCase
{
    use RefreshDatabase;

    private const SPREADSHEET_ID = '1JQKtj4wsrdx5vKOd1vbkTsmVqBGcpokHwyCR5MMI8AI';

    /** @var BookingSheetsClient&MockObject */
    protected BookingSheetsClient $sheetsClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sheetsClient = $this->createMock(BookingSheetsClient::class);
        $this->app->instance(BookingSheetsClient::class, $this->sheetsClient);

        config([
            'booking.google_sheets.enabled' => true,
            'booking.google_sheets.spreadsheet_id' => self::SPREADSHEET_ID,
            'booking.google_sheets.tab_name' => 'Trang tính1',
        ]);
    }

    public function test_disabled_sync_does_not_call_google_and_booking_still_succeeds(): void
    {
        config(['booking.google_sheets.enabled' => false]);

        $this->sheetsClient->expects($this->never())->method('readHeader');
        $this->sheetsClient->expects($this->never())->method('readReferences');
        $this->sheetsClient->expects($this->never())->method('appendRow');

        $service = $this->createBookableService('Dịch vụ không đồng bộ');

        $this->post('/dat-lich', $this->validPayload($service->id))
            ->assertRedirect('/dat-lich')
            ->assertSessionHas('booking_status');

        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseCount('booking_google_sheets_syncs', 0);
    }

    public function test_enabled_sync_appends_exactly_one_mapped_row_after_booking_persists(): void
    {
        $service = $this->createBookableService('Chăm sóc da chuyên sâu');
        $date = CarbonImmutable::now('Asia/Ho_Chi_Minh')->addDays(2);

        $this->sheetsClient->expects($this->never())
            ->method('readHeader');
        $this->sheetsClient->expects($this->never())->method('readReferences');
        $this->sheetsClient->expects($this->never())->method('appendRow');

        $this->post('/dat-lich', $this->validPayload($service->id, [
            'customer_name' => 'Nguyễn Thị Lan',
            'phone' => '0902309026',
            'preferred_date' => $date->toDateString(),
            'preferred_time' => '16:45',
        ]))
            ->assertRedirect('/dat-lich')
            ->assertSessionHas('booking_status');

        $this->assertDatabaseCount('bookings', 1);
        $this->assertSame('0902309026', Booking::sole()->phone);
        $this->assertSame(BookingGoogleSheetsSyncStatus::PENDING, BookingGoogleSheetsSyncRecord::sole()->status);
        $this->assertSame(0, BookingGoogleSheetsSyncRecord::sole()->attempts);

        $commandClient = $this->createMock(BookingSheetsClient::class);
        $this->app->instance(BookingSheetsClient::class, $commandClient);
        $testTransactionLevel = DB::transactionLevel();
        $commandClient->expects($this->once())
            ->method('readHeader')
            ->with(self::SPREADSHEET_ID, "'Trang tính1'!A1:F1")
            ->willReturn($this->validHeader());
        $commandClient->expects($this->once())
            ->method('readReferences')
            ->with(self::SPREADSHEET_ID, "'Trang tính1'!F2:F")
            ->willReturn([]);
        $commandClient->expects($this->once())
            ->method('appendRow')
            ->willReturnCallback(function (string $spreadsheetId, string $range, array $row) use ($date, $testTransactionLevel): void {
                $this->assertSame($testTransactionLevel, DB::transactionLevel());
                $this->assertDatabaseCount('bookings', 1);
                $this->assertSame('Chăm sóc da chuyên sâu', Booking::sole()->service_name_snapshot);
                $this->assertSame(self::SPREADSHEET_ID, $spreadsheetId);
                $this->assertSame("'Trang tính1'!A:F", $range);
                $this->assertSame([
                    $date->format('d/m/Y'),
                    'Chăm sóc da chuyên sâu',
                    'Nguyễn Thị Lan',
                    '0902309026',
                    '16:45',
                    Booking::sole()->reference,
                ], $row);
            });

        $this->artisan('bookings:sync-google-sheets')->assertSuccessful();
        $this->assertSame('synced', BookingGoogleSheetsSyncRecord::sole()->status->value);

        $this->artisan('bookings:sync-google-sheets')->assertSuccessful();
        $this->assertSame(1, BookingGoogleSheetsSyncRecord::sole()->attempts);
    }

    public function test_google_403_does_not_fail_or_roll_back_booking(): void
    {
        $service = $this->createBookableService('Dịch vụ 403');

        $this->sheetsClient->expects($this->never())->method('readHeader');
        $this->sheetsClient->expects($this->never())->method('readReferences');
        $this->sheetsClient->expects($this->never())->method('appendRow');

        $this->post('/dat-lich', $this->validPayload($service->id))
            ->assertRedirect('/dat-lich')
            ->assertSessionHas('booking_status');

        $this->assertDatabaseCount('bookings', 1);
        $this->assertSame('pending', BookingGoogleSheetsSyncRecord::sole()->status->value);

        $commandClient = $this->createMock(BookingSheetsClient::class);
        $commandClient->method('readHeader')->willReturn($this->validHeader());
        $commandClient->method('readReferences')->willReturn([]);
        $commandClient->expects($this->once())
            ->method('appendRow')
            ->willThrowException(new RuntimeException('Permission denied', 403));
        $this->app->instance(BookingSheetsClient::class, $commandClient);

        $this->artisan('bookings:sync-google-sheets')->assertSuccessful();

        $this->assertSame('failed', BookingGoogleSheetsSyncRecord::sole()->status->value);
        $this->assertSame('HTTP_403', BookingGoogleSheetsSyncRecord::sole()->last_error_code);
        $this->assertTrue(BookingGoogleSheetsSyncRecord::sole()->append_safe);
        $this->assertFalse(BookingGoogleSheetsSyncRecord::sole()->retryable);

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->get('/admin/bookings/'.Booking::sole()->id)
            ->assertOk()
            ->assertSee('HTTP_403');
    }

    public function test_google_timeout_does_not_fail_or_roll_back_booking(): void
    {
        $service = $this->createBookableService('Dịch vụ timeout');

        $this->sheetsClient->expects($this->never())->method('readHeader');
        $this->sheetsClient->expects($this->never())->method('readReferences');
        $this->sheetsClient->expects($this->never())->method('appendRow');

        $this->post('/dat-lich', $this->validPayload($service->id))
            ->assertRedirect('/dat-lich')
            ->assertSessionHas('booking_status');

        $this->assertDatabaseCount('bookings', 1);
        $this->assertSame('pending', BookingGoogleSheetsSyncRecord::sole()->status->value);

        $commandClient = $this->createMock(BookingSheetsClient::class);
        $commandClient->expects($this->exactly(2))->method('readHeader')->willReturn($this->validHeader());
        $commandClient->expects($this->exactly(2))->method('readReferences')->willReturn([]);
        $commandClient->expects($this->once())
            ->method('appendRow')
            ->willThrowException(new RuntimeException('Connection timed out'));
        $this->app->instance(BookingSheetsClient::class, $commandClient);

        $this->artisan('bookings:sync-google-sheets')->assertSuccessful();

        $this->assertSame('uncertain', BookingGoogleSheetsSyncRecord::sole()->status->value);
        $this->assertTrue(BookingGoogleSheetsSyncRecord::sole()->requires_manual_intervention);

        $this->artisan('bookings:sync-google-sheets')->assertSuccessful();
        $this->assertSame('uncertain', BookingGoogleSheetsSyncRecord::sole()->status->value);
        $this->assertSame('REFERENCE_NOT_FOUND', BookingGoogleSheetsSyncRecord::sole()->last_error_code);
    }

    public function test_header_mismatch_skips_append_without_overwriting_client_header(): void
    {
        $service = $this->createBookableService('Dịch vụ schema mismatch');

        $this->sheetsClient->expects($this->never())->method('readHeader');
        $this->sheetsClient->expects($this->never())->method('readReferences');
        $this->sheetsClient->expects($this->never())->method('appendRow');

        $this->post('/dat-lich', $this->validPayload($service->id))
            ->assertRedirect('/dat-lich')
            ->assertSessionHas('booking_status');

        $commandClient = $this->createMock(BookingSheetsClient::class);
        $commandClient->expects($this->once())
            ->method('readHeader')
            ->willReturn(['DATE', 'SERVICE', 'CUSTOMER', 'PHONE']);
        $commandClient->expects($this->never())->method('readReferences');
        $commandClient->expects($this->never())->method('appendRow');
        $this->app->instance(BookingSheetsClient::class, $commandClient);

        $this->artisan('bookings:sync-google-sheets')->assertSuccessful();

        $this->assertDatabaseCount('bookings', 1);
        $this->assertSame('HEADER_MISMATCH', BookingGoogleSheetsSyncRecord::sole()->last_error_code);
    }

    public function test_same_booking_reference_is_appended_at_most_once_by_sync_service(): void
    {
        $service = $this->createBookableService('Dịch vụ idempotency');
        $booking = Booking::factory()->create([
            'service_id' => $service->id,
            'reference' => 'BK-SHEETS-ONCE',
        ]);
        $record = BookingGoogleSheetsSyncRecord::query()->create([
            'booking_id' => $booking->id,
            'booking_reference' => $booking->reference,
            'destination' => BookingGoogleSheetsSyncRecord::DESTINATION,
            'status' => 'pending',
        ]);

        $this->expectValidHeader();
        $this->sheetsClient->expects($this->once())->method('appendRow');

        $processor = new BookingGoogleSheetsSyncProcessor(
            $this->sheetsClient,
            new BookingGoogleSheetsRowMapper,
        );
        $sync = new BookingGoogleSheetsSync($processor);

        $this->assertTrue($sync->sync($booking));
        $this->assertTrue($sync->sync($booking));
        $this->assertSame('synced', $record->fresh()->status->value);
    }

    /** @return list<string> */
    protected function validHeader(): array
    {
        return ['NGÀY', 'DỊCH VỤ', 'TÊN KHÁCH HÀNG', 'SỐ ĐIỆN THOẠI', 'GIỜ MONG MUỐN', 'MÃ ĐẶT LỊCH'];
    }

    protected function expectValidHeader(): void
    {
        $this->sheetsClient->expects($this->once())
            ->method('readHeader')
            ->willReturn($this->validHeader());
        $this->sheetsClient->expects($this->once())
            ->method('readReferences')
            ->willReturn([]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function validPayload(int $serviceId, array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Khách hàng Google Sheets',
            'phone' => '0902309026',
            'service_id' => $serviceId,
            'preferred_date' => CarbonImmutable::now('Asia/Ho_Chi_Minh')->addDay()->toDateString(),
            'preferred_time' => '14:30',
        ], $overrides);
    }

    protected function createBookableService(string $name): Service
    {
        $category = ServiceCategory::create([
            'status' => ContentStatus::PUBLISHED,
            'sort_order' => 1,
        ]);

        $service = Service::create([
            'service_category_id' => $category->id,
            'hero_media_id' => null,
            'status' => ContentStatus::PUBLISHED,
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        ServiceTranslation::create([
            'service_id' => $service->id,
            'locale' => 'vi',
            'name' => $name,
            'slug' => 'sheets-'.strtolower((string) $service->id),
            'excerpt' => $name,
            'content' => '<p>'.$name.'</p>',
        ]);

        return $service;
    }
}
