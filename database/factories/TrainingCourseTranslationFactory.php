<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TrainingCourse;
use App\Models\TrainingCourseTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TrainingCourseTranslation>
 */
class TrainingCourseTranslationFactory extends Factory
{
    protected $model = TrainingCourseTranslation::class;

    public function definition(): array
    {
        $title = 'Khóa Học '.$this->faker->words(3, true);

        return [
            'training_course_id' => TrainingCourse::factory(),
            'locale' => 'vi',
            'title' => ucfirst($title),
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1000, 99999),
            'excerpt' => $this->faker->sentence(15),
            'content' => '<p>'.$this->faker->paragraphs(3, true).'</p>',
            'duration_display' => '4 tuần (80 giờ)',
            'schedule_display' => 'T2 - T6 / Ca Sáng & Chiều',
            'target_audience' => 'Học viên muốn khởi nghiệp spa hoặc nâng cao tay nghề.',
            'curriculum_modules' => [
                ['module' => 1, 'title' => 'Lý thuyết da học căn bản', 'hours' => 15],
                ['module' => 2, 'title' => 'Thực hành kỹ thuật massage Hàn Quốc', 'hours' => 35],
                ['module' => 3, 'title' => 'Kỹ năng vận hành spa & tư vấn khách hàng', 'hours' => 30],
            ],
            'benefits' => [
                'Cấp chứng chỉ tốt nghiệp chuẩn nghề quốc gia',
                'Cam kết giới thiệu việc làm sau khóa học',
                'Thực hành 80% trên mẫu thật',
            ],
            'faqs' => [
                ['q' => 'Chưa có kinh nghiệm có học được không?', 'a' => 'Khóa học đào tạo từ căn bản đến nâng cao.'],
            ],
            'seo_title' => ucfirst($title).' - Học Viện Spa Việt Hàn',
            'seo_description' => $this->faker->sentence(15),
        ];
    }
}
