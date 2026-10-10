<?php

namespace App\Providers;

use App\Services\Booking\Contracts\BookingSheetsClient;
use App\Services\Booking\GoogleBookingSheetsClient;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BookingSheetsClient::class, GoogleBookingSheetsClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('booking-submissions', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip() ?: 'public-booking');
        });
    }
}
