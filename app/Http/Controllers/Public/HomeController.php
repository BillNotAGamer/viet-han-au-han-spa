<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PublicSite\HomepageContent;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected HomepageContent $homepageContent
    ) {}

    public function index(): View
    {
        $locale = app()->getLocale();
        $data = $this->homepageContent->getForLocale($locale);

        return view('public.home', $data);
    }
}
