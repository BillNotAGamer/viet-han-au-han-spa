<?php

declare(strict_types=1);

namespace Tests\Feature\Localization;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Utf8IntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_vietnamese_ui_strings_render_without_mojibake(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Core Brand Name
        $response->assertSee('Việt Hàn Âu Hàn Spa');

        // Navigation & Core CTA
        $response->assertSee('Trang chủ');
        $response->assertSee('Dịch vụ');
        $response->assertSee('Đào tạo học viên');
        $response->assertSee('Giới thiệu');
        $response->assertSee('Liên hệ');
        $response->assertSee('Đặt lịch');

        // Status text
        $response->assertSee('Hệ Thống Trực Tuyến Đang Được Xây Dựng');
    }
}
