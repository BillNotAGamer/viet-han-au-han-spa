<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Tracking\MarketingAttribution;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureMarketingAttribution
{
    public function __construct(
        protected MarketingAttribution $attribution
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('admin') && ! $request->is('admin/*') && ! $request->is('livewire/*')) {
            $this->attribution->captureFromRequest($request);
        }

        return $next($request);
    }
}
