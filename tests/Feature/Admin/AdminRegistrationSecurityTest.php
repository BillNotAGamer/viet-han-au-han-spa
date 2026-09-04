<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminRegistrationSecurityTest extends TestCase
{
    public function test_admin_registration_endpoint_is_not_accessible(): void
    {
        $response = $this->get('/admin/register');

        $response->assertStatus(404);
    }

    public function test_public_registration_endpoint_is_not_accessible(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(404);
    }
}
