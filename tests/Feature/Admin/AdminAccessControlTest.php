<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin');

        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_login_page_is_accessible(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('Việt Hàn Âu Hàn Spa');
    }

    public function test_non_admin_user_is_denied_access_to_panel(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $response = $this->actingAs($user)->get('/admin');

        // Filament throws 403 Forbidden when canAccessPanel returns false
        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Việt Hàn Âu Hàn Spa');
    }

    public function test_database_default_for_is_admin_is_false(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->is_admin);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_admin' => 0,
        ]);
    }

    public function test_is_admin_is_not_mass_assignable(): void
    {
        $user = new User([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret',
            'is_admin' => true,
        ]);

        $this->assertNull($user->is_admin);
    }
}
