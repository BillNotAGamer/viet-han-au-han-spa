<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PublicSite\ServicesContent;
use App\Services\Seo\SeoManager;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __construct(
        protected ServicesContent $servicesContent,
        protected SeoManager $seoManager
    ) {}

    public function index(Request $request): View
    {
        $locale = app()->getLocale();

        $request->attributes->set('localized_urls', [
            'vi' => route('vi.services.index'),
            'en' => route('en.services.index'),
        ]);

        $page = (int) $request->query('page', 1);
        $seo = $this->seoManager->composeForListing('services', $locale, $page);

        return view('public.services.index', [
            'locale' => $locale,
            'title' => __('services.meta.index_title'),
            'services' => $this->servicesContent->listingForLocale($locale),
            'contactHref' => $locale === 'en' ? route('en.booking.create') : route('vi.booking.create'),
            'seo' => $seo,
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $locale = app()->getLocale();
        $service = $this->servicesContent->detailForSlug($locale, $slug);

        abort_if($service === null, 404);

        $request->attributes->set('localized_urls', $service['localized_urls']);

        $seo = $this->seoManager->composeForDetail('services', $locale, $service);

        return view('public.services.show', [
            'locale' => $locale,
            'title' => $service['name'],
            'service' => $service,
            'contactHref' => $locale === 'en' ? route('en.booking.create') : route('vi.booking.create'),
            'seo' => $seo,
        ]);
    }
}
