@props([
    'title' => null,
    'headerMode' => 'overlay',
    'contactHref' => null,
    'seo' => null,
])

@php
    $bookingHref = $contactHref ?? (app()->getLocale() === 'en' ? route('en.booking.create') : route('vi.booking.create'));
    $seoMetadata = $seo ?? ($__data['seo'] ?? null);
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo.head :seo="$seoMetadata" :title="$title" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-tracking.head />
</head>
<body class="min-h-full flex flex-col bg-brand-ivory text-brand-text font-sans antialiased selection:bg-brand-gold-light selection:text-brand-primary">
    <x-tracking.body />
    <!-- Accessible Skip Link (Targeting single primary #main-content) -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-5 focus:py-3 focus:bg-brand-primary focus:text-white focus:font-semibold focus:rounded-lg focus:shadow-xl focus:ring-2 focus:ring-brand-gold focus:outline-none transition">
        {{ __('navigation.skip_to_content') }}
    </a>

    <!-- Global Public Header -->
    <x-public.header :mode="$headerMode" />

    <!-- Primary Main Landmark -->
    <main id="main-content" class="flex-1 focus:outline-none" tabindex="-1">
        {{ $slot }}
    </main>

    <div class="public-floating-booking">
        <a href="{{ $bookingHref }}" class="public-floating-booking__link" aria-label="{{ __('navigation.book_now') }}">
            <svg class="public-floating-booking__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"/>
            </svg>
            <span class="public-floating-booking__text">{{ __('navigation.book_now') }}</span>
        </a>
    </div>

    <!-- Global Public Footer -->
    <x-public.footer />
</body>
</html>
