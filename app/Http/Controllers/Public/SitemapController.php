<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\TrainingCourse;
use App\Services\Seo\SeoManager;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(
        protected SeoManager $seoManager
    ) {}

    public function index(): Response
    {
        $items = [];

        // 1. Home routes
        $homePage = Page::query()
            ->where('key', 'home')
            ->where('status', ContentStatus::PUBLISHED)
            ->first();

        $homeLastmod = $homePage?->updated_at?->toAtomString();
        $items[] = ['loc' => $this->seoManager->canonicalUrl('/'), 'lastmod' => $homeLastmod];
        $items[] = ['loc' => $this->seoManager->canonicalUrl('/en'), 'lastmod' => $homeLastmod];

        // 2. Static public routes (About, Contact, listings, booking) - code-owned, zero DB dependency
        $staticRoutes = [
            '/gioi-thieu',
            '/en/about',
            '/lien-he',
            '/en/contact',
            '/dich-vu',
            '/en/services',
            '/dao-tao',
            '/en/training',
            '/blog',
            '/en/blog',
            '/dat-lich',
            '/en/booking',
        ];

        foreach ($staticRoutes as $path) {
            $items[] = ['loc' => $this->seoManager->canonicalUrl($path), 'lastmod' => null];
        }

        // 4. Dynamic Services (PUBLISHED only, exact-locale translations)
        $services = Service::query()
            ->where('status', ContentStatus::PUBLISHED)
            ->with('translations')
            ->get();

        foreach ($services as $service) {
            foreach ($service->translations as $trans) {
                if (! empty($trans->slug)) {
                    $path = $trans->locale === 'en'
                        ? '/en/services/'.$trans->slug
                        : '/dich-vu/'.$trans->slug;

                    $items[] = [
                        'loc' => $this->seoManager->canonicalUrl($path),
                        'lastmod' => ($trans->updated_at ?? $service->updated_at)?->toAtomString(),
                    ];
                }
            }
        }

        // 5. Dynamic Training Courses (PUBLISHED, published_at <= now, exact-locale)
        $courses = TrainingCourse::query()
            ->where('status', ContentStatus::PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with('translations')
            ->get();

        foreach ($courses as $course) {
            foreach ($course->translations as $trans) {
                if (! empty($trans->slug)) {
                    $path = $trans->locale === 'en'
                        ? '/en/training/'.$trans->slug
                        : '/dao-tao/'.$trans->slug;

                    $items[] = [
                        'loc' => $this->seoManager->canonicalUrl($path),
                        'lastmod' => ($trans->updated_at ?? $course->updated_at)?->toAtomString(),
                    ];
                }
            }
        }

        // 6. Dynamic Blog Posts (PUBLISHED, published_at <= now, exact-locale)
        $posts = Post::query()
            ->where('status', ContentStatus::PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with('translations')
            ->get();

        foreach ($posts as $post) {
            foreach ($post->translations as $trans) {
                if (! empty($trans->slug)) {
                    $path = $trans->locale === 'en'
                        ? '/en/blog/'.$trans->slug
                        : '/blog/'.$trans->slug;

                    $items[] = [
                        'loc' => $this->seoManager->canonicalUrl($path),
                        'lastmod' => ($trans->updated_at ?? $post->updated_at)?->toAtomString(),
                    ];
                }
            }
        }

        // 7. Deduplicate items by loc
        $unique = [];
        foreach ($items as $item) {
            $unique[$item['loc']] = $item;
        }

        // 8. Assemble XML
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        foreach ($unique as $entry) {
            $xml .= "    <url>\n";
            $xml .= '        <loc>'.htmlspecialchars($entry['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</loc>\n";
            if (! empty($entry['lastmod'])) {
                $xml .= '        <lastmod>'.htmlspecialchars($entry['lastmod'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</lastmod>\n";
            }
            $xml .= "    </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }
}
