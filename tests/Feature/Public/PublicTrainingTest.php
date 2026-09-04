<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\MediaTranslation;
use App\Models\TrainingCourse;
use App\Models\TrainingCourseTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicTrainingTest extends TestCase
{
    use RefreshDatabase;

    public function test_training_routes_render_and_vi_prefixed_route_is_not_canonical(): void
    {
        $this->get('/dao-tao')->assertStatus(200);
        $this->get('/en/training')->assertStatus(200);
        $this->get('/vi')->assertStatus(301)->assertRedirect('/');
        $this->get('/vi/dao-tao')->assertStatus(404);
    }

    public function test_empty_database_training_listing_renders_gracefully(): void
    {
        $this->get('/dao-tao')
            ->assertStatus(200)
            ->assertSee(__('training.index.empty', [], 'vi'))
            ->assertDontSee('TrainingCourse #');

        $this->get('/en/training')
            ->assertStatus(200)
            ->assertSee(__('training.index.empty', [], 'en'))
            ->assertDontSee('TrainingCourse #');
    }

    public function test_listing_filters_by_publication_status_and_published_at_schedule(): void
    {
        $published = $this->createCourseWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Visible Published Course',
            'visible-published-course',
            ['published_at' => now()->subMinute()]
        );
        $draft = $this->createCourseWithTranslation(
            ContentStatus::DRAFT,
            'vi',
            'Hidden Draft Course',
            'hidden-draft-course',
            ['published_at' => null]
        );
        $archived = $this->createCourseWithTranslation(
            ContentStatus::ARCHIVED,
            'vi',
            'Hidden Archived Course',
            'hidden-archived-course',
            ['published_at' => now()->subMinute()]
        );
        $future = $this->createCourseWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Hidden Future Course',
            'hidden-future-course',
            ['published_at' => now()->addDay()]
        );

        $response = $this->get('/dao-tao');

        $response->assertStatus(200);
        $response->assertSee('Visible Published Course');
        $response->assertDontSee('Hidden Draft Course');
        $response->assertDontSee('Hidden Archived Course');
        $response->assertDontSee('Hidden Future Course');

        $this->get('/dao-tao/'.$published->translationFor('vi')->slug)->assertStatus(200);
        $this->get('/dao-tao/'.$draft->translationFor('vi')->slug)->assertStatus(404);
        $this->get('/dao-tao/'.$archived->translationFor('vi')->slug)->assertStatus(404);
        $this->get('/dao-tao/'.$future->translationFor('vi')->slug)->assertStatus(404);
    }

    public function test_public_training_requires_exact_requested_locale_without_vi_fallback(): void
    {
        $course = $this->createCourseWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'VI Only Training',
            'vi-only-training'
        );

        $this->get('/dao-tao')
            ->assertStatus(200)
            ->assertSee('VI Only Training');

        $this->get('/en/training')
            ->assertStatus(200)
            ->assertDontSee('VI Only Training');

        $this->get('/dao-tao/vi-only-training')->assertStatus(200);
        $this->get('/en/training/vi-only-training')->assertStatus(404);

        TrainingCourseTranslation::create([
            'training_course_id' => $course->id,
            'locale' => 'en',
            'title' => 'English Training',
            'slug' => 'english-training',
        ]);

        $this->get('/en/training')
            ->assertStatus(200)
            ->assertSee('English Training')
            ->assertDontSee('VI Only Training');

        $this->get('/en/training/english-training')->assertStatus(200);
    }

    public function test_training_detail_slug_is_locale_specific(): void
    {
        $course = $this->createCourseWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Dao Tao Massage',
            'dao-tao-massage'
        );

        TrainingCourseTranslation::create([
            'training_course_id' => $course->id,
            'locale' => 'en',
            'title' => 'Massage Training',
            'slug' => 'massage-training',
        ]);

        $this->get('/dao-tao/dao-tao-massage')->assertStatus(200);
        $this->get('/en/training/massage-training')->assertStatus(200);
        $this->get('/dao-tao/massage-training')->assertStatus(404);
        $this->get('/en/training/dao-tao-massage')->assertStatus(404);
    }

    public function test_tuition_fee_renders_as_persisted_numeric_vnd_without_invented_zero_semantics(): void
    {
        $course = $this->createCourseWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Tuition Checked Course',
            'tuition-checked-course',
            ['tuition_fee' => 12500000]
        );

        TrainingCourseTranslation::create([
            'training_course_id' => $course->id,
            'locale' => 'en',
            'title' => 'Tuition Checked Course EN',
            'slug' => 'tuition-checked-course-en',
        ]);

        $this->get('/dao-tao/tuition-checked-course')
            ->assertStatus(200)
            ->assertSee('12.500.000');

        $this->get('/en/training/tuition-checked-course-en')
            ->assertStatus(200)
            ->assertSee('12.500.000');

        $this->createCourseWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Zero Tuition Course',
            'zero-tuition-course',
            ['tuition_fee' => 0]
        );

        $this->get('/dao-tao/zero-tuition-course')
            ->assertStatus(200)
            ->assertSee('0');
    }

    public function test_existing_missing_and_gallery_training_media_are_resolved_without_crashing(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/training-hero.jpg', 'fake-image');
        Storage::disk('public')->put('media/training-gallery.jpg', 'fake-image');

        $hero = $this->createMedia('media/training-hero.jpg');
        MediaTranslation::create([
            'media_id' => $hero->id,
            'locale' => 'vi',
            'alt_text' => 'Exact VI Training Hero Alt',
        ]);

        $gallery = $this->createMedia('media/training-gallery.jpg');
        MediaTranslation::create([
            'media_id' => $gallery->id,
            'locale' => 'vi',
            'alt_text' => 'Exact VI Training Gallery Alt',
            'caption' => 'Training gallery caption',
        ]);

        $course = $this->createCourseWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Training With Real Media',
            'training-with-real-media',
            ['hero_media_id' => $hero->id]
        );
        $course->media()->attach($gallery->id, ['sort_order' => 0, 'created_at' => now()]);

        $this->get('/dao-tao')
            ->assertStatus(200)
            ->assertSee('training-hero.jpg')
            ->assertSee('Exact VI Training Hero Alt');

        $this->get('/dao-tao/training-with-real-media')
            ->assertStatus(200)
            ->assertSee('training-hero.jpg')
            ->assertSee('Exact VI Training Hero Alt')
            ->assertSee('training-gallery.jpg')
            ->assertSee('Exact VI Training Gallery Alt')
            ->assertSee('Training gallery caption');

        $missingMedia = $this->createMedia('media/missing-training.jpg');
        $missingCourse = $this->createCourseWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Training With Missing Media',
            'training-with-missing-media',
            ['hero_media_id' => $missingMedia->id]
        );

        $this->get('/dao-tao/'.$missingCourse->translationFor('vi')->slug)
            ->assertStatus(200)
            ->assertSee('Training With Missing Media')
            ->assertDontSee('missing-training.jpg');
    }

    public function test_wrong_locale_training_media_alt_text_does_not_leak(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/en-training-alt.jpg', 'fake-image');

        $media = $this->createMedia('media/en-training-alt.jpg');
        MediaTranslation::create([
            'media_id' => $media->id,
            'locale' => 'en',
            'alt_text' => 'English Training Alt Must Not Leak',
        ]);

        $this->createCourseWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Training With Locale Safe Alt',
            'training-with-locale-safe-alt',
            ['hero_media_id' => $media->id]
        );

        $this->get('/dao-tao/training-with-locale-safe-alt')
            ->assertStatus(200)
            ->assertSee('en-training-alt.jpg')
            ->assertSee('alt=""', false)
            ->assertDontSee('English Training Alt Must Not Leak');
    }

    public function test_training_structured_content_uses_actual_translation_schema_data(): void
    {
        $course = $this->createCourseWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Structured Training Course',
            'structured-training-course'
        );

        $translation = $course->translationFor('vi');
        $translation->update([
            'duration_display' => '6 tuần',
            'schedule_display' => 'Tối thứ 2-4-6',
            'target_audience' => 'Học viên mới bắt đầu',
            'curriculum_modules' => [
                ['title' => 'Kỹ thuật nền tảng', 'description' => 'Thực hành theo quy trình đã biên soạn.'],
            ],
            'benefits' => [
                'Thực hành trên mẫu thật',
            ],
            'faqs' => [
                ['question' => 'Có cần kinh nghiệm trước không?', 'answer' => 'Không bắt buộc.'],
            ],
        ]);

        $this->get('/dao-tao/structured-training-course')
            ->assertStatus(200)
            ->assertSee('6 tuần')
            ->assertSee('Tối thứ 2-4-6')
            ->assertSee('Học viên mới bắt đầu')
            ->assertSee('Kỹ thuật nền tảng')
            ->assertSee('Thực hành theo quy trình đã biên soạn.')
            ->assertSee('Thực hành trên mẫu thật')
            ->assertSee('Có cần kinh nghiệm trước không?')
            ->assertSee('Không bắt buộc.');
    }

    public function test_training_detail_language_switch_targets_exact_translated_slug_and_index_fallback(): void
    {
        $course = $this->createCourseWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Dao Tao Chuyen Sau',
            'dao-tao-chuyen-sau'
        );
        TrainingCourseTranslation::create([
            'training_course_id' => $course->id,
            'locale' => 'en',
            'title' => 'Advanced Training',
            'slug' => 'advanced-training',
        ]);

        $this->get('/dao-tao/dao-tao-chuyen-sau')
            ->assertStatus(200)
            ->assertSee('href="'.route('en.training.show', ['slug' => 'advanced-training']).'"', false);

        $this->get('/en/training/advanced-training')
            ->assertStatus(200)
            ->assertSee('href="'.route('vi.training.show', ['slug' => 'dao-tao-chuyen-sau']).'"', false);

        $this->createCourseWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Missing English Training',
            'missing-english-training'
        );

        $this->get('/dao-tao/missing-english-training')
            ->assertStatus(200)
            ->assertSee('href="'.route('en.training.index').'"', false);
    }

    public function test_header_training_navigation_targets_real_listing_routes_without_visual_class_changes(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('href="'.route('vi.training.index').'"', false)
            ->assertSee('href="'.route('vi.services.index').'"', false)
            ->assertSee('class="public-header__link"', false);

        $this->get('/en')
            ->assertStatus(200)
            ->assertSee('href="'.route('en.training.index').'"', false)
            ->assertSee('href="'.route('en.services.index').'"', false)
            ->assertSee('class="public-header__link"', false);

        $this->get('/dao-tao')
            ->assertStatus(200)
            ->assertSee('public-header--sticky', false)
            ->assertSee('href="'.route('vi.training.index').'"', false);
    }

    public function test_phase_10b_does_not_expose_public_training_inquiry_mutation_routes(): void
    {
        $publicTrainingMutationRoutes = collect(Route::getRoutes())
            ->filter(function ($route) {
                $uri = $route->uri();

                return str_contains($uri, 'dao-tao')
                    || str_contains($uri, 'training');
            })
            ->filter(fn ($route) => in_array('POST', $route->methods(), true))
            ->reject(fn ($route) => str_starts_with($route->uri(), 'admin'))
            ->values();

        $this->assertCount(0, $publicTrainingMutationRoutes);
    }

    /**
     * @param  array<string, mixed>  $courseOverrides
     */
    protected function createCourseWithTranslation(
        ContentStatus $status,
        string $locale,
        string $title,
        string $slug,
        array $courseOverrides = []
    ): TrainingCourse {
        $course = TrainingCourse::create(array_merge([
            'hero_media_id' => null,
            'tuition_fee' => 12000000,
            'status' => $status,
            'is_featured' => false,
            'sort_order' => 1,
            'published_at' => $status === ContentStatus::PUBLISHED ? now()->subMinute() : null,
        ], $courseOverrides));

        TrainingCourseTranslation::create([
            'training_course_id' => $course->id,
            'locale' => $locale,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $title.' excerpt',
            'content' => '<p>'.$title.' content paragraph.</p>',
        ]);

        return $course->fresh(['translations']);
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
