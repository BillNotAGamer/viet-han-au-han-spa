<?php

namespace Tests\Feature;

use Tests\TestCase;

class FilamentPanelTest extends TestCase
{
    public function test_admin_route_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_admin_login_page_is_accessible(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }
}
