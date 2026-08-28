<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TrainingInquiryStatus;
use App\Models\TrainingCourse;
use App\Models\TrainingInquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingInquiry>
 */
class TrainingInquiryFactory extends Factory
{
    protected $model = TrainingInquiry::class;

    public function definition(): array
    {
        $phoneNum = '090'.$this->faker->numberBetween(1000000, 9999999);

        return [
            'reference' => 'TRN-'.date('Ymd').'-'.strtoupper($this->faker->bothify('??###?')),
            'training_course_id' => TrainingCourse::factory(),
            'customer_name' => $this->faker->name(),
            'phone' => $phoneNum,
            'phone_normalized' => '+84'.substr($phoneNum, 1),
            'email' => $this->faker->safeEmail(),
            'message' => 'Tôi muốn nhận tư vấn lịch học và ưu đãi học phí.',
            'status' => TrainingInquiryStatus::NEW,
            'locale' => 'vi',
            'admin_note' => null,
            'contacted_at' => null,
            'enrolled_at' => null,
            'closed_at' => null,
            'utm_source' => 'facebook',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'academy_august_2026',
            'utm_content' => 'ad_creative_01',
            'utm_term' => 'hoc spa chuyen nghiep',
            'gclid' => null,
            'gbraid' => null,
            'wbraid' => null,
            'fbclid' => 'IwAR'.$this->faker->regexify('[A-Za-z0-9]{20}'),
            'fbp' => 'fb.1.'.time().'.'.$this->faker->numberBetween(100000, 999999),
            'fbc' => null,
            'landing_page' => 'https://viethanauhanspa.com/dao-tao',
            'referrer' => 'https://facebook.com/',
        ];
    }
}
