<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PublicSite\StaticPageContent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaticPageController extends Controller
{
    public function __construct(
        protected StaticPageContent $staticPageContent
    ) {}

    public function about(Request $request): View
    {
        return $this->renderFixedPage($request, 'about', 'public.about');
    }

    public function contact(Request $request): View
    {
        return $this->renderFixedPage($request, 'contact', 'public.contact');
    }

    protected function renderFixedPage(Request $request, string $key, string $view): View
    {
        $locale = app()->getLocale();
        $page = $this->staticPageContent->pageForKey($key, $locale);

        abort_if($page === null, 404);

        $request->attributes->set('localized_urls', $page['localized_urls']);

        return view($view, [
            'locale' => $locale,
            'title' => $page['title'],
            'page' => $page,
            'contact' => $key === 'contact' ? $this->staticPageContent->contactSettings() : null,
            'contactHref' => $locale === 'en' ? route('en.booking.create') : route('vi.booking.create'),
        ]);
    }
}
