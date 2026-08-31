@props([
    'mode' => 'overlay',
])

@php
    $locale = app()->getLocale();
    $isVi = $locale === 'vi';
    $homeUrl = $isVi ? route('vi.home') : route('en.home');
    $isSolid = $mode === 'solid';
    $routeName = \Illuminate\Support\Facades\Route::currentRouteName();
    $isActive = fn (string $section) => match ($section) {
        'home' => in_array($routeName, ['vi.home', 'en.home'], true),
        'about' => in_array($routeName, ['vi.about', 'en.about'], true),
        'services' => str_contains((string) $routeName, '.services.'),
        'training' => str_contains((string) $routeName, '.training.'),
        'blog' => str_contains((string) $routeName, '.blog.'),
        'contact' => in_array($routeName, ['vi.contact', 'en.contact'], true),
        default => false,
    };
    $brandName = __('common.brand_name');
    $logoUrl = Vite::asset('resources/images/general/viet-han-logo.png');
    $aboutUrl = $isVi ? route('vi.about') : route('en.about');
    $servicesUrl = $isVi ? route('vi.services.index') : route('en.services.index');
    $trainingUrl = $isVi ? route('vi.training.index') : route('en.training.index');
    $blogUrl = $isVi ? route('vi.blog.index') : route('en.blog.index');
    $contactUrl = $isVi ? route('vi.contact') : route('en.contact');

    $leftNavLinks = [
        ['key' => 'home', 'label' => __('navigation.home'), 'url' => $homeUrl],
        ['key' => 'about', 'label' => __('navigation.about'), 'url' => $aboutUrl],
        ['key' => 'services', 'label' => __('navigation.services'), 'url' => $servicesUrl],
    ];

    $rightNavLinks = [
        ['key' => 'training', 'label' => __('navigation.training'), 'url' => $trainingUrl],
        ['key' => 'blog', 'label' => __('navigation.blog'), 'url' => $blogUrl],
        ['key' => 'contact', 'label' => __('navigation.contact'), 'url' => $contactUrl],
    ];

    $allNavLinks = array_merge($leftNavLinks, $rightNavLinks);
@endphp

<header
    class="public-header {{ $isSolid ? 'public-header--sticky' : 'public-header--overlay' }}"
    x-data="{
        mobileOpen: false,
        isSolid: @js($isSolid),
        isSticky: @js($isSolid),
        init() {
            const updateHeader = () => {
                if (this.isSolid) {
                    this.isSticky = true;
                    return;
                }

                this.isSticky = window.scrollY > 96;
            };

            updateHeader();
            window.addEventListener('scroll', updateHeader, { passive: true });
        }
    }"
    :class="isSticky ? 'public-header--sticky' : 'public-header--overlay'"
    @keydown.escape.window="mobileOpen = false"
>
    <div class="public-header__desktop">
        <nav aria-label="{{ __('navigation.main_navigation') }}" class="public-header__nav public-header__nav--left">
            @foreach($leftNavLinks as $link)
                <a href="{{ $link['url'] }}" class="{{ $isActive($link['key']) ? 'public-header__link public-header__link--active' : 'public-header__link' }}" @if($isActive($link['key'])) aria-current="page" @endif>
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="public-header__brand">
            <a href="{{ $homeUrl }}" aria-label="{{ $brandName }}" class="public-header__brand-link">
                <img src="{{ $logoUrl }}" alt="" class="public-header__logo" aria-hidden="true">
                <span class="sr-only">{{ $brandName }}</span>
            </a>
        </div>

        <div class="public-header__nav public-header__nav--right">
            @foreach($rightNavLinks as $link)
                <a href="{{ $link['url'] }}" class="{{ $isActive($link['key']) ? 'public-header__link public-header__link--active' : 'public-header__link' }}" @if($isActive($link['key'])) aria-current="page" @endif>
                    {{ $link['label'] }}
                </a>
            @endforeach

            <x-public.language-switcher />
        </div>
    </div>

    <div class="public-header__mobile">
        <a href="{{ $homeUrl }}" aria-label="{{ $brandName }}" class="public-header__mobile-brand">
            <img src="{{ $logoUrl }}" alt="" class="public-header__mobile-logo" aria-hidden="true">
            <span class="sr-only">{{ $brandName }}</span>
        </a>

        <div class="public-header__mobile-actions">
            <x-public.language-switcher />

            <button
                type="button"
                class="public-header__menu-button"
                @click="mobileOpen = !mobileOpen"
                :aria-expanded="mobileOpen.toString()"
                aria-controls="public-mobile-menu"
                aria-label="{{ __('navigation.toggle_menu') }}"
            >
                <svg x-show="!mobileOpen" class="public-header__menu-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="mobileOpen" x-cloak class="public-header__menu-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <div
        id="public-mobile-menu"
        x-show="mobileOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="public-header__drawer"
    >
        <nav aria-label="{{ __('navigation.main_navigation') }}" class="public-header__drawer-nav">
            <ul class="public-header__drawer-list">
                @foreach($allNavLinks as $link)
                    <li>
                        <a href="{{ $link['url'] }}" @click="mobileOpen = false" class="{{ $isActive($link['key']) ? 'public-header__drawer-link public-header__drawer-link--active' : 'public-header__drawer-link' }}" @if($isActive($link['key'])) aria-current="page" @endif>
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <a href="{{ $contactUrl }}" @click="mobileOpen = false" class="public-header__drawer-cta">
                {{ __('navigation.book_now') }}
            </a>
        </nav>
    </div>
</header>
