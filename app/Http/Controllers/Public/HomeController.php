<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PublicSite\HomepageContent;
use App\Services\Seo\SeoManager;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected HomepageContent $homepageContent,
        protected SeoManager $seoManager
    ) {}

    public function index(): View
    {
        $locale = app()->getLocale();
        $data = $this->homepageContent->getForLocale($locale);
        $data['seo'] = $this->seoManager->composeForHome(
            $locale,
            $data['page'] ?? null,
            $data['heroMedia'] ?? null
        );

        return view('public.home', $data);
    }
}
