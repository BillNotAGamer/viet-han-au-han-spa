<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\BookingGoogleSheetsSyncStatus;
use App\Models\BookingGoogleSheetsSyncRecord;
use App\Services\Booking\BookingGoogleSheetsSyncProcessor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncBookingGoogleSheets extends Command
{
    protected $signature = 'bookings:sync-google-sheets
        {--limit= : Maximum outbox records to inspect in this run}
        {--dry-run : Report eligible work without database mutations or Google calls}';

    protected $description = 'Reconcile and safely synchronize Booking outbox records to Google Sheets';

    public function handle(BookingGoogleSheetsSyncProcessor $processor): int
    {
        if (! (bool) config('booking.google_sheets.enabled', false)) {
            $this->components->info('Google Sheets Booking synchronization is disabled; no work was performed.');

            return self::SUCCESS;
        }

        $limit = $this->validatedLimit();

        if ($limit === null) {
            return self::FAILURE;
        }

        if ((bool) $this->option('dry-run')) {
            $count = count($processor->eligibleIds($limit, includeExpiredProcessing: true));
            $this->components->info("Dry run: {$count} record(s) are eligible for processing or reconciliation; no state was changed and Google was not called.");

            return self::SUCCESS;
        }

        $expired = 0;
        $ids = $processor->eligibleIds($limit, includeExpiredProcessing: true);
        $counts = [
            'synced' => 0,
            'failed' => 0,
            'uncertain' => 0,
            'unchanged' => 0,
        ];

        foreach ($ids as $id) {
            $record = BookingGoogleSheetsSyncRecord::query()->find($id);

            if ($record === null) {
                $counts['unchanged']++;

                continue;
            }

            if ($record->status === BookingGoogleSheetsSyncStatus::PROCESSING) {
                if ($processor->expireStaleClaim($record)) {
                    $expired++;
                }

                $record = $record->fresh();

                if ($record === null) {
                    $counts['unchanged']++;

                    continue;
                }
            }

            try {
                $result = $record->status === BookingGoogleSheetsSyncStatus::UNCERTAIN
                    ? $processor->reconcile($record)
                    : $processor->process($record);

                $key = $result->status->value;
                $counts[array_key_exists($key, $counts) ? $key : 'unchanged']++;
            } catch (Throwable $exception) {
                $counts['failed']++;
                Log::error('Scheduled Booking Google Sheets synchronization failed unexpectedly.', [
                    'reference' => $record->booking_reference,
                    'exception' => $exception::class,
                ]);
            }
        }

        $this->line(sprintf(
            'Google Sheets Booking sync: expired=%d inspected=%d synced=%d failed=%d uncertain=%d unchanged=%d',
            $expired,
            count($ids),
            $counts['synced'],
            $counts['failed'],
            $counts['uncertain'],
            $counts['unchanged'],
        ));

        return self::SUCCESS;
    }

    protected function validatedLimit(): ?int
    {
        $configured = max(1, (int) config('booking.google_sheets.batch_limit', 25));
        $maximum = max(1, (int) config('booking.google_sheets.max_batch_limit', 200));
        $raw = $this->option('limit');

        if ($raw === null || $raw === '') {
            return min($configured, $maximum);
        }

        if (filter_var($raw, FILTER_VALIDATE_INT) === false || (int) $raw < 1 || (int) $raw > $maximum) {
            $this->components->error("The --limit value must be an integer between 1 and {$maximum}.");

            return null;
        }

        return (int) $raw;
    }
}
