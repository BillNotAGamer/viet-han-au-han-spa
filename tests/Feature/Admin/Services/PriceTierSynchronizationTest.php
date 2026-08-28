<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Services;

use App\Enums\ContentStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\ServiceCatalog\ServiceWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceTierSynchronizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_existing_price_tier_preserves_stable_id(): void
    {
        $writer = app(ServiceWriter::class);
        $category = ServiceCategory::factory()->create();

        $service = $writer->create([
            'service_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Massage Chân Thảo Dược', 'slug' => 'massage-chan-thao-duoc'],
            'prices' => [
                [
                    'duration_minutes' => 45,
                    'price_amount' => 250000,
                    'sort_order' => 1,
                    'is_active' => true,
                    'label_vi' => 'Gói 45 Phút',
                ],
            ],
        ]);

        $initialPrice = $service->prices()->first();
        $this->assertNotNull($initialPrice);
        $stablePriceId = $initialPrice->id;

        // Update price amount while retaining id
        $updatedService = $writer->update($service, [
            'service_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Massage Chân Thảo Dược', 'slug' => 'massage-chan-thao-duoc'],
            'prices' => [
                [
                    'id' => $stablePriceId,
                    'duration_minutes' => 45,
                    'price_amount' => 280000, // Price updated
                    'sort_order' => 1,
                    'is_active' => true,
                    'label_vi' => 'Gói 45 Phút Cao Cấp',
                ],
            ],
        ]);

        $updatedPrice = $updatedService->prices()->first();
        $this->assertSame($stablePriceId, $updatedPrice->id);
        $this->assertSame(280000, $updatedPrice->price_amount);
        $this->assertSame('Gói 45 Phút Cao Cấp', $updatedPrice->translationFor('vi')?->label);
    }

    public function test_omitted_price_tier_referenced_by_booking_is_deactivated_instead_of_hard_deleted(): void
    {
        $writer = app(ServiceWriter::class);
        $category = ServiceCategory::factory()->create();

        $service = $writer->create([
            'service_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Chăm Sóc Body', 'slug' => 'cham-soc-body'],
            'prices' => [
                [
                    'duration_minutes' => 60,
                    'price_amount' => 300000,
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'duration_minutes' => 90,
                    'price_amount' => 450000,
                    'sort_order' => 2,
                    'is_active' => true,
                ],
            ],
        ]);

        $price60 = $service->prices()->where('duration_minutes', 60)->first();
        $price90 = $service->prices()->where('duration_minutes', 90)->first();

        // Create booking referencing the 60 min price
        Booking::factory()->create([
            'service_id' => $service->id,
            'service_price_id' => $price60->id,
        ]);

        // Update service omitting price60 (simulating deletion from UI) and keeping price90
        $writer->update($service, [
            'service_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Chăm Sóc Body', 'slug' => 'cham-soc-body'],
            'prices' => [
                [
                    'id' => $price90->id,
                    'duration_minutes' => 90,
                    'price_amount' => 450000,
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            ],
        ]);

        // Price 60 MUST still exist in database, but marked as is_active = false
        $this->assertDatabaseHas('service_prices', [
            'id' => $price60->id,
            'is_active' => 0,
        ]);

        // Booking remains valid and intact
        $this->assertDatabaseHas('bookings', [
            'service_price_id' => $price60->id,
        ]);
    }
}
