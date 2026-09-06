<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PublicStaticPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_and_contact_routes_render_with_zero_page_records(): void
    {
        $this->assertDatabaseCount('pages', 0);
        $this->assertDatabaseCount('page_translations', 0);

        // VI About
        $this->get('/gioi-thieu')
            ->assertStatus(200)
            ->assertSee(__('about.hero.title', [], 'vi'))
            ->assertSee(__('about.hero.eyebrow', [], 'vi'))
            ->assertSee(__('about.story.title', [], 'vi'))
            ->assertSee(__('about.team.title', [], 'vi'))
            ->assertSee('about-hero-treatment-space');

        // EN About
        $this->get('/en/about')
            ->assertStatus(200)
            ->assertSee(__('about.hero.title', [], 'en'))
            ->assertSee(__('about.hero.eyebrow', [], 'en'))
            ->assertSee(__('about.story.title', [], 'en'))
            ->assertSee(__('about.team.title', [], 'en'))
            ->assertSee('about-hero-treatment-space');

        // VI Contact
        $this->get('/lien-he')
            ->assertStatus(200)
            ->assertSee(__('contact.hero.title', [], 'vi'))
            ->assertSee('090 123 4567')
            ->assertSee('tel:0901234567', false)
            ->assertSee('mailto:info@viethanauhanspa.com', false)
            ->assertSee('contact-reception-lobby');

        // EN Contact
        $this->get('/en/contact')
            ->assertStatus(200)
            ->assertSee(__('contact.hero.title', [], 'en'))
            ->assertSee('090 123 4567')
            ->assertSee('tel:0901234567', false)
            ->assertSee('mailto:info@viethanauhanspa.com', false)
            ->assertSee('contact-reception-lobby');

        // Non-canonical /vi prefix routes return 404
        $this->get('/vi/gioi-thieu')->assertStatus(404);
        $this->get('/vi/lien-he')->assertStatus(404);
    }

    public function test_cms_database_records_cannot_override_static_about_and_contact_pages(): void
    {
        // Malicious / different database CMS records
        $maliciousAbout = Page::create([
            'key' => 'about',
            'status' => ContentStatus::PUBLISHED,
        ]);
        PageTranslation::create([
            'page_id' => $maliciousAbout->id,
            'locale' => 'vi',
            'title' => 'Hacked About Title From CMS',
            'content' => '<p>Malicious CMS content should never show.</p>',
            'seo_title' => 'Hacked SEO Title',
            'seo_description' => 'Hacked SEO Description',
        ]);

        $maliciousContact = Page::create([
            'key' => 'contact',
            'status' => ContentStatus::PUBLISHED,
        ]);
        PageTranslation::create([
            'page_id' => $maliciousContact->id,
            'locale' => 'vi',
            'title' => 'Hacked Contact Title From CMS',
            'content' => '<p>Malicious Contact content.</p>',
            'seo_title' => 'Hacked Contact SEO',
        ]);

        // Public About must render code-owned static copy, ignoring CMS records
        $aboutResponse = $this->get('/gioi-thieu');
        $aboutResponse->assertStatus(200);
        $aboutResponse->assertDontSee('Hacked About Title From CMS');
        $aboutResponse->assertDontSee('Malicious CMS content should never show.');
        $aboutResponse->assertDontSee('Hacked SEO Title');
        $aboutResponse->assertSee(__('about.hero.title', [], 'vi'));

        // Public Contact must render code-owned static copy, ignoring CMS records
        $contactResponse = $this->get('/lien-he');
        $contactResponse->assertStatus(200);
        $contactResponse->assertDontSee('Hacked Contact Title From CMS');
        $contactResponse->assertDontSee('Malicious Contact content.');
        $contactResponse->assertDontSee('Hacked Contact SEO');
        $contactResponse->assertSee(__('contact.hero.title', [], 'vi'));
    }

    public function test_static_pages_exact_locale_isolation_and_no_fallback_leakage(): void
    {
        $this->assertDatabaseCount('pages', 0);

        // VI About contains VI copy and no EN copy
        $viAbout = $this->get('/gioi-thieu');
        $viAbout->assertStatus(200);
        $viAbout->assertSee(__('about.hero.title', [], 'vi'));
        $viAbout->assertDontSee(__('about.hero.title', [], 'en'));
        $viAbout->assertDontSee('A Sanctuary of Balance');

        // EN About contains EN copy and no VI copy
        $enAbout = $this->get('/en/about');
        $enAbout->assertStatus(200);
        $enAbout->assertSee(__('about.hero.title', [], 'en'));
        $enAbout->assertDontSee(__('about.hero.title', [], 'vi'));
        $enAbout->assertDontSee('Nơi tìm lại sự cân bằng');

        // VI Contact contains VI copy and no EN copy
        $viContact = $this->get('/lien-he');
        $viContact->assertStatus(200);
        $viContact->assertSee(__('contact.hero.title', [], 'vi'));
        $viContact->assertDontSee(__('contact.hero.title', [], 'en'));
        $viContact->assertDontSee('We Are Here to Listen');

        // EN Contact contains EN copy and no VI copy
        $enContact = $this->get('/en/contact');
        $enContact->assertStatus(200);
        $enContact->assertSee(__('contact.hero.title', [], 'en'));
        $enContact->assertDontSee(__('contact.hero.title', [], 'vi'));
        $enContact->assertDontSee('Chúng tôi luôn sẵn sàng');
    }

    public function test_static_pages_seo_metadata_integrity_without_database_lookups(): void
    {
        $this->assertDatabaseCount('pages', 0);

        // 1. VI About SEO
        $this->get('/gioi-thieu')
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="https://viethanauhanspa.com/gioi-thieu">', false)
            ->assertSee('<link rel="alternate" hreflang="vi" href="https://viethanauhanspa.com/gioi-thieu">', false)
            ->assertSee('<link rel="alternate" hreflang="en" href="https://viethanauhanspa.com/en/about">', false)
            ->assertSee('<link rel="alternate" hreflang="x-default" href="https://viethanauhanspa.com/gioi-thieu">', false)
            ->assertSee('<title>Giới thiệu — Việt Hàn Âu Hàn Spa</title>', false)
            ->assertSee('<meta name="description" content="'.htmlspecialchars(__('about.meta.description', [], 'vi'), ENT_QUOTES, 'UTF-8').'">', false);

        // 2. EN About SEO
        $this->get('/en/about')
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="https://viethanauhanspa.com/en/about">', false)
            ->assertSee('<link rel="alternate" hreflang="vi" href="https://viethanauhanspa.com/gioi-thieu">', false)
            ->assertSee('<link rel="alternate" hreflang="en" href="https://viethanauhanspa.com/en/about">', false)
            ->assertSee('<link rel="alternate" hreflang="x-default" href="https://viethanauhanspa.com/gioi-thieu">', false)
            ->assertSee('<title>About — Việt Hàn Âu Hàn Spa</title>', false)
            ->assertSee('<meta name="description" content="'.htmlspecialchars(__('about.meta.description', [], 'en'), ENT_QUOTES, 'UTF-8').'">', false);

        // 3. VI Contact SEO
        $this->get('/lien-he')
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="https://viethanauhanspa.com/lien-he">', false)
            ->assertSee('<link rel="alternate" hreflang="vi" href="https://viethanauhanspa.com/lien-he">', false)
            ->assertSee('<link rel="alternate" hreflang="en" href="https://viethanauhanspa.com/en/contact">', false)
            ->assertSee('<link rel="alternate" hreflang="x-default" href="https://viethanauhanspa.com/lien-he">', false)
            ->assertSee('<title>Liên hệ — Việt Hàn Âu Hàn Spa</title>', false)
            ->assertSee('<meta name="description" content="'.htmlspecialchars(__('contact.meta.description', [], 'vi'), ENT_QUOTES, 'UTF-8').'">', false);

        // 4. EN Contact SEO
        $this->get('/en/contact')
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="https://viethanauhanspa.com/en/contact">', false)
            ->assertSee('<link rel="alternate" hreflang="vi" href="https://viethanauhanspa.com/lien-he">', false)
            ->assertSee('<link rel="alternate" hreflang="en" href="https://viethanauhanspa.com/en/contact">', false)
            ->assertSee('<link rel="alternate" hreflang="x-default" href="https://viethanauhanspa.com/lien-he">', false)
            ->assertSee('<title>Contact — Việt Hàn Âu Hàn Spa</title>', false)
            ->assertSee('<meta name="description" content="'.htmlspecialchars(__('contact.meta.description', [], 'en'), ENT_QUOTES, 'UTF-8').'">', false);
    }

    public function test_sitemap_includes_about_and_contact_routes_with_zero_page_records(): void
    {
        $this->assertDatabaseCount('pages', 0);

        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('<loc>https://viethanauhanspa.com/gioi-thieu</loc>', $content);
        $this->assertStringContainsString('<loc>https://viethanauhanspa.com/en/about</loc>', $content);
        $this->assertStringContainsString('<loc>https://viethanauhanspa.com/lien-he</loc>', $content);
        $this->assertStringContainsString('<loc>https://viethanauhanspa.com/en/contact</loc>', $content);
    }

    public function test_static_page_language_switch_uses_fixed_deterministic_route_pairs(): void
    {
        $this->assertDatabaseCount('pages', 0);

        // From VI About -> switch link points to EN About
        $this->get('/gioi-thieu')
            ->assertStatus(200)
            ->assertSee('href="'.route('en.about').'"', false);

        // From EN About -> switch link points to VI About
        $this->get('/en/about')
            ->assertStatus(200)
            ->assertSee('href="'.route('vi.about').'"', false);

        // From VI Contact -> switch link points to EN Contact
        $this->get('/lien-he')
            ->assertStatus(200)
            ->assertSee('href="'.route('en.contact').'"', false);

        // From EN Contact -> switch link points to VI Contact
        $this->get('/en/contact')
            ->assertStatus(200)
            ->assertSee('href="'.route('vi.contact').'"', false);
    }

    public function test_contact_page_has_no_post_mutation_route_and_uses_safe_links(): void
    {
        $publicStaticMutationRoutes = collect(Route::getRoutes())
            ->filter(function ($route) {
                $uri = $route->uri();

                return str_contains($uri, 'gioi-thieu')
                    || str_contains($uri, 'lien-he')
                    || str_contains($uri, 'about')
                    || str_contains($uri, 'contact')
                    || str_contains($uri, 'dat-lich')
                    || str_contains($uri, 'booking');
            })
            ->filter(fn ($route) => array_intersect(['POST', 'PUT', 'PATCH', 'DELETE'], $route->methods()) !== [])
            ->reject(fn ($route) => str_starts_with($route->uri(), 'admin'))
            ->values();

        // Only booking submission POST routes are allowed; Contact page is strictly read-only
        $this->assertSame(['dat-lich', 'en/booking'], $publicStaticMutationRoutes->pluck('uri')->sort()->values()->all());

        // Contact page contains verified links and no form
        $response = $this->get('/lien-he');
        $response->assertStatus(200);
        $response->assertSee('href="tel:0901234567"', false);
        $response->assertSee('href="mailto:info@viethanauhanspa.com"', false);
        $response->assertDontSee('<form', false);
    }
}
