<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Enums\BookingStatus;
use App\Enums\ContentStatus;
use App\Enums\SiteSettingType;
use App\Enums\TrainingInquiryStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceTranslation;
use App\Models\SiteSetting;
use App\Models\TrainingCourseTranslation;
use App\Models\TrainingInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelCastTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_columns_cast_to_backed_enums(): void
    {
        $service = Service::factory()->create(['status' => ContentStatus::PUBLISHED]);
        $this->assertInstanceOf(ContentStatus::class, $service->fresh()->status);
        $this->assertSame(ContentStatus::PUBLISHED, $service->fresh()->status);

        $booking = Booking::factory()->create(['status' => BookingStatus::CONFIRMED]);
        $this->assertInstanceOf(BookingStatus::class, $booking->fresh()->status);
        $this->assertSame(BookingStatus::CONFIRMED, $booking->fresh()->status);

        $inquiry = TrainingInquiry::factory()->create(['status' => TrainingInquiryStatus::ENROLLED]);
        $this->assertInstanceOf(TrainingInquiryStatus::class, $inquiry->fresh()->status);
        $this->assertSame(TrainingInquiryStatus::ENROLLED, $inquiry->fresh()->status);

        $setting = SiteSetting::factory()->create(['type' => SiteSettingType::BOOLEAN]);
        $this->assertInstanceOf(SiteSettingType::class, $setting->fresh()->type);
        $this->assertSame(SiteSettingType::BOOLEAN, $setting->fresh()->type);
    }

    public function test_json_editorial_fields_cast_to_php_arrays(): void
    {
        $benefits = [
            ['title' => 'Lợi ích 1', 'desc' => 'Mô tả 1'],
            ['title' => 'Lợi ích 2', 'desc' => 'Mô tả 2'],
        ];
        $steps = [
            ['step' => 1, 'title' => 'Bước 1', 'desc' => 'Thực hiện 1'],
        ];

        $serviceTranslation = ServiceTranslation::factory()->create([
            'benefits' => $benefits,
            'process_steps' => $steps,
        ]);

        $fresh = $serviceTranslation->fresh();
        $this->assertIsArray($fresh->benefits);
        $this->assertCount(2, $fresh->benefits);
        $this->assertSame('Lợi ích 1', $fresh->benefits[0]['title']);
        $this->assertIsArray($fresh->process_steps);
        $this->assertSame(1, $fresh->process_steps[0]['step']);

        $courseTranslation = TrainingCourseTranslation::factory()->create([
            'curriculum_modules' => [
                ['module' => 1, 'title' => 'Căn bản', 'hours' => 20],
            ],
        ]);

        $freshCourse = $courseTranslation->fresh();
        $this->assertIsArray($freshCourse->curriculum_modules);
        $this->assertSame(20, $freshCourse->curriculum_modules[0]['hours']);
    }
}
