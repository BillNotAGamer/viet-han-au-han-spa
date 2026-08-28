<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Training;

use App\Enums\ContentStatus;
use App\Services\Training\TrainingCourseWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TrainingCoursePublishedAtTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishing_with_null_published_at_receives_current_timestamp(): void
    {
        $writer = app(TrainingCourseWriter::class);

        $course = $writer->create([
            'status' => ContentStatus::PUBLISHED,
            'published_at' => null,
            'vi' => ['title' => 'Khóa Xuất Bản Ngay', 'slug' => 'khoa-xuat-ban-ngay'],
        ]);

        $this->assertNotNull($course->published_at);
        $this->assertTrue($course->published_at->isPast() || $course->published_at->isCurrentSecond());
    }

    public function test_editing_published_course_preserves_published_at(): void
    {
        $writer = app(TrainingCourseWriter::class);

        $historicalDate = Carbon::parse('2026-05-01 10:00:00');
        $course = $writer->create([
            'status' => ContentStatus::PUBLISHED,
            'published_at' => $historicalDate,
            'vi' => ['title' => 'Khóa Quá Khứ', 'slug' => 'khoa-qua-khu'],
        ]);

        $updated = $writer->update($course, [
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['title' => 'Khóa Quá Khứ Sửa Tên', 'slug' => 'khoa-qua-khu'],
        ]);

        $this->assertEquals($historicalDate, $updated->published_at);
    }

    public function test_explicit_scheduled_future_timestamp_is_preserved(): void
    {
        $writer = app(TrainingCourseWriter::class);

        $futureDate = Carbon::parse('2026-12-01 08:00:00');
        $course = $writer->create([
            'status' => ContentStatus::PUBLISHED,
            'published_at' => $futureDate,
            'vi' => ['title' => 'Khóa Tương Lai', 'slug' => 'khoa-tuong-lai'],
        ]);

        $this->assertEquals($futureDate, $course->published_at);
    }
}
