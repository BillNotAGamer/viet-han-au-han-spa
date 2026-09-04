<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Settings;

use App\Enums\SiteSettingType;
use App\Services\Settings\SiteSettingWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class SiteSettingSecretGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_secret_bearing_keys_are_rejected(): void
    {
        $writer = app(SiteSettingWriter::class);

        $forbiddenKeys = [
            'mail.smtp_password',
            'meta.capi_access_token',
            'security.app_key',
            'google.client_secret',
            'database.db_password',
            'service.private_key',
            'admin.master_passwd',
        ];

        foreach ($forbiddenKeys as $forbiddenKey) {
            try {
                $writer->create([
                    'key' => $forbiddenKey,
                    'value' => 'secret_value',
                    'type' => SiteSettingType::STRING,
                ]);
                $this->fail("Expected InvalidArgumentException for forbidden secret key '{$forbiddenKey}'");
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString('contains forbidden keyword', $e->getMessage());
            }
        }
    }

    public function test_public_tracking_identifiers_are_permitted(): void
    {
        $writer = app(SiteSettingWriter::class);

        $publicKeys = [
            'tracking.gtm_container_id',
            'tracking.ga4_measurement_id',
            'tracking.meta_pixel_id',
        ];

        foreach ($publicKeys as $publicKey) {
            $setting = $writer->create([
                'key' => $publicKey,
                'value' => 'ID_SAMPLE_123',
                'type' => SiteSettingType::STRING,
                'is_public' => true,
            ]);

            $this->assertSame($publicKey, $setting->key);
            $this->assertDatabaseHas('site_settings', ['key' => $publicKey]);
        }
    }
}
