<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\ContentStatus;
use App\Enums\HeaderServiceGroup;
use App\Models\Media;
use App\Models\MediaTranslation;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceCategoryTranslation;
use App\Models\ServicePrice;
use App\Models\ServicePriceTranslation;
use App\Models\ServiceTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_routes_render_and_vi_prefixed_route_is_not_canonical(): void
    {
        $this->get('/dich-vu')->assertStatus(200);
        $this->get('/en/services')->assertStatus(200);
        $this->get('/vi')->assertStatus(301)->assertRedirect('/');
        $this->get('/vi/dich-vu')->assertStatus(404);
    }

    public function test_empty_database_services_listing_renders_gracefully(): void
    {
        $this->get('/dich-vu')
            ->assertStatus(200)
            ->assertSee(__('services.index.empty', [], 'vi'))
            ->assertDontSee('Service #');

        $this->get('/en/services')
            ->assertStatus(200)
            ->assertSee(__('services.index.empty', [], 'en'))
            ->assertDontSee('Service #');
    }

    public function test_header_service_groups_render_exactly_four_localized_desktop_and_mobile_links(): void
    {
        foreach ([
            ['/', 'vi', 'vi.services.index'],
            ['/en', 'en', 'en.services.index'],
        ] as [$path, $locale, $indexRoute]) {
            $content = (string) $this->get($path)->assertOk()->getContent();
            preg_match('/<div[^>]*class="v2-header__services-menu".*?<\/div>/s', $content, $desktopMatch);
            preg_match('/<ul class="public-header__drawer-service-groups">.*?<\/ul>/s', $content, $mobileMatch);

            $desktop = $desktopMatch[0] ?? '';
            $mobile = $mobileMatch[0] ?? '';

            $this->assertNotSame('', $desktop);
            $this->assertNotSame('', $mobile);
            $this->assertSame(4, substr_count($desktop, 'v2-header__services-menu-link'));
            $this->assertSame(4, substr_count($mobile, 'public-header__drawer-service-group-link'));
            $this->assertStringContainsString('href="'.route($indexRoute).'"', $content);

            foreach (HeaderServiceGroup::cases() as $group) {
                $route = route($locale === 'vi' ? 'vi.services.group' : 'en.services.group', [
                    'group' => $group->routeSlug($locale),
                ]);

                $this->assertStringContainsString(e($group->label($locale)), $desktop);
                $this->assertStringContainsString('href="'.$route.'"', $desktop);
                $this->assertStringContainsString(e($group->label($locale)), $mobile);
                $this->assertStringContainsString('href="'.$route.'"', $mobile);
            }
        }
    }

    public function test_desktop_service_dropdown_uses_a_continuous_pointer_bridge(): void
    {
        $header = (string) file_get_contents(resource_path('views/components/public/header.blade.php'));
        $styles = (string) file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('v2-header__services-menu-panel', $header);
        $this->assertStringContainsString('top: 100%;', $styles);
        $this->assertStringContainsString('padding-top: 0.45rem;', $styles);
        $this->assertStringNotContainsString('top: calc(100% + 0.45rem);', $styles);
    }

    public function test_group_pages_are_database_driven_and_preserve_publication_locale_and_ordering_rules(): void
    {
        $matching = $this->createServiceWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Assigned Skin Care Service',
            'assigned-skin-care-service',
            ['header_group_key' => HeaderServiceGroup::SKIN_CARE, 'sort_order' => 20]
        );
        $first = $this->createServiceWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'First Assigned Skin Care Service',
            'first-assigned-skin-care-service',
            ['header_group_key' => HeaderServiceGroup::SKIN_CARE, 'sort_order' => 5]
        );
        $otherGroup = $this->createServiceWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Other Header Group Service',
            'other-header-group-service',
            ['header_group_key' => HeaderServiceGroup::ACNE_SCAR_TREATMENT]
        );
        $ungrouped = $this->createServiceWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Ungrouped Catalog Service',
            'ungrouped-catalog-service'
        );
        $draft = $this->createServiceWithTranslation(
            ContentStatus::DRAFT,
            'vi',
            'Draft Group Service',
            'draft-group-service',
            ['header_group_key' => HeaderServiceGroup::SKIN_CARE]
        );

        ServiceTranslation::create([
            'service_id' => $matching->id,
            'locale' => 'en',
            'name' => 'Assigned Skin Care Service EN',
            'slug' => 'assigned-skin-care-service-en',
        ]);

        $viResponse = $this->get('/dich-vu/nhom/cham-soc-da');
        $viResponse
            ->assertOk()
            ->assertSee('CHĂM SÓC DA')
            ->assertSee('Assigned Skin Care Service')
            ->assertSee('First Assigned Skin Care Service')
            ->assertSee('<link rel="canonical" href="https://viethanauhanspa.com/dich-vu/nhom/cham-soc-da">', false)
            ->assertSee('hreflang="en" href="https://viethanauhanspa.com/en/services/group/skin-care"', false);

        $viContent = (string) $viResponse->getContent();
        preg_match('/<main\b[^>]*>(.*?)<\/main>/s', $viContent, $mainMatch);
        $main = $mainMatch[1] ?? '';
        $this->assertStringNotContainsString('Other Header Group Service', $main);
        $this->assertStringNotContainsString('Ungrouped Catalog Service', $main);
        $this->assertStringNotContainsString('Draft Group Service', $main);
        $this->assertTrue(
            strpos($main, 'First Assigned Skin Care Service') < strpos($main, 'Assigned Skin Care Service')
        );

        $enResponse = $this->get('/en/services/group/skin-care');
        $enResponse
            ->assertOk()
            ->assertSee('SKIN CARE')
            ->assertSee('Assigned Skin Care Service EN');

        $enContent = (string) $enResponse->getContent();
        preg_match('/<main\b[^>]*>(.*?)<\/main>/s', $enContent, $enMainMatch);
        $enMain = $enMainMatch[1] ?? '';
        $this->assertStringNotContainsString('/dich-vu/assigned-skin-care-service', $enMain);

        $this->get('/dich-vu')
            ->assertOk()
            ->assertSee('Ungrouped Catalog Service')
            ->assertSee('Other Header Group Service');

        $this->get('/dich-vu/nhom/not-a-group')->assertNotFound();
    }

    public function test_empty_header_groups_return_successful_localized_empty_states(): void
    {
        $this->get('/dich-vu/nhom/goi-dau-duong-sinh-dong-y')
            ->assertOk()
            ->assertSee('GỘI ĐẦU DƯỠNG SINH ĐÔNG Y')
            ->assertSee(__('services.groups.empty', [], 'vi'));

        $this->get('/en/services/group/herbal-hair-scalp-care')
            ->assertOk()
            ->assertSee('TRADITIONAL HERBAL HAIR & SCALP CARE')
            ->assertSee(__('services.groups.empty', [], 'en'));
    }

    public function test_listing_filters_by_publication_status_and_detail_hides_unpublished_services(): void
    {
        $published = $this->createServiceWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Visible Published Service',
            'visible-published-service'
        );
        $draft = $this->createServiceWithTranslation(
            ContentStatus::DRAFT,
            'vi',
            'Hidden Draft Service',
            'hidden-draft-service'
        );
        $archived = $this->createServiceWithTranslation(
            ContentStatus::ARCHIVED,
            'vi',
            'Hidden Archived Service',
            'hidden-archived-service'
        );

        $response = $this->get('/dich-vu');

        $response->assertStatus(200);
        $response->assertSee('Visible Published Service');
        $response->assertDontSee('Hidden Draft Service');
        $response->assertDontSee('Hidden Archived Service');

        $this->get('/dich-vu/'.$published->translationFor('vi')->slug)->assertStatus(200);
        $this->get('/dich-vu/'.$draft->translationFor('vi')->slug)->assertStatus(404);
        $this->get('/dich-vu/'.$archived->translationFor('vi')->slug)->assertStatus(404);
    }

    public function test_public_services_require_exact_requested_locale_without_vi_fallback(): void
    {
        $service = $this->createServiceWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'VI Only Therapy',
            'vi-only-therapy'
        );

        $this->get('/dich-vu')
            ->assertStatus(200)
            ->assertSee('VI Only Therapy');

        $this->get('/en/services')
            ->assertStatus(200)
            ->assertDontSee('VI Only Therapy');

        $this->get('/dich-vu/vi-only-therapy')->assertStatus(200);
        $this->get('/en/services/vi-only-therapy')->assertStatus(404);

        ServiceTranslation::create([
            'service_id' => $service->id,
            'locale' => 'en',
            'name' => 'English Therapy',
            'slug' => 'english-therapy',
        ]);

        $this->get('/en/services')
            ->assertStatus(200)
            ->assertSee('English Therapy')
            ->assertDontSee('VI Only Therapy');

        $this->get('/en/services/english-therapy')->assertStatus(200);
    }

    public function test_service_detail_slug_is_locale_specific(): void
    {
        $service = $this->createServiceWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Massage Tri Lieu',
            'massage-tri-lieu'
        );

        ServiceTranslation::create([
            'service_id' => $service->id,
            'locale' => 'en',
            'name' => 'Therapeutic Massage',
            'slug' => 'therapeutic-massage',
        ]);

        $this->get('/dich-vu/massage-tri-lieu')->assertStatus(200);
        $this->get('/en/services/therapeutic-massage')->assertStatus(200);
        $this->get('/en/services/massage-tri-lieu')->assertStatus(404);
        $this->get('/dich-vu/therapeutic-massage')->assertStatus(404);
    }

    public function test_existing_and_missing_service_media_are_resolved_without_crashing(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/service-hero.jpg', 'fake-image');

        $media = Media::create([
            'disk' => 'public',
            'path' => 'media/service-hero.jpg',
            'file_name' => 'service-hero.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size_bytes' => 1024,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        MediaTranslation::create([
            'media_id' => $media->id,
            'locale' => 'vi',
            'alt_text' => 'Exact VI Hero Alt',
        ]);

        $service = $this->createServiceWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Service With Real Media',
            'service-with-real-media',
            ['hero_media_id' => $media->id]
        );

        $this->get('/dich-vu')
            ->assertStatus(200)
            ->assertSee('service-hero.jpg')
            ->assertSee('Exact VI Hero Alt');

        $this->get('/dich-vu/'.$service->translationFor('vi')->slug)
            ->assertStatus(200)
            ->assertSee('service-hero.jpg')
            ->assertSee('Exact VI Hero Alt');

        $missingMedia = Media::create([
            'disk' => 'public',
            'path' => 'media/missing-service.jpg',
            'file_name' => 'missing-service.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size_bytes' => 1024,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $missingService = $this->createServiceWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Service With Missing Media',
            'service-with-missing-media',
            ['hero_media_id' => $missingMedia->id]
        );

        $this->get('/dich-vu/'.$missingService->translationFor('vi')->slug)
            ->assertStatus(200)
            ->assertSee('Service With Missing Media')
            ->assertDontSee('missing-service.jpg');
    }

    public function test_wrong_locale_media_alt_text_does_not_leak(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/en-only-alt.jpg', 'fake-image');

        $media = Media::create([
            'disk' => 'public',
            'path' => 'media/en-only-alt.jpg',
            'file_name' => 'en-only-alt.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size_bytes' => 1024,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        MediaTranslation::create([
            'media_id' => $media->id,
            'locale' => 'en',
            'alt_text' => 'English Alt Must Not Leak',
        ]);

        $this->createServiceWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Service With Locale Safe Alt',
            'service-with-locale-safe-alt',
            ['hero_media_id' => $media->id]
        );

        $this->get('/dich-vu/service-with-locale-safe-alt')
            ->assertStatus(200)
            ->assertSee('en-only-alt.jpg')
            ->assertSee('alt=""', false)
            ->assertDontSee('English Alt Must Not Leak');
    }

    public function test_prices_display_numeric_value_without_leaking_wrong_locale_label(): void
    {
        $service = $this->createServiceWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Price Checked Service',
            'price-checked-service'
        );

        $priceWithLabel = ServicePrice::create([
            'service_id' => $service->id,
            'duration_minutes' => 90,
            'price_amount' => 690000,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        ServicePriceTranslation::create([
            'service_price_id' => $priceWithLabel->id,
            'locale' => 'vi',
            'label' => 'Goi Chuyen Sau',
        ]);
        ServicePriceTranslation::create([
            'service_price_id' => $priceWithLabel->id,
            'locale' => 'en',
            'label' => 'Leaked English Package',
        ]);

        ServicePrice::create([
            'service_id' => $service->id,
            'duration_minutes' => 60,
            'price_amount' => 490000,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response = $this->get('/dich-vu/price-checked-service');

        $response->assertStatus(200);
        $response->assertSee('Goi Chuyen Sau');
        $response->assertSee('690.000');
        $response->assertSee('490.000');
        $response->assertSee('90 phút');
        $response->assertSee('60 phút');
        $response->assertDontSee('Leaked English Package');
    }

    public function test_price_without_duration_renders_normally_without_duration_copy(): void
    {
        $service = $this->createServiceWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Dịch Vụ Giá Chưa Rõ Thời Lượng',
            'dich-vu-gia-chua-ro-thoi-luong'
        );
        ServicePrice::create([
            'service_id' => $service->id,
            'duration_minutes' => null,
            'price_amount' => 1200000,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->get('/dich-vu/dich-vu-gia-chua-ro-thoi-luong')
            ->assertOk()
            ->assertSee('1.200.000')
            ->assertDontSee('0 phút')
            ->assertDontSee('null phút');

        $this->get('/dich-vu')
            ->assertOk()
            ->assertSee('1.200.000')
            ->assertDontSee('&middot;', false);
    }

    public function test_category_label_uses_exact_locale_only(): void
    {
        $category = ServiceCategory::create([
            'status' => ContentStatus::PUBLISHED,
            'sort_order' => 1,
        ]);
        ServiceCategoryTranslation::create([
            'service_category_id' => $category->id,
            'locale' => 'vi',
            'name' => 'Danh Muc Tieng Viet',
            'slug' => 'danh-muc-tieng-viet',
        ]);

        $this->createServiceWithTranslation(
            ContentStatus::PUBLISHED,
            'en',
            'English Service Without English Category',
            'english-service-without-english-category',
            ['service_category_id' => $category->id]
        );

        $this->get('/en/services')
            ->assertStatus(200)
            ->assertSee('English Service Without English Category')
            ->assertDontSee('Danh Muc Tieng Viet');
    }

    public function test_faq_rendering_uses_service_translation_schema_data_only(): void
    {
        $service = $this->createServiceWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'FAQ Checked Service',
            'faq-checked-service'
        );

        $translation = $service->translationFor('vi');
        $translation->update([
            'faqs' => [
                [
                    'question' => 'Authored FAQ Question',
                    'answer' => 'Authored FAQ Answer',
                ],
            ],
        ]);

        $this->get('/dich-vu/faq-checked-service')
            ->assertStatus(200)
            ->assertSee(__('services.detail.faqs', [], 'vi'))
            ->assertSee('Authored FAQ Question')
            ->assertSee('Authored FAQ Answer')
            ->assertDontSee('Fabricated FAQ');
    }

    public function test_service_detail_language_switch_targets_exact_translated_slug(): void
    {
        $service = $this->createServiceWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Massage Tri Lieu',
            'massage-tri-lieu'
        );
        ServiceTranslation::create([
            'service_id' => $service->id,
            'locale' => 'en',
            'name' => 'Therapeutic Massage',
            'slug' => 'therapeutic-massage',
        ]);

        $this->get('/dich-vu/massage-tri-lieu')
            ->assertStatus(200)
            ->assertSee('href="'.route('en.services.show', ['slug' => 'therapeutic-massage']).'"', false);

        $this->get('/en/services/therapeutic-massage')
            ->assertStatus(200)
            ->assertSee('href="'.route('vi.services.show', ['slug' => 'massage-tri-lieu']).'"', false);
    }

    public function test_header_services_navigation_targets_real_listing_routes_across_v2_pages(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('href="'.route('vi.services.index').'"', false)
            ->assertSee('v2-header__link', false);

        $this->get('/en')
            ->assertStatus(200)
            ->assertSee('href="'.route('en.services.index').'"', false)
            ->assertSee('v2-header__link', false);

        $this->get('/dich-vu')
            ->assertStatus(200)
            ->assertSee('public-header--sticky', false)
            ->assertSee('v2-public-shell', false)
            ->assertSee('href="'.route('vi.services.index').'"', false);
    }

    public function test_service_listing_and_detail_use_the_same_v2_experience(): void
    {
        $service = $this->createServiceWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'V2 Service Experience',
            'v2-service-experience'
        );

        $this->get('/dich-vu/'.$service->translationFor('vi')->slug)
            ->assertStatus(200)
            ->assertSee('v2-public-shell', false)
            ->assertSee('v2-service-hero', false)
            ->assertSee('v2-header__nav--left', false)
            ->assertSee('v2-header__nav--right', false)
            ->assertSee('v2-language-switcher', false)
            ->assertSee('data-booking-modal-trigger', false)
            ->assertSee('href="'.route('vi.booking.create').'"', false);

        ServiceTranslation::create([
            'service_id' => $service->id,
            'locale' => 'en',
            'name' => 'V2 English Service Experience',
            'slug' => 'v2-english-service-experience',
            'excerpt' => 'Exact English service presentation.',
        ]);

        $this->get('/en/services/v2-english-service-experience')
            ->assertStatus(200)
            ->assertSee('v2-public-shell', false)
            ->assertSee('V2 English Service Experience')
            ->assertSee('href="'.route('en.booking.create').'"', false);

        $this->get('/dich-vu')
            ->assertStatus(200)
            ->assertSee('v2-public-shell', false)
            ->assertSee('v2-treatment-menu', false);
    }

    /**
     * @param  array<string, mixed>  $serviceOverrides
     */
    protected function createServiceWithTranslation(
        ContentStatus $status,
        string $locale,
        string $name,
        string $slug,
        array $serviceOverrides = []
    ): Service {
        $categoryId = $serviceOverrides['service_category_id'] ?? ServiceCategory::create([
            'status' => ContentStatus::PUBLISHED,
            'sort_order' => 1,
        ])->id;

        $service = Service::create(array_merge([
            'service_category_id' => $categoryId,
            'hero_media_id' => null,
            'status' => $status,
            'is_featured' => false,
            'sort_order' => 1,
        ], $serviceOverrides));

        ServiceTranslation::create([
            'service_id' => $service->id,
            'locale' => $locale,
            'name' => $name,
            'slug' => $slug,
            'excerpt' => $name.' excerpt',
            'content' => '<p>'.$name.' content paragraph.</p>',
        ]);

        return $service->fresh(['translations']);
    }
}
