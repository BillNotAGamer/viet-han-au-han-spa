<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Pages;

use App\Enums\ContentStatus;
use App\Services\Pages\PageWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PageKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_key_normalization_and_validation(): void
    {
        $writer = app(PageWriter::class);

        // Normalized to lowercase trimmed
        $this->assertSame('home', $writer->normalizeKey('  HOME  '));
        $this->assertSame('about-us', $writer->normalizeKey('About-Us'));
        $this->assertSame('brand_story_2026', $writer->normalizeKey('Brand_Story_2026'));

        // Invalid keys rejected
        $invalidKeys = [
            'home page',       // Spaces
            'about/contact',   // Slashes
            '../traversal',    // Path traversal
            '<h1>title</h1>',  // HTML
            '--leading-dash',  // Invalid format
            'trailing-dash-',  // Invalid format
            'viet-hàn-spa',    // Non-ascii characters in machine key
        ];

        foreach ($invalidKeys as $invalidKey) {
            try {
                $writer->normalizeKey($invalidKey);
                $this->fail("Expected InvalidArgumentException for invalid key '{$invalidKey}'");
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString('Invalid page key', $e->getMessage());
            }
        }
    }

    public function test_unique_key_enforced(): void
    {
        $writer = app(PageWriter::class);

        $writer->create([
            'key' => 'unique-test',
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Trang 1'],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Page with key 'unique-test' already exists.");

        $writer->create([
            'key' => 'unique-test',
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Trang 2'],
        ]);
    }

    public function test_page_key_remains_immutable_across_normal_edits(): void
    {
        $writer = app(PageWriter::class);

        $page = $writer->create([
            'key' => 'immutable-key',
            'status' => ContentStatus::DRAFT,
            'vi' => ['title' => 'Tiêu đề ban đầu'],
        ]);

        $this->assertSame('immutable-key', $page->key);

        // Attempt to pass another key in edit data
        $updated = $writer->update($page, [
            'key' => 'hacked-key',
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['title' => 'Tiêu đề đã sửa'],
        ]);

        // Key must remain strictly unchanged
        $this->assertSame('immutable-key', $updated->key);
        $this->assertSame('immutable-key', $page->fresh()->key);
        $this->assertDatabaseMissing('pages', ['key' => 'hacked-key']);
    }
}
