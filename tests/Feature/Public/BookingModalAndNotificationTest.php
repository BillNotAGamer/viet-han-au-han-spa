<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\BookingStatus;
use App\Enums\ContentStatus;
use App\Mail\BookingRequestSubmitted;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceTranslation;
use App\Services\Booking\BookingNotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingModalAndNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_modal_public_rendering_vi_and_en_and_admin_isolation(): void
    {
        // 1. Vietnamese public layout
        $viResponse = $this->get('/');
        $viResponse->assertStatus(200);
        $viResponse->assertSee('id="booking-modal"', false);
        $viResponse->assertSee('role="dialog"', false);
        $viResponse->assertSee('aria-modal="true"', false);
        $viResponse->assertSee('aria-labelledby="booking-modal-title"', false);
        $viResponse->assertSee('id="booking-modal-title"', false);
        $viResponse->assertSee(route('vi.booking.store'), false);
        $viResponse->assertSee('name="_token"', false);

        // 2. English public layout
        $enResponse = $this->get('/en');
        $enResponse->assertStatus(200);
        $enResponse->assertSee('id="booking-modal"', false);
        $enResponse->assertSee('role="dialog"', false);
        $enResponse->assertSee('aria-modal="true"', false);
        $enResponse->assertSee('aria-labelledby="booking-modal-title"', false);
        $enResponse->assertSee(route('en.booking.store'), false);
        $enResponse->assertSee('name="_token"', false);

        // 3. Admin panel must NEVER contain the public booking modal
        $adminResponse = $this->get('/admin/login');
        $adminResponse->assertDontSee('id="booking-modal"', false);
        $adminResponse->assertDontSee('data-booking-modal-trigger', false);
    }

    public function test_public_booking_ctas_have_real_href_fallback_and_modal_trigger(): void
    {
        $viResponse = $this->get('/');
        $viResponse->assertStatus(200);
        $viContent = (string) $viResponse->getContent();

        $this->assertStandaloneBookingTrigger($viContent, route('vi.booking.create'), 'public-floating-booking__link');
        $this->assertFooterBookingTrigger($viContent, route('vi.booking.create'), 'Đặt lịch');
        $this->assertStringNotContainsString('navigation.booking', $viContent);

        // Verify no JS-only hrefs on booking triggers
        $this->assertStringNotContainsString('href="#" data-booking-modal-trigger', $viContent);
        $this->assertStringNotContainsString('href="javascript:void(0)"', $viContent);

        $enResponse = $this->get('/en');
        $enResponse->assertStatus(200);
        $enContent = (string) $enResponse->getContent();

        $this->assertStandaloneBookingTrigger($enContent, route('en.booking.create'), 'public-floating-booking__link');
        $this->assertFooterBookingTrigger($enContent, route('en.booking.create'), 'Book now');
        $this->assertStringNotContainsString('navigation.booking', $enContent);
    }

    public function test_mobile_drawer_booking_cta_has_real_href_fallback_and_modal_trigger(): void
    {
        $viResponse = $this->get('/');
        $viResponse->assertStatus(200);

        // The header drawer CTA (the only global booking trigger below the 1180px
        // desktop breakpoint, where the floating side-tab is not rendered) must carry
        // the same real href + dispatch wiring as every other booking CTA in the layout.
        $viContent = (string) $viResponse->getContent();
        $this->assertMatchesRegularExpression(
            '/<a\s+href="'.preg_quote(route('vi.booking.create'), '/').'"\s+data-booking-modal-trigger\s+@click\.prevent="mobileOpen = false; \$dispatch\(\'open-booking-modal\', \{ trigger: \$el \}\)"\s+class="public-header__drawer-cta"/',
            $viContent
        );

        $enResponse = $this->get('/en');
        $enResponse->assertStatus(200);
        $enContent = (string) $enResponse->getContent();
        $this->assertMatchesRegularExpression(
            '/<a\s+href="'.preg_quote(route('en.booking.create'), '/').'"\s+data-booking-modal-trigger\s+@click\.prevent="mobileOpen = false; \$dispatch\(\'open-booking-modal\', \{ trigger: \$el \}\)"\s+class="public-header__drawer-cta"/',
            $enContent
        );
    }

    public function test_booking_modal_open_pushes_pii_free_gtm_event(): void
    {
        $viResponse = $this->get('/');
        $viResponse->assertStatus(200);
        $viContent = (string) $viResponse->getContent();

        // Guarded client-side push: never assumes dataLayer exists (tracking may be disabled).
        $this->assertStringContainsString("typeof window.dataLayer !== 'undefined' && Array.isArray(window.dataLayer)", $viContent);
        $this->assertStringContainsString("event: 'booking_modal_opened'", $viContent);
        $this->assertStringContainsString("locale: 'vi'", $viContent);

        // The push fires on open(), before any customer input exists — verify the pushed
        // payload carries no name/phone/email/notes keys (PII prohibition, AGENTS.md #16).
        $openMethod = substr($viContent, (int) strpos($viContent, 'open(event) {'));
        $openMethod = substr($openMethod, 0, (int) strpos($openMethod, 'this.$nextTick'));
        $this->assertStringNotContainsString('customer_name', $openMethod);
        $this->assertStringNotContainsString('phone', $openMethod);
        $this->assertStringNotContainsString('email', $openMethod);

        $enResponse = $this->get('/en');
        $enResponse->assertStatus(200);
        $enResponse->assertSee("locale: 'en'", false);
    }

    public function test_modal_and_full_page_reuse_authoritative_form_fields(): void
    {
        $service = $this->createTestService('vi', 'Chăm Sóc Da');

        // Full booking page
        $pageResponse = $this->get('/dat-lich');
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('name="customer_name"', false);
        $pageResponse->assertSee('name="phone"', false);
        $pageResponse->assertSee('name="email"', false);
        $pageResponse->assertSee('name="service_id"', false);
        $pageResponse->assertSee('name="preferred_date"', false);
        $pageResponse->assertSee('name="preferred_time"', false);
        $pageResponse->assertSee('name="notes"', false);
        $pageResponse->assertSee('name="consent"', false);
        $pageHtml = explode('id="booking-modal"', (string) $pageResponse->getContent())[0];
        $this->assertStringContainsString('sm:p-10 shadow-lg space-y-6', $pageHtml);
        $this->assertStringContainsString('rows="4"', $pageHtml);
        $this->assertStringNotContainsString('class="space-y-3"', $pageHtml);

        // Modal component on public page
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $modalHtml = explode('id="booking-modal"', (string) $homeResponse->getContent())[1] ?? '';
        $this->assertStringContainsString('name="customer_name"', $modalHtml);
        $this->assertStringContainsString('name="phone"', $modalHtml);
        $this->assertStringContainsString('name="email"', $modalHtml);
        $this->assertStringContainsString('name="service_id"', $modalHtml);
        $this->assertStringContainsString('name="preferred_date"', $modalHtml);
        $this->assertStringContainsString('name="preferred_time"', $modalHtml);
        $this->assertStringContainsString('name="notes"', $modalHtml);
        $this->assertStringContainsString('name="consent"', $modalHtml);
        $this->assertStringContainsString('max-w-[840px]', $modalHtml);
        $this->assertStringContainsString('max-h-[calc(100dvh-48px)]', $modalHtml);
        $this->assertStringContainsString('relative h-40 sm:h-32', $modalHtml);
        $this->assertStringContainsString('object-[center_45%]', $modalHtml);
        $this->assertStringContainsString('sm:px-8 sm:py-5', $modalHtml);
        $this->assertStringContainsString('class="space-y-3"', $modalHtml);
        $this->assertStringContainsString('sm:gap-x-5 sm:gap-y-3', $modalHtml);
        $this->assertStringContainsString('h-12 sm:h-11', $modalHtml);
        $this->assertStringContainsString('sm:h-[76px] sm:min-h-[76px]', $modalHtml);
        $this->assertStringContainsString('rows="3"', $modalHtml);
    }

    public function test_email_notification_disabled_by_default(): void
    {
        Mail::fake();

        // Ensure default config is disabled
        $this->assertFalse(config('booking.notifications.email.enabled'));

        $service = $this->createTestService('vi', 'Trị Liệu Cổ Vai Gáy');
        $payload = $this->validPayload($service->id);

        $response = $this->from('/dat-lich')->post('/dat-lich', $payload);
        $response->assertRedirect('/dat-lich');

        $this->assertDatabaseCount('bookings', 1);
        $booking = Booking::first();
        $this->assertSame(BookingStatus::NEW, $booking->status);

        // No mail sent because notification is disabled by default
        Mail::assertNothingSent();
    }

    public function test_email_notification_sent_when_enabled(): void
    {
        Mail::fake();

        config([
            'booking.notifications.email.enabled' => true,
            'booking.notifications.email.recipient' => 'spa-reception@viethanauhanspa.com',
        ]);

        $service = $this->createTestService('vi', 'Massage Body Tinh Dầu');
        $payload = $this->validPayload($service->id, [
            'customer_name' => 'Tran Thi Mai',
            'phone' => '091 234 5678',
            'email' => 'mai@example.test',
        ]);

        $response = $this->from('/dat-lich')->post('/dat-lich', $payload);
        $response->assertRedirect('/dat-lich');

        $this->assertDatabaseCount('bookings', 1);
        $booking = Booking::first();

        // Exactly one mailable sent to configured recipient
        Mail::assertSent(BookingRequestSubmitted::class, function (BookingRequestSubmitted $mail) use ($booking) {
            return $mail->hasTo('spa-reception@viethanauhanspa.com')
                && $mail->booking->id === $booking->id
                && $mail->booking->reference === $booking->reference;
        });
    }

    public function test_invalid_booking_does_not_send_notification_email(): void
    {
        Mail::fake();

        config([
            'booking.notifications.email.enabled' => true,
            'booking.notifications.email.recipient' => 'spa@example.test',
        ]);

        $service = $this->createTestService('vi', 'Dịch Vụ Test');
        $invalidPayload = $this->validPayload($service->id, [
            'customer_name' => '',
            'phone' => 'invalid-phone',
            'consent' => '0',
        ]);

        $response = $this->from('/dat-lich')->post('/dat-lich', $invalidPayload);
        $response->assertSessionHasErrors(['customer_name', 'phone', 'consent']);

        $this->assertDatabaseCount('bookings', 0);
        Mail::assertNothingSent();
    }

    public function test_mailable_content_built_from_persisted_booking_and_escaped(): void
    {
        $service = $this->createTestService('vi', 'Liệu Trình Trẻ Hóa Da');

        $booking = Booking::create([
            'reference' => 'BK-TEST-2026-9999',
            'locale' => 'vi',
            'service_id' => $service->id,
            'status' => BookingStatus::NEW,
            'customer_name' => 'Le Van C',
            'phone' => '098 765 4321',
            'phone_normalized' => '+84987654321',
            'email' => 'levanc@example.test',
            'preferred_date' => CarbonImmutable::now('Asia/Ho_Chi_Minh')->addDays(2)->toDateString(),
            'preferred_time' => '10:00',
            'customer_note' => 'Ghi chú đặc biệt <script>alert("xss")</script>',
            'service_name_snapshot' => 'Liệu Trình Trẻ Hóa Da Snapshot',
            'service_category_name_snapshot' => 'Chăm Sóc Da',
            'submitted_at' => CarbonImmutable::now('UTC'),
        ]);

        $mailable = new BookingRequestSubmitted($booking);
        $mailable->assertHasSubject('[Việt Hàn Âu Hàn Spa] Yêu cầu đặt lịch BK-TEST-2026-9999');

        $html = $mailable->render();

        // Assert essential booking info is present
        $this->assertStringContainsString('BK-TEST-2026-9999', $html);
        $this->assertStringContainsString('Le Van C', $html);
        $this->assertStringContainsString('098 765 4321', $html);
        $this->assertStringContainsString('levanc@example.test', $html);
        $this->assertStringContainsString('Liệu Trình Trẻ Hóa Da Snapshot', $html);
        $this->assertStringContainsString('10:00', $html);

        // Assert customer input is escaped safely
        $this->assertStringContainsString('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert("xss")</script>', $html);

        // Assert no tracking cookies/attributions dumped
        $this->assertStringNotContainsString('utm_source', $html);
        $this->assertStringNotContainsString('fbp', $html);
        $this->assertStringNotContainsString('fbc', $html);
        $this->assertStringNotContainsString('session_id', $html);
    }

    public function test_email_notification_failure_does_not_destroy_or_fail_booking(): void
    {
        config([
            'booking.notifications.email.enabled' => true,
            'booking.notifications.email.recipient' => 'spa@example.test',
        ]);

        // Mock BookingNotificationService to simulate transport failure
        $notifierMock = $this->createMock(BookingNotificationService::class);
        $notifierMock->expects($this->once())
            ->method('notifySubmitted')
            ->willReturnCallback(function (Booking $b) {
                // Simulate what BookingNotificationService does on error:
                // logs without PII and returns false
                Log::error('Booking notification email failed to send.', [
                    'reference' => $b->reference,
                    'exception' => \RuntimeException::class,
                    'message' => 'Connection timed out',
                ]);

                return false;
            });

        $this->app->instance(BookingNotificationService::class, $notifierMock);

        $service = $this->createTestService('vi', 'Dịch Vụ Ngoại Lệ');
        $payload = $this->validPayload($service->id, [
            'customer_name' => 'Nguyen Isolation',
            'phone' => '093 333 4444',
            'email' => 'isolation@example.test',
            'notes' => 'Secret customer note',
        ]);

        // Submission must succeed from user standpoint (302 redirect with flash)
        $response = $this->from('/dat-lich')->post('/dat-lich', $payload);
        $response->assertRedirect('/dat-lich');
        $response->assertSessionHas('booking_status');

        // Booking MUST be preserved in database
        $this->assertDatabaseCount('bookings', 1);
        $booking = Booking::first();
        $this->assertSame('Nguyen Isolation', $booking->customer_name);
        $this->assertSame(BookingStatus::NEW, $booking->status);
    }

    public function test_notification_service_isolates_transport_exception_and_omits_pii(): void
    {
        config([
            'booking.notifications.email.enabled' => true,
            'booking.notifications.email.recipient' => 'spa@example.test',
        ]);

        $loggedErrors = [];
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use (&$loggedErrors) {
                $loggedErrors = ['message' => $message, 'context' => $context];

                return $message === 'Booking notification email failed to send.';
            });

        Mail::shouldReceive('to')
            ->once()
            ->with('spa@example.test')
            ->andReturnSelf();

        Mail::shouldReceive('send')
            ->once()
            ->andThrow(new \RuntimeException('SMTP Connection timed out: connection refused (500)'));

        $service = $this->createTestService('vi', 'Dịch Vụ Test Service');
        $booking = Booking::create([
            'reference' => 'BK-ISOLATION-001',
            'locale' => 'vi',
            'service_id' => $service->id,
            'status' => BookingStatus::NEW,
            'customer_name' => 'Secret Customer Name',
            'phone' => '091 111 2222',
            'phone_normalized' => '+84911112222',
            'email' => 'secret@example.test',
            'preferred_date' => CarbonImmutable::now('Asia/Ho_Chi_Minh')->addDay()->toDateString(),
            'preferred_time' => '10:00',
            'customer_note' => 'Highly confidential customer medical condition',
            'service_name_snapshot' => 'Dịch Vụ Test Service',
        ]);

        $notifier = new BookingNotificationService;
        $result = $notifier->notifySubmitted($booking);

        // Result is false (failure), but no exception thrown
        $this->assertFalse($result);

        // Verify logged error context has NO customer PII
        $this->assertNotEmpty($loggedErrors);
        $this->assertSame('BK-ISOLATION-001', $loggedErrors['context']['reference'] ?? null);
        $this->assertSame(\RuntimeException::class, $loggedErrors['context']['exception'] ?? null);
        $this->assertArrayNotHasKey('phone', $loggedErrors['context']);
        $this->assertArrayNotHasKey('phone_normalized', $loggedErrors['context']);
        $this->assertArrayNotHasKey('email', $loggedErrors['context']);
        $this->assertArrayNotHasKey('customer_note', $loggedErrors['context']);
        $this->assertArrayNotHasKey('notes', $loggedErrors['context']);
        $this->assertArrayNotHasKey('customer_name', $loggedErrors['context']);
    }

    protected function validPayload(int $serviceId, array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Khách Hàng Mẫu',
            'phone' => '090 123 4567',
            'email' => 'khach@example.test',
            'service_id' => $serviceId,
            'preferred_date' => CarbonImmutable::now('Asia/Ho_Chi_Minh')->addDay()->toDateString(),
            'preferred_time' => '14:30',
            'notes' => 'Phòng yên tĩnh',
            'consent' => '1',
        ], $overrides);
    }

    protected function assertStandaloneBookingTrigger(string $content, string $href, ?string $componentClass = null): void
    {
        $classLookahead = $componentClass === null
            ? ''
            : '(?=[^>]*class="[^"]*\b'.preg_quote($componentClass, '/').'\b)';

        $this->assertMatchesRegularExpression(
            '/<a(?=[^>]*href="'.preg_quote($href, '/').'")(?=[^>]*x-data="\{\}")(?=[^>]*data-booking-modal-trigger)(?=[^>]*@click\.prevent="\$dispatch\(\'open-booking-modal\', \{ trigger: \$el \}\)")'.$classLookahead.'[^>]*>/',
            $content
        );
    }

    protected function assertFooterBookingTrigger(string $content, string $href, string $label): void
    {
        preg_match('/<footer\b[^>]*>.*<\/footer>/s', $content, $footerMatch);
        $footer = $footerMatch[0] ?? '';

        $this->assertNotSame('', $footer);
        $this->assertStandaloneBookingTrigger($footer, $href);
        $this->assertStringContainsString($label, $footer);
    }

    protected function createTestService(string $locale, string $name): Service
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
            'locale' => $locale,
            'name' => $name,
            'slug' => Str::slug($name),
            'excerpt' => $name.' excerpt',
        ]);

        return $service;
    }
}
