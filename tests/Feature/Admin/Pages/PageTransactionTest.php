<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Pages;

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Services\Pages\PageWriter;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_rollback_leaves_no_partial_state_on_failure(): void
    {
        $writer = app(PageWriter::class);

        $initialCount = Page::count();

        // 1. First valid page
        $writer->create([
            'key' => 'rollback-base',
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Trang Gốc', 'slug' => 'slug-ton-tai'],
        ]);

        $this->assertSame($initialCount + 1, Page::count());

        // 2. Second page fails due to duplicate localized slug
        try {
            $writer->create([
                'key' => 'rollback-fail',
                'status' => ContentStatus::DRAFT,
                'vi' => ['title' => 'Trang Trùng Slug', 'slug' => 'slug-ton-tai'],
            ]);
            $this->fail('Expected exception on duplicate slug');
        } catch (Exception $e) {
            $this->assertNotEmpty($e->getMessage());
        }

        // Count remains exactly initialCount + 1 (rollback executed cleanly, no orphan page)
        $this->assertSame($initialCount + 1, Page::count());
        $this->assertDatabaseMissing('pages', ['key' => 'rollback-fail']);
    }
}
