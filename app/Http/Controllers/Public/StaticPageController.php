<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Seo\SeoManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\View;

class StaticPageController extends Controller
{
    public function __construct(
        protected SeoManager $seoManager
    ) {}

    public function about(Request $request): View
    {
        $locale = app()->getLocale();
        $localizedUrls = [
            'vi' => route('vi.about'),
            'en' => route('en.about'),
        ];
        $request->attributes->set('localized_urls', $localizedUrls);

        $content = trans('about', [], $locale);

        $seo = $this->seoManager->composeForStaticPage('about', $locale, [
            'title' => $content['meta']['title'] ?? null,
            'seo_title' => null,
            'seo_description' => $content['meta']['description'] ?? null,
            'lead_media' => [
                'url' => Vite::asset('resources/images/pages/about/about-hero-treatment-space.webp'),
            ],
            'seo_alternates' => $localizedUrls,
        ]);

        return view('public.about', [
            'locale' => $locale,
            'title' => $content['meta']['title'] ?? '',
            'content' => $content,
            'contactHref' => $locale === 'en' ? route('en.booking.create') : route('vi.booking.create'),
            'seo' => $seo,
        ]);
    }

    public function contact(Request $request): View
    {
        $locale = app()->getLocale();
        $localizedUrls = [
            'vi' => route('vi.contact'),
            'en' => route('en.contact'),
        ];
        $request->attributes->set('localized_urls', $localizedUrls);

        $content = trans('contact', [], $locale);

        $seo = $this->seoManager->composeForStaticPage('contact', $locale, [
            'title' => $content['meta']['title'] ?? null,
            'seo_title' => null,
            'seo_description' => $content['meta']['description'] ?? null,
            'lead_media' => [
                'url' => Vite::asset('resources/images/pages/contact/contact-reception-lobby.webp'),
            ],
            'seo_alternates' => $localizedUrls,
        ]);

        return view('public.contact', [
            'locale' => $locale,
            'title' => $content['meta']['title'] ?? '',
            'content' => $content,
            'contactHref' => $locale === 'en' ? route('en.booking.create') : route('vi.booking.create'),
            'seo' => $seo,
        ]);
    }
}
