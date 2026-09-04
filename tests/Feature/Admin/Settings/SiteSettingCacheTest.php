<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Settings;

use App\Enums\SiteSettingType;
use App\Services\Settings\SiteSettings;
use App\Services\Settings\SiteSettingWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SiteSettingCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_setting_reads_are_cached_and_invalidated_on_update(): void
    {
        $writer = app(SiteSettingWriter::class);
        $reader = app(SiteSettings::class);

        $setting = $writer->create([
            'key' => 'cache.test_key',
            'value' => 'Initial Value',
            'type' => SiteSettingType::STRING,
        ]);

        // 1. First get reads from DB and caches
        $this->assertSame('Initial Value', $reader->get('cache.test_key'));
        $this->assertTrue(Cache::has('site_settings:cache.test_key'));

        // 2. Direct database update bypasses cache to test that reader uses cache
        DB::table('site_settings')->where('key', 'cache.test_key')->update(['value' => 'Direct DB Value']);
        $this->assertSame('Initial Value', $reader->get('cache.test_key'), 'Expected cached value to be returned');

        // 3. Proper writer update invalidates cache
        $writer->update($setting, ['value' => 'Proper Updated Value']);
        $this->assertFalse(Cache::has('site_settings:cache.test_key'));

        // 4. Next read retrieves new value
        $this->assertSame('Proper Updated Value', $reader->get('cache.test_key'));
    }

    public function test_missing_setting_returns_default_without_throwing(): void
    {
        $reader = app(SiteSettings::class);

        $this->assertNull($reader->get('non.existent.key'));
        $this->assertSame('Custom Default', $reader->get('non.existent.key', 'Custom Default'));
    }
}
