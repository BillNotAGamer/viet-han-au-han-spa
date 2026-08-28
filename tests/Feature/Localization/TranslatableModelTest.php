<?php

declare(strict_types=1);

namespace Tests\Feature\Localization;

use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\Service;
use App\Models\ServiceTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TranslatableModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_exact_translation_retrieval(): void
    {
        $service = Service::factory()->create();
        $viTranslation = ServiceTranslation::factory()->create([
            'service_id' => $service->id,
            'locale' => 'vi',
            'name' => 'Massage Trị Liệu Hàn Quốc',
        ]);
        $enTranslation = ServiceTranslation::factory()->create([
            'service_id' => $service->id,
            'locale' => 'en',
            'name' => 'Korean Therapeutic Massage',
        ]);

        $this->assertSame('Massage Trị Liệu Hàn Quốc', $service->translationFor('vi')?->name);
        $this->assertSame('Korean Therapeutic Massage', $service->translationFor('en')?->name);
    }

    public function test_model_returns_null_when_exact_translation_missing(): void
    {
        $page = Page::factory()->create();
        PageTranslation::factory()->create([
            'page_id' => $page->id,
            'locale' => 'vi',
            'title' => 'Giới thiệu',
        ]);

        // Exact translation rule: Must return null for missing EN translation
        $this->assertNull($page->translationFor('en'));
    }

    public function test_model_explicit_fallback_returns_default_translation(): void
    {
        $page = Page::factory()->create();
        PageTranslation::factory()->create([
            'page_id' => $page->id,
            'locale' => 'vi',
            'title' => 'Giới thiệu',
        ]);

        $fallback = $page->translationOrFallback('en');
        $this->assertNotNull($fallback);
        $this->assertSame('Giới thiệu', $fallback->title);
    }

    public function test_translation_lookup_reuses_eager_loaded_relation_without_extra_queries(): void
    {
        $service = Service::factory()->create();
        ServiceTranslation::factory()->create([
            'service_id' => $service->id,
            'locale' => 'vi',
            'name' => 'Massage Body',
        ]);
        ServiceTranslation::factory()->create([
            'service_id' => $service->id,
            'locale' => 'en',
            'name' => 'Body Massage',
        ]);

        // Eager load translations
        $loadedService = Service::with('translations')->find($service->id);

        DB::enableQueryLog();

        $viTrans = $loadedService->translationFor('vi');
        $enTrans = $loadedService->translationFor('en');

        $queries = DB::getQueryLog();

        // 0 database queries executed because 'translations' is already eager-loaded
        $this->assertCount(0, $queries);
        $this->assertSame('Massage Body', $viTrans?->name);
        $this->assertSame('Body Massage', $enTrans?->name);
    }
}
