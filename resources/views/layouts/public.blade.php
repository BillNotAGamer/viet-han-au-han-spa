<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title . ' — ' . __('common.brand_name') : __('common.brand_name') . ' — ' . __('common.tagline') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col bg-brand-ivory text-brand-text font-sans antialiased selection:bg-brand-gold-light selection:text-brand-primary relative">
    <!-- Accessible Skip Link (Targeting single primary #main-content) -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-5 focus:py-3 focus:bg-brand-primary focus:text-white focus:font-semibold focus:rounded-lg focus:shadow-xl focus:ring-2 focus:ring-brand-gold focus:outline-none transition">
        {{ __('navigation.skip_to_content') }}
    </a>

    <!-- Global Public Header (True Hero-Overlay, floating transparently over hero) -->
    <x-public.header />

    <!-- Primary Main Landmark -->
    <main id="main-content" class="flex-1 focus:outline-none" tabindex="-1">
        {{ $slot }}
    </main>

    <!-- Zen-Style Floating Vertical Booking Tab (Desktop: docked right edge, vertically centered, rotated text) -->
    <div class="fixed right-0 top-1/2 -translate-y-1/2 z-50 hidden lg:flex">
        <a href="#contact-preview" class="group flex flex-col items-center justify-center bg-[#5B1121] hover:bg-[#4a0d1a] border-t border-b border-l border-[#C5A880]/50 rounded-l-xl w-[58px] h-[175px] shadow-2xl transition-all duration-300 hover:pr-1" aria-label="{{ __('navigation.book_now') }}">
            <!-- Spa Booking Calendar Icon -->
            <svg class="w-5 h-5 text-[#C5A880] mb-3 group-hover:scale-110 transition duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <!-- Vertical Rotated Text: "Đặt lịch" / "Book now" -->
            <span class="[writing-mode:vertical-rl] rotate-180 font-serif text-xs font-semibold tracking-widest uppercase text-[#FAF7F2] group-hover:text-[#C5A880] transition">
                {{ __('navigation.book_now') }}
            </span>
        </a>
    </div>

    <!-- Mobile Floating Quick Action Affordance (Hotline & Quick Booking Pill) -->
    @inject('siteSettings', 'App\Services\Settings\SiteSettings')
    @php
        $floatPhone = $siteSettings->getPublic('contact.phone');
        $cleanPhone = $floatPhone ? preg_replace('/[^0-9+]/', '', $floatPhone) : null;
    @endphp
    <div class="fixed right-4 bottom-6 z-40 flex lg:hidden flex-col items-end space-y-2.5">
        @if($cleanPhone)
            <a href="tel:{{ $cleanPhone }}" class="w-11 h-11 rounded-full bg-[#181312]/90 border border-[#C5A880]/40 text-[#C5A880] flex items-center justify-center shadow-xl hover:bg-[#C5A880] hover:text-[#181312] transition backdrop-blur-md" aria-label="{{ __('home.floating.call_now') }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </a>
        @endif

        <a href="#contact-preview" class="flex items-center space-x-1.5 px-4 py-2.5 rounded-full bg-gradient-to-r from-[#C5A880] to-[#B3956B] text-[#181312] font-semibold text-xs tracking-wider uppercase shadow-xl hover:scale-105 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>{{ __('navigation.book_now') }}</span>
        </a>
    </div>

    <!-- Global Public Footer -->
    <x-public.footer />
</body>
</html>
