<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Training;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\TrainingCourse;
use App\Models\TrainingInquiry;
use App\Models\User;
use App\Services\Training\TrainingCourseWriter;
use DomainException;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingCourseResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_non_admin_cannot_access_training_course_admin(): void
    {
        $this->get('/admin/training-courses')->assertStatus(302);

        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->get('/admin/training-courses')->assertStatus(403);
    }

    public function test_admin_can_access_course_admin_pages(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/training-courses')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/training-courses/create')->assertStatus(200);
    }

    public function test_course_writer_creates_course_atomically_with_bilingual_translations_and_gallery(): void
    {
        $writer = app(TrainingCourseWriter::class);
        $heroMedia = Media::factory()->create();
        $gallery = Media::factory()->count(2)->create();

        $course = $writer->create([
            'status' => ContentStatus::PUBLISHED,
            'is_featured' => true,
            'sort_order' => 1,
            'tuition_fee' => 8500000,
            'hero_media_id' => $heroMedia->id,
            'vi' => [
                'title' => 'Khóa Học Spa Chuyên Nghiệp',
                'slug' => 'khoa-hoc-spa-chuyen-nghiep',
                'duration_display' => '3 tháng (120 giờ)',
                'schedule_display' => 'T2-T4-T6',
                'target_audience' => 'Người mới bắt đầu',
                'excerpt' => 'Đào tạo kỹ thuật viên spa từ cơ bản đến nâng cao',
                'content' => '<p>Chương trình chuẩn Hàn Quốc</p>',
                'curriculum_modules' => [
                    ['title' => 'Module 1: Chăm sóc da cơ bản', 'description' => 'Giải phẫu da và các bước làm sạch'],
                ],
                'benefits' => [
                    ['title' => 'Cấp chứng chỉ hành nghề', 'description' => 'Chứng chỉ có giá trị toàn quốc'],
                ],
                'faqs' => [
                    ['question' => 'Học phí có bao gồm dụng cụ không?', 'answer' => 'Có, bao gồm toàn bộ mỹ phẩm thực hành'],
                ],
                'seo_title' => 'Khóa Học Spa Chuyên Nghiệp Đà Nẵng',
                'seo_description' => 'Học spa uy tín chất lượng cao',
            ],
            'en' => [
                'title' => 'Professional Spa Course',
                'slug' => 'professional-spa-course',
                'duration_display' => '3 Months',
            ],
            'gallery_media_ids' => $gallery->pluck('id')->toArray(),
        ]);

        $this->assertInstanceOf(TrainingCourse::class, $course);
        $this->assertSame(ContentStatus::PUBLISHED, $course->status);
        $this->assertSame(8500000, $course->tuition_fee);
        $this->assertTrue($course->is_featured);

        $this->assertDatabaseHas('training_courses', [
            'id' => $course->id,
            'tuition_fee' => 8500000,
            'status' => 'PUBLISHED',
            'is_featured' => 1,
        ]);

        $this->assertDatabaseHas('training_course_translations', [
            'training_course_id' => $course->id,
            'locale' => 'vi',
            'title' => 'Khóa Học Spa Chuyên Nghiệp',
            'slug' => 'khoa-hoc-spa-chuyen-nghiep',
        ]);

        $this->assertDatabaseHas('training_course_translations', [
            'training_course_id' => $course->id,
            'locale' => 'en',
            'title' => 'Professional Spa Course',
            'slug' => 'professional-spa-course',
        ]);

        $this->assertCount(2, $course->media);
    }

    public function test_course_update_preserves_translation_ids(): void
    {
        $writer = app(TrainingCourseWriter::class);

        $course = $writer->create([
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Khóa Học Gốc', 'slug' => 'khoa-hoc-goc'],
            'en' => ['title' => 'Original Course', 'slug' => 'original-course'],
        ]);

        $originalViId = $course->translationFor('vi')?->id;
        $originalEnId = $course->translationFor('en')?->id;

        $updated = $writer->update($course, [
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['title' => 'Khóa Học Cập Nhật', 'slug' => 'khoa-hoc-cap-nhat'],
            'en' => ['title' => 'Updated Course', 'slug' => 'updated-course'],
        ]);

        $this->assertSame($originalViId, $updated->translationFor('vi')?->id);
        $this->assertSame($originalEnId, $updated->translationFor('en')?->id);
        $this->assertSame('Khóa Học Cập Nhật', $updated->translationFor('vi')?->title);
    }

    public function test_omitted_en_translation_is_not_fabricated(): void
    {
        $writer = app(TrainingCourseWriter::class);

        $course = $writer->create([
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Khóa Học Chỉ Có Tiếng Việt', 'slug' => 'khoa-hoc-chi-co-tieng-viet'],
        ]);

        $this->assertNotNull($course->translationFor('vi'));
        $this->assertNull($course->translationFor('en'));
        $this->assertDatabaseMissing('training_course_translations', [
            'training_course_id' => $course->id,
            'locale' => 'en',
        ]);
    }

    public function test_tuition_fee_persists_strictly_as_integer(): void
    {
        $writer = app(TrainingCourseWriter::class);

        $course = $writer->create([
            'status' => ContentStatus::DRAFT,
            'tuition_fee' => 12000000,
            'vi' => ['title' => 'Khóa Nâng Cao', 'slug' => 'khoa-nang-cao'],
        ]);

        $this->assertSame(12000000, $course->tuition_fee);
        $this->assertDatabaseHas('training_courses', [
            'id' => $course->id,
            'tuition_fee' => 12000000,
        ]);
    }

    public function test_empty_json_repeaters_persist_as_null(): void
    {
        $writer = app(TrainingCourseWriter::class);

        $course = $writer->create([
            'status' => ContentStatus::DRAFT,
            'vi' => [
                'title' => 'Khóa Học Test JSON',
                'slug' => 'khoa-hoc-test-json',
                'curriculum_modules' => [],
                'benefits' => null,
                'faqs' => [['question' => '', 'answer' => '']],
            ],
        ]);

        $trans = $course->translationFor('vi');
        $this->assertNull($trans->curriculum_modules);
        $this->assertNull($trans->benefits);
        $this->assertNull($trans->faqs);
    }

    public function test_course_archive_and_restore_workflow(): void
    {
        $writer = app(TrainingCourseWriter::class);

        $course = $writer->create([
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['title' => 'Khóa Lưu Trữ', 'slug' => 'khoa-luu-tru'],
        ]);

        $publishedAt = $course->published_at;
        $this->assertNotNull($publishedAt);

        // Archive
        $writer->archive($course);
        $this->assertSame(ContentStatus::ARCHIVED, $course->fresh()->status);
        $this->assertEquals($publishedAt, $course->fresh()->published_at);

        // Restore to DRAFT
        $writer->restore($course);
        $this->assertSame(ContentStatus::DRAFT, $course->fresh()->status);
        $this->assertNull($course->fresh()->published_at); // Cleared on restore to prevent immediate republishing
    }

    public function test_draft_course_without_inquiries_can_be_deleted(): void
    {
        $writer = app(TrainingCourseWriter::class);

        $course = $writer->create([
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Khóa Xóa Tạm', 'slug' => 'khoa-xoa-tam'],
        ]);

        $id = $course->id;
        $writer->delete($course);

        $this->assertDatabaseMissing('training_courses', ['id' => $id]);
        $this->assertDatabaseMissing('training_course_translations', ['training_course_id' => $id]);
    }

    public function test_draft_course_with_inquiries_cannot_be_deleted(): void
    {
        $writer = app(TrainingCourseWriter::class);

        $course = $writer->create([
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Khóa Đã Có Yêu Cầu', 'slug' => 'khoa-da-co-yeu-cau'],
        ]);

        TrainingInquiry::factory()->create(['training_course_id' => $course->id]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot delete course because it has associated inquiries.');

        $writer->delete($course);
    }

    public function test_published_course_cannot_be_deleted(): void
    {
        $writer = app(TrainingCourseWriter::class);

        $course = $writer->create([
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['title' => 'Khóa Đang Xuất Bản', 'slug' => 'khoa-dang-xuat-ban'],
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Only draft courses can be deleted.');

        $writer->delete($course);
    }

    public function test_archived_course_cannot_be_deleted(): void
    {
        $writer = app(TrainingCourseWriter::class);

        $course = $writer->create([
            'status' => ContentStatus::ARCHIVED,
            'vi' => ['title' => 'Khóa Đã Lưu Trữ', 'slug' => 'khoa-da-luu-tru'],
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Only draft courses can be deleted.');

        $writer->delete($course);
    }

    public function test_transaction_rollback_leaves_no_partial_state(): void
    {
        $writer = app(TrainingCourseWriter::class);

        $initialCount = TrainingCourse::count();

        // Create base course with slug 'khoa-hoc-chuan'
        $writer->create([
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Khóa Học Chuẩn', 'slug' => 'khoa-hoc-chuan'],
        ]);

        $this->assertSame($initialCount + 1, TrainingCourse::count());

        try {
            // Trigger failure by violating UNIQUE(locale, slug) on vi
            $writer->create([
                'status' => ContentStatus::DRAFT,
                'vi' => ['title' => 'Khóa Học Trùng', 'slug' => 'khoa-hoc-chuan'],
            ]);
        } catch (Exception $e) {
            // Expected duplicate slug exception
        }

        // Count must remain exactly initialCount + 1 (no partial course record was created)
        $this->assertSame($initialCount + 1, TrainingCourse::count());
    }
}
