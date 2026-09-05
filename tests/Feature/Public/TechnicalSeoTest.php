<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\ContentStatus;
use App\Enums\SiteSettingType;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTranslation;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceTranslation;
use App\Models\SiteSetting;
use App\Models\TrainingCourse;
use App\Models\TrainingCourseTranslation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TechnicalSeoTest extends TestCase
{
    use RefreshDatabase;

    protected function createService(array $attributes = []): Service
    {
        $categoryId = $attributes['service_category_id'] ?? ServiceCategory::create([
            'status' => ContentStatus::PUBLISHED,
            'sort_order' => 1,
        ])->id;

        return Service::create(array_merge([
            'service_category_id' => $categoryId,
            'status' => ContentStatus::PUBLISHED,
            'sort_order' => 1,
        ], $attributes));
    }

    protected function createPost(array $attributes = []): Post
    {
        $categoryId = $attributes['post_category_id'] ?? PostCategory::create([
            'status' => ContentStatus::PUBLISHED,
            'sort_order' => 1,
        ])->id;

        return Post::create(array_merge([
            'post_category_id' => $categoryId,
            'status' => ContentStatus::PUBLISHED,
            'published_at' => now()->subDay(),
        ], $attributes));
    }

    public function test_basic_meta_tags_on_vi_and_en_static_routes(): void
    {
        // VI Home
        $viResponse = $this->get('/');
        $viResponse->assertStatus(200)
            ->assertSee('<title>', false)
            ->assertSee('<link rel="canonical" href="https://viethanauhanspa.com/">', false)
            ->assertSee('<link rel="alternate" hreflang="vi" href="https://viethanauhanspa.com/">', false)
            ->assertSee('<link rel="alternate" hreflang="en" href="https://viethanauhanspa.com/en">', false)
            ->assertSee('<link rel="alternate" hreflang="x-default" href="https://viethanauhanspa.com/">', false)
            ->assertSee('<meta name="robots" content="index, follow">', false)
            ->assertSee('<meta property="og:type" content="website">', false)
            ->assertSee('<meta property="og:locale" content="vi_VN">', false);

        // EN Home
        $enResponse = $this->get('/en');
        $enResponse->assertStatus(200)
            ->assertSee('<title>', false)
            ->assertSee('<link rel="canonical" href="https://viethanauhanspa.com/en">', false)
            ->assertSee('<link rel="alternate" hreflang="vi" href="https://viethanauhanspa.com/">', false)
            ->assertSee('<link rel="alternate" hreflang="en" href="https://viethanauhanspa.com/en">', false)
            ->assertSee('<link rel="alternate" hreflang="x-default" href="https://viethanauhanspa.com/">', false)
            ->assertSee('<meta property="og:locale" content="en_US">', false);
    }

    public function test_title_and_description_fallback_hierarchy(): void
    {
        // 1. Explicit SEO title and description
        $service1 = $this->createService(['sort_order' => 1]);
        ServiceTranslation::create([
            'service_id' => $service1->id,
            'locale' => 'vi',
            'name' => 'Chăm Sóc Da Mặt Cơ Bản',
            'slug' => 'cham-soc-da-mat-co-ban',
            'seo_title' => 'Chuyên Sâu Trẻ Hóa Da | Việt Hàn Spa',
            'seo_description' => 'Liệu trình chăm sóc da chuẩn y khoa Hàn Quốc.',
        ]);

        $this->get('/dich-vu/cham-soc-da-mat-co-ban')
            ->assertStatus(200)
            ->assertSee('<title>Chuyên Sâu Trẻ Hóa Da | Việt Hàn Spa</title>', false)
            ->assertSee('<meta name="description" content="Liệu trình chăm sóc da chuẩn y khoa Hàn Quốc.">', false);

        // 2. Localized title fallback when seo_title is null, and excerpt fallback
        $service2 = $this->createService(['sort_order' => 2]);
        ServiceTranslation::create([
            'service_id' => $service2->id,
            'locale' => 'vi',
            'name' => 'Massage Body Đá Nóng',
            'slug' => 'massage-body-da-nong',
            'excerpt' => 'Thư giãn toàn thân với đá núi lửa bazan thiên nhiên.',
            'seo_title' => null,
            'seo_description' => null,
        ]);

        $this->get('/dich-vu/massage-body-da-nong')
            ->assertStatus(200)
            ->assertSee('<title>Massage Body Đá Nóng — Việt Hàn Âu Hàn Spa</title>', false)
            ->assertSee('<meta name="description" content="Thư giãn toàn thân với đá núi lửa bazan thiên nhiên.">', false);

        // 3. When description and excerpt are completely absent, description tag is omitted
        $service3 = $this->createService(['sort_order' => 3]);
        ServiceTranslation::create([
            'service_id' => $service3->id,
            'locale' => 'vi',
            'name' => 'Tẩy Tế Bào Chết',
            'slug' => 'tay-te-bao-chet',
            'excerpt' => null,
            'content' => null,
            'seo_title' => null,
            'seo_description' => null,
        ]);

        $response3 = $this->get('/dich-vu/tay-te-bao-chet');
        $response3->assertStatus(200);
        $this->assertStringNotContainsString('<meta name="description"', $response3->getContent());
    }

    public function test_canonical_strips_marketing_campaign_parameters(): void
    {
        $this->get('/dich-vu?utm_source=facebook&utm_medium=cpc&utm_campaign=summer_sale&fbclid=abc123xyz')
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="https://viethanauhanspa.com/dich-vu">', false)
            ->assertDontSee('utm_source')
            ->assertDontSee('fbclid');

        $this->get('/en/services?gclid=test_gclid_123&wbraid=test_wbraid')
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="https://viethanauhanspa.com/en/services">', false);
    }

    public function test_pagination_canonical_preserves_legitimate_page_param_and_strips_tracking(): void
    {
        // Page 2 with marketing params
        $this->get('/dich-vu?page=2&utm_source=facebook&fbclid=xyz')
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="https://viethanauhanspa.com/dich-vu?page=2">', false)
            ->assertSee('<link rel="alternate" hreflang="vi" href="https://viethanauhanspa.com/dich-vu?page=2">', false)
            ->assertSee('<link rel="alternate" hreflang="en" href="https://viethanauhanspa.com/en/services?page=2">', false);

        // Page 1 should omit the ?page=1 parameter
        $this->get('/dich-vu?page=1&utm_source=facebook')
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="https://viethanauhanspa.com/dich-vu">', false);
    }

    public function test_detail_hreflang_bilingual_emits_actual_translated_slugs(): void
    {
        $service = $this->createService(['sort_order' => 1]);

        ServiceTranslation::create([
            'service_id' => $service->id,
            'locale' => 'vi',
            'name' => 'Chăm Sóc Da Mụn',
            'slug' => 'cham-soc-da-mun',
        ]);

        ServiceTranslation::create([
            'service_id' => $service->id,
            'locale' => 'en',
            'name' => 'Acne Skin Treatment',
            'slug' => 'acne-skin-treatment',
        ]);

        $this->get('/dich-vu/cham-soc-da-mun')
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="https://viethanauhanspa.com/dich-vu/cham-soc-da-mun">', false)
            ->assertSee('<link rel="alternate" hreflang="vi" href="https://viethanauhanspa.com/dich-vu/cham-soc-da-mun">', false)
            ->assertSee('<link rel="alternate" hreflang="en" href="https://viethanauhanspa.com/en/services/acne-skin-treatment">', false)
            ->assertSee('<link rel="alternate" hreflang="x-default" href="https://viethanauhanspa.com/dich-vu/cham-soc-da-mun">', false);

        $this->get('/en/services/acne-skin-treatment')
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="https://viethanauhanspa.com/en/services/acne-skin-treatment">', false)
            ->assertSee('<link rel="alternate" hreflang="vi" href="https://viethanauhanspa.com/dich-vu/cham-soc-da-mun">', false)
            ->assertSee('<link rel="alternate" hreflang="en" href="https://viethanauhanspa.com/en/services/acne-skin-treatment">', false);
    }

    public function test_detail_hreflang_single_locale_does_not_emit_fake_alternate(): void
    {
        $service = $this->createService(['sort_order' => 1]);

        ServiceTranslation::create([
            'service_id' => $service->id,
            'locale' => 'vi',
            'name' => 'Dịch Vụ Chỉ Có Tiếng Việt',
            'slug' => 'dich-vu-chi-co-tieng-viet',
        ]);

        $response = $this->get('/dich-vu/dich-vu-chi-co-tieng-viet');
        $response->assertStatus(200)
            ->assertSee('<link rel="canonical" href="https://viethanauhanspa.com/dich-vu/dich-vu-chi-co-tieng-viet">', false)
            ->assertSee('<link rel="alternate" hreflang="vi" href="https://viethanauhanspa.com/dich-vu/dich-vu-chi-co-tieng-viet">', false);

        // MUST NOT emit hreflang="en"
        $this->assertStringNotContainsString('hreflang="en"', $response->getContent());
    }

    public function test_sitemap_returns_valid_xml_with_canonical_routes(): void
    {
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $this->assertStringContainsString('application/xml', (string) $response->headers->get('Content-Type'));

        $content = $response->getContent();
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $content);

        // Static routes
        $this->assertStringContainsString('<loc>https://viethanauhanspa.com/</loc>', $content);
        $this->assertStringContainsString('<loc>https://viethanauhanspa.com/en</loc>', $content);
        $this->assertStringContainsString('<loc>https://viethanauhanspa.com/dich-vu</loc>', $content);
        $this->assertStringContainsString('<loc>https://viethanauhanspa.com/en/services</loc>', $content);
        $this->assertStringContainsString('<loc>https://viethanauhanspa.com/dao-tao</loc>', $content);
        $this->assertStringContainsString('<loc>https://viethanauhanspa.com/en/training</loc>', $content);
        $this->assertStringContainsString('<loc>https://viethanauhanspa.com/blog</loc>', $content);
        $this->assertStringContainsString('<loc>https://viethanauhanspa.com/en/blog</loc>', $content);
        $this->assertStringContainsString('<loc>https://viethanauhanspa.com/dat-lich</loc>', $content);
        $this->assertStringContainsString('<loc>https://viethanauhanspa.com/en/booking</loc>', $content);
    }

    public function test_sitemap_filtering_policy_excludes_draft_archived_and_future_content(): void
    {
        // Published Service
        $pubService = $this->createService(['status' => ContentStatus::PUBLISHED]);
        ServiceTranslation::create([
            'service_id' => $pubService->id,
            'locale' => 'vi',
            'name' => 'Service Pub',
            'slug' => 'service-pub-slug',
        ]);

        // Draft Service
        $draftService = $this->createService(['status' => ContentStatus::DRAFT]);
        ServiceTranslation::create([
            'service_id' => $draftService->id,
            'locale' => 'vi',
            'name' => 'Service Draft',
            'slug' => 'service-draft-slug',
        ]);

        // Archived Service
        $archService = $this->createService(['status' => ContentStatus::ARCHIVED]);
        ServiceTranslation::create([
            'service_id' => $archService->id,
            'locale' => 'vi',
            'name' => 'Service Arch',
            'slug' => 'service-arch-slug',
        ]);

        // Published Training Course
        $pubCourse = TrainingCourse::create([
            'status' => ContentStatus::PUBLISHED,
            'published_at' => now()->subDay(),
            'tuition_fee' => 5000000,
        ]);
        TrainingCourseTranslation::create([
            'training_course_id' => $pubCourse->id,
            'locale' => 'vi',
            'title' => 'Course Pub',
            'slug' => 'course-pub-slug',
        ]);

        // Future Training Course
        $futureCourse = TrainingCourse::create([
            'status' => ContentStatus::PUBLISHED,
            'published_at' => now()->addWeek(),
            'tuition_fee' => 5000000,
        ]);
        TrainingCourseTranslation::create([
            'training_course_id' => $futureCourse->id,
            'locale' => 'vi',
            'title' => 'Course Future',
            'slug' => 'course-future-slug',
        ]);

        // Published Blog Post
        $pubPost = $this->createPost([
            'status' => ContentStatus::PUBLISHED,
            'published_at' => now()->subDay(),
        ]);
        PostTranslation::create([
            'post_id' => $pubPost->id,
            'locale' => 'vi',
            'title' => 'Post Pub',
            'slug' => 'post-pub-slug',
        ]);

        // Future Blog Post
        $futurePost = $this->createPost([
            'status' => ContentStatus::PUBLISHED,
            'published_at' => now()->addWeek(),
        ]);
        PostTranslation::create([
            'post_id' => $futurePost->id,
            'locale' => 'vi',
            'title' => 'Post Future',
            'slug' => 'post-future-slug',
        ]);

        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $content = $response->getContent();

        // Must contain published items
        $this->assertStringContainsString('https://viethanauhanspa.com/dich-vu/service-pub-slug', $content);
        $this->assertStringContainsString('https://viethanauhanspa.com/dao-tao/course-pub-slug', $content);
        $this->assertStringContainsString('https://viethanauhanspa.com/blog/post-pub-slug', $content);

        // Must NOT contain drafts, archives, or future-scheduled items
        $this->assertStringNotContainsString('service-draft-slug', $content);
        $this->assertStringNotContainsString('service-arch-slug', $content);
        $this->assertStringNotContainsString('course-future-slug', $content);
        $this->assertStringNotContainsString('post-future-slug', $content);
    }

    public function test_sitemap_privacy_and_security_boundary(): void
    {
        $response = $this->get('/sitemap.xml');
        $content = $response->getContent();

        $this->assertStringNotContainsString('/admin', $content);
        $this->assertStringNotContainsString('/login', $content);
        $this->assertStringNotContainsString('livewire', $content);
        $this->assertStringNotContainsString('utm_', $content);
        $this->assertStringNotContainsString('fbclid', $content);
    }

    public function test_robots_txt_file_rules(): void
    {
        $robotsPath = public_path('robots.txt');
        $this->assertTrue(File::exists($robotsPath), 'public/robots.txt must exist.');

        $content = File::get($robotsPath);
        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Allow: /', $content);
        $this->assertStringContainsString('Disallow: /admin', $content);
        $this->assertStringContainsString('Sitemap: https://viethanauhanspa.com/sitemap.xml', $content);
        $this->assertStringNotContainsString('Disallow: /'."\n", $content, 'Must not disallow entire site');
    }

    public function test_og_and_json_ld_escaping_and_security(): void
    {
        $maliciousTitle = '<script>alert("xss")</script>';
        $maliciousDesc = '"><script>alert(1)</script>';

        $service = $this->createService(['sort_order' => 1]);

        ServiceTranslation::create([
            'service_id' => $service->id,
            'locale' => 'vi',
            'name' => 'Service Normal',
            'slug' => 'service-malicious-test',
            'seo_title' => $maliciousTitle,
            'seo_description' => $maliciousDesc,
        ]);

        $response = $this->get('/dich-vu/service-malicious-test');
        $response->assertStatus(200);
        $content = $response->getContent();

        // Title and description must be escaped in HTML
        $this->assertStringContainsString('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $content);
        $this->assertStringNotContainsString('<script>alert("xss")</script>', $content);

        // JSON-LD must be serialized safely without breakout
        $this->assertStringNotContainsString('</script><script>', $content);
    }

    public function test_json_ld_minimal_beauty_salon_and_blog_posting_schema(): void
    {
        Cache::flush();

        // Seed allow-listed public settings
        SiteSetting::create([
            'key' => 'contact.phone',
            'value' => '0901234567',
            'type' => SiteSettingType::STRING,
            'is_public' => true,
        ]);
        SiteSetting::create([
            'key' => 'contact.email',
            'value' => 'contact@viethanauhanspa.com',
            'type' => SiteSettingType::STRING,
            'is_public' => true,
        ]);
        SiteSetting::create([
            'key' => 'contact.address',
            'value' => '123 Đường Hàn Quốc, Đà Nẵng',
            'type' => SiteSettingType::STRING,
            'is_public' => true,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('"@type":"BeautySalon"', $content);
        $this->assertStringContainsString('"telephone":"0901234567"', $content);
        $this->assertStringContainsString('"email":"contact@viethanauhanspa.com"', $content);
        $this->assertStringContainsString('"streetAddress":"123 Đường Hàn Quốc, Đà Nẵng"', $content);

        // Zero fabricated properties
        $this->assertStringNotContainsString('aggregateRating', $content);
        $this->assertStringNotContainsString('reviewCount', $content);
        $this->assertStringNotContainsString('geo', $content);
        $this->assertStringNotContainsString('openingHoursSpecification', $content);

        // Blog Posting JSON-LD
        $author = User::factory()->create(['name' => 'Dr. Kim']);
        $post = $this->createPost([
            'published_at' => CarbonImmutable::parse('2026-09-01 10:00:00'),
            'author_id' => $author->id,
        ]);
        PostTranslation::create([
            'post_id' => $post->id,
            'locale' => 'vi',
            'title' => 'Bí Quyết Chăm Sóc Da Chuẩn Hàn',
            'slug' => 'bi-quyet-cham-soc-da',
        ]);

        $blogResponse = $this->get('/blog/bi-quyet-cham-soc-da');
        $blogResponse->assertStatus(200);
        $blogContent = $blogResponse->getContent();

        $this->assertStringContainsString('"@type":"BlogPosting"', $blogContent);
        $this->assertStringContainsString('"headline":"Bí Quyết Chăm Sóc Da Chuẩn Hàn"', $blogContent);
        $this->assertStringContainsString('"name":"Dr. Kim"', $blogContent);

        // Never expose sensitive author fields
        $this->assertStringNotContainsString($author->email, $blogContent);
    }

    public function test_booking_seo_metadata_integrity(): void
    {
        $response = $this->get('/dat-lich');
        $response->assertStatus(200)
            ->assertSee('<link rel="canonical" href="https://viethanauhanspa.com/dat-lich">', false)
            ->assertSee('<meta name="robots" content="index, follow">', false);

        // Submit booking request
        $service = $this->createService(['status' => ContentStatus::PUBLISHED]);
        ServiceTranslation::create([
            'service_id' => $service->id,
            'locale' => 'vi',
            'name' => 'Dịch Vụ Test',
            'slug' => 'dich-vu-test',
        ]);

        $postResponse = $this->post('/dat-lich', [
            'customer_name' => 'Nguyễn Văn A',
            'phone' => '0901234567',
            'service_id' => $service->id,
            'preferred_date' => now()->addDays(2)->toDateString(),
            'preferred_time' => '10:00',
            'notes' => 'Secret Customer Note',
            'consent' => '1',
        ]);

        $postResponse->assertRedirect('/dat-lich');

        $followed = $this->followRedirects($postResponse);
        $followed->assertStatus(200)
            ->assertSee('<link rel="canonical" href="https://viethanauhanspa.com/dat-lich">', false);

        $content = $followed->getContent();
        $this->assertStringNotContainsString('Secret Customer Note', $content);
        $this->assertStringNotContainsString('0901234567', $content);
    }
}
