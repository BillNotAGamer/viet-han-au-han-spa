@props([
    'mode' => 'overlay',
    'variant' => 'legacy',
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
    $scrolledLogoUrl = Vite::asset('resources/images/general/viet-han-spa-no-bg-original-logo.png');
    $aboutUrl = $isVi ? route('vi.about') : route('en.about');
    $servicesUrl = $isVi ? route('vi.services.index') : route('en.services.index');
    $trainingUrl = $isVi ? route('vi.training.index') : route('en.training.index');
    $blogUrl = $isVi ? route('vi.blog.index') : route('en.blog.index');
    $contactUrl = $isVi ? route('vi.contact') : route('en.contact');
    $bookingUrl = $isVi ? route('vi.booking.create') : route('en.booking.create');
    $isV2 = $variant === 'v2';
    $serviceGroups = array_map(fn (\App\Enums\HeaderServiceGroup $group) => [
        'label' => $group->label($locale),
        'url' => route($isVi ? 'vi.services.group' : 'en.services.group', ['group' => $group->routeSlug($locale)]),
    ], \App\Enums\HeaderServiceGroup::cases());

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
    class="public-header {{ $isSolid ? 'public-header--sticky' : 'public-header--overlay' }}{{ $isV2 ? ' v2-header' : '' }}"
    x-data="{
        mobileOpen: false,
        mobileMenuActivatedByUser: false,
        desktopMediaQuery: null,
        desktopMediaQueryHandler: null,
        pageHideHandler: null,
        pageShowHandler: null,
        resizeHandler: null,
        visibilityChangeHandler: null,
        isSolid: @js($isSolid),
        isSticky: @js($isSolid),
        get effectiveDrawerOpen() {
            return this.mobileOpen && this.mobileMenuActivatedByUser;
        },
        syncMobileDrawerState() {
            document.body.classList.toggle('public-mobile-menu-open', this.effectiveDrawerOpen);
        },
        closeMobileMenu(restoreFocus = true) {
            const wasOpen = this.effectiveDrawerOpen;
            this.mobileOpen = false;
            this.mobileMenuActivatedByUser = false;
            this.syncMobileDrawerState();

            if (restoreFocus && wasOpen) {
                this.$nextTick(() => this.$refs.mobileMenuTrigger?.focus());
            }
        },
        openMobileMenu() {
            this.mobileMenuActivatedByUser = true;
            this.mobileOpen = true;
            this.syncMobileDrawerState();
        },
        init() {
            // A Header is recreated on every public navigation: never carry an
            // open drawer across refreshes, locale changes, or breakpoint changes.
            this.mobileOpen = false;
            this.mobileMenuActivatedByUser = false;
            this.$watch('mobileOpen', () => this.syncMobileDrawerState());
            this.$watch('mobileMenuActivatedByUser', () => this.syncMobileDrawerState());
            this.syncMobileDrawerState();

            this.desktopMediaQuery = window.matchMedia('(min-width: 1180px)');
            this.desktopMediaQueryHandler = (event) => {
                if (event.matches) {
                    this.closeMobileMenu(false);
                }
            };
            this.desktopMediaQuery.addEventListener('change', this.desktopMediaQueryHandler);

            // A drawer is never navigation state. Clear it before a document can be
            // frozen into BFCache and again whenever it becomes active.
            this.pageHideHandler = () => this.closeMobileMenu(false);
            window.addEventListener('pagehide', this.pageHideHandler);

            this.pageShowHandler = () => this.closeMobileMenu(false);
            window.addEventListener('pageshow', this.pageShowHandler);

            // Resizing must never reveal or preserve an armed mobile drawer.
            this.resizeHandler = () => this.closeMobileMenu(false);
            window.addEventListener('resize', this.resizeHandler, { passive: true });

            this.visibilityChangeHandler = () => {
                if (document.visibilityState === 'visible') {
                    this.closeMobileMenu(false);
                }
            };
            document.addEventListener('visibilitychange', this.visibilityChangeHandler);

            let scrollFrame = null;
            const updateHeader = () => {
                if (this.isSolid) {
                    this.isSticky = true;
                    return;
                }

                this.isSticky = window.scrollY > 96;
            };

            updateHeader();
            window.addEventListener('scroll', () => {
                if (scrollFrame !== null) {
                    return;
                }

                scrollFrame = window.requestAnimationFrame(() => {
                    updateHeader();
                    scrollFrame = null;
                });
            }, { passive: true });
        },
        destroy() {
            this.desktopMediaQuery?.removeEventListener('change', this.desktopMediaQueryHandler);
            window.removeEventListener('pagehide', this.pageHideHandler);
            window.removeEventListener('pageshow', this.pageShowHandler);
            window.removeEventListener('resize', this.resizeHandler);
            document.removeEventListener('visibilitychange', this.visibilityChangeHandler);
            this.closeMobileMenu(false);
        }
    }"
    :class="isSticky ? 'public-header--sticky' : 'public-header--overlay'"
    @keydown.escape.window="closeMobileMenu()"
>
    @if($isV2)
        <div class="v2-header__desktop">
            <nav aria-label="{{ __('navigation.main_navigation') }}" class="v2-header__nav v2-header__nav--left">
                @foreach($leftNavLinks as $link)
                    @if($link['key'] === 'services')
                        <div
                            class="v2-header__services"
                            x-data="{ open: false }"
                            @mouseenter="open = true"
                            @mouseleave="open = false"
                            @focusin="open = true"
                            @focusout="if (!$el.contains($event.relatedTarget)) open = false"
                            @keydown.escape.prevent.stop="open = false; $refs.servicesLink.focus()"
                        >
                            <a x-ref="servicesLink" href="{{ $link['url'] }}" class="{{ $isActive($link['key']) ? 'v2-header__link v2-header__link--active' : 'v2-header__link' }}" @if($isActive($link['key'])) aria-current="page" @endif>
                                {{ $link['label'] }}
                            </a>

                            <div x-cloak x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1" class="v2-header__services-menu">
                                <div class="v2-header__services-menu-panel">
                                    <ul>
                                        @foreach($serviceGroups as $group)
                                            <li><a href="{{ $group['url'] }}" class="v2-header__services-menu-link">{{ $group['label'] }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ $link['url'] }}" class="{{ $isActive($link['key']) ? 'v2-header__link v2-header__link--active' : 'v2-header__link' }}" @if($isActive($link['key'])) aria-current="page" @endif>
                            {{ $link['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>

            <a href="{{ $homeUrl }}" aria-label="{{ $brandName }}" class="v2-header__brand-link">
                <x-public.v2.logo variant="white" decorative class="v2-header__logo v2-header__logo--white" />
                <img src="{{ $scrolledLogoUrl }}" alt="" width="1254" height="1254" loading="eager" decoding="async" aria-hidden="true" class="v2-header__logo v2-header__logo--compact">
                <span class="sr-only">{{ $brandName }}</span>
            </a>

            <nav aria-label="{{ __('navigation.main_navigation') }}" class="v2-header__nav v2-header__nav--right">
                @foreach($rightNavLinks as $link)
                    <a href="{{ $link['url'] }}" class="{{ $isActive($link['key']) ? 'v2-header__link v2-header__link--active' : 'v2-header__link' }}" @if($isActive($link['key'])) aria-current="page" @endif>
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    @else
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
    @endif

    <div class="public-header__mobile">
        <a href="{{ $homeUrl }}" aria-label="{{ $brandName }}" class="public-header__mobile-brand">
            @if($isV2)
                <x-public.v2.logo variant="compact" decorative class="public-header__mobile-logo v2-header__mobile-logo" />
            @else
                <img src="{{ $logoUrl }}" alt="" class="public-header__mobile-logo" aria-hidden="true">
            @endif
            <span class="sr-only">{{ $brandName }}</span>
        </a>

        <div class="public-header__mobile-actions">
            @unless($isV2)
                <x-public.language-switcher />
            @endunless

            <button
                type="button"
                class="public-header__menu-button"
                x-ref="mobileMenuTrigger"
                @click="effectiveDrawerOpen ? closeMobileMenu() : openMobileMenu()"
                :aria-expanded="effectiveDrawerOpen.toString()"
                aria-controls="public-mobile-menu"
                aria-label="{{ __('navigation.toggle_menu') }}"
            >
                <svg x-show="!effectiveDrawerOpen" class="public-header__menu-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="effectiveDrawerOpen" x-cloak class="public-header__menu-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <div
        x-cloak
        x-show="effectiveDrawerOpen"
        @click="closeMobileMenu()"
        class="public-header__drawer-backdrop"
        aria-hidden="true"
    ></div>

    <div
        id="public-mobile-menu"
        x-show="effectiveDrawerOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-400"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-280"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="public-header__drawer"
    >
        <nav aria-label="{{ __('navigation.main_navigation') }}" class="public-header__drawer-nav">
            <ul class="public-header__drawer-list">
                @foreach($allNavLinks as $link)
                    <li>
                        <a href="{{ $link['url'] }}" @click="closeMobileMenu(false)" class="{{ $isActive($link['key']) ? 'public-header__drawer-link public-header__drawer-link--active' : 'public-header__drawer-link' }}" @if($isActive($link['key'])) aria-current="page" @endif>
                            {{ $link['label'] }}
                        </a>
                        @if($isV2 && $link['key'] === 'services')
                            <ul class="public-header__drawer-service-groups">
                                @foreach($serviceGroups as $group)
                                    <li><a href="{{ $group['url'] }}" @click="closeMobileMenu(false)" class="public-header__drawer-service-group-link">{{ $group['label'] }}</a></li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>

            <a
                href="{{ $bookingUrl }}"
                data-booking-modal-trigger
                @click.prevent="closeMobileMenu(false); $dispatch('open-booking-modal', { trigger: $el })"
                class="public-header__drawer-cta"
            >
                {{ __('navigation.book_now') }}
            </a>
        </nav>
    </div>
</header>
