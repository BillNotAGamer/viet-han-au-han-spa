<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Settings;

use App\Enums\SiteSettingType;
use App\Services\Settings\SiteSettings;
use App\Services\Settings\SiteSettingWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingPublicAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_and_private_filtering(): void
    {
        $writer = app(SiteSettingWriter::class);
        $reader = app(SiteSettings::class);

        // Setting A: Public
        $writer->create([
            'key' => 'contact.public_phone',
            'value' => '0987654321',
            'type' => SiteSettingType::STRING,
            'is_public' => true,
        ]);

        // Setting B: Private
        $writer->create([
            'key' => 'internal.webhook_url',
            'value' => 'https://internal.ops/hook',
            'type' => SiteSettingType::STRING,
            'is_public' => false,
        ]);

        // 1. getPublic returns public setting
        $this->assertSame('0987654321', $reader->getPublic('contact.public_phone'));

        // 2. getPublic returns null for private setting
        $this->assertNull($reader->getPublic('internal.webhook_url'));
        $this->assertSame('Default Private', $reader->getPublic('internal.webhook_url', 'Default Private'));

        // 3. publicSettings() map contains public and excludes private
        $publicMap = $reader->publicSettings();
        $this->assertArrayHasKey('contact.public_phone', $publicMap);
        $this->assertSame('0987654321', $publicMap['contact.public_phone']);
        $this->assertArrayNotHasKey('internal.webhook_url', $publicMap);

        // 4. Internal get() can access both
        $this->assertSame('0987654321', $reader->get('contact.public_phone'));
        $this->assertSame('https://internal.ops/hook', $reader->get('internal.webhook_url'));
    }
}
