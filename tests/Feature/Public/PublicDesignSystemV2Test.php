<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class PublicDesignSystemV2Test extends TestCase
{
    public function test_official_logo_assets_exist_with_expected_dimensions(): void
    {
        $logos = [
            'resources/images/general/viet-han-spa-no-bg-logo.png' => [1254, 1254],
            'resources/images/general/viet-han-spa-no-bg-original-logo.png' => [1536, 1024],
            'resources/images/general/viet-han-spa-white-logo.png' => [1254, 1254],
            'resources/images/general/viet-han-logo.png' => [480, 480],
        ];

        foreach ($logos as $relativePath => $expectedDimensions) {
            $path = base_path($relativePath);

            $this->assertFileExists($path);
            $dimensions = getimagesize($path);
            $this->assertIsArray($dimensions);
            $this->assertSame($expectedDimensions, [$dimensions[0], $dimensions[1]]);
        }
    }

    public function test_logo_component_renders_accessible_variants(): void
    {
        $colored = Blade::render('<x-public.v2.logo variant="colored" />');
        $white = Blade::render('<x-public.v2.logo variant="white" />');
        $compact = Blade::render('<x-public.v2.logo variant="compact" decorative />');

        $this->assertStringContainsString('viet-han-spa-no-bg-logo-', $colored);
        $this->assertStringContainsString('alt="Việt Hàn Âu Hàn Spa"', $colored);
        $this->assertStringContainsString('viet-han-spa-white-logo-', $white);
        $this->assertStringContainsString('width="1254"', $white);
        $this->assertStringContainsString('viet-han-logo-', $compact);
        $this->assertStringContainsString('alt=""', $compact);
        $this->assertStringContainsString('aria-hidden="true"', $compact);
    }

    public function test_button_component_preserves_link_and_button_semantics(): void
    {
        $link = Blade::render('<x-public.v2.button href="/dat-lich">Đặt lịch</x-public.v2.button>');
        $button = Blade::render('<x-public.v2.button type="submit" variant="secondary">Gửi</x-public.v2.button>');
        $disabledLink = Blade::render('<x-public.v2.button href="/dat-lich" disabled>Đặt lịch</x-public.v2.button>');

        $this->assertStringContainsString('<a', $link);
        $this->assertStringContainsString('href="/dat-lich"', $link);
        $this->assertStringNotContainsString('<button', $link);
        $this->assertStringContainsString('<button', $button);
        $this->assertStringContainsString('type="submit"', $button);
        $this->assertStringContainsString('v2-button--secondary', $button);
        $this->assertStringNotContainsString('href="/dat-lich"', $disabledLink);
        $this->assertStringContainsString('aria-disabled="true"', $disabledLink);
    }

    public function test_composition_primitives_render_semantic_foundation_markup(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-public.v2.section surface="deep" spacing="spacious">
                <x-public.v2.container size="reading">
                    <x-public.v2.eyebrow inverse>Chăm sóc</x-public.v2.eyebrow>
                    <x-public.v2.display-heading level="1" size="xl">Không gian tĩnh tại</x-public.v2.display-heading>
                    <x-public.v2.editorial-copy size="lg">Nội dung biên tập.</x-public.v2.editorial-copy>
                    <x-public.v2.media-frame src="/spa.jpg" alt="Không gian spa" role="portrait" position="portrait" priority caption="Không gian spa" />
                </x-public.v2.container>
            </x-public.v2.section>
            BLADE);

        $this->assertStringContainsString('<section', $html);
        $this->assertStringContainsString('<h1', $html);
        $this->assertStringContainsString('<figure', $html);
        $this->assertStringContainsString('alt="Không gian spa"', $html);
        $this->assertStringContainsString('loading="eager"', $html);
        $this->assertStringContainsString('fetchpriority="high"', $html);
        $this->assertStringContainsString('<figcaption', $html);
    }
}
