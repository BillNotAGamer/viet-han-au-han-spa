@php
    $isVi = app()->getLocale() === 'vi';
    $bookingUrl = $isVi ? route('vi.booking.create') : route('en.booking.create');
    $servicesUrl = $isVi ? route('vi.services.index') : route('en.services.index');
    $trainingUrl = $isVi ? route('vi.training.index') : route('en.training.index');
    $blogUrl = $isVi ? route('vi.blog.index') : route('en.blog.index');
    $aboutUrl = $isVi ? route('vi.about') : route('en.about');

    $defaultHeroUrl = Vite::asset('resources/images/homepage/viet-han-banner-hero.webp');
    $heroImageUrl = !empty($heroMedia['url']) ? $heroMedia['url'] : $defaultHeroUrl;
    $heroImageAlt = !empty($heroMedia['alt']) ? $heroMedia['alt'] : '';
    $introImage = Vite::asset('resources/images/pages/about/about-herbal-head-spa.webp');
    $philosophyImage = Vite::asset('resources/images/pages/about/about-facial-therapy-care.webp');
    $sanctuaryImage = Vite::asset('resources/images/pages/about/about-sound-bath-sanctuary.webp');
    $trainingImage = Vite::asset('resources/images/pages/training/training-practical-mastery.webp');
    $serviceFallbackImage = Vite::asset('resources/images/pages/services/services-signature-therapy.webp');
    $serviceEmptyImage = Vite::asset('resources/images/pages/services/services-signature-therapy.webp');
    $ctaImage = Vite::asset('resources/images/pages/homepage/homepage-atmosphere-foot-ritual.webp');
@endphp

<x-layouts.public variant="v2" header-mode="overlay" :contact-href="$bookingUrl" :seo="$seo ?? null">
    {{-- =========================================================================
       HERO SECTION — Editorial Sanctuary Entrance
       ========================================================================= --}}
    <section class="v2-home-hero relative" aria-labelledby="home-hero-title">
        <img src="{{ $heroImageUrl }}" alt="{{ $heroImageAlt }}" class="v2-home-hero__image" loading="eager" fetchpriority="high">
        <div class="v2-home-hero__veil" aria-hidden="true"></div>
        
        <div class="v2-container v2-home-hero__inner flex flex-col justify-between">
            <div class="my-auto pt-24 sm:pt-28 pb-12">
                <div class="v2-home-hero__content" data-reveal>
                    <x-public.v2.eyebrow inverse>{{ __('home.hero.eyebrow') }}</x-public.v2.eyebrow>
                    <x-public.v2.display-heading level="1" size="xl" id="home-hero-title" class="font-serif">
                        {{ !empty($page['title']) ? $page['title'] : __('home.hero.title') }}
                    </x-public.v2.display-heading>
                    <p class="v2-home-hero__copy">
                        {{ !empty($page['excerpt']) ? $page['excerpt'] : __('home.hero.subtitle') }}
                    </p>
                    <div class="v2-home-hero__actions">
                        <a href="{{ $bookingUrl }}" x-data="{}" data-booking-modal-trigger @click.prevent="$dispatch('open-booking-modal', { trigger: $el })" class="v2-button v2-button--primary">
                            {{ __('navigation.book_now') }}
                        </a>
                        <a href="{{ $servicesUrl }}" class="v2-home-hero__link">
                            {{ __('home.hero.primary_cta') }} <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Architectural Baseline Strip --}}
            <div class="v2-hero-strip border-t border-[#E2D9CC]/20 py-4 sm:py-5 text-[#EFE8DC]/90" aria-label="{{ $isVi ? 'Thông tin chuẩn mực spa' : 'Spa standards and location' }}">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-6 text-xs tracking-wider uppercase font-medium">
                    <div class="flex items-center gap-2.5">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#C9A24D]"></span>
                        <span class="text-[#EFE8DC]/80 normal-case tracking-normal font-serif text-sm">{{ $isVi ? 'Liệu trình Đông Y Dưỡng Sinh Hoàng Cung' : 'Royal Holistic Oriental Therapy' }}</span>
                    </div>
                    <div class="flex items-center gap-2.5 sm:justify-center border-t sm:border-t-0 sm:border-x border-[#E2D9CC]/15 pt-2 sm:pt-0">
                        <span class="text-[#C9A24D] text-xs font-serif italic">115</span>
                        <span class="text-[#EFE8DC]/80 normal-case tracking-normal">{{ $isVi ? 'Nguyễn Bỉnh Khiêm, Đa Kao, Quận 1' : 'Nguyen Binh Khiem, Da Kao, Dist 1' }}</span>
                    </div>
                    <div class="flex items-center gap-2.5 sm:justify-end border-t sm:border-t-0 border-[#E2D9CC]/15 pt-2 sm:pt-0">
                        <span class="text-[#C9A24D] text-xs font-serif italic">09:00 — 21:00</span>
                        <span class="text-[#EFE8DC]/80 normal-case tracking-normal">{{ $isVi ? 'Không gian tĩnh tại đón tiếp' : 'Daily Sanctuary Hours' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================================================================
       EDITORIAL STORY SECTION — Asymmetric Magazine Split
       ========================================================================= --}}
    <section class="v2-home-intro v2-surface--paper" aria-labelledby="home-intro-title">
        <div class="v2-container v2-home-intro__editorial">
            <x-public.v2.media-frame :src="$introImage" alt="" role="portrait" position="portrait" class="v2-home-intro__media border border-[#E2D9CC]/80" data-reveal />
            <div class="v2-home-intro__statement" data-reveal>
                <x-public.v2.eyebrow>{{ __('home.editorial.eyebrow') }}</x-public.v2.eyebrow>
                <x-public.v2.display-heading id="home-intro-title" size="heading-lg" class="font-serif">
                    {{ __('home.editorial.title') }}
                </x-public.v2.display-heading>
                <div class="w-12 h-px bg-[#C9A24D] my-6"></div>
                <p class="v2-type-body-lg text-[#594B42] leading-relaxed">{{ __('home.editorial.content') }}</p>
                <div class="mt-8">
                    <x-public.v2.button :href="$aboutUrl" variant="text">
                        {{ __('navigation.about') }} <span aria-hidden="true">&rarr;</span>
                    </x-public.v2.button>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================================================================
       FEATURED SERVICES — Editorial Showcase Cards
       ========================================================================= --}}
    <section class="v2-section v2-home-services v2-surface--ivory" aria-labelledby="home-services-title">
        <div class="v2-container">
            <header class="v2-section-heading" data-reveal>
                <div>
                    <x-public.v2.eyebrow>{{ __('home.services.eyebrow') }}</x-public.v2.eyebrow>
                    <x-public.v2.display-heading id="home-services-title" size="heading-lg" class="font-serif">
                        {{ __('home.services.title') }}
                    </x-public.v2.display-heading>
                </div>
                <p class="v2-type-body-lg text-[#594B42]">{{ __('home.services.subtitle') }}</p>
            </header>

            @if(!empty($featuredServices))
                <div class="v2-home-services__collection" data-reveal-group>
                    @foreach(array_slice($featuredServices, 0, 3) as $service)
                        <article class="v2-home-service-card group relative border border-[#E2D9CC] bg-[#FBF9F4] hover:border-[#C9A24D]/60 transition-all duration-500 rounded-none shadow-none" data-reveal>
                            <div class="relative overflow-hidden">
                                <x-public.v2.media-frame
                                    :src="!empty($service['media']['url']) ? $service['media']['url'] : $serviceFallbackImage"
                                    :alt="$service['media']['alt'] ?? ''"
                                    role="landscape"
                                    class="v2-home-service-card__media aspect-[16/11] w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                />
                                <div class="absolute top-4 left-4 z-10 px-2.5 py-1 bg-[#181311]/70 backdrop-blur-sm border border-[#C9A24D]/30 text-[#C9A24D] font-serif text-sm italic">
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </div>
                            </div>
                            <div class="v2-home-service-card__body">
                                <p class="v2-home-service-card__number sr-only">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                @if(!empty($service['category_name']))
                                    <p class="v2-type-caption uppercase tracking-[0.2em] text-[#C9A24D] text-xs font-semibold mb-2">{{ $service['category_name'] }}</p>
                                @endif
                                <h3 class="v2-type-heading-md font-serif text-xl sm:text-2xl text-[#181311] leading-snug mb-3 group-hover:text-[#6A141A] transition-colors">
                                    {{ $service['name'] }}
                                </h3>
                                @if(!empty($service['excerpt']))
                                    <p class="v2-type-body text-[#594B42] text-sm leading-relaxed line-clamp-3 mb-6">{{ $service['excerpt'] }}</p>
                                @endif
                                <div class="mt-auto pt-4 border-t border-[#E2D9CC]/60 flex items-center justify-between text-xs tracking-wider uppercase font-semibold text-[#6A141A]">
                                    <span>{{ $isVi ? 'Nghi thức thượng hạng' : 'Signature Ritual' }}</span>
                                    <span class="text-[#C9A24D] group-hover:translate-x-1 transition-transform" aria-hidden="true">&rarr;</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="v2-home-services__empty border border-[#E2D9CC]" data-reveal>
                    <x-public.v2.media-frame :src="$serviceEmptyImage" alt="" role="landscape" class="v2-home-services__empty-media" />
                    <p class="v2-empty-state">{{ __('home.services.empty_notice') }}</p>
                </div>
            @endif

            <div class="v2-section-action">
                <x-public.v2.button :href="$servicesUrl" variant="text">
                    {{ __('home.services.view_all') }} <span aria-hidden="true">&rarr;</span>
                </x-public.v2.button>
            </div>
        </div>
    </section>

    {{-- =========================================================================
       PHILOSOPHY SECTION — Healing Fusion Principles
       ========================================================================= --}}
    <section class="v2-section v2-philosophy v2-surface--paper" aria-labelledby="home-philosophy-title">
        <div class="v2-container v2-editorial-split">
            <x-public.v2.media-frame :src="$philosophyImage" alt="" role="portrait" position="portrait" class="v2-editorial-split__media border border-[#E2D9CC]/80" data-reveal />
            <div class="v2-editorial-split__copy" data-reveal>
                <x-public.v2.eyebrow>{{ __('home.editorial.eyebrow') }}</x-public.v2.eyebrow>
                <x-public.v2.display-heading id="home-philosophy-title" size="heading-lg" class="font-serif">
                    {{ __('home.editorial.title') }}
                </x-public.v2.display-heading>
                <div class="w-12 h-px bg-[#C9A24D] my-6"></div>
                <p class="v2-type-body-lg text-[#594B42] leading-relaxed">{{ __('home.editorial.content') }}</p>
                <div class="v2-philosophy__principles" aria-label="{{ $isVi ? 'Nguyên tắc chăm sóc' : 'Care principles' }}">
                    <p><span>01</span>{{ __('home.atmosphere.space_1_title') }}</p>
                    <p><span>02</span>{{ __('home.atmosphere.space_2_title') }}</p>
                    <p><span>03</span>{{ __('home.atmosphere.space_3_title') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================================================================
       SANCTUARY ATMOSPHERE — Immersive Full-Bleed Sanctuary
       ========================================================================= --}}
    <section class="v2-sanctuary" aria-labelledby="home-sanctuary-title">
        <img src="{{ $sanctuaryImage }}" alt="" class="v2-sanctuary__image" loading="lazy">
        <div class="v2-sanctuary__veil" aria-hidden="true"></div>
        <div class="v2-container v2-sanctuary__content" data-reveal>
            <x-public.v2.eyebrow inverse>{{ __('home.atmosphere.eyebrow') }}</x-public.v2.eyebrow>
            <x-public.v2.display-heading id="home-sanctuary-title" size="display-lg" class="font-serif">
                {{ __('home.atmosphere.title') }}
            </x-public.v2.display-heading>
            <p>{{ __('home.atmosphere.subtitle') }}</p>
        </div>
    </section>

    {{-- =========================================================================
       TRAINING ACADEMY — Professional Academy Showcase
       ========================================================================= --}}
    <section class="v2-section v2-home-training v2-surface--ivory" aria-labelledby="home-training-title">
        <div class="v2-container v2-editorial-split v2-editorial-split--reverse">
            <div class="v2-editorial-split__copy" data-reveal>
                <x-public.v2.eyebrow>{{ __('home.training.eyebrow') }}</x-public.v2.eyebrow>
                <x-public.v2.display-heading id="home-training-title" size="heading-lg" class="font-serif">
                    {{ __('home.training.title') }}
                </x-public.v2.display-heading>
                <div class="w-12 h-px bg-[#C9A24D] my-6"></div>
                <p class="v2-type-body-lg text-[#594B42]">{{ __('home.training.subtitle') }}</p>

                @if(!empty($featuredCourses))
                    <div class="v2-ruled-list my-8 border-y border-[#E2D9CC]">
                        @foreach($featuredCourses as $course)
                            <article class="py-5 border-b border-[#E2D9CC] last:border-b-0">
                                <h3 class="font-serif text-xl text-[#181311] font-medium mb-1.5">{{ $course['title'] }}</h3>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-[#594B42]">
                                    @if(!empty($course['duration_display']))
                                        <p class="m-0 font-medium"><span class="text-[#C9A24D] font-serif italic mr-1">/</span>{{ __('home.training.duration_prefix') }} {{ $course['duration_display'] }}</p>
                                    @endif
                                    @if($course['tuition_fee'] !== null)
                                        <p class="m-0 font-medium text-[#6A141A]"><span class="text-[#C9A24D] font-serif italic mr-1">/</span>{{ __('home.training.tuition_prefix') }} {{ number_format((int) $course['tuition_fee'], 0, ',', '.') }} ₫</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="v2-empty-state my-8">{{ __('home.training.empty_notice') }}</p>
                @endif

                <x-public.v2.button :href="$trainingUrl" variant="text">
                    {{ __('navigation.training') }} <span aria-hidden="true">&rarr;</span>
                </x-public.v2.button>
            </div>
            <x-public.v2.media-frame :src="$trainingImage" alt="" role="portrait" position="portrait" class="v2-editorial-split__media border border-[#E2D9CC]/80" data-reveal />
        </div>
    </section>

    {{-- =========================================================================
       WELLNESS JOURNAL — Kinfolk-Inspired Thought Leadership
       ========================================================================= --}}
    <section class="v2-section v2-home-journal v2-surface--paper" aria-labelledby="home-journal-title">
        <div class="v2-container">
            <header class="v2-section-heading" data-reveal>
                <div>
                    <x-public.v2.eyebrow>{{ __('home.journal.eyebrow') }}</x-public.v2.eyebrow>
                    <x-public.v2.display-heading id="home-journal-title" size="heading-lg" class="font-serif">
                        {{ __('home.journal.title') }}
                    </x-public.v2.display-heading>
                </div>
                <p class="v2-type-body-lg text-[#594B42]">{{ __('home.journal.subtitle') }}</p>
            </header>

            @if(!empty($latestPosts))
                <div class="v2-journal-list border-t border-[#E2D9CC]" data-reveal-group>
                    @foreach($latestPosts as $post)
                        <article class="v2-journal-entry py-8 border-b border-[#E2D9CC] group" data-reveal>
                            <div class="v2-journal-entry__meta font-mono text-xs uppercase tracking-widest text-[#C9A24D]">
                                <span>{{ $post['published_date'] }}</span>
                                @if(!empty($post['category_name']))
                                    <span class="text-[#E2D9CC]">&middot;</span>
                                    <span>{{ $post['category_name'] }}</span>
                                @endif
                            </div>
                            <h3 class="v2-type-heading-md font-serif text-2xl text-[#181311] group-hover:text-[#6A141A] transition-colors">
                                {{ $post['title'] }}
                            </h3>
                            @if(!empty($post['excerpt']))
                                <p class="v2-type-body text-[#594B42] text-sm leading-relaxed">{{ $post['excerpt'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <p class="v2-empty-state">{{ __('home.journal.empty_notice') }}</p>
            @endif

            <div class="v2-section-action">
                <x-public.v2.button :href="$blogUrl" variant="text">
                    {{ __('navigation.blog') }} <span aria-hidden="true">&rarr;</span>
                </x-public.v2.button>
            </div>
        </div>
    </section>

    {{-- =========================================================================
       CONCIERGE CTA — Quiet Luxury Booking Invitation
       ========================================================================= --}}
    <section class="v2-home-cta v2-surface--brand" aria-labelledby="home-cta-title">
        <div class="v2-home-cta__media" data-reveal-image aria-hidden="true">
            <img src="{{ $ctaImage }}" alt="" class="v2-home-cta__image" loading="lazy" decoding="async">
        </div>
        <div class="v2-home-cta__veil" aria-hidden="true"></div>
        <div class="v2-container v2-home-cta__inner" data-reveal>
            <x-public.v2.eyebrow inverse>{{ __('home.cta.eyebrow') }}</x-public.v2.eyebrow>
            <x-public.v2.display-heading id="home-cta-title" size="heading-lg" class="font-serif">
                {{ __('home.cta.title') }}
            </x-public.v2.display-heading>
            <p>{{ __('home.cta.subtitle') }}</p>
            <a href="{{ $bookingUrl }}" x-data="{}" data-booking-modal-trigger @click.prevent="$dispatch('open-booking-modal', { trigger: $el })" class="v2-button v2-button--inverse">
                {{ !empty($featuredServices) ? __('home.cta.button') : __('home.cta.button_empty') }}
            </a>
        </div>
    </section>
</x-layouts.public>
