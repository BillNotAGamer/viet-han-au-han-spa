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
        $response->assertSee(':aria-expanded="effectiveDrawerOpen.toString()"', false);
        $response->assertSee('aria-label="Chuyển đổi menu di động"', false);
    }

    public function test_mobile_drawer_is_closed_by_default_and_uses_the_header_state_lifecycle(): void
    {
        foreach ([
            '/',
            '/gioi-thieu',
            '/dich-vu',
            '/dao-tao',
            '/blog',
            '/lien-he',
            '/en',
            '/en/about',
            '/en/services',
            '/en/training',
            '/en/blog',
            '/en/contact',
        ] as $path) {
            $content = (string) $this->get($path)->assertOk()->getContent();

            $this->assertStringContainsString('mobileOpen: false', $content);
            $this->assertStringContainsString('mobileMenuActivatedByUser: false', $content);
            $this->assertStringContainsString('return this.mobileOpen && this.mobileMenuActivatedByUser', $content);
            $this->assertStringContainsString('x-ref="mobileMenuTrigger"', $content);
            $this->assertStringContainsString('@click="effectiveDrawerOpen ? closeMobileMenu() : openMobileMenu()"', $content);
            $this->assertStringContainsString(':aria-expanded="effectiveDrawerOpen.toString()"', $content);
            $this->assertStringContainsString('id="public-mobile-menu"', $content);
            $this->assertSame(3, substr_count($content, 'x-show="effectiveDrawerOpen"'));
            $this->assertStringContainsString('x-show="!effectiveDrawerOpen"', $content);
            $this->assertStringContainsString('class="public-header__drawer"', $content);
            $this->assertStringContainsString('class="public-header__drawer-backdrop"', $content);
            $this->assertStringContainsString('@keydown.escape.window="closeMobileMenu()"', $content);
            $this->assertStringContainsString("window.matchMedia('(min-width: 1180px)')", $content);
            $this->assertStringContainsString("window.addEventListener('pagehide', this.pageHideHandler)", $content);
            $this->assertStringContainsString("window.addEventListener('pageshow', this.pageShowHandler)", $content);
            $this->assertStringContainsString('this.pageHideHandler = () => this.closeMobileMenu(false)', $content);
            $this->assertStringContainsString('this.pageShowHandler = () => this.closeMobileMenu(false)', $content);
            $this->assertStringContainsString('this.resizeHandler = () => this.closeMobileMenu(false)', $content);
            $this->assertStringContainsString("window.addEventListener('resize', this.resizeHandler, { passive: true })", $content);
            $this->assertStringContainsString("window.removeEventListener('resize', this.resizeHandler)", $content);
            $this->assertStringContainsString("document.addEventListener('visibilitychange', this.visibilityChangeHandler)", $content);
            $this->assertStringContainsString("document.removeEventListener('visibilitychange', this.visibilityChangeHandler)", $content);
            $this->assertStringContainsString("if (document.visibilityState === 'visible')", $content);
            $this->assertStringContainsString('window.removeEventListener(\'pagehide\', this.pageHideHandler)', $content);
            $this->assertStringContainsString('window.removeEventListener(\'pageshow\', this.pageShowHandler)', $content);
            $this->assertStringContainsString('this.mobileMenuActivatedByUser = false;', $content);
            $this->assertStringContainsString("document.body.classList.toggle('public-mobile-menu-open', this.effectiveDrawerOpen)", $content);
            $this->assertSame(1, substr_count($content, 'this.mobileMenuActivatedByUser = true;'));
            $this->assertSame(1, substr_count($content, 'this.mobileOpen = true;'));
            $this->assertStringContainsString('public-mobile-menu-open', $content);
        }

        $styles = (string) file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('[x-cloak]', $styles);
        $this->assertStringContainsString('display: none !important;', $styles);
        $this->assertStringContainsString('.public-mobile-menu-open', $styles);
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

        // V2 semantic heading roles carry word-safe wrapping in the design-system CSS.
        $response->assertSee('v2-type-display-xl', false);
        $response->assertDontSee('break-all', false);

        // Verify the V2 logo-only header branding remains accessible.
        $response->assertSee('aria-label="Việt Hàn Âu Hàn Spa"', false);
        $response->assertSee('v2-header__logo--white', false);
        $response->assertSee('v2-header__mobile-logo', false);
        $response->assertSee('<span class="sr-only">Việt Hàn Âu Hàn Spa</span>', false);
    }

    public function test_all_normal_public_pages_use_the_v2_shell(): void
    {
        foreach ([
            '/',
            '/en',
            '/gioi-thieu',
            '/en/about',
            '/lien-he',
            '/en/contact',
            '/dich-vu',
            '/en/services',
            '/dao-tao',
            '/en/training',
            '/blog',
            '/en/blog',
            '/dat-lich',
            '/en/booking',
        ] as $path) {
            $this->get($path)
                ->assertStatus(200)
                ->assertSee('v2-public-shell', false)
                ->assertSee('v2-header__nav--left', false)
                ->assertSee('v2-language-switcher', false);
        }
    }

    public function test_primary_inner_page_heroes_opt_into_the_shared_display_scale_without_affecting_home(): void
    {
        foreach ([
            '/gioi-thieu',
            '/en/about',
            '/dich-vu',
            '/en/services',
            '/dao-tao',
            '/en/training',
            '/blog',
            '/en/blog',
            '/lien-he',
            '/en/contact',
        ] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('v2-primary-page-hero__title', false);
        }

        $this->get('/')
            ->assertOk()
            ->assertDontSee('v2-primary-page-hero__title', false);
    }

    public function test_v2_motion_is_progressive_public_only_and_keeps_persistent_controls_independent_of_the_home_hero(): void
    {
        $content = (string) $this->get('/')->assertStatus(200)->getContent();

        $this->assertStringContainsString('@view-transition { navigation: auto; }', $content);
        $this->assertStringContainsString('<div class="public-floating-booking v2-floating-booking">', $content);
        $this->assertStringContainsString('<nav class="contact-dock" aria-label="Kênh liên hệ nhanh">', $content);
        $this->assertStringNotContainsString('data-persistent-quiet-hero', $content);
        $this->assertStringNotContainsString('data-persistent-ui', $content);
        $this->assertStringNotContainsString('motion-ready', $content);

        $heroPath = resource_path('images/homepage/viet-han-banner-hero.webp');
        $this->assertFileExists($heroPath);
        $this->assertLessThan(500 * 1024, filesize($heroPath));

        $homepage = (string) file_get_contents(resource_path('views/public/home.blade.php'));
        $this->assertStringContainsString('viet-han-banner-hero.webp', $homepage);
        $this->assertStringNotContainsString('viet-han-banner-hero.png', $homepage);

        $styles = (string) file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('html.motion-ready [data-reveal]', $styles);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $styles);
        $this->assertStringContainsString('::view-transition-old(v2-public-root)', $styles);

        $motionCoordinator = (string) file_get_contents(resource_path('js/app.js'));
        $this->assertStringContainsString('IntersectionObserver', $motionCoordinator);
        $this->assertStringContainsString('revealObserver.unobserve', $motionCoordinator);
        $this->assertStringContainsString('v2-skip-next-transition', $motionCoordinator);
        $this->assertStringNotContainsString('setPersistentQuiet', $motionCoordinator);
        $this->assertStringNotContainsString('persistentObserver', $motionCoordinator);
        $this->assertStringNotContainsString('data-parallax', $motionCoordinator);

        $this->assertStringNotContainsString('[data-persistent-ui]', $styles);
        $this->assertStringNotContainsString('.is-quiet', $styles);
    }

    public function test_contact_dock_and_floating_booking_cta_remain_usable_on_home_and_other_public_pages(): void
    {
        foreach (['/', '/dich-vu', '/en'] as $path) {
            $content = (string) $this->get($path)->assertOk()->getContent();

            $this->assertStringContainsString('class="contact-dock"', $content);
            $this->assertStringContainsString('class="public-floating-booking__link"', $content);
            $this->assertStringContainsString('data-booking-modal-trigger', $content);
            $this->assertStringNotContainsString('data-persistent-ui', $content);
            $this->assertStringNotContainsString('data-persistent-quiet-hero', $content);
        }
    }

    public function test_v2_header_centers_logo_and_moves_language_switching_to_footer(): void
    {
        foreach ([
            ['path' => '/', 'target' => url('/en'), 'active_label' => 'Tiếng Việt'],
            ['path' => '/en', 'target' => url('/'), 'active_label' => 'English'],
        ] as $case) {
            $content = (string) $this->get($case['path'])->assertStatus(200)->getContent();

            preg_match('/<header\b[^>]*>.*<\/header>/s', $content, $fullHeaderMatch);
            $fullHeader = $fullHeaderMatch[0] ?? '';
            preg_match('/<div class="v2-header__desktop">(.*?)<div class="public-header__mobile">/s', $content, $headerMatch);
            $desktopHeader = $headerMatch[1] ?? '';
            preg_match('/<div class="public-header__mobile">(.*?)<div class="public-header__mobile-actions">/s', $content, $mobileHeaderMatch);
            $mobileHeader = $mobileHeaderMatch[1] ?? '';
            preg_match('/<footer class="v2-footer">.*<\/footer>/s', $content, $footerMatch);
            $footer = $footerMatch[0] ?? '';

            $this->assertNotSame('', $desktopHeader);
            $this->assertNotSame('', $mobileHeader);
            $this->assertNotSame('', $fullHeader);
            $this->assertStringContainsString('v2-header__nav--left', $desktopHeader);
            $this->assertStringContainsString('v2-header__brand-link', $desktopHeader);
            $this->assertStringContainsString('v2-header__nav--right', $desktopHeader);
            $this->assertStringContainsString('viet-han-spa-white-logo-', $desktopHeader);
            $this->assertStringContainsString('viet-han-spa-no-bg-original-logo-', $desktopHeader);
            $this->assertStringContainsString('viet-han-spa-no-bg-original-logo-', $mobileHeader);
            $this->assertStringNotContainsString('viet-han-logo-', $mobileHeader);
            $this->assertTrue(strpos($desktopHeader, 'v2-header__nav--left') < strpos($desktopHeader, 'v2-header__brand-link'));
            $this->assertTrue(strpos($desktopHeader, 'v2-header__brand-link') < strpos($desktopHeader, 'v2-header__nav--right'));
            $this->assertStringNotContainsString('data-booking-modal-trigger', $desktopHeader);
            $this->assertStringNotContainsString('public-language-switcher', $desktopHeader);
            $this->assertStringNotContainsString('public-language-switcher', $fullHeader);
            $this->assertStringNotContainsString('v2-language-switcher', $fullHeader);

            $this->assertNotSame('', $footer);
            $this->assertStringContainsString('v2-language-switcher', $footer);
            $this->assertStringContainsString('href="'.$case['target'].'"', $footer);
            $this->assertMatchesRegularExpression(
                '/<a(?=[^>]*aria-current="page")(?=[^>]*aria-label="'.preg_quote($case['active_label'], '/').'")[^>]*>/',
                $footer
            );
        }

        $aboutContent = (string) $this->get('/gioi-thieu')->assertStatus(200)->getContent();
        $this->assertStringNotContainsString('public-language-switcher', $aboutContent);
        $this->assertStringContainsString('v2-language-switcher', $aboutContent);
    }

    public function test_v2_footer_renders_authoritative_contact_information_and_lazy_google_map_for_both_locales(): void
    {
        $mapUrl = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1162.253074056725!2d106.6994668582927!3d10.791759610991093!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317528b57e3038f9%3A0xdabf60e42628bc2f!2zMTE1IE5ndXnhu4VuIELhu4luaCBLaGnDqm0sIFTDom4gxJDhu4tuaCwgSOG7kyBDaMOtIE1pbmgsIFZp4buHdCBOYW0!5e1!3m2!1svi!2s!4v1790164676789!5m2!1svi!2s';

        foreach ([
            ['path' => '/', 'locale' => 'vi'],
            ['path' => '/en', 'locale' => 'en'],
        ] as $case) {
            $content = (string) $this->get($case['path'])->assertOk()->getContent();
            preg_match('/<footer class="v2-footer">.*<\/footer>/s', $content, $footerMatch);
            $footer = $footerMatch[0] ?? '';

            $this->assertNotSame('', $footer);
            $this->assertStringContainsString('href="tel:0902309026"', $footer);
            $this->assertStringContainsString('0902309026', $footer);
            $this->assertStringContainsString('115 Nguyễn Bỉnh Khiêm,', $footer);
            $this->assertStringContainsString('Tân Định,', $footer);
            $this->assertStringContainsString('Hồ Chí Minh,', $footer);
            $this->assertStringContainsString('Việt Nam', $footer);
            $this->assertStringContainsString(__('footer.phone', [], $case['locale']), $footer);
            $this->assertStringContainsString(__('footer.address', [], $case['locale']), $footer);
            $this->assertStringContainsString(__('footer.location', [], $case['locale']), $footer);
            $this->assertStringContainsString('class="v2-footer__map-frame"', $footer);
            $this->assertStringContainsString('loading="lazy"', $footer);
            $this->assertStringContainsString('referrerpolicy="strict-origin-when-cross-origin"', $footer);
            $this->assertStringContainsString('title="'.__('footer.map_title', [], $case['locale']).'"', $footer);
            $this->assertStringContainsString($mapUrl, html_entity_decode($footer, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $this->assertStringContainsString('v2-language-switcher', $footer);
        }
    }

    public function test_floating_contact_dock_renders_correct_targets_order_and_accessibility(): void
    {
        // 1. Vietnamese layout
        $viResponse = $this->get('/');
        $viResponse->assertStatus(200);

        // Verify dock container exists
        $viResponse->assertSee('class="contact-dock"', false);
        $viResponse->assertSee('aria-label="Kênh liên hệ nhanh"', false);

        // Verify Facebook target & attributes
        $viResponse->assertSee('href="https://www.facebook.com/profile.php?id=61575606630966"', false);
        $viResponse->assertSee('aria-label="Facebook Việt Hàn Âu Hàn Spa"', false);

        // Verify Messenger target & attributes
        $viResponse->assertSee('aria-label="Messenger Việt Hàn Âu Hàn Spa"', false);

        // Verify Zalo target & attributes
        $viResponse->assertSee('href="https://zalo.me/0902309026"', false);
        $viResponse->assertSee('aria-label="Zalo Việt Hàn Âu Hàn Spa"', false);

        // Verify Hotline tel target (no target="_blank")
        $viResponse->assertSee('href="tel:0902309026"', false);
        $viResponse->assertSee('aria-label="Gọi hotline Việt Hàn Âu Hàn Spa"', false);

        // Verify target="_blank" and rel="noopener noreferrer" on external links
        $content = (string) $viResponse->getContent();
        $this->assertMatchesRegularExpression('/href="https:\/\/zalo\.me\/0902309026"\s+target="_blank"\s+rel="noopener noreferrer"/', $content);

        // Verify strict vertical order: Facebook -> Messenger -> Zalo -> Hotline
        $fbPos = strpos($content, 'aria-label="Facebook Việt Hàn Âu Hàn Spa"');
        $msgPos = strpos($content, 'aria-label="Messenger Việt Hàn Âu Hàn Spa"');
        $zaloPos = strpos($content, 'aria-label="Zalo Việt Hàn Âu Hàn Spa"');
        $phonePos = strpos($content, 'aria-label="Gọi hotline Việt Hàn Âu Hàn Spa"');

        $this->assertNotFalse($fbPos);
        $this->assertNotFalse($msgPos);
        $this->assertNotFalse($zaloPos);
        $this->assertNotFalse($phonePos);
        $this->assertTrue($fbPos < $msgPos, 'Facebook must precede Messenger');
        $this->assertTrue($msgPos < $zaloPos, 'Messenger must precede Zalo');
        $this->assertTrue($zaloPos < $phonePos, 'Zalo must precede Hotline');

        // Verify Vietnamese tooltips
        $viResponse->assertSee('role="tooltip" aria-hidden="true">Facebook</span>', false);
        $viResponse->assertSee('role="tooltip" aria-hidden="true">Messenger</span>', false);
        $viResponse->assertSee('role="tooltip" aria-hidden="true">Zalo</span>', false);
        $viResponse->assertSee('role="tooltip" aria-hidden="true">Gọi ngay</span>', false);

        // 2. English layout
        $enResponse = $this->get('/en');
        $enResponse->assertStatus(200);
        $enResponse->assertSee('class="contact-dock"', false);
        $enResponse->assertSee('aria-label="Call hotline Việt Hàn Âu Hàn Spa"', false);
        $enResponse->assertSee('role="tooltip" aria-hidden="true">Call now</span>', false);
    }

    public function test_floating_contact_dock_is_excluded_from_admin_panel(): void
    {
        $response = $this->get('/admin/login');

        // Dock must NEVER be rendered on admin authentication or Filament pages
        $response->assertDontSee('contact-dock', false);
        $response->assertDontSee('https://zalo.me/0902309026', false);
        $response->assertDontSee('tel:0902309026', false);
    }
}
