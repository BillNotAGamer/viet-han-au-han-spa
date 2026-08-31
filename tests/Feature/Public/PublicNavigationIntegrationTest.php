<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PublicNavigationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_uses_final_vi_and_en_route_matrix_without_placeholder_links(): void
    {
        $vi = $this->get('/');
        $vi->assertStatus(200);
        $vi->assertSee('href="'.route('vi.home').'"', false);
        $vi->assertSee('href="'.route('vi.about').'"', false);
        $vi->assertSee('href="'.route('vi.services.index').'"', false);
        $vi->assertSee('href="'.route('vi.training.index').'"', false);
        $vi->assertSee('href="'.route('vi.blog.index').'"', false);
        $vi->assertSee('href="'.route('vi.contact').'"', false);
        $vi->assertDontSee('href="#"', false);
        $vi->assertDontSee('public-header__chevron', false);
        $vi->assertDontSee('aria-haspopup', false);
        $vi->assertSee('aria-current="page"', false);

        $en = $this->get('/en');
        $en->assertStatus(200);
        $en->assertSee('href="'.route('en.home').'"', false);
        $en->assertSee('href="'.route('en.about').'"', false);
        $en->assertSee('href="'.route('en.services.index').'"', false);
        $en->assertSee('href="'.route('en.training.index').'"', false);
        $en->assertSee('href="'.route('en.blog.index').'"', false);
        $en->assertSee('href="'.route('en.contact').'"', false);
        $en->assertDontSee('href="#"', false);
        $en->assertDontSee('public-header__chevron', false);
        $en->assertDontSee('aria-haspopup', false);
    }

    public function test_footer_quick_navigation_uses_final_canonical_route_matrix(): void
    {
        $this->createStaticPage('about', 'vi', 'About Footer VI');
        $this->createStaticPage('contact', 'vi', 'Contact Footer VI');
        $this->createStaticPage('about', 'en', 'About Footer EN');
        $this->createStaticPage('contact', 'en', 'Contact Footer EN');

        $vi = $this->get('/gioi-thieu');
        $vi->assertStatus(200);
        $vi->assertSeeInOrder([
            'href="'.route('vi.home').'"',
            'href="'.route('vi.about').'"',
            'href="'.route('vi.services.index').'"',
            'href="'.route('vi.training.index').'"',
            'href="'.route('vi.blog.index').'"',
            'href="'.route('vi.contact').'"',
        ], false);

        $en = $this->get('/en/about');
        $en->assertStatus(200);
        $en->assertSeeInOrder([
            'href="'.route('en.home').'"',
            'href="'.route('en.about').'"',
            'href="'.route('en.services.index').'"',
            'href="'.route('en.training.index').'"',
            'href="'.route('en.blog.index').'"',
            'href="'.route('en.contact').'"',
        ], false);
    }

    public function test_index_language_switch_matrix_uses_canonical_public_routes(): void
    {
        $this->createStaticPage('about', 'vi', 'About Switch VI');
        $this->createStaticPage('about', 'en', 'About Switch EN');
        $this->createStaticPage('contact', 'vi', 'Contact Switch VI');
        $this->createStaticPage('contact', 'en', 'Contact Switch EN');

        $this->get('/')->assertStatus(200)->assertSee('href="'.route('en.home').'"', false);
        $this->get('/en')->assertStatus(200)->assertSee('href="'.route('vi.home').'"', false);
        $this->get('/dich-vu')->assertStatus(200)->assertSee('href="'.route('en.services.index').'"', false);
        $this->get('/en/services')->assertStatus(200)->assertSee('href="'.route('vi.services.index').'"', false);
        $this->get('/dao-tao')->assertStatus(200)->assertSee('href="'.route('en.training.index').'"', false);
        $this->get('/en/training')->assertStatus(200)->assertSee('href="'.route('vi.training.index').'"', false);
        $this->get('/blog')->assertStatus(200)->assertSee('href="'.route('en.blog.index').'"', false);
        $this->get('/en/blog')->assertStatus(200)->assertSee('href="'.route('vi.blog.index').'"', false);
        $this->get('/gioi-thieu')->assertStatus(200)->assertSee('href="'.route('en.about').'"', false);
        $this->get('/en/about')->assertStatus(200)->assertSee('href="'.route('vi.about').'"', false);
        $this->get('/lien-he')->assertStatus(200)->assertSee('href="'.route('en.contact').'"', false);
        $this->get('/en/contact')->assertStatus(200)->assertSee('href="'.route('vi.contact').'"', false);
    }

    public function test_phase_10e_does_not_add_public_booking_or_mutation_routes(): void
    {
        $publicMutationRoutes = collect(Route::getRoutes())
            ->filter(fn ($route) => array_intersect(['POST', 'PUT', 'PATCH', 'DELETE'], $route->methods()) !== [])
            ->reject(fn ($route) => str_starts_with($route->uri(), 'admin'))
            ->reject(fn ($route) => str_starts_with($route->uri(), 'livewire'))
            ->reject(fn ($route) => str_starts_with($route->uri(), 'storage'))
            ->values();

        $bookingRoutes = collect(Route::getRoutes())
            ->filter(fn ($route) => str_contains($route->uri(), 'booking') || str_contains($route->uri(), 'dat-lich'))
            ->reject(fn ($route) => str_starts_with($route->uri(), 'admin'))
            ->values();

        $this->assertCount(0, $publicMutationRoutes);
        $this->assertCount(0, $bookingRoutes);
    }

    public function test_shared_public_blade_navigation_files_do_not_query_the_database(): void
    {
        $files = [
            resource_path('views/components/public/header.blade.php'),
            resource_path('views/components/public/footer.blade.php'),
            resource_path('views/components/public/language-switcher.blade.php'),
            resource_path('views/components/layouts/public.blade.php'),
        ];

        foreach ($files as $file) {
            $contents = file_get_contents($file);

            $this->assertIsString($contents);
            $this->assertStringNotContainsString('DB::', $contents);
            $this->assertStringNotContainsString('::query(', $contents);
            $this->assertStringNotContainsString('where(', $contents);
            $this->assertStringNotContainsString('first(', $contents);
            $this->assertStringNotContainsString('find(', $contents);
        }
    }

    protected function createStaticPage(string $key, string $locale, string $title): Page
    {
        $page = Page::updateOrCreate(
            ['key' => $key],
            ['status' => ContentStatus::PUBLISHED]
        );

        PageTranslation::updateOrCreate(
            ['page_id' => $page->id, 'locale' => $locale],
            [
                'title' => $title,
                'slug' => null,
                'content' => '<p>'.$title.' content.</p>',
            ]
        );

        return $page->fresh(['translations']);
    }
}
