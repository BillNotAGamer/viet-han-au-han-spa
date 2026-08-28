<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_twenty_three_business_tables_exist(): void
    {
        $expectedBusinessTables = [
            'service_categories',
            'service_category_translations',
            'services',
            'service_translations',
            'service_prices',
            'service_price_translations',
            'service_media',
            'training_courses',
            'training_course_translations',
            'training_course_media',
            'training_inquiries',
            'post_categories',
            'post_category_translations',
            'posts',
            'post_translations',
            'post_media',
            'pages',
            'page_translations',
            'page_media',
            'media',
            'media_translations',
            'bookings',
            'site_settings',
        ];

        $actualBusinessTables = [];
        foreach ($expectedBusinessTables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Table {$table} does not exist in schema.");
            $actualBusinessTables[] = $table;
        }

        $this->assertCount(23, $actualBusinessTables);
    }

    public function test_critical_columns_exist_on_lead_and_configuration_tables(): void
    {
        // Soft deletes
        $this->assertTrue(Schema::hasColumn('bookings', 'deleted_at'));
        $this->assertTrue(Schema::hasColumn('training_inquiries', 'deleted_at'));

        // Booking historical snapshots
        $this->assertTrue(Schema::hasColumn('bookings', 'service_name_snapshot'));
        $this->assertTrue(Schema::hasColumn('bookings', 'service_price_label_snapshot'));
        $this->assertTrue(Schema::hasColumn('bookings', 'duration_minutes_snapshot'));
        $this->assertTrue(Schema::hasColumn('bookings', 'price_amount_snapshot'));

        // Service price translation label
        $this->assertTrue(Schema::hasColumn('service_price_translations', 'label'));

        // Page translation slug
        $this->assertTrue(Schema::hasColumn('page_translations', 'slug'));

        // Site settings security
        $this->assertTrue(Schema::hasColumn('site_settings', 'is_public'));
    }
}
