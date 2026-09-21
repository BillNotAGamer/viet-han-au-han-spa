@php
    $isVi = app()->getLocale() === 'vi';
    $bookingUrl = $isVi ? route('vi.booking.create') : route('en.booking.create');
    $servicesUrl = $isVi ? route('vi.services.index') : route('en.services.index');
    $trainingUrl = $isVi ? route('vi.training.index') : route('en.training.index');
    $blogUrl = $isVi ? route('vi.blog.index') : route('en.blog.index');
    $aboutUrl = $isVi ? route('vi.about') : route('en.about');

    $defaultHeroUrl = Vite::asset('resources/images/homepage/viet-han-banner-hero.png');
    $heroImageUrl = !empty($heroMedia['url']) ? $heroMedia['url'] : $defaultHeroUrl;
    $heroImageAlt = !empty($heroMedia['alt']) ? $heroMedia['alt'] : '';
    $philosophyImage = Vite::asset('resources/images/pages/about/about-facial-therapy-care.webp');
    $sanctuaryImage = Vite::asset('resources/images/pages/about/about-sound-bath-sanctuary.webp');
    $trainingImage = Vite::asset('resources/images/pages/training/training-practical-mastery.webp');
    $serviceFallbackImage = Vite::asset('resources/images/pages/services/services-signature-therapy.webp');
@endphp

<x-layouts.public variant="v2" header-mode="overlay" :contact-href="$bookingUrl" :seo="$seo ?? null">
    <section class="v2-home-hero" aria-labelledby="home-hero-title">
        <img src="{{ $heroImageUrl }}" alt="{{ $heroImageAlt }}" class="v2-home-hero__image" loading="eager" fetchpriority="high">
        <div class="v2-home-hero__veil" aria-hidden="true"></div>
        <div class="v2-container v2-home-hero__inner">
            <div class="v2-home-hero__content" data-reveal>
                <x-public.v2.eyebrow inverse>{{ __('home.hero.eyebrow') }}</x-public.v2.eyebrow>
                <x-public.v2.display-heading level="1" size="xl" id="home-hero-title">
                    {{ !empty($page['title']) ? $page['title'] : __('home.hero.title') }}
                </x-public.v2.display-heading>
                <p class="v2-home-hero__copy">
                    {{ !empty($page['excerpt']) ? $page['excerpt'] : __('home.hero.subtitle') }}
                </p>
                <div class="v2-home-hero__actions">
                    <a href="{{ $bookingUrl }}" x-data="{}" data-booking-modal-trigger @click.prevent="$dispatch('open-booking-modal', { trigger: $el })" class="v2-button v2-button--primary">
                        {{ __('navigation.book_now') }}
                    </a>
                    <a href="{{ $servicesUrl }}" class="v2-home-hero__link">{{ __('home.hero.primary_cta') }} <span aria-hidden="true">&rarr;</span></a>
                </div>
            </div>
        </div>
    </section>

    <section class="v2-home-intro v2-surface--paper" aria-labelledby="home-intro-title">
        <div class="v2-container v2-home-intro__grid">
            <x-public.v2.eyebrow>{{ __('home.editorial.eyebrow') }}</x-public.v2.eyebrow>
            <div class="v2-home-intro__statement" data-reveal>
                <x-public.v2.display-heading id="home-intro-title" size="heading-lg">{{ __('home.editorial.title') }}</x-public.v2.display-heading>
                <p class="v2-type-body-lg">{{ __('home.editorial.content') }}</p>
                <x-public.v2.button :href="$aboutUrl" variant="text">{{ __('navigation.about') }} <span aria-hidden="true">&rarr;</span></x-public.v2.button>
            </div>
        </div>
    </section>

    <section class="v2-section v2-home-services v2-surface--ivory" aria-labelledby="home-services-title">
        <div class="v2-container">
            <header class="v2-section-heading" data-reveal>
                <div>
                    <x-public.v2.eyebrow>{{ __('home.services.eyebrow') }}</x-public.v2.eyebrow>
                    <x-public.v2.display-heading id="home-services-title" size="heading-lg">{{ __('home.services.title') }}</x-public.v2.display-heading>
                </div>
                <p class="v2-type-body-lg">{{ __('home.services.subtitle') }}</p>
            </header>

            @if(!empty($featuredServices))
                <div class="v2-experience-list" data-reveal-group>
                    @foreach(array_slice($featuredServices, 0, 4) as $service)
                        <article class="v2-experience" data-reveal>
                            <x-public.v2.media-frame
                                :src="!empty($service['media']['url']) ? $service['media']['url'] : $serviceFallbackImage"
                                :alt="$service['media']['alt'] ?? ''"
                                role="landscape"
                                class="v2-experience__media"
                            />
                            <div class="v2-experience__body">
                                <p class="v2-experience__number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                @if(!empty($service['category_name']))
                                    <p class="v2-type-caption">{{ $service['category_name'] }}</p>
                                @endif
                                <h3 class="v2-type-heading-md">{{ $service['name'] }}</h3>
                                @if(!empty($service['excerpt']))
                                    <p class="v2-type-body">{{ $service['excerpt'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="v2-empty-state">{{ __('home.services.empty_notice') }}</p>
            @endif

            <div class="v2-section-action">
                <x-public.v2.button :href="$servicesUrl" variant="text">{{ __('home.services.view_all') }} <span aria-hidden="true">&rarr;</span></x-public.v2.button>
            </div>
        </div>
    </section>

    <section class="v2-section v2-philosophy v2-surface--paper" aria-labelledby="home-philosophy-title">
        <div class="v2-container v2-editorial-split">
            <x-public.v2.media-frame :src="$philosophyImage" alt="" role="portrait" position="portrait" class="v2-editorial-split__media" data-reveal />
            <div class="v2-editorial-split__copy" data-reveal>
                <x-public.v2.eyebrow>{{ __('home.editorial.eyebrow') }}</x-public.v2.eyebrow>
                <x-public.v2.display-heading id="home-philosophy-title" size="heading-lg">{{ __('home.editorial.title') }}</x-public.v2.display-heading>
                <p class="v2-type-body-lg">{{ __('home.editorial.content') }}</p>
                <div class="v2-philosophy__principles" aria-label="{{ $isVi ? 'Nguyên tắc chăm sóc' : 'Care principles' }}">
                    <p><span>01</span>{{ __('home.atmosphere.space_1_title') }}</p>
                    <p><span>02</span>{{ __('home.atmosphere.space_2_title') }}</p>
                    <p><span>03</span>{{ __('home.atmosphere.space_3_title') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="v2-sanctuary" aria-labelledby="home-sanctuary-title">
        <img src="{{ $sanctuaryImage }}" alt="" class="v2-sanctuary__image" loading="lazy">
        <div class="v2-sanctuary__veil" aria-hidden="true"></div>
        <div class="v2-container v2-sanctuary__content" data-reveal>
            <x-public.v2.eyebrow inverse>{{ __('home.atmosphere.eyebrow') }}</x-public.v2.eyebrow>
            <x-public.v2.display-heading id="home-sanctuary-title" size="display-lg">{{ __('home.atmosphere.title') }}</x-public.v2.display-heading>
            <p>{{ __('home.atmosphere.subtitle') }}</p>
        </div>
    </section>

    <section class="v2-section v2-home-training v2-surface--ivory" aria-labelledby="home-training-title">
        <div class="v2-container v2-editorial-split v2-editorial-split--reverse">
            <div class="v2-editorial-split__copy" data-reveal>
                <x-public.v2.eyebrow>{{ __('home.training.eyebrow') }}</x-public.v2.eyebrow>
                <x-public.v2.display-heading id="home-training-title" size="heading-lg">{{ __('home.training.title') }}</x-public.v2.display-heading>
                <p class="v2-type-body-lg">{{ __('home.training.subtitle') }}</p>

                @if(!empty($featuredCourses))
                    <div class="v2-ruled-list">
                        @foreach($featuredCourses as $course)
                            <article>
                                <h3>{{ $course['title'] }}</h3>
                                @if(!empty($course['duration_display']))
                                    <p>{{ __('home.training.duration_prefix') }} {{ $course['duration_display'] }}</p>
                                @endif
                                @if($course['tuition_fee'] !== null)
                                    <p>{{ __('home.training.tuition_prefix') }} {{ number_format((int) $course['tuition_fee'], 0, ',', '.') }} ₫</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="v2-empty-state">{{ __('home.training.empty_notice') }}</p>
                @endif

                <x-public.v2.button :href="$trainingUrl" variant="text">{{ __('navigation.training') }} <span aria-hidden="true">&rarr;</span></x-public.v2.button>
            </div>
            <x-public.v2.media-frame :src="$trainingImage" alt="" role="portrait" position="portrait" class="v2-editorial-split__media" data-reveal />
        </div>
    </section>

    <section class="v2-section v2-home-journal v2-surface--paper" aria-labelledby="home-journal-title">
        <div class="v2-container">
            <header class="v2-section-heading" data-reveal>
                <div>
                    <x-public.v2.eyebrow>{{ __('home.journal.eyebrow') }}</x-public.v2.eyebrow>
                    <x-public.v2.display-heading id="home-journal-title" size="heading-lg">{{ __('home.journal.title') }}</x-public.v2.display-heading>
                </div>
                <p class="v2-type-body-lg">{{ __('home.journal.subtitle') }}</p>
            </header>

            @if(!empty($latestPosts))
                <div class="v2-journal-list" data-reveal-group>
                    @foreach($latestPosts as $post)
                        <article class="v2-journal-entry" data-reveal>
                            <div class="v2-journal-entry__meta">
                                <span>{{ $post['published_date'] }}</span>
                                @if(!empty($post['category_name']))<span>{{ $post['category_name'] }}</span>@endif
                            </div>
                            <h3 class="v2-type-heading-md">{{ $post['title'] }}</h3>
                            @if(!empty($post['excerpt']))<p class="v2-type-body">{{ $post['excerpt'] }}</p>@endif
                        </article>
                    @endforeach
                </div>
            @else
                <p class="v2-empty-state">{{ __('home.journal.empty_notice') }}</p>
            @endif

            <div class="v2-section-action">
                <x-public.v2.button :href="$blogUrl" variant="text">{{ __('navigation.blog') }} <span aria-hidden="true">&rarr;</span></x-public.v2.button>
            </div>
        </div>
    </section>

    <section class="v2-home-cta v2-surface--brand" aria-labelledby="home-cta-title">
        <div class="v2-container v2-home-cta__inner" data-reveal>
            <x-public.v2.eyebrow inverse>{{ __('home.cta.eyebrow') }}</x-public.v2.eyebrow>
            <x-public.v2.display-heading id="home-cta-title" size="heading-lg">{{ __('home.cta.title') }}</x-public.v2.display-heading>
            <p>{{ __('home.cta.subtitle') }}</p>
            <a href="{{ $bookingUrl }}" x-data="{}" data-booking-modal-trigger @click.prevent="$dispatch('open-booking-modal', { trigger: $el })" class="v2-button v2-button--inverse">
                {{ !empty($featuredServices) ? __('home.cta.button') : __('home.cta.button_empty') }}
            </a>
        </div>
    </section>
</x-layouts.public>
