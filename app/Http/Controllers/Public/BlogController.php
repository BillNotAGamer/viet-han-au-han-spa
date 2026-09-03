<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PublicSite\BlogContent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function __construct(
        protected BlogContent $blogContent
    ) {}

    public function index(Request $request): View
    {
        $locale = app()->getLocale();

        $request->attributes->set('localized_urls', [
            'vi' => route('vi.blog.index'),
            'en' => route('en.blog.index'),
        ]);

        return view('public.blog.index', [
            'locale' => $locale,
            'title' => __('blog.meta.index_title'),
            'posts' => $this->blogContent->listingForLocale($locale),
            'contactHref' => $locale === 'en' ? route('en.booking.create') : route('vi.booking.create'),
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $locale = app()->getLocale();
        $post = $this->blogContent->detailForSlug($locale, $slug);

        abort_if($post === null, 404);

        $request->attributes->set('localized_urls', $post['localized_urls']);

        return view('public.blog.show', [
            'locale' => $locale,
            'title' => $post['title'],
            'post' => $post,
            'contactHref' => $locale === 'en' ? route('en.booking.create') : route('vi.booking.create'),
        ]);
    }
}
