<?php

declare(strict_types=1);

namespace Tests\Feature\Localization;

use App\Support\Localization;
use Tests\TestCase;

class UnsupportedLocaleTest extends TestCase
{
    public function test_localization_helper_validates_supported_locales(): void
    {
        $this->assertTrue(Localization::isSupported('vi'));
        $this->assertTrue(Localization::isSupported('en'));
        $this->assertFalse(Localization::isSupported('fr'));
        $this->assertFalse(Localization::isSupported('de'));
        $this->assertFalse(Localization::isSupported('../../'));
        $this->assertFalse(Localization::isSupported('<script>'));
        $this->assertFalse(Localization::isSupported(null));
        $this->assertFalse(Localization::isSupported(''));
    }

    public function test_unsupported_prefix_returns_404(): void
    {
        $response = $this->get('/fr');
        $response->assertStatus(404);

        $response2 = $this->get('/de');
        $response2->assertStatus(404);
    }
}
