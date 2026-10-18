@props([
    'title' => null,
    'headerMode' => 'overlay',
    'contactHref' => null,
    'seo' => null,
    'variant' => 'legacy',
])

@php
    $bookingHref = $contactHref ?? (app()->getLocale() === 'en' ? route('en.booking.create') : route('vi.booking.create'));
    $seoMetadata = $seo ?? ($__data['seo'] ?? null);
    $isV2 = $variant === 'v2';
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <x-seo.head :seo="$seoMetadata" :title="$title" />
    {{ Vite::fonts() }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if($isV2)
        <style>
            @view-transition { navigation: auto; }
        </style>
    @endif
    <x-tracking.head />
</head>
<body @class([
    'min-h-full flex flex-col bg-[#FBF9F4] text-[#231F20] font-sans antialiased selection:bg-[#E8D8A6] selection:text-[#6A141A]',
    'v2-public-shell' => $isV2,
])>
    <x-tracking.body />
    <!-- Accessible Skip Link (Targeting single primary #main-content) -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-5 focus:py-3 focus:bg-[#6A141A] focus:text-[#FBF9F4] focus:font-medium focus:tracking-wider focus:uppercase focus:text-xs focus:rounded-[2px] focus:shadow-2xl focus:ring-1 focus:ring-[#C9A24D] focus:outline-none transition">
        {{ __('navigation.skip_to_content') }}
    </a>

    <!-- Global Public Header -->
    <x-public.header :mode="$headerMode" :variant="$variant" />

    <!-- Primary Main Landmark -->
    <main id="main-content" class="flex-1 focus:outline-none" tabindex="-1">
        {{ $slot }}
    </main>

    <div @class(['public-floating-booking', 'v2-floating-booking' => $isV2])>
        <a
            href="{{ $bookingHref }}"
            x-data="{}"
            data-booking-modal-trigger
            @click.prevent="$dispatch('open-booking-modal', { trigger: $el })"
            class="public-floating-booking__link"
            aria-label="{{ __('navigation.book_now') }}"
        >
            <svg class="public-floating-booking__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"/>
            </svg>
            <span class="public-floating-booking__text">{{ __('navigation.book_now') }}</span>
        </a>
    </div>

    <!-- Floating Contact Dock -->
    <x-public.contact-dock />

    <!-- Global Booking Modal -->
    <x-public.booking-modal />

    <!-- Global Public Footer -->
    <x-public.footer :variant="$variant" />
</body>
</html>
