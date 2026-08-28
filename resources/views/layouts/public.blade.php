<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title . ' — ' . __('common.brand_name') : __('common.brand_name') . ' — ' . __('common.tagline') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col bg-brand-ivory text-brand-text font-sans antialiased selection:bg-brand-gold-light selection:text-brand-primary">
    <!-- Accessible Skip Link (Targeting single primary #main-content) -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-5 focus:py-3 focus:bg-brand-primary focus:text-white focus:font-semibold focus:rounded-lg focus:shadow-xl focus:ring-2 focus:ring-brand-gold focus:outline-none transition">
        {{ __('navigation.skip_to_content') }}
    </a>

    <!-- Global Public Header (Overlay over hero) -->
    <x-public.header />

    <!-- Primary Main Landmark -->
    <main id="main-content" class="flex-1 focus:outline-none" tabindex="-1">
        {{ $slot }}
    </main>

    <!-- Global Floating CTA Affordance (Desktop floating pill, mobile quick action) -->
    @inject('siteSettings', 'App\Services\Settings\SiteSettings')
    @php
        $floatPhone = $siteSettings->getPublic('contact.phone', '090 123 4567');
        $cleanPhone = preg_replace('/[^0-9+]/', '', $floatPhone);
    @endphp
    <div class="fixed right-6 bottom-8 z-40 hidden sm:flex flex-col items-center space-y-3">
        @if($floatPhone)
            <a href="tel:{{ $cleanPhone }}" class="w-12 h-12 rounded-full bg-[#181312]/90 border border-[#C5A880]/40 text-[#C5A880] flex items-center justify-center shadow-xl hover:bg-[#C5A880] hover:text-[#181312] hover:scale-110 transition duration-300 backdrop-blur-md" title="{{ $floatPhone }}" aria-label="{{ __('home.floating.call_now') }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </a>
        @endif

        <a href="#contact-preview" class="group flex items-center space-x-2 px-4 py-2.5 rounded-full bg-gradient-to-r from-[#C5A880] to-[#B3956B] text-[#181312] font-semibold text-xs tracking-wider uppercase shadow-xl hover:shadow-[#C5A880]/30 hover:scale-105 transition duration-300">
            <svg class="w-4 h-4 text-[#181312]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span>{{ __('home.floating.inquire') }}</span>
        </a>
    </div>

    <!-- Global Public Footer -->
    <x-public.footer />
</body>
</html>
