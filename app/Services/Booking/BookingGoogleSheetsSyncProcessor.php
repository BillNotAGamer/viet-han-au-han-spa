<?php

declare(strict_types=1);

namespace App\Services\Booking;

use App\Enums\BookingGoogleSheetsSyncStatus;
use App\Models\Booking;
use App\Models\BookingGoogleSheetsSyncRecord;
use App\Services\Booking\Contracts\BookingSheetsClient;
use App\Services\Booking\Exceptions\GoogleSheetsConfigurationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class BookingGoogleSheetsSyncProcessor
{
    public function __construct(
        protected BookingSheetsClient $client,
        protected BookingGoogleSheetsRowMapper $rowMapper,
    ) {}

    public function process(
        BookingGoogleSheetsSyncRecord $record,
        bool $manualRetry = false,
    ): BookingGoogleSheetsSyncRecord {
        if (! $this->enabled()) {
            return $record->fresh() ?? $record;
        }

        $claim = $this->claim($record, reconcileOnly: false, ignoreSchedule: $manualRetry);

        if ($claim === null) {
            return $record->fresh() ?? $record;
        }

        return $this->execute($claim['record'], $claim['original'], reconcileOnly: false);
    }

    public function reconcile(BookingGoogleSheetsSyncRecord $record): BookingGoogleSheetsSyncRecord
    {
        if (! $this->enabled()) {
            return $record->fresh() ?? $record;
        }

        $claim = $this->claim($record, reconcileOnly: true, ignoreSchedule: true);

        if ($claim === null) {
            return $record->fresh() ?? $record;
        }

        return $this->execute($claim['record'], $claim['original'], reconcileOnly: true);
    }

    public function retry(BookingGoogleSheetsSyncRecord $record): BookingGoogleSheetsSyncRecord
    {
        return $this->process($record, manualRetry: true);
    }

    /**
     * A crashed or timed-out owner may have transmitted an append. Never turn
     * its expired lease back into retryable work automatically.
     */
    public function expireStaleClaims(): int
    {
        return BookingGoogleSheetsSyncRecord::query()
            ->where('status', BookingGoogleSheetsSyncStatus::PROCESSING->value)
            ->whereNotNull('lease_expires_at')
            ->where('lease_expires_at', '<=', now())
            ->update([
                'status' => BookingGoogleSheetsSyncStatus::UNCERTAIN->value,
                'append_safe' => false,
                'retryable' => false,
                'requires_manual_intervention' => true,
                'last_error_code' => 'LEASE_EXPIRED',
                'last_error_summary' => 'The processing lease expired; reconcile the Booking reference before any retry.',
                'next_attempt_at' => null,
                'lease_token' => null,
                'lease_expires_at' => null,
                'updated_at' => now(),
            ]);
    }

    public function expireStaleClaim(BookingGoogleSheetsSyncRecord $record): bool
    {
        return BookingGoogleSheetsSyncRecord::query()
            ->whereKey($record->getKey())
            ->where('status', BookingGoogleSheetsSyncStatus::PROCESSING->value)
            ->whereNotNull('lease_expires_at')
            ->where('lease_expires_at', '<=', now())
            ->update([
                'status' => BookingGoogleSheetsSyncStatus::UNCERTAIN->value,
                'append_safe' => false,
                'retryable' => false,
                'requires_manual_intervention' => true,
                'last_error_code' => 'LEASE_EXPIRED',
                'last_error_summary' => 'The processing lease expired; reconcile the Booking reference before any retry.',
                'next_attempt_at' => null,
                'lease_token' => null,
                'lease_expires_at' => null,
                'updated_at' => now(),
            ]) === 1;
    }

    /**
     * @return list<int>
     */
    public function eligibleIds(int $limit, bool $includeExpiredProcessing = false): array
    {
        $ids = BookingGoogleSheetsSyncRecord::query()
            ->where(function (Builder $query) use ($includeExpiredProcessing): void {
                $query->where(function (Builder $query): void {
                    $query->where('status', BookingGoogleSheetsSyncStatus::PENDING->value)
                        ->where(function (Builder $query): void {
                            $query->whereNull('next_attempt_at')
                                ->orWhere('next_attempt_at', '<=', now());
                        });
                })
                    ->orWhere(function (Builder $query): void {
                        $query->where('status', BookingGoogleSheetsSyncStatus::UNCERTAIN->value)
                            ->where(function (Builder $query): void {
                                $query->whereNull('last_error_code')
                                    ->orWhere('last_error_code', '!=', 'REFERENCE_NOT_FOUND');
                            })
                            ->where(function (Builder $query): void {
                                $query->whereNull('next_attempt_at')
                                    ->orWhere('next_attempt_at', '<=', now());
                            });
                    })
                    ->orWhere(function (Builder $query): void {
                        $query->where('status', BookingGoogleSheetsSyncStatus::FAILED->value)
                            ->where('retryable', true)
                            ->where(function (Builder $query): void {
                                $query->whereNull('next_attempt_at')
                                    ->orWhere('next_attempt_at', '<=', now());
                            });
                    });

                if ($includeExpiredProcessing) {
                    $query->orWhere(function (Builder $query): void {
                        $query->where('status', BookingGoogleSheetsSyncStatus::PROCESSING->value)
                            ->whereNotNull('lease_expires_at')
                            ->where('lease_expires_at', '<=', now());
                    });
                }
            })
            ->orderBy('id')
            ->limit($limit)
            ->pluck('id');

        return $ids->map(static fn (mixed $id): int => (int) $id)->all();
    }

    /**
     * @return array{record: BookingGoogleSheetsSyncRecord, original: BookingGoogleSheetsSyncRecord}|null
     */
    protected function claim(
        BookingGoogleSheetsSyncRecord $record,
        bool $reconcileOnly,
        bool $ignoreSchedule,
    ): ?array {
        $original = $record->fresh();

        if ($original === null
            || $original->status === BookingGoogleSheetsSyncStatus::SYNCED
            || $original->status === BookingGoogleSheetsSyncStatus::PROCESSING
            || $original->lease_token !== null) {
            return null;
        }

        if (! $reconcileOnly) {
            if (! $original->canRetrySafely()) {
                return null;
            }

            if (! $ignoreSchedule
                && $original->next_attempt_at !== null
                && $original->next_attempt_at->isFuture()) {
                return null;
            }

            if (! $ignoreSchedule && $original->attempts >= $this->maxAttempts()) {
                return null;
            }
        }

        $leaseToken = (string) Str::uuid();
        $claimedAt = now();
        $updated = BookingGoogleSheetsSyncRecord::query()
            ->whereKey($original->getKey())
            ->where('status', $original->status->value)
            ->whereNull('lease_token')
            ->update([
                'status' => BookingGoogleSheetsSyncStatus::PROCESSING->value,
                'attempts' => DB::raw('attempts + 1'),
                'last_attempted_at' => $claimedAt,
                'lease_token' => $leaseToken,
                'lease_expires_at' => $claimedAt->copy()->addSeconds($this->leaseSeconds()),
                'updated_at' => $claimedAt,
            ]);

        if ($updated !== 1) {
            return null;
        }

        $claimed = BookingGoogleSheetsSyncRecord::query()->findOrFail($original->getKey());

        return ['record' => $claimed, 'original' => $original];
    }

    protected function execute(
        BookingGoogleSheetsSyncRecord $record,
        BookingGoogleSheetsSyncRecord $original,
        bool $reconcileOnly,
    ): BookingGoogleSheetsSyncRecord {
        $spreadsheetId = trim((string) config('booking.google_sheets.spreadsheet_id'));
        $tabName = trim((string) config('booking.google_sheets.tab_name'));

        if ($spreadsheetId === '' || $tabName === '') {
            return $this->finishFailed(
                $record,
                'CONFIGURATION_MISSING',
                'Google Sheets synchronization configuration is incomplete.',
                retryable: false,
            );
        }

        $quotedTab = "'".str_replace("'", "''", $tabName)."'";
        $headerRange = $quotedTab.'!A1:F1';
        $referenceRange = $quotedTab.'!F2:F';
        $appendRange = $quotedTab.'!A:F';

        try {
            $header = $this->client->readHeader($spreadsheetId, $headerRange);

            if ($header !== array_values(config('booking.google_sheets.expected_headers', []))) {
                return $this->finishFailed(
                    $record,
                    'HEADER_MISMATCH',
                    'The Google Sheets header does not match the configured A:F schema.',
                    retryable: false,
                );
            }

            $references = $this->client->readReferences($spreadsheetId, $referenceRange);
        } catch (Throwable $exception) {
            if ($reconcileOnly) {
                return $this->finishReconciliationReadFailure($record, $original, $exception);
            }

            return $this->finishBeforeWriteFailure($record, $exception);
        }

        $matchingReferences = count(array_filter(
            $references,
            static fn (string $reference): bool => $reference === $record->booking_reference,
        ));

        if ($matchingReferences > 1) {
            return $this->finishFailed(
                $record,
                'DUPLICATE_REFERENCE',
                'Multiple Google Sheets rows contain this exact Booking reference.',
                retryable: false,
                appendSafe: false,
            );
        }

        if ($matchingReferences === 1) {
            return $this->finishSynced($record);
        }

        if ($reconcileOnly) {
            return $this->finishNegativeReconciliation($record, $original);
        }

        if (! $this->hasValidClaim($record) || ! $this->enabled()) {
            return $this->finishUncertain(
                $record,
                'CLAIM_NOT_VALID',
                'The synchronization claim was no longer valid before the remote write.',
            );
        }

        $booking = Booking::query()->find($record->booking_id);

        if ($booking === null) {
            return $this->finishFailed(
                $record,
                'BOOKING_MISSING',
                'The persisted Booking could not be loaded for synchronization.',
                retryable: false,
            );
        }

        try {
            $this->client->appendRow(
                $spreadsheetId,
                $appendRange,
                $this->rowMapper->map($booking, $record->booking_reference),
            );
        } catch (Throwable $exception) {
            return $this->finishAppendFailure($record, $exception);
        }

        return $this->finishSynced($record);
    }

    protected function finishBeforeWriteFailure(
        BookingGoogleSheetsSyncRecord $record,
        Throwable $exception,
    ): BookingGoogleSheetsSyncRecord {
        $status = $this->httpStatus($exception);
        $blocked = $exception instanceof GoogleSheetsConfigurationException
            || in_array($status, [400, 401, 403, 404], true);

        return $this->finishFailed(
            $record,
            $this->errorCode($exception, 'READ_FAILED'),
            $blocked
                ? 'Google Sheets configuration or access prevented a pre-write check.'
                : 'A pre-write Google Sheets check failed safely before any append.',
            retryable: ! $blocked,
        );
    }

    protected function finishAppendFailure(
        BookingGoogleSheetsSyncRecord $record,
        Throwable $exception,
    ): BookingGoogleSheetsSyncRecord {
        $status = $this->httpStatus($exception);

        if ($status === 429) {
            return $this->finishFailed(
                $record,
                'HTTP_429',
                'Google Sheets rate-limited the append before accepting it.',
                retryable: true,
            );
        }

        if ($status >= 400 && $status < 500 && $status !== 408) {
            return $this->finishFailed(
                $record,
                'HTTP_'.$status,
                'Google Sheets rejected the append request.',
                retryable: false,
            );
        }

        return $this->finishUncertain(
            $record,
            $this->errorCode($exception, 'APPEND_OUTCOME_UNKNOWN'),
            'The append outcome is ambiguous; reconcile the Booking reference before any retry.',
        );
    }

    protected function finishSynced(BookingGoogleSheetsSyncRecord $record): BookingGoogleSheetsSyncRecord
    {
        $this->finishOwned($record, [
            'status' => BookingGoogleSheetsSyncStatus::SYNCED->value,
            'synced_at' => now(),
            'next_attempt_at' => null,
            'last_error_code' => null,
            'last_error_summary' => null,
            'append_safe' => false,
            'retryable' => false,
            'requires_manual_intervention' => false,
        ]);

        return $record->fresh() ?? $record;
    }

    protected function finishFailed(
        BookingGoogleSheetsSyncRecord $record,
        string $code,
        string $summary,
        bool $retryable,
        bool $appendSafe = true,
    ): BookingGoogleSheetsSyncRecord {
        $retryable = $retryable && $record->attempts < $this->maxAttempts();

        $this->finishOwned($record, [
            'status' => BookingGoogleSheetsSyncStatus::FAILED->value,
            'next_attempt_at' => $retryable ? now()->addSeconds($this->retryDelay($record->attempts)) : null,
            'last_error_code' => $code,
            'last_error_summary' => $summary,
            'append_safe' => $appendSafe,
            'retryable' => $retryable,
            'requires_manual_intervention' => ! $retryable,
        ]);

        return $record->fresh() ?? $record;
    }

    protected function finishUncertain(
        BookingGoogleSheetsSyncRecord $record,
        string $code,
        string $summary,
    ): BookingGoogleSheetsSyncRecord {
        $this->finishOwned($record, [
            'status' => BookingGoogleSheetsSyncStatus::UNCERTAIN->value,
            'next_attempt_at' => null,
            'last_error_code' => $code,
            'last_error_summary' => $summary,
            'append_safe' => false,
            'retryable' => false,
            'requires_manual_intervention' => true,
        ]);

        return $record->fresh() ?? $record;
    }

    protected function finishNegativeReconciliation(
        BookingGoogleSheetsSyncRecord $record,
        BookingGoogleSheetsSyncRecord $original,
    ): BookingGoogleSheetsSyncRecord {
        $this->finishOwned($record, [
            'status' => $original->status->value,
            'next_attempt_at' => $original->next_attempt_at,
            'last_error_code' => 'REFERENCE_NOT_FOUND',
            'last_error_summary' => 'No exact Booking reference was found; no row was appended during reconciliation.',
            'append_safe' => $original->append_safe,
            'retryable' => $original->retryable,
            'requires_manual_intervention' => $original->status === BookingGoogleSheetsSyncStatus::UNCERTAIN
                ? true
                : $original->requires_manual_intervention,
        ]);

        return $record->fresh() ?? $record;
    }

    protected function finishReconciliationReadFailure(
        BookingGoogleSheetsSyncRecord $record,
        BookingGoogleSheetsSyncRecord $original,
        Throwable $exception,
    ): BookingGoogleSheetsSyncRecord {
        $this->finishOwned($record, [
            'status' => $original->status->value,
            'next_attempt_at' => now()->addSeconds($this->retryDelay($record->attempts)),
            'last_error_code' => $this->errorCode($exception, 'RECONCILIATION_READ_FAILED'),
            'last_error_summary' => 'A read-only Google Sheets reconciliation check failed; no row was appended.',
            'append_safe' => $original->append_safe,
            'retryable' => $original->retryable,
            'requires_manual_intervention' => $original->status === BookingGoogleSheetsSyncStatus::UNCERTAIN
                ? true
                : $original->requires_manual_intervention,
        ]);

        return $record->fresh() ?? $record;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function finishOwned(BookingGoogleSheetsSyncRecord $record, array $attributes): int
    {
        return BookingGoogleSheetsSyncRecord::query()
            ->whereKey($record->getKey())
            ->where('status', BookingGoogleSheetsSyncStatus::PROCESSING->value)
            ->where('lease_token', $record->lease_token)
            ->update(array_merge($attributes, [
                'lease_token' => null,
                'lease_expires_at' => null,
                'updated_at' => now(),
            ]));
    }

    protected function hasValidClaim(BookingGoogleSheetsSyncRecord $record): bool
    {
        return BookingGoogleSheetsSyncRecord::query()
            ->whereKey($record->getKey())
            ->where('status', BookingGoogleSheetsSyncStatus::PROCESSING->value)
            ->where('lease_token', $record->lease_token)
            ->where('lease_expires_at', '>', now())
            ->exists();
    }

    protected function enabled(): bool
    {
        return (bool) config('booking.google_sheets.enabled', false);
    }

    protected function httpStatus(Throwable $exception): int
    {
        $code = $exception->getCode();

        return is_int($code) ? $code : 0;
    }

    protected function errorCode(Throwable $exception, string $fallback): string
    {
        $status = $this->httpStatus($exception);

        return $status > 0 ? 'HTTP_'.$status : $fallback;
    }

    protected function leaseSeconds(): int
    {
        return max(60, (int) config('booking.google_sheets.lease_seconds', 120));
    }

    protected function maxAttempts(): int
    {
        return max(1, (int) config('booking.google_sheets.max_attempts', 5));
    }

    protected function retryDelay(int $attempt): int
    {
        $base = max(10, (int) config('booking.google_sheets.retry_base_seconds', 60));

        return min(3600, $base * (2 ** max(0, $attempt - 1)));
    }
}
