<?php

declare(strict_types=1);

namespace App\Services\Booking;

use App\Models\Booking;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingXlsxExport
{
    /**
     * @var list<string>
     */
    private const HEADINGS = [
        'Mã đặt lịch',
        'Họ và tên',
        'Số điện thoại',
        'Dịch vụ',
        'Ngày mong muốn',
        'Giờ mong muốn',
        'Trạng thái',
        'Ngôn ngữ',
        'Nguồn',
        'Ngày gửi',
    ];

    /**
     * @param  Builder<Booking>  $query
     */
    public function download(Builder $query): StreamedResponse
    {
        $filename = 'booking-requests-'.now('Asia/Ho_Chi_Minh')->format('Y-m-d-Hi').'.xlsx';

        return response()->streamDownload(function () use ($query): void {
            $writer = new Writer;
            $writer->openToFile('php://output');
            $writer->addRow(Row::fromValues(self::HEADINGS));

            $this->exportQuery($query, $writer);

            $writer->close();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    /**
     * @param  Builder<Booking>  $query
     */
    private function exportQuery(Builder $query, Writer $writer): void
    {
        $query
            ->select([
                'bookings.id',
                'bookings.reference',
                'bookings.customer_name',
                'bookings.phone',
                'bookings.service_id',
                'bookings.service_name_snapshot',
                'bookings.preferred_date',
                'bookings.preferred_time',
                'bookings.status',
                'bookings.locale',
                'bookings.utm_source',
                'bookings.created_at',
            ])
            // Export by a stable primary-key cursor so large filtered result sets
            // remain bounded in memory. The snapshot normally makes the current
            // Service relation unnecessary; the constrained relation is only a
            // compatibility fallback for legacy rows without a snapshot.
            ->with([
                'service:id',
                'service.translations:id,service_id,locale,name',
            ])
            ->reorder('bookings.id')
            ->lazyById(200, 'bookings.id')
            ->each(function (Booking $booking) use ($writer): void {
                $writer->addRow(Row::fromValues([
                    $this->text($booking->reference),
                    $this->text($booking->customer_name),
                    $this->text($booking->phone),
                    $this->text($this->serviceName($booking)),
                    $booking->preferred_date?->format('d/m/Y'),
                    $this->preferredTime($booking->preferred_time),
                    $booking->status->getLabel(),
                    $booking->locale,
                    $this->text($booking->utm_source),
                    $this->createdAt($booking->created_at),
                ]));
            });
    }

    private function serviceName(Booking $booking): string
    {
        return $booking->service_name_snapshot
            ?: $booking->service?->translationFor('vi')?->name
            ?: '—';
    }

    private function preferredTime(?string $time): ?string
    {
        if ($time === null || $time === '') {
            return null;
        }

        return substr($time, 0, 5);
    }

    private function createdAt(?CarbonInterface $createdAt): ?string
    {
        return $createdAt?->copy()->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i');
    }

    private function text(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // XLSX cells are explicitly strings, and this prevents an historic
        // free-text value beginning with "=" from becoming a spreadsheet formula.
        return str_starts_with($value, '=') ? "'{$value}" : $value;
    }
}
