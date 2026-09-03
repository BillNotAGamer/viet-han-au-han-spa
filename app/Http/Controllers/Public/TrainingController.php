<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PublicSite\TrainingContent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function __construct(
        protected TrainingContent $trainingContent
    ) {}

    public function index(Request $request): View
    {
        $locale = app()->getLocale();

        $request->attributes->set('localized_urls', [
            'vi' => route('vi.training.index'),
            'en' => route('en.training.index'),
        ]);

        return view('public.training.index', [
            'locale' => $locale,
            'title' => __('training.meta.index_title'),
            'courses' => $this->trainingContent->listingForLocale($locale),
            'contactHref' => $locale === 'en' ? route('en.booking.create') : route('vi.booking.create'),
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $locale = app()->getLocale();
        $course = $this->trainingContent->detailForSlug($locale, $slug);

        abort_if($course === null, 404);

        $request->attributes->set('localized_urls', $course['localized_urls']);

        return view('public.training.show', [
            'locale' => $locale,
            'title' => $course['title'],
            'course' => $course,
            'contactHref' => $locale === 'en' ? route('en.booking.create') : route('vi.booking.create'),
        ]);
    }
}
