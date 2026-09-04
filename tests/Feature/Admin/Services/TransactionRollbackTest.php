<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Services;

use App\Enums\ContentStatus;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\ServiceCatalog\ServiceWriter;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionRollbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_service_creation_rolls_back_entire_transaction(): void
    {
        $writer = app(ServiceWriter::class);
        $category = ServiceCategory::factory()->create();

        $initialServicesCount = Service::count();

        // Deliberately trigger failure inside transaction by violating UNIQUE(locale, slug)
        // First create a service with slug 'test-unique-slug'
        $writer->create([
            'service_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Dịch Vụ Gốc', 'slug' => 'test-unique-slug'],
        ]);

        $this->assertSame($initialServicesCount + 1, Service::count());

        try {
            // Attempt to create second service with same slug and a price
            $writer->create([
                'service_category_id' => $category->id,
                'status' => ContentStatus::DRAFT,
                'vi' => ['name' => 'Dịch Vụ Trùng Slug', 'slug' => 'test-unique-slug'],
                'prices' => [
                    ['duration_minutes' => 60, 'price_amount' => 200000],
                ],
            ]);
        } catch (Exception $e) {
            // Expected duplicate slug exception
        }

        // Count must remain exactly initialServicesCount + 1 (no orphan Service or ServicePrice was created)
        $this->assertSame($initialServicesCount + 1, Service::count());
        $this->assertDatabaseMissing('service_translations', [
            'name' => 'Dịch Vụ Trùng Slug',
        ]);
    }
}
