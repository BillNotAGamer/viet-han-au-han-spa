<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TrustProxies::at([]);
        RateLimiter::clear('booking-submissions:198.51.100.1');
        RateLimiter::clear('booking-submissions:198.51.100.2');
        RateLimiter::clear('booking-submissions:203.0.113.50');
        RateLimiter::clear('booking-submissions:127.0.0.1');

        parent::tearDown();
    }

    public function test_baseline_security_headers_are_present_on_public_home(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_security_headers_present_on_english_home_and_services(): void
    {
        $responseEn = $this->get('/en');
        $responseEn->assertOk();
        $responseEn->assertHeader('X-Content-Type-Options', 'nosniff');
        $responseEn->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $responseEn->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $responseEn->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $responseServices = $this->get('/dich-vu');
        $responseServices->assertOk();
        $responseServices->assertHeader('X-Content-Type-Options', 'nosniff');
        $responseServices->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_security_headers_present_on_sitemap(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_hsts_is_omitted_under_local_or_non_secure_requests(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $this->assertFalse($response->headers->has('Strict-Transport-Security'));
    }

    public function test_hsts_is_injected_when_production_and_secure(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $response = $this->get('https://localhost/');

        $response->assertOk();
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000');
    }

    public function test_untrusted_client_cannot_spoof_ip_with_x_forwarded_for(): void
    {
        // No proxies are trusted
        TrustProxies::at([]);

        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '198.51.100.1',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.199',
        ])->get('/');

        $response->assertOk();

        // Resolving request IP should strictly equal the real remote socket IP, not the spoofed header
        $resolvedIp = request()->ip();
        $this->assertSame('198.51.100.1', $resolvedIp);
    }

    public function test_trusted_proxy_correctly_forwards_real_visitor_ip(): void
    {
        // Trust proxy 192.168.1.1
        TrustProxies::at(['192.168.1.1']);
        TrustProxies::withHeaders(
            Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO
        );

        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.50',
        ])->get('/');

        $response->assertOk();

        // The resolved client IP should be the forwarded IP since the remote proxy is in the trusted list
        $resolvedIp = request()->ip();
        $this->assertSame('203.0.113.50', $resolvedIp);
    }

    public function test_booking_rate_limiter_distinguishes_different_client_ips(): void
    {
        $service = $this->createActiveService();

        $payload = [
            'service_id' => $service->id,
            'customer_name' => 'Test Customer',
            'phone' => '0901234567',
            'preferred_date' => now()->addDays(2)->format('Y-m-d'),
            'preferred_time' => '10:00',
            'consent' => '1',
        ];

        // Send request from IP 1
        $response1 = $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.1'])
            ->post('/dat-lich', $payload);
        $response1->assertRedirect();
        $this->assertNotEquals(429, $response1->getStatusCode());

        // Send request from IP 2
        $response2 = $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.2'])
            ->post('/dat-lich', $payload);
        $response2->assertRedirect();
        $this->assertNotEquals(429, $response2->getStatusCode());
    }

    public function test_booking_rate_limiter_throttles_excessive_submissions_from_same_ip(): void
    {
        $service = $this->createActiveService();

        $payload = [
            'service_id' => $service->id,
            'customer_name' => 'Rate Limit Test',
            'phone' => '0901234567',
            'preferred_date' => now()->addDays(2)->format('Y-m-d'),
            'preferred_time' => '10:00',
            'consent' => '1',
        ];

        $server = ['REMOTE_ADDR' => '198.51.100.1'];

        // 5 allowed requests per minute
        for ($i = 0; $i < 5; $i++) {
            $res = $this->withServerVariables($server)->post('/dat-lich', $payload);
            $res->assertRedirect();
        }

        // 6th request must be throttled (HTTP 429)
        $res6 = $this->withServerVariables($server)->post('/dat-lich', $payload);
        $res6->assertStatus(429);
    }

    public function test_production_debug_mode_error_does_not_expose_stack_trace(): void
    {
        Config::set('app.debug', false);

        $response = $this->get('/non-existent-page-404-check');

        $response->assertNotFound();
        $content = $response->getContent();
        $this->assertStringNotContainsString('Stack trace:', (string) $content);
        $this->assertStringNotContainsString('SQLSTATE', (string) $content);
        $this->assertStringNotContainsString('APP_KEY', (string) $content);
    }

    public function test_session_cookie_configuration_has_secure_defaults(): void
    {
        $this->assertTrue(Config::get('session.http_only'));
        $this->assertSame('lax', Config::get('session.same_site'));
    }

    public function test_anonymous_user_cannot_access_filament_admin(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_public_cannot_register_as_admin(): void
    {
        $response = $this->get('/admin/register');

        $response->assertNotFound();
    }

    protected function createActiveService(): Service
    {
        $category = ServiceCategory::create([
            'status' => 'PUBLISHED',
            'sort_order' => 1,
        ]);

        $service = Service::create([
            'service_category_id' => $category->id,
            'hero_media_id' => null,
            'status' => 'PUBLISHED',
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        ServiceTranslation::create([
            'service_id' => $service->id,
            'locale' => 'vi',
            'name' => 'Chăm sóc da',
            'slug' => 'cham-soc-da',
            'excerpt' => 'Mô tả dịch vụ',
            'content' => '<p>Chi tiết dịch vụ</p>',
        ]);

        return $service;
    }
}
