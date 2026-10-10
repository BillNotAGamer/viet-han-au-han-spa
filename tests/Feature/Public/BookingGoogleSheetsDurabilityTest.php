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
use App\Services\Booking\BookingGoogleSheetsOutbox;
use App\Services\Booking\BookingGoogleSheetsRowMapper;
use App\Services\Booking\BookingGoogleSheetsSyncProcessor;
use App\Services\Booking\Contracts\BookingSheetsClient;
use App\Services\Booking\Exceptions\GoogleSheetsConfigurationException;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Tests\TestCase;

class BookingGoogleSheetsDurabilityTest extends TestCase
{
    use RefreshDatabase;

    private const SPREADSHEET_ID = '1JQKtj4wsrdx5vKOd1vbkTsmVqBGcpokHwyCR5MMI8AI';

    /** @var BookingSheetsClient&MockObject */
    protected BookingSheetsClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(BookingSheetsClient::class);
        $this->app->instance(BookingSheetsClient::class, $this->client);

        config([
            'booking.google_sheets.enabled' => true,
            'booking.google_sheets.spreadsheet_id' => self::SPREADSHEET_ID,
            'booking.google_sheets.tab_name' => 'Trang tính1',
            'booking.google_sheets.retry_base_seconds' => 10,
        ]);
    }

    public function test_booking_and_outbox_are_created_atomically_when_enabled(): void
    {
        $this->client->expects($this->never())->method('readHeader');
        $this->client->expects($this->never())->method('readReferences');
        $this->client->expects($this->never())->method('appendRow');
        $service = $this->createBookableService('Atomic outbox service');

        $this->post('/dat-lich', $this->validPayload($service->id))
            ->assertRedirect('/dat-lich')
            ->assertSessionHas('booking_status');

        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseCount('booking_google_sheets_syncs', 1);
        $booking = Booking::sole();
        $sync = BookingGoogleSheetsSyncRecord::sole();
        $this->assertSame($booking->id, $sync->booking_id);
        $this->assertSame($booking->reference, $sync->booking_reference);
        $this->assertSame(BookingGoogleSheetsSyncStatus::PENDING, $sync->status);
        $this->assertSame(0, $sync->attempts);
    }

    public function test_outbox_failure_rolls_back_booking_and_sync_intent(): void
    {
        $outbox = $this->createMock(BookingGoogleSheetsOutbox::class);
        $outbox->expects($this->once())
            ->method('createFor')
            ->willThrowException(new RuntimeException('Simulated outbox persistence failure'));
        $this->app->instance(BookingGoogleSheetsOutbox::class, $outbox);
        $service = $this->createBookableService('Rollback service');
        $this->withoutExceptionHandling();

        try {
            $this->post('/dat-lich', $this->validPayload($service->id));
            $this->fail('The simulated outbox failure was not raised.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated outbox persistence failure', $exception->getMessage());
        }

        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('booking_google_sheets_syncs', 0);
    }

    public function test_historical_booking_is_not_backfilled_when_integration_is_enabled(): void
    {
        config(['booking.google_sheets.enabled' => false]);
        $booking = Booking::factory()->create();
        config(['booking.google_sheets.enabled' => true]);

        $this->client->expects($this->never())->method('readHeader');
        $this->client->expects($this->never())->method('readReferences');
        $this->client->expects($this->never())->method('appendRow');

        $this->artisan('bookings:sync-google-sheets')->assertSuccessful();

        $this->assertDatabaseCount('booking_google_sheets_syncs', 0);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    public function test_exact_reference_match_marks_synced_without_append(): void
    {
        $record = $this->pendingRecord('BK-EXACT-MATCH');
        $this->expectHeaderAndReferences(['BK-OTHER', 'BK-EXACT-MATCH']);
        $this->client->expects($this->never())->method('appendRow');

        $result = $this->processor()->process($record);

        $this->assertSame(BookingGoogleSheetsSyncStatus::SYNCED, $result->status);
        $this->assertNotNull($result->synced_at);
    }

    public function test_multiple_exact_reference_matches_require_manual_intervention(): void
    {
        $record = $this->pendingRecord('BK-DUPLICATE');
        $this->expectHeaderAndReferences(['BK-DUPLICATE', 'BK-OTHER', 'BK-DUPLICATE']);
        $this->client->expects($this->never())->method('appendRow');

        $result = $this->processor()->process($record);

        $this->assertSame(BookingGoogleSheetsSyncStatus::FAILED, $result->status);
        $this->assertSame('DUPLICATE_REFERENCE', $result->last_error_code);
        $this->assertTrue($result->requires_manual_intervention);
        $this->assertFalse($result->retryable);
    }

    public function test_rate_limit_is_safe_to_retry_but_server_error_is_uncertain(): void
    {
        $rateLimited = $this->pendingRecord('BK-RATE-LIMIT');
        $this->expectHeaderAndReferences([]);
        $this->client->expects($this->once())
            ->method('appendRow')
            ->willThrowException(new RuntimeException('Rate limited', 429));

        $rateResult = $this->processor()->process($rateLimited);
        $this->assertSame(BookingGoogleSheetsSyncStatus::FAILED, $rateResult->status);
        $this->assertTrue($rateResult->retryable);
        $this->assertNotNull($rateResult->next_attempt_at);

        $serverClient = $this->createMock(BookingSheetsClient::class);
        $serverClient->method('readHeader')->willReturn($this->validHeader());
        $serverClient->method('readReferences')->willReturn([]);
        $serverClient->expects($this->once())
            ->method('appendRow')
            ->willThrowException(new RuntimeException('Server error', 500));
        $serverRecord = $this->pendingRecord('BK-SERVER-ERROR');

        $serverResult = $this->processor($serverClient)->process($serverRecord);
        $this->assertSame(BookingGoogleSheetsSyncStatus::UNCERTAIN, $serverResult->status);
        $this->assertFalse($serverResult->retryable);
        $this->assertTrue($serverResult->requires_manual_intervention);
    }

    public function test_timeout_becomes_uncertain_and_cannot_be_blindly_retried(): void
    {
        $record = $this->pendingRecord('BK-TIMEOUT');
        $this->expectHeaderAndReferences([]);
        $this->client->expects($this->once())
            ->method('appendRow')
            ->willThrowException(new RuntimeException('Connection timed out'));
        $processor = $this->processor();

        $result = $processor->process($record);
        $second = $processor->retry($result);

        $this->assertSame(BookingGoogleSheetsSyncStatus::UNCERTAIN, $result->status);
        $this->assertSame(BookingGoogleSheetsSyncStatus::UNCERTAIN, $second->status);
        $this->assertSame(1, $second->attempts);
    }

    public function test_known_rejected_append_can_be_manually_retried_after_operator_correction(): void
    {
        $record = $this->pendingRecord('BK-MANUAL-SAFE-RETRY');
        $record->update([
            'status' => BookingGoogleSheetsSyncStatus::FAILED,
            'attempts' => 1,
            'last_error_code' => 'HTTP_403',
            'append_safe' => true,
            'retryable' => false,
            'requires_manual_intervention' => true,
        ]);
        $this->expectHeaderAndReferences([]);
        $this->client->expects($this->once())->method('appendRow');

        $result = $this->processor()->retry($record);

        $this->assertSame(BookingGoogleSheetsSyncStatus::SYNCED, $result->status);
        $this->assertSame(2, $result->attempts);
    }

    public function test_expired_claim_becomes_uncertain_then_reconciles_without_append(): void
    {
        $record = $this->pendingRecord('BK-EXPIRED-LEASE');
        $record->update([
            'status' => BookingGoogleSheetsSyncStatus::PROCESSING,
            'attempts' => 1,
            'lease_token' => '11111111-1111-4111-8111-111111111111',
            'lease_expires_at' => now()->subMinute(),
        ]);
        $processor = $this->processor();

        $this->assertSame(1, $processor->expireStaleClaims());
        $this->assertSame(BookingGoogleSheetsSyncStatus::UNCERTAIN, $record->fresh()->status);

        $this->expectHeaderAndReferences(['BK-EXPIRED-LEASE']);
        $this->client->expects($this->never())->method('appendRow');
        $result = $processor->reconcile($record->fresh());

        $this->assertSame(BookingGoogleSheetsSyncStatus::SYNCED, $result->status);
    }

    public function test_second_worker_cannot_claim_while_first_worker_owns_lease(): void
    {
        $record = $this->pendingRecord('BK-CONCURRENT');
        $secondClient = $this->createMock(BookingSheetsClient::class);
        $secondClient->expects($this->never())->method('readHeader');
        $secondClient->expects($this->never())->method('readReferences');
        $secondClient->expects($this->never())->method('appendRow');
        $secondProcessor = $this->processor($secondClient);

        $this->client->expects($this->once())
            ->method('readHeader')
            ->willReturnCallback(function () use ($record, $secondProcessor): array {
                $concurrentResult = $secondProcessor->process($record);
                $this->assertSame(BookingGoogleSheetsSyncStatus::PROCESSING, $concurrentResult->status);

                return $this->validHeader();
            });
        $this->client->expects($this->once())->method('readReferences')->willReturn([]);
        $this->client->expects($this->once())->method('appendRow');

        $result = $this->processor()->process($record);

        $this->assertSame(BookingGoogleSheetsSyncStatus::SYNCED, $result->status);
        $this->assertSame(1, $result->attempts);
    }

    public function test_reconciliation_and_retry_cannot_bypass_an_active_claim(): void
    {
        $record = $this->pendingRecord('BK-ACTIVE-CLAIM');
        $record->update([
            'status' => BookingGoogleSheetsSyncStatus::PROCESSING,
            'attempts' => 1,
            'lease_token' => '22222222-2222-4222-8222-222222222222',
            'lease_expires_at' => now()->addMinute(),
        ]);
        $this->client->expects($this->never())->method('readHeader');
        $this->client->expects($this->never())->method('readReferences');
        $this->client->expects($this->never())->method('appendRow');
        $processor = $this->processor();

        $this->assertSame(BookingGoogleSheetsSyncStatus::PROCESSING, $processor->reconcile($record)->status);
        $this->assertSame(BookingGoogleSheetsSyncStatus::PROCESSING, $processor->retry($record)->status);
        $this->assertSame(1, $record->fresh()->attempts);
    }

    public function test_negative_uncertain_reconciliation_never_appends_or_becomes_pending(): void
    {
        $record = $this->pendingRecord('BK-UNCERTAIN-NOT-FOUND');
        $record->update([
            'status' => BookingGoogleSheetsSyncStatus::UNCERTAIN,
            'attempts' => 1,
            'last_error_code' => 'APPEND_OUTCOME_UNKNOWN',
            'requires_manual_intervention' => true,
        ]);
        $this->expectHeaderAndReferences([]);
        $this->client->expects($this->never())->method('appendRow');

        $result = $this->processor()->reconcile($record);

        $this->assertSame(BookingGoogleSheetsSyncStatus::UNCERTAIN, $result->status);
        $this->assertSame('REFERENCE_NOT_FOUND', $result->last_error_code);
        $this->assertTrue($result->requires_manual_intervention);
        $this->assertFalse($result->retryable);
    }

    public function test_uncertain_record_with_null_error_code_is_eligible_for_read_only_reconciliation(): void
    {
        $record = $this->pendingRecord('BK-UNCERTAIN-NULL-CODE');
        $record->update([
            'status' => BookingGoogleSheetsSyncStatus::UNCERTAIN,
            'attempts' => 1,
            'last_error_code' => null,
            'append_safe' => false,
            'retryable' => false,
            'requires_manual_intervention' => true,
        ]);
        $processor = $this->processor();

        $this->assertContains($record->id, $processor->eligibleIds(10));
        $this->expectHeaderAndReferences([]);
        $this->client->expects($this->never())->method('appendRow');

        $this->artisan('bookings:sync-google-sheets')->assertSuccessful();

        $fresh = $record->fresh();
        $this->assertSame(BookingGoogleSheetsSyncStatus::UNCERTAIN, $fresh->status);
        $this->assertSame('REFERENCE_NOT_FOUND', $fresh->last_error_code);
        $this->assertSame(2, $fresh->attempts);
        $this->assertTrue($fresh->requires_manual_intervention);
        $this->assertFalse($fresh->append_safe);
    }

    public function test_missing_credentials_is_a_sanitized_blocked_failure(): void
    {
        $record = $this->pendingRecord('BK-NO-CREDENTIALS');
        $this->client->expects($this->once())
            ->method('readHeader')
            ->willThrowException(new GoogleSheetsConfigurationException('secret path detail'));
        $this->client->expects($this->never())->method('appendRow');

        $result = $this->processor()->process($record);

        $this->assertSame(BookingGoogleSheetsSyncStatus::FAILED, $result->status);
        $this->assertFalse($result->retryable);
        $this->assertTrue($result->requires_manual_intervention);
        $this->assertStringNotContainsString('secret path detail', (string) $result->last_error_summary);
    }

    public function test_disabled_processor_makes_no_google_call_or_state_change(): void
    {
        $record = $this->pendingRecord('BK-DISABLED');
        config(['booking.google_sheets.enabled' => false]);
        $this->client->expects($this->never())->method('readHeader');
        $this->client->expects($this->never())->method('readReferences');
        $this->client->expects($this->never())->method('appendRow');

        $result = $this->processor()->process($record);

        $this->assertSame(BookingGoogleSheetsSyncStatus::PENDING, $result->status);
        $this->assertSame(0, $result->attempts);
    }

    public function test_dry_run_never_mutates_database_or_calls_google(): void
    {
        $record = $this->pendingRecord('BK-DRY-RUN');
        $this->client->expects($this->never())->method('readHeader');
        $this->client->expects($this->never())->method('readReferences');
        $this->client->expects($this->never())->method('appendRow');

        $this->artisan('bookings:sync-google-sheets --dry-run --limit=10')
            ->expectsOutputToContain('Dry run: 1 record(s)')
            ->assertSuccessful();

        $this->assertSame(BookingGoogleSheetsSyncStatus::PENDING, $record->fresh()->status);
        $this->assertSame(0, $record->fresh()->attempts);
    }

    public function test_disabled_command_performs_no_sync_work(): void
    {
        $record = $this->pendingRecord('BK-COMMAND-DISABLED');
        config(['booking.google_sheets.enabled' => false]);
        $this->client->expects($this->never())->method('readHeader');
        $this->client->expects($this->never())->method('readReferences');
        $this->client->expects($this->never())->method('appendRow');

        $this->artisan('bookings:sync-google-sheets')->assertSuccessful();

        $this->assertSame(BookingGoogleSheetsSyncStatus::PENDING, $record->fresh()->status);
        $this->assertSame(0, $record->fresh()->attempts);
    }

    public function test_repeated_command_execution_does_not_reappend_a_confirmed_reference(): void
    {
        $record = $this->pendingRecord('BK-COMMAND-ONCE');
        $this->expectHeaderAndReferences(['BK-COMMAND-ONCE']);
        $this->client->expects($this->never())->method('appendRow');

        $this->artisan('bookings:sync-google-sheets')->assertSuccessful();
        $this->artisan('bookings:sync-google-sheets')->assertSuccessful();

        $this->assertSame(BookingGoogleSheetsSyncStatus::SYNCED, $record->fresh()->status);
        $this->assertSame(1, $record->fresh()->attempts);
    }

    public function test_command_respects_batch_limit_and_continues_after_failures(): void
    {
        $this->pendingRecord('BK-BATCH-1');
        $this->pendingRecord('BK-BATCH-2');
        $third = $this->pendingRecord('BK-BATCH-3');
        $this->client->expects($this->exactly(2))
            ->method('readHeader')
            ->willThrowException(new RuntimeException('Forbidden', 403));
        $this->client->expects($this->never())->method('appendRow');

        $this->artisan('bookings:sync-google-sheets --limit=2')->assertSuccessful();

        $this->assertSame(2, BookingGoogleSheetsSyncRecord::query()->where('status', 'failed')->count());
        $this->assertSame(BookingGoogleSheetsSyncStatus::PENDING, $third->fresh()->status);
    }

    public function test_outbox_uniqueness_prevents_duplicate_intents(): void
    {
        $record = $this->pendingRecord('BK-UNIQUE');

        $this->expectException(QueryException::class);
        BookingGoogleSheetsSyncRecord::query()->create([
            'booking_id' => $record->booking_id,
            'booking_reference' => $record->booking_reference,
            'destination' => BookingGoogleSheetsSyncRecord::DESTINATION,
            'status' => BookingGoogleSheetsSyncStatus::PENDING,
        ]);
    }

    public function test_outbox_booking_reference_and_destination_are_immutable(): void
    {
        $record = $this->pendingRecord('BK-IMMUTABLE');

        try {
            $record->update(['booking_reference' => 'BK-CHANGED']);
            $this->fail('The outbox identity mutation was not rejected.');
        } catch (LogicException $exception) {
            $this->assertSame('Booking Google Sheets synchronization identity is immutable.', $exception->getMessage());
        }

        $this->assertSame('BK-IMMUTABLE', $record->fresh()->booking_reference);
        $this->assertSame(BookingGoogleSheetsSyncRecord::DESTINATION, $record->fresh()->destination);
    }

    protected function processor(?BookingSheetsClient $client = null): BookingGoogleSheetsSyncProcessor
    {
        return new BookingGoogleSheetsSyncProcessor(
            $client ?? $this->client,
            new BookingGoogleSheetsRowMapper,
        );
    }

    protected function pendingRecord(string $reference): BookingGoogleSheetsSyncRecord
    {
        $booking = Booking::factory()->create(['reference' => $reference]);

        return BookingGoogleSheetsSyncRecord::query()->create([
            'booking_id' => $booking->id,
            'booking_reference' => $reference,
            'destination' => BookingGoogleSheetsSyncRecord::DESTINATION,
            'status' => BookingGoogleSheetsSyncStatus::PENDING,
        ]);
    }

    /** @param list<string> $references */
    protected function expectHeaderAndReferences(array $references): void
    {
        $this->client->expects($this->once())
            ->method('readHeader')
            ->with(self::SPREADSHEET_ID, "'Trang tính1'!A1:F1")
            ->willReturn($this->validHeader());
        $this->client->expects($this->once())
            ->method('readReferences')
            ->with(self::SPREADSHEET_ID, "'Trang tính1'!F2:F")
            ->willReturn($references);
    }

    /** @return list<string> */
    protected function validHeader(): array
    {
        return ['NGÀY', 'DỊCH VỤ', 'TÊN KHÁCH HÀNG', 'SỐ ĐIỆN THOẠI', 'GIỜ MONG MUỐN', 'MÃ ĐẶT LỊCH'];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function validPayload(int $serviceId, array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Durable Sync Guest',
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
            'status' => ContentStatus::PUBLISHED,
            'is_featured' => false,
            'sort_order' => 1,
        ]);
        ServiceTranslation::create([
            'service_id' => $service->id,
            'locale' => 'vi',
            'name' => $name,
            'slug' => 'durable-'.$service->id,
        ]);

        return $service;
    }
}
