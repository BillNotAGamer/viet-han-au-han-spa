<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Training;

use App\Enums\ContentStatus;
use App\Services\Training\TrainingCourseWriter;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingCourseSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_locale_slug_collision_is_rejected(): void
    {
        $writer = app(TrainingCourseWriter::class);

        $writer->create([
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Khóa Học Gội Đầu', 'slug' => 'khoa-hoc-goi-dau'],
        ]);

        $this->expectException(Exception::class);

        $writer->create([
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Khóa Học Gội Đầu 2', 'slug' => 'khoa-hoc-goi-dau'],
        ]);
    }

    public function test_same_slug_in_different_locale_is_allowed(): void
    {
        $writer = app(TrainingCourseWriter::class);

        $course = $writer->create([
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Khóa Học Spa', 'slug' => 'academy'],
            'en' => ['title' => 'Spa Academy', 'slug' => 'academy'],
        ]);

        $this->assertDatabaseHas('training_course_translations', [
            'training_course_id' => $course->id,
            'locale' => 'vi',
            'slug' => 'academy',
        ]);

        $this->assertDatabaseHas('training_course_translations', [
            'training_course_id' => $course->id,
            'locale' => 'en',
            'slug' => 'academy',
        ]);
    }

    public function test_existing_translation_self_update_succeeds(): void
    {
        $writer = app(TrainingCourseWriter::class);

        $course = $writer->create([
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Tên Gốc', 'slug' => 'slug-on-dinh'],
        ]);

        $updated = $writer->update($course, [
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['title' => 'Tên Mới Sau Khi Cập Nhật', 'slug' => 'slug-on-dinh'],
        ]);

        $this->assertSame('slug-on-dinh', $updated->translationFor('vi')?->slug);
    }

    public function test_blank_slug_auto_generates_and_manual_slug_remains_stable(): void
    {
        $writer = app(TrainingCourseWriter::class);

        // Blank slug auto-generates
        $c1 = $writer->create([
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Đào Tạo Trị Mụn', 'slug' => ''],
        ]);
        $this->assertSame('dao-tao-tri-mun', $c1->translationFor('vi')?->slug);

        // Manual slug is preserved on later updates
        $c2 = $writer->create([
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Khóa Học Ban Đầu', 'slug' => 'slug-dac-biet'],
        ]);
        $this->assertSame('slug-dac-biet', $c2->translationFor('vi')?->slug);

        $c2Updated = $writer->update($c2, [
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Khóa Học Đã Đổi Tên', 'slug' => 'slug-dac-biet'],
        ]);
        $this->assertSame('slug-dac-biet', $c2Updated->translationFor('vi')?->slug);
    }
}
