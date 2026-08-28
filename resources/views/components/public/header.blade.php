@php
    $locale = app()->getLocale();
    $isVi = $locale === 'vi';
    
    // Future canonical paths per Section 22
    $navLinks = [
        ['label' => __('navigation.home'), 'url' => $isVi ? url('/') : url('/en'), 'active' => request()->is('/') || request()->is('en')],
        ['label' => __('navigation.services'), 'url' => $isVi ? url('/dich-vu') : url('/en/services'), 'active' => request()->is('dich-vu*') || request()->is('en/services*')],
        ['label' => __('navigation.training'), 'url' => $isVi ? url('/dao-tao-hoc-vien') : url('/en/training'), 'active' => request()->is('dao-tao-hoc-vien*') || request()->is('en/training*')],
        ['label' => __('navigation.blog'), 'url' => $isVi ? url('/blog') : url('/en/blog'), 'active' => request()->is('blog*') || request()->is('en/blog*')],
        ['label' => __('navigation.about'), 'url' => $isVi ? url('/gioi-thieu') : url('/en/about'), 'active' => request()->is('gioi-thieu*') || request()->is('en/about*')],
        ['label' => __('navigation.contact'), 'url' => $isVi ? url('/lien-he') : url('/en/contact'), 'active' => request()->is('lien-he*') || request()->is('en/contact*')],
    ];

    $ctaUrl = $isVi ? url('/lien-he') : url('/en/contact');
@endphp

<header class="sticky top-0 z-40 w-full bg-brand-surface/90 backdrop-blur-md border-b border-brand-border transition duration-200" x-data="{ mobileOpen: false }" @keydown.escape.window="mobileOpen = false">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        <!-- Brand Lockup -->
        <a href="{{ $isVi ? url('/') : url('/en') }}" class="group flex flex-col focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded-lg p-1">
            <span class="font-serif font-bold text-lg sm:text-2xl tracking-tight text-brand-primary group-hover:text-brand-primary-hover transition leading-tight">
                Việt Hàn Âu Hàn Spa
            </span>
            <span class="hidden sm:block text-[10px] uppercase tracking-widest text-brand-text-muted font-medium mt-0.5">
                {{ __('common.tagline') }}
            </span>
        </a>

        <!-- Desktop Navigation -->
        <nav aria-label="{{ __('navigation.main_navigation') }}" class="hidden lg:flex items-center space-x-6 xl:space-x-8">
            @foreach($navLinks as $link)
                <a href="{{ $link['url'] }}" class="text-sm font-medium tracking-wide transition {{ $link['active'] ? 'text-brand-primary font-semibold border-b-2 border-brand-primary pb-1' : 'text-brand-text-secondary hover:text-brand-primary' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <!-- Right Utility Area (Desktop) -->
        <div class="hidden lg:flex items-center space-x-5">
            <!-- Language Switcher -->
            <x-public.language-switcher />

            <!-- Global CTA Button -->
            <x-public.button as="a" href="{{ $ctaUrl }}" variant="primary" size="sm">
                {{ __('navigation.book_now') }}
            </x-public.button>
        </div>

        <!-- Mobile Menu Trigger -->
        <div class="flex items-center space-x-3 lg:hidden">
            <x-public.language-switcher />

            <button type="button" @click="mobileOpen = !mobileOpen" :aria-expanded="mobileOpen.toString()" aria-label="{{ __('navigation.toggle_menu') }}" class="p-2.5 rounded-lg border border-brand-border text-brand-primary hover:bg-brand-warm focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary transition">
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

    <!-- Mobile Navigation Drawer -->
    <div x-show="mobileOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="lg:hidden bg-brand-surface border-b border-brand-border shadow-lg">
        <nav aria-label="{{ __('navigation.main_navigation') }}" class="max-w-7xl mx-auto px-4 sm:px-6 py-6 space-y-4">
            <ul class="space-y-2">
                @foreach($navLinks as $link)
                    <li>
                        <a href="{{ $link['url'] }}" @click="mobileOpen = false" class="block px-3 py-2.5 rounded-lg text-base font-medium transition {{ $link['active'] ? 'bg-brand-warm text-brand-primary font-semibold' : 'text-brand-text hover:bg-brand-warm hover:text-brand-primary' }}">
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="pt-4 border-t border-brand-border">
                <x-public.button as="a" href="{{ $ctaUrl }}" variant="primary" size="md" class="w-full justify-center">
                    {{ __('navigation.book_now') }}
                </x-public.button>
            </div>
        </nav>
    </div>
</header>
