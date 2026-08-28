<x-layouts.public>
    {{-- 1. HERO SECTION --}}
    <section class="relative w-full bg-brand-ivory pt-12 pb-16 sm:pt-20 sm:pb-24 lg:pt-24 lg:pb-32 overflow-hidden border-b border-brand-border">
        <x-public.container>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                <!-- Text Column -->
                <div class="lg:col-span-7 space-y-6 sm:space-y-8 text-center lg:text-left">
                    <div class="inline-flex items-center space-x-2">
                        <span class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-brand-gold-hover">
                            {{ __('home.hero.eyebrow') }}
                        </span>
                    </div>

                    <h1 class="font-serif text-3xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-brand-primary leading-tight break-words [text-wrap:balance]">
                        {{ !empty($page['title']) ? $page['title'] : __('home.hero.title') }}
                    </h1>

                    <p class="text-base sm:text-lg text-brand-text-secondary leading-relaxed max-w-2xl mx-auto lg:mx-0 break-words">
                        {{ !empty($page['excerpt']) ? $page['excerpt'] : __('home.hero.subtitle') }}
                    </p>

                    <div class="pt-2 flex flex-wrap items-center justify-center lg:justify-start gap-4">
                        @if(!empty($featuredServices))
                            <x-public.button as="a" href="#featured-services" variant="primary" size="lg">
                                {{ __('home.hero.primary_cta') }}
                            </x-public.button>
                        @else
                            <x-public.button as="a" href="#wellness-philosophy" variant="primary" size="lg">
                                {{ __('home.hero.primary_cta_empty') }}
                            </x-public.button>
                        @endif

                        @if(!empty($featuredCourses))
                            <x-public.button as="a" href="#training" variant="secondary" size="lg">
                                {{ __('home.hero.secondary_cta') }}
                            </x-public.button>
                        @endif
                    </div>
                </div>

                <!-- Media Column -->
                <div class="lg:col-span-5 relative">
                    <div class="relative mx-auto max-w-md lg:max-w-none">
                        <!-- Decorative Frame -->
                        <div class="absolute -inset-4 rounded-3xl bg-brand-gold/10 border border-brand-gold/20 transform rotate-1 hidden sm:block"></div>
                        
                        <div class="relative rounded-2xl overflow-hidden shadow-md bg-brand-warm border border-brand-border aspect-4/5 flex items-center justify-center">
                            @if(!empty($heroMedia['url']))
                                <img src="{{ $heroMedia['url'] }}" alt="{{ $heroMedia['alt'] }}" class="w-full h-full object-cover" loading="eager">
                            @else
                                <!-- Elegant Fallback Illustration / Brand Monogram -->
                                <div class="text-center p-8 space-y-4">
                                    <div class="w-20 h-20 mx-auto rounded-full bg-brand-primary/10 border border-brand-primary/20 flex items-center justify-center text-brand-primary font-serif text-3xl font-bold">
                                        VH
                                    </div>
                                    <div class="font-serif text-xl font-bold text-brand-primary">
                                        Việt Hàn Âu Hàn Spa
                                    </div>
                                    <p class="text-xs text-brand-text-muted max-w-xs mx-auto">
                                        {{ __('common.tagline') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </x-public.container>
    </section>

    {{-- 2. FEATURED SERVICES SECTION --}}
    @if(!empty($featuredServices))
        <section id="featured-services" class="w-full bg-brand-warm py-16 sm:py-24 lg:py-32 border-b border-brand-border scroll-mt-20">
            <x-public.container>
                <x-public.section-heading
                    :eyebrow="__('home.services.eyebrow')"
                    :title="__('home.services.title')"
                    :subtitle="__('home.services.subtitle')"
                />

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($featuredServices as $service)
                        <article class="flex flex-col rounded-2xl border border-brand-border bg-brand-surface overflow-hidden shadow-xs hover:shadow-md transition duration-200">
                            @if(!empty($service['media']['url']))
                                <div class="aspect-16/10 w-full overflow-hidden bg-brand-warm">
                                    <img src="{{ $service['media']['url'] }}" alt="{{ $service['media']['alt'] }}" class="w-full h-full object-cover transition duration-300 hover:scale-105" loading="lazy">
                                </div>
                            @endif

                            <div class="p-6 sm:p-8 flex-1 flex flex-col justify-between space-y-4">
                                <div class="space-y-2">
                                    @if(!empty($service['category_name']))
                                        <div class="text-xs font-semibold uppercase tracking-widest text-brand-gold-hover">
                                            {{ $service['category_name'] }}
                                        </div>
                                    @endif

                                    <h3 class="font-serif text-xl sm:text-2xl font-bold text-brand-primary leading-snug break-words">
                                        {{ $service['name'] }}
                                    </h3>

                                    @if(!empty($service['excerpt']))
                                        <p class="text-sm text-brand-text-secondary leading-relaxed break-words">
                                            {{ $service['excerpt'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </x-public.container>
        </section>
    @endif

    {{-- 3. BRAND / EDITORIAL INTRODUCTION --}}
    <section id="wellness-philosophy" class="w-full bg-brand-ivory py-16 sm:py-24 border-b border-brand-border scroll-mt-20">
        <x-public.container size="lg">
            <div class="rounded-3xl bg-brand-warm border border-brand-border p-8 sm:p-12 lg:p-16 text-center space-y-6">
                <span class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-brand-gold-hover">
                    {{ __('home.editorial.eyebrow') }}
                </span>

                <h2 class="font-serif text-2xl sm:text-4xl font-bold text-brand-primary leading-tight max-w-3xl mx-auto break-words [text-wrap:balance]">
                    {{ __('home.editorial.title') }}
                </h2>

                <p class="text-base sm:text-lg text-brand-text-secondary leading-relaxed max-w-3xl mx-auto break-words">
                    {{ __('home.editorial.content') }}
                </p>
            </div>
        </x-public.container>
    </section>

    {{-- 4. FEATURED TRAINING SECTION --}}
    @if(!empty($featuredCourses))
        <section id="training" class="w-full bg-brand-warm py-16 sm:py-24 lg:py-32 border-b border-brand-border scroll-mt-20">
            <x-public.container>
                <x-public.section-heading
                    :eyebrow="__('home.training.eyebrow')"
                    :title="__('home.training.title')"
                    :subtitle="__('home.training.subtitle')"
                />

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($featuredCourses as $course)
                        <article class="flex flex-col rounded-2xl border border-brand-border bg-brand-surface overflow-hidden shadow-xs hover:shadow-md transition duration-200">
                            @if(!empty($course['media']['url']))
                                <div class="aspect-16/10 w-full overflow-hidden bg-brand-warm">
                                    <img src="{{ $course['media']['url'] }}" alt="{{ $course['media']['alt'] }}" class="w-full h-full object-cover transition duration-300 hover:scale-105" loading="lazy">
                                </div>
                            @endif

                            <div class="p-6 sm:p-8 flex-1 flex flex-col justify-between space-y-4">
                                <div class="space-y-2">
                                    <h3 class="font-serif text-xl sm:text-2xl font-bold text-brand-primary leading-snug break-words">
                                        {{ $course['title'] }}
                                    </h3>

                                    @if(!empty($course['excerpt']))
                                        <p class="text-sm text-brand-text-secondary leading-relaxed break-words">
                                            {{ $course['excerpt'] }}
                                        </p>
                                    @endif
                                </div>

                                <div class="pt-4 border-t border-brand-border space-y-2 text-xs text-brand-text-muted">
                                    @if(!empty($course['duration_display']))
                                        <div>
                                            <span class="font-semibold text-brand-text">{{ __('home.training.duration_prefix') }}</span> {{ $course['duration_display'] }}
                                        </div>
                                    @endif

                                    @if(!is_null($course['tuition_fee']) && $course['tuition_fee'] > 0)
                                        <div>
                                            <span class="font-semibold text-brand-text">{{ __('home.training.tuition_prefix') }}</span>
                                            <span class="text-brand-primary font-bold text-sm">{{ number_format($course['tuition_fee'], 0, ',', '.') }} ₫</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </x-public.container>
        </section>
    @endif

    {{-- 5. LATEST BLOG / JOURNAL SECTION --}}
    @if(!empty($latestPosts))
        <section id="journal" class="w-full bg-brand-ivory py-16 sm:py-24 lg:py-32 border-b border-brand-border scroll-mt-20">
            <x-public.container>
                <x-public.section-heading
                    :eyebrow="__('home.journal.eyebrow')"
                    :title="__('home.journal.title')"
                    :subtitle="__('home.journal.subtitle')"
                />

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($latestPosts as $post)
                        <article class="flex flex-col rounded-2xl border border-brand-border bg-brand-surface overflow-hidden shadow-xs hover:shadow-md transition duration-200">
                            @if(!empty($post['media']['url']))
                                <div class="aspect-16/10 w-full overflow-hidden bg-brand-warm">
                                    <img src="{{ $post['media']['url'] }}" alt="{{ $post['media']['alt'] }}" class="w-full h-full object-cover transition duration-300 hover:scale-105" loading="lazy">
                                </div>
                            @endif

                            <div class="p-6 sm:p-8 flex-1 flex flex-col justify-between space-y-4">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-xs text-brand-text-muted">
                                        @if(!empty($post['category_name']))
                                            <span class="font-semibold uppercase tracking-widest text-brand-gold-hover">{{ $post['category_name'] }}</span>
                                        @endif

                                        @if(!empty($post['published_date']))
                                            <span>{{ $post['published_date'] }}</span>
                                        @endif
                                    </div>

                                    <h3 class="font-serif text-xl font-bold text-brand-primary leading-snug break-words">
                                        {{ $post['title'] }}
                                    </h3>

                                    @if(!empty($post['excerpt']))
                                        <p class="text-sm text-brand-text-secondary leading-relaxed break-words">
                                            {{ $post['excerpt'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </x-public.container>
        </section>
    @endif

    {{-- 6. CLOSING CTA SECTION --}}
    <section class="w-full bg-brand-warm py-16 sm:py-24 lg:py-28">
        <x-public.container size="lg">
            <div class="rounded-3xl bg-brand-primary text-white p-8 sm:p-12 lg:p-16 text-center space-y-6 shadow-xl">
                <span class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-brand-gold">
                    {{ __('home.cta.eyebrow') }}
                </span>

                <h2 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-white leading-tight max-w-3xl mx-auto break-words [text-wrap:balance]">
                    {{ __('home.cta.title') }}
                </h2>

                <p class="text-base sm:text-lg text-brand-gold-light/90 leading-relaxed max-w-2xl mx-auto break-words">
                    {{ __('home.cta.subtitle') }}
                </p>

                <div class="pt-4 flex flex-wrap items-center justify-center gap-4">
                    @if(!empty($featuredServices))
                        <x-public.button as="a" href="#featured-services" variant="gold" size="lg">
                            {{ __('home.cta.button') }}
                        </x-public.button>
                    @else
                        <x-public.button as="a" href="#wellness-philosophy" variant="gold" size="lg">
                            {{ __('home.cta.button_empty') }}
                        </x-public.button>
                    @endif
                </div>
            </div>
        </x-public.container>
    </section>
</x-layouts.public>
