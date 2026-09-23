<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Bookings;

use App\Enums\BookingStatus;
use App\Enums\ContentStatus;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Services\Booking\BookingXlsxExport;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;

class BookingXlsxExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_a_real_xlsx_from_the_booking_table(): void
    {
        $admin = User::factory()->admin()->create();
        $booking = $this->booking([
            'reference' => 'BK-20260923-XLSX01',
            'customer_name' => 'Nguyễn Thị Xuất Excel',
            'phone' => '0902309026',
            'service_name_snapshot' => 'Chăm sóc da snapshot',
            'preferred_date' => '2026-10-05',
            'preferred_time' => '09:30:00',
            'status' => BookingStatus::NEW,
            'locale' => 'vi',
            'utm_source' => 'facebook',
            'created_at' => CarbonImmutable::create(2026, 10, 1, 3, 15, 0, 'UTC'),
        ]);

        $this->actingAs($admin)->get('/admin/bookings')
            ->assertOk()
            ->assertSee('Xuất Excel');

        $download = Livewire::actingAs($admin)
            ->test(ListBookings::class)
            ->assertTableActionVisible('exportXlsx')
            ->callTableAction('exportXlsx');

        $download->assertFileDownloaded();

        $response = app(BookingXlsxExport::class)->download(Booking::query());
        $content = $this->streamedContent($response);
        $this->assertStringStartsWith('PK', $content);
        $this->assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));

        $xlsxRows = $this->readXlsxRows($content);
        $rows = array_map(fn (Row $row): array => $row->toArray(), $xlsxRows);

        $this->assertInstanceOf(StringCell::class, $xlsxRows[1]->getCellAtIndex(2));

        $this->assertSame([
            'Mã đặt lịch', 'Họ và tên', 'Số điện thoại', 'Dịch vụ', 'Ngày mong muốn',
            'Giờ mong muốn', 'Trạng thái', 'Ngôn ngữ', 'Nguồn', 'Ngày gửi',
        ], $rows[0]);
        $this->assertSame([
            $booking->reference,
            $booking->customer_name,
            '0902309026',
            'Chăm sóc da snapshot',
            '05/10/2026',
            '09:30',
            BookingStatus::NEW->getLabel(),
            'vi',
            'facebook',
            '01/10/2026 10:15',
        ], $rows[1]);
    }

    public function test_export_uses_the_current_filtered_table_query_and_snapshot_when_service_is_unavailable(): void
    {
        $admin = User::factory()->admin()->create();
        $matching = $this->booking([
            'reference' => 'BK-20260923-MATCH',
            'status' => BookingStatus::NEW,
            'service_name_snapshot' => 'Dịch vụ đã chụp tại lúc đặt',
        ]);
        $matching->service->update(['status' => ContentStatus::ARCHIVED]);
        $this->booking([
            'reference' => 'BK-20260923-EXCLUDE',
            'status' => BookingStatus::CONTACTED,
        ]);

        $component = Livewire::actingAs($admin)
            ->test(ListBookings::class)
            ->set('tableFilters.status.value', BookingStatus::NEW->value);

        $response = app(BookingXlsxExport::class)->download($component->instance()->getTableQueryForExport());
        $rows = $this->readXlsx($this->streamedContent($response));
        $exportedReferences = array_column(array_slice($rows, 1), 0);

        $this->assertSame([$matching->reference], $exportedReferences);
        $this->assertSame('Dịch vụ đã chụp tại lúc đặt', $rows[1][3]);
    }

    public function test_guests_and_non_admins_cannot_access_the_booking_export_ui(): void
    {
        $this->get('/admin/bookings')->assertRedirect();

        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin/bookings')->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function booking(array $attributes = []): Booking
    {
        $service = Service::factory()->create();

        return Booking::factory()->create(array_merge([
            'service_id' => $service->id,
            'service_name_snapshot' => 'Dịch vụ snapshot',
        ], $attributes));
    }

    /**
     * @return list<list<string|null>>
     */
    private function readXlsx(string $content): array
    {
        return array_map(fn (Row $row): array => $row->toArray(), $this->readXlsxRows($content));
    }

    /**
     * @return list<Row>
     */
    private function readXlsxRows(string $content): array
    {
        $path = tempnam(sys_get_temp_dir(), 'booking-export-');
        file_put_contents($path, $content);

        try {
            $reader = new Reader;
            $reader->open($path);
            $rows = [];

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rows[] = $row;
                }
            }

            $reader->close();

            return $rows;
        } finally {
            @unlink($path);
        }
    }

    private function streamedContent(object $response): string
    {
        ob_start();
        $response->sendContent();

        return (string) ob_get_clean();
    }
}
