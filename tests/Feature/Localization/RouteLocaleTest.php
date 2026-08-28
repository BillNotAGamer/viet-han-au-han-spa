<?php

declare(strict_types=1);

namespace Tests\Feature\Localization;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_vietnamese_root_route_sets_locale_and_renders_correctly(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $this->assertSame('vi', app()->getLocale());
        $response->assertSee('<html lang="vi"', false);
        $response->assertSee('Việt Hàn Âu Hàn Spa');
        $response->assertSee('Trang chủ');
        $response->assertSee('Đặt lịch');
    }

    public function test_english_prefix_route_sets_locale_and_renders_correctly(): void
    {
        $response = $this->get('/en');

        $response->assertStatus(200);
        $this->assertSame('en', app()->getLocale());
        $response->assertSee('<html lang="en"', false);
        $response->assertSee('Việt Hàn Âu Hàn Spa');
        $response->assertSee('Home');
        $response->assertSee('Book now');
    }

    public function test_vi_prefix_redirects_permanently_to_root(): void
    {
        $response = $this->get('/vi');

        $response->assertStatus(301);
        $response->assertRedirect('/');
    }
}
