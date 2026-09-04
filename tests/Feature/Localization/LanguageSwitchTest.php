<?php

declare(strict_types=1);

namespace Tests\Feature\Localization;

use App\Support\Localization;
use Tests\TestCase;

class LanguageSwitchTest extends TestCase
{
    public function test_switch_from_vietnamese_home_to_english_home(): void
    {
        $url = Localization::switchLocaleUrl('en', 'vi.home');

        $this->assertSame(route('en.home'), $url);
    }

    public function test_switch_from_english_home_to_vietnamese_home(): void
    {
        $url = Localization::switchLocaleUrl('vi', 'en.home');

        $this->assertSame(route('vi.home'), $url);
    }

    public function test_switch_with_unsupported_target_locale_defaults_to_default_locale(): void
    {
        $url = Localization::switchLocaleUrl('fr', 'en.home');

        $this->assertSame(route('vi.home'), $url);
    }
}
