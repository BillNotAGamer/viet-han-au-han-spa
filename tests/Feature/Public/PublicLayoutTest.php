<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\SiteSettingType;
use App\Services\Settings\SiteSettingWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_vi_root_uses_vi_lang_and_renders_public_layout_markers(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('<html lang="vi"', false);
        $response->assertSee('id="main-content"', false);
        $response->assertSee('Việt Hàn Âu Hàn Spa');
    }

    public function test_en_root_uses_en_lang_and_renders_public_layout_markers(): void
    {
        $response = $this->get('/en');

        $response->assertStatus(200);
        $response->assertSee('<html lang="en"', false);
        $response->assertSee('id="main-content"', false);
        $response->assertSee('Việt Hàn Âu Hàn Spa');
    }

    public function test_vi_prefix_redirects_301_to_root(): void
    {
        $response = $this->get('/vi');

        $response->assertStatus(301);
        $response->assertRedirect('/');
    }

    public function test_header_navigation_labels_are_localized(): void
    {
        // 1. Vietnamese header labels
        $viResponse = $this->get('/');
        $viResponse->assertSee('Trang chủ');
        $viResponse->assertSee('Dịch vụ');
        $viResponse->assertSee('Đào tạo học viên');
        $viResponse->assertSee('Blog');
        $viResponse->assertSee('Giới thiệu');
        $viResponse->assertSee('Liên hệ');
        $viResponse->assertSee('Đặt lịch');

        // 2. English header labels
        $enResponse = $this->get('/en');
        $enResponse->assertSee('Home');
        $enResponse->assertSee('Services');
        $enResponse->assertSee('Training');
        $enResponse->assertSee('Blog');
        $enResponse->assertSee('About');
        $enResponse->assertSee('Contact');
        $enResponse->assertSee('Book now');
    }

    public function test_language_switcher_targets_correct_urls_without_vi_prefix(): void
    {
        // 1. On VI homepage, switch target is /en
        $viResponse = $this->get('/');
        $viResponse->assertSee(url('/en'), false);
        $viResponse->assertDontSee(url('/vi'), false);

        // 2. On EN homepage, switch target is / (not /vi)
        $enResponse = $this->get('/en');
        $enResponse->assertSee('href="'.url('/').'"', false);
        $enResponse->assertDontSee(url('/vi'), false);
    }

    public function test_accessibility_markup_invariants(): void
    {
        $response = $this->get('/');

        // 1. Skip link exists
        $response->assertSee('href="#main-content"', false);
        $response->assertSee('Bỏ qua đến nội dung chính');

        // 2. Main landmark exists
        $response->assertSee('<main id="main-content"', false);

        // Verify exactly one main landmark exists in output
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertSame(1, substr_count($content, '<main id="main-content"'));

        // 3. Navigation landmarks exist
        $response->assertSee('aria-label="Điều hướng chính"', false);

        // 4. Mobile trigger button exists with aria-expanded
        $response->assertSee(':aria-expanded="mobileOpen.toString()"', false);
        $response->assertSee('aria-label="Chuyển đổi menu di động"', false);
    }

    public function test_site_settings_public_consumption_and_private_safety(): void
    {
        $writer = app(SiteSettingWriter::class);

        // 1. Public setting
        $writer->create([
            'key' => 'contact.phone',
            'value' => '0988 777 666',
            'type' => SiteSettingType::STRING,
            'is_public' => true,
        ]);

        // 2. Private setting
        $writer->create([
            'key' => 'internal.dispatch_phone',
            'value' => '0911 222 333',
            'type' => SiteSettingType::STRING,
            'is_public' => false,
        ]);

        $response = $this->get('/');

        // Public phone appears
        $response->assertSee('0988 777 666');

        // Private phone NEVER appears
        $response->assertDontSee('0911 222 333');
    }

    public function test_rendering_succeeds_with_zero_business_records(): void
    {
        // Database is refreshed and completely empty of business entities
        $viResponse = $this->get('/');
        $viResponse->assertStatus(200);

        $enResponse = $this->get('/en');
        $enResponse->assertStatus(200);
    }

    public function test_typography_invariants_and_heading_word_safety(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Verify section heading uses safe word-boundary wrapping and no break-all
        $response->assertSee('break-words', false);
        $response->assertDontSee('break-all', false);

        // Verify mobile brand lockup sizing
        $response->assertSee('text-lg sm:text-2xl', false);
    }
}
