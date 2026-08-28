<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServicePrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $phoneNum = '090'.$this->faker->numberBetween(1000000, 9999999);

        return [
            'reference' => 'BK-'.date('Ymd').'-'.strtoupper($this->faker->bothify('??###?')),
            'service_id' => Service::factory(),
            'service_price_id' => ServicePrice::factory(),
            'service_name_snapshot' => 'Massage Trị Liệu Cổ Vai Gáy Chuẩn Hàn',
            'service_price_label_snapshot' => 'Gói Chuyên Sâu 90 Phút',
            'duration_minutes_snapshot' => 90,
            'price_amount_snapshot' => 490000,
            'customer_name' => $this->faker->name(),
            'phone' => $phoneNum,
            'phone_normalized' => '+84'.substr($phoneNum, 1),
            'email' => $this->faker->safeEmail(),
            'preferred_date' => date('Y-m-d', strtotime('+2 days')),
            'preferred_time' => '19:00',
            'guest_count' => 1,
            'customer_note' => 'Yêu cầu nhân viên nữ tay nghề cao.',
            'admin_note' => null,
            'status' => BookingStatus::NEW,
            'locale' => 'vi',
            'contacted_at' => null,
            'confirmed_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'spa_brand_search',
            'utm_content' => 'ad_text_01',
            'utm_term' => 'spa tri lieu uy tin',
            'gclid' => 'CjwKCAjw'.$this->faker->regexify('[A-Za-z0-9]{20}'),
            'gbraid' => null,
            'wbraid' => null,
            'fbclid' => null,
            'fbp' => null,
            'fbc' => null,
            'landing_page' => 'https://viethanauhanspa.com/dat-lich',
            'referrer' => 'https://google.com/',
        ];
    }
}
