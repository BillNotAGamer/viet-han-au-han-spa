<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\Media;
use App\Models\MediaTranslation;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\PostCategoryTranslation;
use App\Models\PostTranslation;
use App\Models\ServiceCategory;
use App\Models\ServiceCategoryTranslation;
use App\Models\ServicePrice;
use App\Models\ServicePriceTranslation;
use App\Models\ServiceTranslation;
use App\Models\TrainingCourseTranslation;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationConstraintTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_category_translation_enforces_unique_locale_per_parent(): void
    {
        $category = ServiceCategory::factory()->create();
        ServiceCategoryTranslation::factory()->create([
            'service_category_id' => $category->id,
            'locale' => 'vi',
        ]);

        $this->expectException(QueryException::class);
        ServiceCategoryTranslation::factory()->create([
            'service_category_id' => $category->id,
            'locale' => 'vi',
        ]);
    }

    public function test_service_translation_enforces_unique_locale_slug(): void
    {
        ServiceTranslation::factory()->create([
            'locale' => 'vi',
            'slug' => 'massage-co-vai-gay',
        ]);

        $this->expectException(QueryException::class);
        ServiceTranslation::factory()->create([
            'locale' => 'vi',
            'slug' => 'massage-co-vai-gay',
        ]);
    }

    public function test_training_course_translation_enforces_unique_locale_slug(): void
    {
        TrainingCourseTranslation::factory()->create([
            'locale' => 'vi',
            'slug' => 'khoa-hoc-cham-soc-da',
        ]);

        $this->expectException(QueryException::class);
        TrainingCourseTranslation::factory()->create([
            'locale' => 'vi',
            'slug' => 'khoa-hoc-cham-soc-da',
        ]);
    }

    public function test_post_category_translation_enforces_unique_locale_slug(): void
    {
        PostCategoryTranslation::factory()->create([
            'locale' => 'vi',
            'slug' => 'kien-thuc-lam-dep',
        ]);

        $this->expectException(QueryException::class);
        PostCategoryTranslation::factory()->create([
            'locale' => 'vi',
            'slug' => 'kien-thuc-lam-dep',
        ]);
    }

    public function test_post_translation_enforces_unique_locale_slug(): void
    {
        PostTranslation::factory()->create([
            'locale' => 'vi',
            'slug' => '5-buoc-cham-soc-da-mua-he',
        ]);

        $this->expectException(QueryException::class);
        PostTranslation::factory()->create([
            'locale' => 'vi',
            'slug' => '5-buoc-cham-soc-da-mua-he',
        ]);
    }

    public function test_service_price_translation_enforces_unique_locale_per_price_tier(): void
    {
        $price = ServicePrice::factory()->create();
        ServicePriceTranslation::factory()->create([
            'service_price_id' => $price->id,
            'locale' => 'vi',
            'label' => 'Gói Tiêu Chuẩn',
        ]);

        $this->expectException(QueryException::class);
        ServicePriceTranslation::factory()->create([
            'service_price_id' => $price->id,
            'locale' => 'vi',
            'label' => 'Gói Khác',
        ]);
    }

    public function test_media_translation_enforces_unique_locale_per_media(): void
    {
        $media = Media::factory()->create();
        MediaTranslation::factory()->create([
            'media_id' => $media->id,
            'locale' => 'vi',
        ]);

        $this->expectException(QueryException::class);
        MediaTranslation::factory()->create([
            'media_id' => $media->id,
            'locale' => 'vi',
        ]);
    }

    public function test_page_translations_allow_multiple_null_slugs_for_homepage_across_pages(): void
    {
        $page1 = Page::factory()->create(['key' => 'home_1']);
        $page2 = Page::factory()->create(['key' => 'home_2']);

        $trans1 = PageTranslation::factory()->create([
            'page_id' => $page1->id,
            'locale' => 'vi',
            'slug' => null,
        ]);

        $trans2 = PageTranslation::factory()->create([
            'page_id' => $page2->id,
            'locale' => 'vi',
            'slug' => null,
        ]);

        $this->assertNull($trans1->slug);
        $this->assertNull($trans2->slug);
    }

    public function test_page_translation_rejects_duplicate_non_null_slug_in_same_locale(): void
    {
        $page1 = Page::factory()->create(['key' => 'about_1']);
        $page2 = Page::factory()->create(['key' => 'about_2']);

        PageTranslation::factory()->create([
            'page_id' => $page1->id,
            'locale' => 'vi',
            'slug' => 've-chung-toi',
        ]);

        $this->expectException(QueryException::class);
        PageTranslation::factory()->create([
            'page_id' => $page2->id,
            'locale' => 'vi',
            'slug' => 've-chung-toi',
        ]);
    }
}
