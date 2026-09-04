<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\ContentStatus;
use App\Enums\SiteSettingType;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceTranslation;
use App\Models\User;
use App\Services\Settings\SiteSettingWriter;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TrackingAndAttributionTest extends TestCase
{
    use RefreshDatabase;

    protected SiteSettingWriter $writer;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->writer = app(SiteSettingWriter::class);
    }

    public function test_tracking_disabled_by_default_and_renders_no_tags(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('googletagmanager.com/gtm.js');
        $response->assertDontSee('googletagmanager.com/ns.html');
        $response->assertDontSee('googletagmanager.com/gtag/js');
        $response->assertDontSee('connect.facebook.net/en_US/fbevents.js');
        $response->assertDontSee('booking_request_submitted');
        $response->assertDontSee('generate_lead');
    }

    public function test_gtm_mode_exclusive_delivery_when_all_providers_configured(): void
    {
        $this->setPublicSetting('tracking.enabled', '1', SiteSettingType::BOOLEAN);
        $this->setPublicSetting('tracking.gtm_container_id', 'GTM-TEST1234', SiteSettingType::STRING);
        $this->setPublicSetting('tracking.ga4_measurement_id', 'G-ABCDEF12', SiteSettingType::STRING);
        $this->setPublicSetting('tracking.meta_pixel_id', '123456789', SiteSettingType::STRING);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee("'dataLayer','GTM-TEST1234'", false);
        $response->assertSee('https://www.googletagmanager.com/ns.html?id=GTM-TEST1234', false);
        $response->assertDontSee('https://www.googletagmanager.com/gtag/js?id=G-ABCDEF12', false);
        $response->assertDontSee('https://connect.facebook.net/en_US/fbevents.js', false);
        $response->assertDontSee("fbq('init'", false);
    }

    public function test_direct_ga4_only_mode(): void
    {
        $this->setPublicSetting('tracking.enabled', '1', SiteSettingType::BOOLEAN);
        $this->setPublicSetting('tracking.ga4_measurement_id', 'G-MEASURE12', SiteSettingType::STRING);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('https://www.googletagmanager.com/gtag/js?id=G-MEASURE12', false);
        $response->assertSee("gtag('config', 'G-MEASURE12')", false);
        $response->assertDontSee('gtm.js', false);
        $response->assertDontSee('fbevents.js', false);
    }

    public function test_direct_meta_only_mode(): void
    {
        $this->setPublicSetting('tracking.enabled', '1', SiteSettingType::BOOLEAN);
        $this->setPublicSetting('tracking.meta_pixel_id', '987654321', SiteSettingType::STRING);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('https://connect.facebook.net/en_US/fbevents.js', false);
        $response->assertSee("fbq('init', '987654321')", false);
        $response->assertDontSee('gtm.js', false);
        $response->assertDontSee('gtag/js', false);
    }

    public function test_direct_both_ga4_and_meta_mode(): void
    {
        $this->setPublicSetting('tracking.enabled', '1', SiteSettingType::BOOLEAN);
        $this->setPublicSetting('tracking.ga4_measurement_id', 'G-MEASURE12', SiteSettingType::STRING);
        $this->setPublicSetting('tracking.meta_pixel_id', '987654321', SiteSettingType::STRING);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('gtag/js?id=G-MEASURE12', false);
        $response->assertSee("fbq('init', '987654321')", false);
        $response->assertDontSee('gtm.js', false);
    }

    public function test_invalid_tracking_identifiers_and_xss_attempts_are_rejected(): void
    {
        $this->setPublicSetting('tracking.enabled', '1', SiteSettingType::BOOLEAN);
        $this->setPublicSetting('tracking.gtm_container_id', '</script><script>alert(1)</script>', SiteSettingType::STRING);
        $this->setPublicSetting('tracking.ga4_measurement_id', 'G-MALICIOUS" onclick="alert(1)"', SiteSettingType::STRING);
        $this->setPublicSetting('tracking.meta_pixel_id', "12345'; alert(1); //", SiteSettingType::STRING);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('alert(1)', false);
        $response->assertDontSee('gtm.js', false);
        $response->assertDontSee('gtag/js', false);
        $response->assertDontSee('fbevents.js', false);
    }

    public function test_admin_and_filament_endpoints_exclude_marketing_tracking(): void
    {
        $this->setPublicSetting('tracking.enabled', '1', SiteSettingType::BOOLEAN);
        $this->setPublicSetting('tracking.gtm_container_id', 'GTM-TEST1234', SiteSettingType::STRING);
        $this->setPublicSetting('tracking.ga4_measurement_id', 'G-ABCDEF12', SiteSettingType::STRING);
        $this->setPublicSetting('tracking.meta_pixel_id', '123456789', SiteSettingType::STRING);

        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/bookings');

        $response->assertStatus(200);
        $response->assertDontSee('gtm.js', false);
        $response->assertDontSee('gtag/js', false);
        $response->assertDontSee('fbevents.js', false);
        $response->assertDontSee('booking_request_submitted', false);
    }

    public function test_primary_campaign_attribution_persistence_from_landing_to_booking(): void
    {
        $service = $this->createServiceWithTranslation('vi', 'Massage Body Thụy Điển');

        $landingUrl = '/?utm_source=facebook&utm_medium=paid_social&utm_campaign=spa_launch&utm_content=video_a&utm_term=massage_neck&fbclid=fb_click_test_123';

        $this->withHeaders(['Referer' => 'https://facebook.com/ad_campaign_1'])
            ->get($landingUrl)
            ->assertStatus(200);

        $this->get('/dat-lich')->assertStatus(200);

        $this->post('/dat-lich', $this->validPayload($service->id, ['customer_name' => 'Tran Thi B']))
            ->assertRedirect('/dat-lich');

        $this->assertDatabaseCount('bookings', 1);
        $booking = Booking::first();

        $this->assertSame('facebook', $booking->utm_source);
        $this->assertSame('paid_social', $booking->utm_medium);
        $this->assertSame('spa_launch', $booking->utm_campaign);
        $this->assertSame('video_a', $booking->utm_content);
        $this->assertSame('massage_neck', $booking->utm_term);
        $this->assertSame('fb_click_test_123', $booking->fbclid);
        $this->assertStringContainsString('utm_source=facebook', (string) $booking->landing_page);
        $this->assertSame('https://facebook.com/ad_campaign_1', $booking->referrer);
    }

    public function test_attribution_survives_internal_navigation(): void
    {
        $service = $this->createServiceWithTranslation('vi', 'Chăm Sóc Da Chuyên Sâu');

        $this->get('/?utm_source=google&utm_medium=cpc&utm_campaign=brand_search&gclid=gclid_sample_999')
            ->assertStatus(200);

        $this->get('/dich-vu')->assertStatus(200);
        $this->get('/dat-lich')->assertStatus(200);

        $this->post('/dat-lich', $this->validPayload($service->id))
            ->assertRedirect('/dat-lich');

        $booking = Booking::first();
        $this->assertSame('google', $booking->utm_source);
        $this->assertSame('cpc', $booking->utm_medium);
        $this->assertSame('brand_search', $booking->utm_campaign);
        $this->assertSame('gclid_sample_999', $booking->gclid);
    }

    public function test_latest_campaign_touch_updates_campaign_while_preserving_initial_landing(): void
    {
        $service = $this->createServiceWithTranslation('vi', 'Dịch Vụ Cổ Vai Gáy');

        $this->get('/?utm_source=facebook&utm_campaign=first_campaign')
            ->assertStatus(200);

        $this->get('/dich-vu?utm_source=google&utm_campaign=second_campaign&utm_medium=cpc')
            ->assertStatus(200);

        $this->post('/dat-lich', $this->validPayload($service->id))
            ->assertRedirect('/dat-lich');

        $booking = Booking::first();
        $this->assertSame('google', $booking->utm_source);
        $this->assertSame('second_campaign', $booking->utm_campaign);
        $this->assertSame('cpc', $booking->utm_medium);
        $this->assertStringContainsString('first_campaign', (string) $booking->landing_page);
    }

    public function test_organic_direct_visitor_attribution_fields_remain_null_without_fabricated_labels(): void
    {
        $service = $this->createServiceWithTranslation('vi', 'Dịch Vụ Trực Tiếp');

        $this->get('/')->assertStatus(200);
        $this->post('/dat-lich', $this->validPayload($service->id))
            ->assertRedirect('/dat-lich');

        $booking = Booking::first();
        $this->assertNull($booking->utm_source);
        $this->assertNull($booking->utm_medium);
        $this->assertNull($booking->utm_campaign);
        $this->assertNull($booking->utm_content);
        $this->assertNull($booking->utm_term);
        $this->assertNull($booking->gclid);
        $this->assertNull($booking->gbraid);
        $this->assertNull($booking->wbraid);
        $this->assertNull($booking->fbclid);
        $this->assertNull($booking->fbp);
        $this->assertNull($booking->fbc);
        $this->assertNotNull($booking->landing_page);
    }

    public function test_click_ids_and_oversized_inputs_are_safely_truncated(): void
    {
        $service = $this->createServiceWithTranslation('vi', 'Dịch Vụ Click ID Test');

        $oversizedGclid = str_repeat('g', 200);
        $oversizedGbraid = str_repeat('b', 150);
        $oversizedWbraid = str_repeat('w', 150);
        $oversizedFbclid = str_repeat('f', 200);

        $query = http_build_query([
            'gclid' => $oversizedGclid,
            'gbraid' => $oversizedGbraid,
            'wbraid' => $oversizedWbraid,
            'fbclid' => $oversizedFbclid,
        ]);

        $this->get('/?'.$query)->assertStatus(200);
        $this->post('/dat-lich', $this->validPayload($service->id))
            ->assertRedirect('/dat-lich');

        $booking = Booking::first();
        $this->assertSame(150, mb_strlen((string) $booking->gclid));
        $this->assertSame(100, mb_strlen((string) $booking->gbraid));
        $this->assertSame(100, mb_strlen((string) $booking->wbraid));
        $this->assertSame(150, mb_strlen((string) $booking->fbclid));
    }

    public function test_meta_browser_cookies_fbp_and_fbc_persisted(): void
    {
        $service = $this->createServiceWithTranslation('vi', 'Dịch Vụ Cookie Meta');

        $this->withCookies([
            '_fbp' => 'fb.1.1680000000.1234567890',
            '_fbc' => 'fb.1.1680000000.IwAR_test_click_id',
        ])->post('/dat-lich', $this->validPayload($service->id))
            ->assertRedirect('/dat-lich');

        $booking = Booking::first();
        $this->assertSame('fb.1.1680000000.1234567890', $booking->fbp);
        $this->assertSame('fb.1.1680000000.IwAR_test_click_id', $booking->fbc);
    }

    public function test_gtm_conversion_event_fired_after_prg_and_never_on_validation_failure_or_refresh(): void
    {
        $this->setPublicSetting('tracking.enabled', '1', SiteSettingType::BOOLEAN);
        $this->setPublicSetting('tracking.gtm_container_id', 'GTM-CONV123', SiteSettingType::STRING);

        $service = $this->createServiceWithTranslation('vi', 'Dịch Vụ GTM Conversion');

        $postResponse = $this->post('/dat-lich', $this->validPayload($service->id, ['customer_name' => 'Khách Hàng GTM']));
        $postResponse->assertRedirect('/dat-lich');

        $followResponse = $this->get('/dat-lich');
        $followResponse->assertStatus(200);
        $followResponse->assertSee('booking_request_submitted', false);
        $followResponse->assertSee("locale: 'vi'", false);
        $followResponse->assertSee('service_id: '.$service->id, false);
        $followResponse->assertDontSee('Khách Hàng GTM', false);

        // Refresh: conversion event must NOT appear again
        $refreshResponse = $this->get('/dat-lich');
        $refreshResponse->assertStatus(200);
        $refreshResponse->assertDontSee('booking_request_submitted', false);

        // Validation failure: conversion must not appear
        $failedPost = $this->post('/dat-lich', $this->validPayload($service->id, ['customer_name' => '']));
        $failedPost->assertRedirect('/dat-lich');
        $failedGet = $this->get('/dat-lich');
        $failedGet->assertDontSee('booking_request_submitted', false);
    }

    public function test_direct_ga4_conversion_event_emits_generate_lead(): void
    {
        $this->setPublicSetting('tracking.enabled', '1', SiteSettingType::BOOLEAN);
        $this->setPublicSetting('tracking.ga4_measurement_id', 'G-CONVGA4', SiteSettingType::STRING);

        $service = $this->createServiceWithTranslation('vi', 'Dịch Vụ GA4 Conversion');

        $this->post('/dat-lich', $this->validPayload($service->id))
            ->assertRedirect('/dat-lich');

        $response = $this->get('/dat-lich');
        $response->assertStatus(200);
        $response->assertSee("gtag('event', 'generate_lead'", false);
        $response->assertSee("locale: 'vi'", false);
        $response->assertSee('service_id: '.$service->id, false);

        $refresh = $this->get('/dat-lich');
        $refresh->assertDontSee('generate_lead', false);
    }

    public function test_direct_meta_conversion_event_emits_lead(): void
    {
        $this->setPublicSetting('tracking.enabled', '1', SiteSettingType::BOOLEAN);
        $this->setPublicSetting('tracking.meta_pixel_id', '123456789', SiteSettingType::STRING);

        $service = $this->createServiceWithTranslation('vi', 'Dịch Vụ Meta Conversion');

        $this->post('/dat-lich', $this->validPayload($service->id))
            ->assertSessionHasNoErrors()
            ->assertRedirect('/dat-lich');

        $response = $this->get('/dat-lich');
        $response->assertStatus(200);
        $response->assertSee("fbq('track', 'Lead'", false);
        $response->assertSee("locale: 'vi'", false);
        $response->assertSee('service_id: '.$service->id, false);

        $refresh = $this->get('/dat-lich');
        $refresh->assertDontSee("fbq('track', 'Lead'", false);
    }

    public function test_no_double_conversion_when_all_providers_configured(): void
    {
        $this->setPublicSetting('tracking.enabled', '1', SiteSettingType::BOOLEAN);
        $this->setPublicSetting('tracking.gtm_container_id', 'GTM-ALL123', SiteSettingType::STRING);
        $this->setPublicSetting('tracking.ga4_measurement_id', 'G-ALL123', SiteSettingType::STRING);
        $this->setPublicSetting('tracking.meta_pixel_id', '123456789', SiteSettingType::STRING);

        $service = $this->createServiceWithTranslation('vi', 'Dịch Vụ Multi Config');

        $this->post('/dat-lich', $this->validPayload($service->id))
            ->assertRedirect('/dat-lich');

        $response = $this->get('/dat-lich');
        $response->assertStatus(200);
        $response->assertSee('booking_request_submitted', false);
        $response->assertDontSee('generate_lead', false);
        $response->assertDontSee("fbq('track', 'Lead'", false);
    }

    public function test_pii_leak_safety_in_tracking_output(): void
    {
        $this->setPublicSetting('tracking.enabled', '1', SiteSettingType::BOOLEAN);
        $this->setPublicSetting('tracking.gtm_container_id', 'GTM-PII123', SiteSettingType::STRING);

        $service = $this->createServiceWithTranslation('vi', 'Dịch Vụ An Toàn PII');

        $payload = $this->validPayload($service->id, [
            'customer_name' => 'Trinh Cong Son',
            'phone' => '0987654321',
            'email' => 'trinhcongson@example.test',
            'notes' => 'Distinctive customer notes 12345',
        ]);

        $this->post('/dat-lich', $payload)->assertRedirect('/dat-lich');

        $response = $this->get('/dat-lich');
        $html = $response->getContent();

        // Extract script tag containing booking_request_submitted
        preg_match('/<script>[\s\S]*?booking_request_submitted[\s\S]*?<\/script>/', $html, $matches);
        $this->assertNotEmpty($matches);
        $trackingScript = $matches[0];

        $this->assertStringNotContainsString('Trinh Cong Son', $trackingScript);
        $this->assertStringNotContainsString('0987654321', $trackingScript);
        $this->assertStringNotContainsString('trinhcongson@example.test', $trackingScript);
        $this->assertStringNotContainsString('Distinctive customer notes 12345', $trackingScript);
    }

    public function test_booking_cta_and_primary_nav_regression(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('href="'.route('vi.booking.create').'"', false)
            ->assertDontSee('href="#contact-preview" class="public-floating-booking__link"', false);

        $this->get('/en')
            ->assertStatus(200)
            ->assertSee('href="'.route('en.booking.create').'"', false);
    }

    protected function setPublicSetting(string $key, string $value, SiteSettingType $type): void
    {
        $this->writer->create([
            'key' => $key,
            'value' => $value,
            'type' => $type,
            'is_public' => true,
        ]);
        Cache::flush();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function validPayload(int $serviceId, array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Test Guest',
            'phone' => '090 123 4567',
            'email' => 'guest@example.test',
            'service_id' => $serviceId,
            'preferred_date' => CarbonImmutable::now('Asia/Ho_Chi_Minh')->addDay()->toDateString(),
            'preferred_time' => '14:30',
            'notes' => 'Quiet room preferred.',
            'consent' => '1',
        ], $overrides);
    }

    protected function createServiceWithTranslation(
        string $locale,
        string $name,
        string $slug = 'service-test'
    ): Service {
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
            'locale' => $locale,
            'name' => $name,
            'slug' => $slug.'-'.uniqid(),
            'excerpt' => $name.' excerpt',
            'content' => '<p>'.$name.' content paragraph.</p>',
        ]);

        return $service->fresh(['translations']);
    }
}
