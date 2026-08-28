<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Localization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $forcedLocale = null): Response
    {
        $locale = $forcedLocale;

        if ($locale === null || $locale === '') {
            $locale = $request->route('locale');
        }

        if ($locale === null || $locale === '') {
            $locale = Localization::defaultLocale();
        }

        if (! Localization::isSupported((string) $locale)) {
            throw new NotFoundHttpException("Unsupported locale: [{$locale}].");
        }

        App::setLocale((string) $locale);

        return $next($request);
    }
}
