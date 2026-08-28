<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\MediaTranslation;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostCategoryTranslation;
use App\Models\PostTranslation;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceCategoryTranslation;
use App\Models\ServiceTranslation;
use App\Models\TrainingCourse;
use App\Models\TrainingCourseTranslation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_database_homepage_renders_cleanly_for_vi_and_en(): void
    {
        $viResponse = $this->get('/');
        $viResponse->assertStatus(200);
        $viResponse->assertSee(__('home.hero.title', [], 'vi'), false);
        $viResponse->assertSee(__('home.cta.button', [], 'vi'), false);

        $enResponse = $this->get('/en');
        $enResponse->assertStatus(200);
        $enResponse->assertSee(__('home.hero.title', [], 'en'), false);
        $enResponse->assertSee(__('home.cta.button', [], 'en'), false);
    }

    public function test_home_page_exact_locale_isolation_and_no_fallback_to_vi(): void
    {
        $page = Page::create([
            'key' => 'home',
            'status' => ContentStatus::PUBLISHED,
        ]);

        PageTranslation::create([
            'page_id' => $page->id,
            'locale' => 'vi',
            'title' => 'Trang Chủ Trị Liệu Dưỡng Sinh',
            'slug' => 'trang-chu',
            'content' => 'Nội dung giới thiệu trang chủ tiếng Việt độc quyền.',
        ]);

        // VI request sees VI title
        $viResponse = $this->get('/');
        $viResponse->assertStatus(200);
        $viResponse->assertSee('Trang Chủ Trị Liệu Dưỡng Sinh');

        // EN request MUST NOT see Vietnamese content (no fallback!)
        $enResponse = $this->get('/en');
        $enResponse->assertStatus(200);
        $enResponse->assertDontSee('Trang Chủ Trị Liệu Dưỡng Sinh');
        $enResponse->assertDontSee('Nội dung giới thiệu trang chủ tiếng Việt độc quyền.');
        $enResponse->assertSee(__('home.hero.title', [], 'en'), false);

        // Add EN translation
        PageTranslation::create([
            'page_id' => $page->id,
            'locale' => 'en',
            'title' => 'Holistic Wellness Sanctuary Home',
            'slug' => 'home',
            'content' => 'Exclusive English homepage introductory narrative.',
        ]);

        $enResponse2 = $this->get('/en');
        $enResponse2->assertStatus(200);
        $enResponse2->assertSee('Holistic Wellness Sanctuary Home');
    }

    public function test_draft_and_archived_home_page_records_are_ignored(): void
    {
        $page = Page::create([
            'key' => 'home',
            'status' => ContentStatus::DRAFT,
        ]);

        PageTranslation::create([
            'page_id' => $page->id,
            'locale' => 'vi',
            'title' => 'Bản Nháp Không Được Hiển Thị',
            'slug' => 'ban-nhap',
            'content' => 'Nội dung bản nháp bí mật.',
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('Bản Nháp Không Được Hiển Thị');

        $page->update(['status' => ContentStatus::ARCHIVED]);

        $responseArchived = $this->get('/');
        $responseArchived->assertStatus(200);
        $responseArchived->assertDontSee('Bản Nháp Không Được Hiển Thị');
    }

    public function test_featured_services_publication_and_exact_locale_filtering(): void
    {
        $cat = ServiceCategory::create(['sort_order' => 1]);
        ServiceCategoryTranslation::create([
            'service_category_id' => $cat->id,
            'locale' => 'vi',
            'name' => 'Trị Liệu Mặt',
            'slug' => 'tri-lieu-mat',
        ]);

        // Service 1: PUBLISHED + featured + VI only
        $service1 = Service::create([
            'service_category_id' => $cat->id,
            'status' => ContentStatus::PUBLISHED,
            'is_featured' => true,
            'sort_order' => 1,
        ]);
        ServiceTranslation::create([
            'service_id' => $service1->id,
            'locale' => 'vi',
            'name' => 'Liệu Trình Chăm Sóc Da Hoàng Cung',
            'slug' => 'cham-soc-da-hoang-cung',
            'excerpt' => 'Mô tả ngắn gọn về liệu trình chăm sóc da cao cấp.',
        ]);

        // Service 2: PUBLISHED + NOT featured
        $service2 = Service::create([
            'service_category_id' => $cat->id,
            'status' => ContentStatus::PUBLISHED,
            'is_featured' => false,
            'sort_order' => 2,
        ]);
        ServiceTranslation::create([
            'service_id' => $service2->id,
            'locale' => 'vi',
            'name' => 'Dịch Vụ Không Nổi Bật',
            'slug' => 'dich-vu-khong-noi-bat',
        ]);

        // Service 3: DRAFT + featured
        $service3 = Service::create([
            'service_category_id' => $cat->id,
            'status' => ContentStatus::DRAFT,
            'is_featured' => true,
            'sort_order' => 3,
        ]);
        ServiceTranslation::create([
            'service_id' => $service3->id,
            'locale' => 'vi',
            'name' => 'Dịch Vụ Bản Nháp',
            'slug' => 'dich-vu-ban-nhap',
        ]);

        $viResponse = $this->get('/');
        $viResponse->assertStatus(200);
        $viResponse->assertSee('Liệu Trình Chăm Sóc Da Hoàng Cung');
        $viResponse->assertSee('Trị Liệu Mặt');
        $viResponse->assertDontSee('Dịch Vụ Không Nổi Bật');
        $viResponse->assertDontSee('Dịch Vụ Bản Nháp');

        // Critical check: Service 1 has no EN translation, must NOT appear on /en
        $enResponse = $this->get('/en');
        $enResponse->assertStatus(200);
        $enResponse->assertDontSee('Liệu Trình Chăm Sóc Da Hoàng Cung');
    }

    public function test_category_label_is_omitted_if_category_translation_missing(): void
    {
        $cat = ServiceCategory::create(['sort_order' => 1]);
        // Category has VI translation only
        ServiceCategoryTranslation::create([
            'service_category_id' => $cat->id,
            'locale' => 'vi',
            'name' => 'Chăm Sóc Body',
            'slug' => 'cham-soc-body',
        ]);

        $service = Service::create([
            'service_category_id' => $cat->id,
            'status' => ContentStatus::PUBLISHED,
            'is_featured' => true,
            'sort_order' => 1,
        ]);
        ServiceTranslation::create([
            'service_id' => $service->id,
            'locale' => 'en',
            'name' => 'Aromatherapy Body Ritual',
            'slug' => 'aromatherapy-body-ritual',
            'excerpt' => 'Holistic aromatic oil body treatment.',
        ]);

        $enResponse = $this->get('/en');
        $enResponse->assertStatus(200);
        $enResponse->assertSee('Aromatherapy Body Ritual');
        // Untranslated Vietnamese category name MUST NOT leak into English
        $enResponse->assertDontSee('Chăm Sóc Body');
    }

    public function test_training_course_scheduling_and_tuition_display(): void
    {
        $now = Carbon::now();

        // 1. Current published course
        $course1 = TrainingCourse::create([
            'status' => ContentStatus::PUBLISHED,
            'is_featured' => true,
            'published_at' => $now->copy()->subDay(),
            'tuition_fee' => 15000000,
            'sort_order' => 1,
        ]);
        TrainingCourseTranslation::create([
            'training_course_id' => $course1->id,
            'locale' => 'vi',
            'title' => 'Khóa Học Trị Liệu Dưỡng Sinh Chuyên Sâu',
            'slug' => 'khoa-hoc-tri-lieu-duong-sinh',
            'duration_display' => '3 tháng',
        ]);

        // 2. Future scheduled course
        $course2 = TrainingCourse::create([
            'status' => ContentStatus::PUBLISHED,
            'is_featured' => true,
            'published_at' => $now->copy()->addDay(),
            'tuition_fee' => 20000000,
            'sort_order' => 2,
        ]);
        TrainingCourseTranslation::create([
            'training_course_id' => $course2->id,
            'locale' => 'vi',
            'title' => 'Khóa Học Tương Lai Chưa Mở',
            'slug' => 'khoa-hoc-tuong-lai',
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Khóa Học Trị Liệu Dưỡng Sinh Chuyên Sâu');
        $response->assertSee('15.000.000 ₫');
        $response->assertSee('3 tháng');
        $response->assertDontSee('Khóa Học Tương Lai Chưa Mở');
    }

    public function test_blog_post_scheduling_and_timezone_formatting(): void
    {
        $user = User::factory()->create();
        $now = Carbon::now();

        $cat = PostCategory::create(['sort_order' => 1]);
        PostCategoryTranslation::create([
            'post_category_id' => $cat->id,
            'locale' => 'vi',
            'name' => 'Làm Đẹp',
            'slug' => 'lam-dep',
        ]);

        // 1. Past published post
        $post1 = Post::create([
            'post_category_id' => $cat->id,
            'author_id' => $user->id,
            'status' => ContentStatus::PUBLISHED,
            'published_at' => Carbon::create(2026, 8, 20, 10, 0, 0, 'UTC'),
        ]);
        PostTranslation::create([
            'post_id' => $post1->id,
            'locale' => 'vi',
            'title' => 'Bí Quyết Trẻ Hóa Làn Da Cùng Thảo Mộc',
            'slug' => 'bi-quyet-tre-hoa-lan-da',
            'excerpt' => 'Chia sẻ phương pháp chăm sóc da từ chuyên gia Hàn Quốc.',
        ]);

        // 2. Future scheduled post
        $post2 = Post::create([
            'post_category_id' => $cat->id,
            'author_id' => $user->id,
            'status' => ContentStatus::PUBLISHED,
            'published_at' => $now->copy()->addWeek(),
        ]);
        PostTranslation::create([
            'post_id' => $post2->id,
            'locale' => 'vi',
            'title' => 'Bài Viết Tương Lai Ẩn',
            'slug' => 'bai-viet-tuong-lai-an',
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Bí Quyết Trẻ Hóa Làn Da Cùng Thảo Mộc');
        // Formatted date in Asia/Ho_Chi_Minh: 20/08/2026
        $response->assertSee('20/08/2026');
        $response->assertDontSee('Bài Viết Tương Lai Ẩn');
    }

    public function test_page_media_hero_convention_selects_first_ordered_item(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/hero-b.jpg', 'fake-image-b');
        Storage::disk('public')->put('media/hero-a.jpg', 'fake-image-a');

        $user = User::factory()->create();

        $mediaA = Media::create([
            'disk' => 'public',
            'path' => 'media/hero-a.jpg',
            'file_name' => 'hero-a.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size_bytes' => 1024,
            'uploaded_by' => $user->id,
        ]);
        MediaTranslation::create([
            'media_id' => $mediaA->id,
            'locale' => 'vi',
            'alt_text' => 'Ảnh Phụ Sort 1',
        ]);

        $mediaB = Media::create([
            'disk' => 'public',
            'path' => 'media/hero-b.jpg',
            'file_name' => 'hero-b.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size_bytes' => 1024,
            'uploaded_by' => $user->id,
        ]);
        MediaTranslation::create([
            'media_id' => $mediaB->id,
            'locale' => 'vi',
            'alt_text' => 'Ảnh Chính Sort 0',
        ]);

        $page = Page::create([
            'key' => 'home',
            'status' => ContentStatus::PUBLISHED,
        ]);
        PageTranslation::create([
            'page_id' => $page->id,
            'locale' => 'vi',
            'title' => 'Trang Chủ Spa',
            'slug' => 'trang-chu',
        ]);

        // Attach with sort order
        $page->media()->attach($mediaA->id, ['sort_order' => 1]);
        $page->media()->attach($mediaB->id, ['sort_order' => 0]);

        $response = $this->get('/');
        $response->assertStatus(200);
        // Media B (sort_order = 0) is selected as hero
        $response->assertSee('hero-b.jpg');
        $response->assertSee('Ảnh Chính Sort 0');
        $response->assertDontSee('hero-a.jpg');
    }

    public function test_missing_physical_media_file_gracefully_falls_back_without_crash(): void
    {
        Storage::fake('public'); // empty disk, no files exist

        $user = User::factory()->create();

        $media = Media::create([
            'disk' => 'public',
            'path' => 'media/ghost-file.jpg',
            'file_name' => 'ghost-file.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size_bytes' => 1024,
            'uploaded_by' => $user->id,
        ]);

        $cat = ServiceCategory::create(['sort_order' => 1]);
        $service = Service::create([
            'service_category_id' => $cat->id,
            'hero_media_id' => $media->id,
            'status' => ContentStatus::PUBLISHED,
            'is_featured' => true,
            'sort_order' => 1,
        ]);
        ServiceTranslation::create([
            'service_id' => $service->id,
            'locale' => 'vi',
            'name' => 'Dịch Vụ Có Ảnh Ảo',
            'slug' => 'dich-vu-co-anh-ao',
        ]);

        // Must return 200 OK without crashing on file existence check
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Dịch Vụ Có Ảnh Ảo');
        $response->assertDontSee('ghost-file.jpg');
    }

    public function test_rich_html_in_page_content_is_safely_rendered_as_plain_text(): void
    {
        $page = Page::create([
            'key' => 'home',
            'status' => ContentStatus::PUBLISHED,
        ]);

        PageTranslation::create([
            'page_id' => $page->id,
            'locale' => 'vi',
            'title' => 'Trang Chủ Bảo Mật',
            'slug' => 'trang-chu-bao-mat',
            'content' => '<p>Chào mừng bạn đến với <strong>Việt Hàn Spa</strong>.</p><script>alert("xss")</script><div class="bad">Nơi thư giãn tuyệt vời.</div>',
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        // Plain text content is visible
        $response->assertSee('Chào mừng bạn đến với Việt Hàn Spa.');
        // Executable script tag is completely stripped
        $response->assertDontSee('<script>', false);
        $response->assertDontSee('alert("xss")', false);
    }

    public function test_rendered_cards_contain_zero_broken_detail_links(): void
    {
        $cat = ServiceCategory::create(['sort_order' => 1]);
        $service = Service::create([
            'service_category_id' => $cat->id,
            'status' => ContentStatus::PUBLISHED,
            'is_featured' => true,
            'sort_order' => 1,
        ]);
        ServiceTranslation::create([
            'service_id' => $service->id,
            'locale' => 'vi',
            'name' => 'Trị Liệu Thử Nghiệm',
            'slug' => 'tri-lieu-thu-nghiem',
        ]);

        $course = TrainingCourse::create([
            'status' => ContentStatus::PUBLISHED,
            'is_featured' => true,
            'published_at' => Carbon::now()->subHour(),
        ]);
        TrainingCourseTranslation::create([
            'training_course_id' => $course->id,
            'locale' => 'vi',
            'title' => 'Khóa Học Thử Nghiệm',
            'slug' => 'khoa-hoc-thu-nghiem',
        ]);

        $user = User::factory()->create();
        $postCat = PostCategory::create(['sort_order' => 1]);
        $post = Post::create([
            'post_category_id' => $postCat->id,
            'author_id' => $user->id,
            'status' => ContentStatus::PUBLISHED,
            'published_at' => Carbon::now()->subHour(),
        ]);
        PostTranslation::create([
            'post_id' => $post->id,
            'locale' => 'vi',
            'title' => 'Bài Viết Thử Nghiệm',
            'slug' => 'bai-viet-thu-nghiem',
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Assert no premature Phase 10 detail URLs exist in page body
        $response->assertDontSee('/dich-vu/', false);
        $response->assertDontSee('/dao-tao-hoc-vien/', false);
        $response->assertDontSee('/blog/', false);
    }
}
