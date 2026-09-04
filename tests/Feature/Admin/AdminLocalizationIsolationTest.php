<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLocalizationIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_panel_is_isolated_from_public_locale_prefixes(): void
    {
        $responseEn = $this->get('/en/admin');
        $responseEn->assertStatus(404);

        $responseVi = $this->get('/vi/admin');
        // /vi redirects to /
        $this->assertTrue(in_array($responseVi->status(), [301, 404], true));
    }

    public function test_public_routes_remain_functional(): void
    {
        $responseVi = $this->get('/');
        $responseVi->assertStatus(200);

        $responseEn = $this->get('/en');
        $responseEn->assertStatus(200);
    }

    public function test_admin_authenticated_session_does_not_leak_into_public_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin')->assertStatus(200);

        // Public routes remain unchanged
        $this->get('/')->assertStatus(200)->assertSee('<html lang="vi"', false);
        $this->get('/en')->assertStatus(200)->assertSee('<html lang="en"', false);
    }
}
