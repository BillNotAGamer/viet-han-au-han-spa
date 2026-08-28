@php
    $locale = app()->getLocale();
    $isVi = $locale === 'vi';
    
    // Zen-style internal fragment anchors across homepage sections
    $navLinks = [
        ['label' => __('navigation.home'), 'url' => '#home', 'active' => true],
        ['label' => __('navigation.about'), 'url' => '#about-preview', 'active' => false],
        ['label' => __('navigation.services'), 'url' => '#services-preview', 'active' => false],
        ['label' => __('navigation.training'), 'url' => '#training-preview', 'active' => false],
        ['label' => __('navigation.blog'), 'url' => '#blog-preview', 'active' => false],
        ['label' => __('navigation.contact'), 'url' => '#contact-preview', 'active' => false],
    ];

    $ctaUrl = '#contact-preview';
@endphp

<header class="sticky top-0 z-50 w-full bg-[#181312]/85 backdrop-blur-md border-b border-[#C5A880]/15 transition-all duration-300" x-data="{ mobileOpen: false }" @keydown.escape.window="mobileOpen = false">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        <!-- Brand Lockup (Refined Zen luxury aesthetic) -->
        <a href="{{ $isVi ? url('/') : url('/en') }}" class="group flex flex-col focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C5A880] rounded-lg p-1">
            <span class="font-serif font-bold text-lg sm:text-2xl tracking-tight text-white group-hover:text-[#C5A880] transition leading-tight">
                Việt Hàn Âu Hàn Spa
            </span>
            <span class="hidden sm:block text-[10px] uppercase tracking-widest text-[#C5A880]/80 font-medium mt-0.5">
                {{ __('common.tagline') }}
            </span>
        </a>

        <!-- Desktop Navigation (Fragment links with subtle gold indicator) -->
        <nav aria-label="{{ __('navigation.main_navigation') }}" class="hidden lg:flex items-center space-x-6 xl:space-x-8">
            @foreach($navLinks as $link)
                <a href="{{ $link['url'] }}" class="text-sm font-medium tracking-wide transition text-white/90 hover:text-[#C5A880] focus:outline-none focus-visible:ring-1 focus-visible:ring-[#C5A880] rounded-sm py-1">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <!-- Right Utility Area (Desktop) -->
        <div class="hidden lg:flex items-center space-x-5">
            <!-- Language Switcher -->
            <x-public.language-switcher />

            <!-- Global CTA Button (Luxury Gold Booking Pill) -->
            <x-public.button as="a" href="{{ $ctaUrl }}" variant="primary" size="sm" class="bg-gradient-to-r from-[#C5A880] to-[#B3956B] text-[#181312] hover:brightness-110 font-bold shadow-md rounded-full px-5 py-2 border-0">
                {{ __('navigation.book_now') }}
            </x-public.button>
        </div>

        <!-- Mobile Menu Trigger -->
        <div class="flex items-center space-x-3 lg:hidden">
            <x-public.language-switcher />

            <button type="button" @click="mobileOpen = !mobileOpen" :aria-expanded="mobileOpen.toString()" aria-label="{{ __('navigation.toggle_menu') }}" class="p-2.5 rounded-lg border border-[#C5A880]/30 text-[#C5A880] hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C5A880] transition">
                <!-- Hamburger Icon -->
                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <!-- Close Icon -->
                <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Navigation Drawer (Dark luxury overlay) -->
    <div x-show="mobileOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="lg:hidden bg-[#181312]/95 backdrop-blur-xl border-b border-[#C5A880]/20 shadow-2xl">
        <nav aria-label="{{ __('navigation.main_navigation') }}" class="max-w-7xl mx-auto px-4 sm:px-6 py-6 space-y-4">
            <ul class="space-y-2">
                @foreach($navLinks as $link)
                    <li>
                        <a href="{{ $link['url'] }}" @click="mobileOpen = false" class="block px-3 py-2.5 rounded-lg text-base font-medium transition text-white/90 hover:text-[#C5A880] hover:bg-white/5">
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="pt-4 border-t border-[#C5A880]/15">
                <x-public.button as="a" href="{{ $ctaUrl }}" @click="mobileOpen = false" variant="primary" size="md" class="w-full justify-center bg-gradient-to-r from-[#C5A880] to-[#B3956B] text-[#181312] font-bold rounded-full py-3 border-0 shadow-md">
                    {{ __('navigation.book_now') }}
                </x-public.button>
            </div>
        </nav>
    </div>
</header>
