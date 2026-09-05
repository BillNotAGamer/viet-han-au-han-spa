<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\ContentStatus;
use App\Enums\SiteSettingType;
use App\Models\Media;
use App\Models\MediaTranslation;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\SiteSetting;
use App\Services\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicStaticPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_and_contact_routes_render_with_public_data_and_vi_prefix_is_not_canonical(): void
    {
        $this->createPageWithTranslation('about', ContentStatus::PUBLISHED, 'vi', 'Gioi Thieu Viet Han');
        $this->createPageWithTranslation('about', ContentStatus::PUBLISHED, 'en', 'About Viet Han');
        $this->createPageWithTranslation('contact', ContentStatus::PUBLISHED, 'vi', 'Lien He Viet Han');
        $this->createPageWithTranslation('contact', ContentStatus::PUBLISHED, 'en', 'Contact Viet Han');

        $this->get('/gioi-thieu')->assertStatus(200)->assertSee('Gioi Thieu Viet Han');
        $this->get('/en/about')->assertStatus(200)->assertSee('About Viet Han');
        $this->get('/lien-he')->assertStatus(200)->assertSee('Lien He Viet Han');
        $this->get('/en/contact')->assertStatus(200)->assertSee('Contact Viet Han');

        $this->get('/vi/gioi-thieu')->assertStatus(404);
        $this->get('/vi/lien-he')->assertStatus(404);
    }

    public function test_static_pages_require_published_status_and_exact_key(): void
    {
        $publishedAbout = $this->createPageWithTranslation('about', ContentStatus::PUBLISHED, 'vi', 'Published About');
        $draftContact = $this->createPageWithTranslation('contact', ContentStatus::DRAFT, 'vi', 'Draft Contact');

        $this->get('/gioi-thieu')->assertStatus(200)->assertSee('Published About');
        $this->get('/lien-he')->assertStatus(404)->assertDontSee('Draft Contact');

        $publishedAbout->update(['status' => ContentStatus::DRAFT]);
        $this->get('/gioi-thieu')->assertStatus(404);

        $publishedAbout->update(['status' => ContentStatus::ARCHIVED]);
        $this->get('/gioi-thieu')->assertStatus(404);

        $draftContact->update(['status' => ContentStatus::PUBLISHED]);
        $this->get('/lien-he')->assertStatus(200)->assertSee('Draft Contact');
        $draftContact->delete();
        $this->get('/lien-he')->assertStatus(404);
    }

    public function test_static_pages_require_exact_requested_locale_without_fallback(): void
    {
        $this->createPageWithTranslation('about', ContentStatus::PUBLISHED, 'vi', 'VI Only About');
        $this->createPageWithTranslation('contact', ContentStatus::PUBLISHED, 'vi', 'VI Only Contact');

        $this->get('/gioi-thieu')
            ->assertStatus(200)
            ->assertSee('VI Only About');

        $this->get('/lien-he')
            ->assertStatus(200)
            ->assertSee('VI Only Contact');

        $this->get('/en/about')
            ->assertStatus(404)
            ->assertDontSee('VI Only About');

        $this->get('/en/contact')
            ->assertStatus(404)
            ->assertDontSee('VI Only Contact');
    }

    public function test_page_translation_slug_is_irrelevant_to_static_public_routes(): void
    {
        $about = $this->createPageWithTranslation('about', ContentStatus::PUBLISHED, 'vi', 'About With Null Slug', null);
        $contact = $this->createPageWithTranslation('contact', ContentStatus::PUBLISHED, 'vi', 'Contact With Arbitrary Slug', 'editorial-contact-slug');

        $this->get('/gioi-thieu')->assertStatus(200)->assertSee('About With Null Slug');
        $this->get('/lien-he')->assertStatus(200)->assertSee('Contact With Arbitrary Slug');

        $this->assertNull($about->translationFor('vi')?->slug);
        $this->get('/editorial-contact-slug')->assertStatus(404);
    }

    public function test_contact_uses_explicit_public_site_setting_allow_list_without_dumping_private_or_unrelated_settings(): void
    {
        $this->createPageWithTranslation('contact', ContentStatus::PUBLISHED, 'vi', 'Contact Settings Page');
        $this->createSetting('contact.phone', '0988 777 666', true);
        $this->createSetting('contact.email', 'hello@example.test', true);
        $this->createSetting('contact.address', 'Published Contact Address', true);
        $this->createSetting('business.hours', '09:00 - 18:00', true);
        $this->createSetting('social.facebook_url', 'https://facebook.example/viet-han', true);
        $this->createSetting('internal.ops_phone', '0911 222 333', false);
        $this->createSetting('marketing.banner', 'Unrelated Public Setting Must Not Dump', true);

        $response = $this->get('/lien-he');

        $response->assertStatus(200);
        $response->assertSee('0988 777 666');
        $response->assertSee('mailto:hello@example.test', false);
        $response->assertSee('Published Contact Address');
        $response->assertSee('09:00 - 18:00');
        $response->assertSee('https://facebook.example/viet-han', false);
        $response->assertDontSee('0911 222 333');
        $response->assertDontSee('Unrelated Public Setting Must Not Dump');
    }

    public function test_contact_omits_unsafe_url_settings_and_invalid_active_links(): void
    {
        $this->createPageWithTranslation('contact', ContentStatus::PUBLISHED, 'vi', 'Unsafe Contact Page');
        $this->createSetting('contact.phone', 'javascript:alert(1)', true);
        $this->createSetting('contact.email', 'javascript:alert(1)', true);
        $this->createSetting('social.facebook_url', 'javascript:alert(1)', true);
        $this->createSetting('social.zalo_url', 'data:text/html,<b>x</b>', true);
        $this->createSetting('social.youtube_url', 'file:///private/path', true);

        $response = $this->get('/lien-he');

        $response->assertStatus(200);
        $response->assertDontSee('href="javascript:', false);
        $response->assertDontSee('href="data:', false);
        $response->assertDontSee('href="file:', false);
        $response->assertDontSee('mailto:javascript:', false);
        $response->assertDontSee('tel:javascript', false);
        $response->assertDontSee('javascript:alert(1)');
        $response->assertDontSee('data:text/html');
        $response->assertDontSee('file:///private/path');
    }

    public function test_contact_page_with_missing_settings_renders_without_fake_contact_details(): void
    {
        $this->createPageWithTranslation('contact', ContentStatus::PUBLISHED, 'vi', 'Contact Without Settings');

        $this->get('/lien-he')
            ->assertStatus(200)
            ->assertSee(__('pages.contact.empty', [], 'vi'))
            ->assertDontSee('090 123 4567')
            ->assertDontSee('info@viethanauhanspa.com');
    }

    public function test_page_media_resolves_exact_locale_alt_and_preserves_order_without_crashing_on_missing_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/page-lead.jpg', 'fake-image');
        Storage::disk('public')->put('media/page-gallery-one.jpg', 'fake-image');
        Storage::disk('public')->put('media/page-gallery-two.jpg', 'fake-image');

        $lead = $this->createMedia('media/page-lead.jpg');
        MediaTranslation::create([
            'media_id' => $lead->id,
            'locale' => 'vi',
            'alt_text' => 'Exact VI Page Lead Alt',
        ]);

        $galleryOne = $this->createMedia('media/page-gallery-one.jpg');
        MediaTranslation::create([
            'media_id' => $galleryOne->id,
            'locale' => 'vi',
            'alt_text' => 'Exact VI Page Gallery One Alt',
            'caption' => 'Page gallery one caption',
        ]);

        $galleryTwo = $this->createMedia('media/page-gallery-two.jpg');
        MediaTranslation::create([
            'media_id' => $galleryTwo->id,
            'locale' => 'en',
            'alt_text' => 'English Page Alt Must Not Leak',
        ]);

        $page = $this->createPageWithTranslation('about', ContentStatus::PUBLISHED, 'vi', 'About With Media');
        $page->media()->attach($galleryTwo->id, ['sort_order' => 2, 'created_at' => now()]);
        $page->media()->attach($lead->id, ['sort_order' => 0, 'created_at' => now()]);
        $page->media()->attach($galleryOne->id, ['sort_order' => 1, 'created_at' => now()]);

        $response = $this->get('/gioi-thieu');

        $response->assertStatus(200);
        $response->assertSee('page-lead.jpg');
        $response->assertSee('Exact VI Page Lead Alt');
        $response->assertSeeInOrder(['page-gallery-one.jpg', 'page-gallery-two.jpg']);
        $response->assertSee('Exact VI Page Gallery One Alt');
        $response->assertSee('alt=""', false);
        $response->assertDontSee('English Page Alt Must Not Leak');

        $missing = $this->createMedia('media/missing-page.jpg');
        $missingPage = $this->createPageWithTranslation('contact', ContentStatus::PUBLISHED, 'vi', 'Contact With Missing Media');
        $missingPage->media()->attach($missing->id, ['sort_order' => 0, 'created_at' => now()]);

        $this->get('/lien-he')
            ->assertStatus(200)
            ->assertSee('Contact With Missing Media')
            ->assertDontSee('missing-page.jpg');
    }

    public function test_page_rich_content_is_rendered_as_safe_plain_text_without_raw_markup(): void
    {
        $page = $this->createPageWithTranslation('about', ContentStatus::PUBLISHED, 'vi', 'Rich Static Page');
        $page->translationFor('vi')->update([
            'content' => '<p>Visible page content.</p><script>alert("x")</script><strong>Styled page text</strong><img src=x onerror=alert(1)>',
        ]);

        $this->get('/gioi-thieu')
            ->assertStatus(200)
            ->assertSee('Visible page content.')
            ->assertSee('Styled page text')
            ->assertDontSee('<script>alert', false)
            ->assertDontSee('alert("x")')
            ->assertDontSee('<strong>', false)
            ->assertDontSee('onerror', false);
    }

    public function test_static_page_language_switch_uses_fixed_route_pairs_and_home_fallback_for_missing_translation(): void
    {
        $about = $this->createPageWithTranslation('about', ContentStatus::PUBLISHED, 'vi', 'Bilingual About VI');
        PageTranslation::create([
            'page_id' => $about->id,
            'locale' => 'en',
            'title' => 'Bilingual About EN',
            'slug' => 'ignored-about-slug',
        ]);

        $contact = $this->createPageWithTranslation('contact', ContentStatus::PUBLISHED, 'vi', 'Bilingual Contact VI');
        PageTranslation::create([
            'page_id' => $contact->id,
            'locale' => 'en',
            'title' => 'Bilingual Contact EN',
            'slug' => null,
        ]);

        $this->get('/gioi-thieu')
            ->assertStatus(200)
            ->assertSee('href="'.route('en.about').'"', false);

        $this->get('/en/about')
            ->assertStatus(200)
            ->assertSee('href="'.route('vi.about').'"', false);

        $this->get('/lien-he')
            ->assertStatus(200)
            ->assertSee('href="'.route('en.contact').'"', false);

        $this->get('/en/contact')
            ->assertStatus(200)
            ->assertSee('href="'.route('vi.contact').'"', false);

        $about->translations()->where('locale', 'en')->delete();
        $about->translations()->where('locale', 'vi')->update(['title' => 'VI Only About Fallback']);

        $this->get('/gioi-thieu')
            ->assertStatus(200)
            ->assertSee('href="'.route('en.home').'"', false);
    }

    public function test_header_and_footer_navigation_use_phase_10d_routes_and_preserve_existing_public_routes(): void
    {
        $this->createPageWithTranslation('about', ContentStatus::PUBLISHED, 'vi', 'About Nav');
        $this->createPageWithTranslation('contact', ContentStatus::PUBLISHED, 'vi', 'Contact Nav');
        $this->createPageWithTranslation('about', ContentStatus::PUBLISHED, 'en', 'About Nav EN');
        $this->createPageWithTranslation('contact', ContentStatus::PUBLISHED, 'en', 'Contact Nav EN');

        $this->get('/gioi-thieu')
            ->assertStatus(200)
            ->assertSee('href="'.route('vi.about').'"', false)
            ->assertSee('href="'.route('vi.contact').'"', false)
            ->assertSee('href="'.route('vi.services.index').'"', false)
            ->assertSee('href="'.route('vi.training.index').'"', false)
            ->assertSee('href="'.route('vi.blog.index').'"', false)
            ->assertSee('class="public-header__link"', false);

        $this->get('/en/about')
            ->assertStatus(200)
            ->assertSee('href="'.route('en.about').'"', false)
            ->assertSee('href="'.route('en.contact').'"', false)
            ->assertSee('href="'.route('en.services.index').'"', false)
            ->assertSee('href="'.route('en.training.index').'"', false)
            ->assertSee('href="'.route('en.blog.index').'"', false)
            ->assertSee('class="public-header__link"', false);
    }

    public function test_phase_11_keeps_static_contact_mutations_closed_while_booking_post_exists(): void
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

        $this->assertSame(['dat-lich', 'en/booking'], $publicStaticMutationRoutes->pluck('uri')->sort()->values()->all());
    }

    protected function createPageWithTranslation(
        string $key,
        ContentStatus $status,
        string $locale,
        string $title,
        ?string $slug = null,
        ?string $content = null
    ): Page {
        $page = Page::updateOrCreate(
            ['key' => $key],
            ['status' => $status]
        );

        PageTranslation::updateOrCreate(
            [
                'page_id' => $page->id,
                'locale' => $locale,
            ],
            [
                'title' => $title,
                'slug' => $slug,
                'content' => $content ?? '<p>'.$title.' body content.</p>',
            ]
        );

        return $page->fresh(['translations']);
    }

    protected function createSetting(string $key, string $value, bool $isPublic): SiteSetting
    {
        $setting = SiteSetting::create([
            'key' => $key,
            'value' => $value,
            'type' => SiteSettingType::STRING,
            'group' => str_contains($key, 'social.') ? 'social' : 'contact',
            'is_public' => $isPublic,
        ]);

        app(SiteSettings::class)->clearCache($key);
        Cache::forget(SiteSettings::PUBLIC_ALL_CACHE_KEY);

        return $setting;
    }

    protected function createMedia(string $path): Media
    {
        return Media::create([
            'disk' => 'public',
            'path' => $path,
            'file_name' => basename($path),
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size_bytes' => 1024,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
