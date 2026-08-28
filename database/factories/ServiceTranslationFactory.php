<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceTranslation>
 */
class ServiceTranslationFactory extends Factory
{
    protected $model = ServiceTranslation::class;

    public function definition(): array
    {
        $name = $this->faker->words(4, true);

        return [
            'service_id' => Service::factory(),
            'locale' => 'vi',
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1000, 99999),
            'excerpt' => $this->faker->sentence(12),
            'content' => '<p>'.$this->faker->paragraphs(3, true).'</p>',
            'benefits' => [
                ['title' => 'Thư giãn sâu', 'desc' => 'Giảm căng thẳng và mệt mỏi toàn thân.'],
                ['title' => 'Lưu thông khí huyết', 'desc' => 'Kích thích tuần hoàn máu hiệu quả.'],
            ],
            'process_steps' => [
                ['step' => 1, 'title' => 'Ngâm chân thảo dược', 'desc' => 'Làm ấm cơ thể.'],
                ['step' => 2, 'title' => 'Massage trị liệu', 'desc' => 'Xoa bóp chuyên sâu vùng cổ vai gáy.'],
            ],
            'faqs' => [
                ['q' => 'Liệu trình kéo dài bao lâu?', 'a' => 'Từ 60 đến 90 phút tùy gói dịch vụ.'],
            ],
            'seo_title' => ucfirst($name).' - Dịch Vụ Spa Chuẩn Hàn',
            'seo_description' => $this->faker->sentence(15),
        ];
    }
}
