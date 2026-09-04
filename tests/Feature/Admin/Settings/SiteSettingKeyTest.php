<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Settings;

use App\Enums\SiteSettingType;
use App\Services\Settings\SiteSettingWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class SiteSettingKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_key_normalization_and_validation(): void
    {
        $writer = app(SiteSettingWriter::class);

        // Valid keys
        $this->assertSame('contact.phone', $writer->validateKey('  CONTACT.PHONE  '));
        $this->assertSame('social.facebook_url', $writer->validateKey('social.facebook_url'));
        $this->assertSame('business-hours.weekday', $writer->validateKey('business-hours.weekday'));

        // Invalid grammar keys
        $invalidKeys = [
            'contact phone',     // Spaces
            'contact/phone',     // Slashes
            '../traversal',      // Path traversal
            '<h1>config</h1>',   // HTML
            'key..double-dot',   // Double punctuation
            '.leading-dot',      // Leading punctuation
            'trailing-dot.',     // Trailing punctuation
            'viet-hàn-spa',      // Non-ascii
        ];

        foreach ($invalidKeys as $invalidKey) {
            try {
                $writer->validateKey($invalidKey);
                $this->fail("Expected InvalidArgumentException for invalid key '{$invalidKey}'");
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString('Invalid setting key', $e->getMessage());
            }
        }
    }

    public function test_duplicate_key_rejected(): void
    {
        $writer = app(SiteSettingWriter::class);

        $writer->create([
            'key' => 'contact.hotline',
            'value' => '19001234',
            'type' => SiteSettingType::STRING,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Setting with key 'contact.hotline' already exists.");

        $writer->create([
            'key' => 'contact.hotline',
            'value' => '19005678',
            'type' => SiteSettingType::STRING,
        ]);
    }

    public function test_setting_key_is_immutable_across_normal_edits(): void
    {
        $writer = app(SiteSettingWriter::class);

        $setting = $writer->create([
            'key' => 'brand.slogan',
            'value' => 'Vẻ đẹp chuẩn Hàn',
            'type' => SiteSettingType::STRING,
        ]);

        $this->assertSame('brand.slogan', $setting->key);

        // Attempt to pass different key during update
        $updated = $writer->update($setting, [
            'key' => 'brand.new_key',
            'value' => 'Slogan mới',
        ]);

        $this->assertSame('brand.slogan', $updated->key);
        $this->assertSame('brand.slogan', $setting->fresh()->key);
        $this->assertDatabaseMissing('site_settings', ['key' => 'brand.new_key']);
    }
}
