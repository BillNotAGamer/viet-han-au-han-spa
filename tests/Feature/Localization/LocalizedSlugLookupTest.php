<?php

declare(strict_types=1);

namespace Tests\Feature\Localization;

use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\Service;
use App\Models\ServiceTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizedSlugLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolving_entity_by_exact_locale_and_slug(): void
    {
        $service = Service::factory()->create();
        ServiceTranslation::factory()->create([
            'service_id' => $service->id,
            'locale' => 'vi',
            'slug' => 'massage-thu-gian',
        ]);
        ServiceTranslation::factory()->create([
            'service_id' => $service->id,
            'locale' => 'en',
            'slug' => 'relaxation-massage',
        ]);

        $foundVi = Service::whereSlug('vi', 'massage-thu-gian')->first();
        $this->assertNotNull($foundVi);
        $this->assertTrue($foundVi->is($service));

        $foundEn = Service::whereSlug('en', 'relaxation-massage')->first();
        $this->assertNotNull($foundEn);
        $this->assertTrue($foundEn->is($service));

        // Mismatched locale/slug must return null
        $mismatch = Service::whereSlug('en', 'massage-thu-gian')->first();
        $this->assertNull($mismatch);
    }

    public function test_resolving_page_by_exact_locale_and_slug(): void
    {
        $page = Page::factory()->create(['key' => 'about']);
        PageTranslation::factory()->create([
            'page_id' => $page->id,
            'locale' => 'vi',
            'slug' => 'gioi-thieu',
        ]);
        PageTranslation::factory()->create([
            'page_id' => $page->id,
            'locale' => 'en',
            'slug' => 'about-us',
        ]);

        $this->assertNotNull(Page::whereSlug('vi', 'gioi-thieu')->first());
        $this->assertNotNull(Page::whereSlug('en', 'about-us')->first());
        $this->assertNull(Page::whereSlug('vi', 'about-us')->first());
    }
}
