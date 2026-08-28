<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Settings;

use App\Enums\SiteSettingType;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\Settings\SiteSettings;
use App\Services\Settings\SiteSettingWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SiteSettingResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_non_admin_cannot_access_site_settings(): void
    {
        $this->get('/admin/site-settings')->assertStatus(302);

        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->get('/admin/site-settings')->assertStatus(403);
    }

    public function test_admin_can_access_setting_list_create_and_edit(): void
    {
        $admin = User::factory()->admin()->create();
        $setting = SiteSetting::factory()->create(['key' => 'general.brand_name']);

        $this->actingAs($admin)->get('/admin/site-settings')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/site-settings/create')->assertStatus(200);
        $this->actingAs($admin)->get("/admin/site-settings/{$setting->id}/edit")->assertStatus(200);
    }

    public function test_new_setting_defaults_to_private(): void
    {
        $writer = app(SiteSettingWriter::class);

        $setting = $writer->create([
            'key' => 'internal.ops_phone',
            'value' => '0901234567',
            'type' => SiteSettingType::STRING,
            // is_public omitted
        ]);

        $this->assertFalse($setting->is_public);
        $this->assertDatabaseHas('site_settings', [
            'key' => 'internal.ops_phone',
            'is_public' => 0,
        ]);
    }

    public function test_site_setting_delete_removes_record_and_invalidates_cache(): void
    {
        $writer = app(SiteSettingWriter::class);
        $reader = app(SiteSettings::class);

        $setting = $writer->create([
            'key' => 'temporary.notice',
            'value' => 'Bảo trì hệ thống',
            'type' => SiteSettingType::STRING,
        ]);

        // Prime cache
        $this->assertSame('Bảo trì hệ thống', $reader->get('temporary.notice'));
        $this->assertTrue(Cache::has('site_settings:temporary.notice'));

        // Delete
        $writer->delete($setting);

        $this->assertDatabaseMissing('site_settings', ['key' => 'temporary.notice']);
        $this->assertFalse(Cache::has('site_settings:temporary.notice'));
        $this->assertNull($reader->get('temporary.notice'));
    }
}
