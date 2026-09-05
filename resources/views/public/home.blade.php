<x-layouts.public :seo="$seo ?? null">
    {{-- 1. HERO SECTION (Zen-Style Full-Width Cinematic Hero with Local Banner Image) --}}
    @php
        $defaultHeroUrl = Vite::asset('resources/images/homepage/viet-han-banner-hero.png');
        $heroImageUrl = !empty($heroMedia['url']) ? $heroMedia['url'] : $defaultHeroUrl;
        $heroImageAlt = !empty($heroMedia['alt']) ? $heroMedia['alt'] : (!empty($page['title']) ? $page['title'] : __('home.hero.title'));
    @endphp
    <section id="home" class="public-hero">
        <div class="public-hero__media">
            <img src="{{ $heroImageUrl }}" alt="{{ $heroImageAlt }}" class="public-hero__image" loading="eager">
        </div>

        <div class="public-hero__wash"></div>
        <div class="hero-readability-overlay"></div>

        <div class="public-hero__content-wrap">
            <div class="public-hero__content">
                <span class="public-hero__eyebrow">
                    {{ __('home.hero.badge') }}
                </span>

                <h1 class="public-hero__title">
                    {{ !empty($page['title']) ? $page['title'] : __('home.hero.title') }}
                </h1>

                <p class="public-hero__copy">
                    {{ !empty($page['excerpt']) ? $page['excerpt'] : __('home.hero.subtitle') }}
                </p>

                <div class="public-hero__actions">
                    @if(!empty($featuredServices))
                        <a href="#services-preview" class="public-hero__cta">
                            {{ __('home.hero.primary_cta') }}
                        </a>
                    @else
                        <a href="#about-preview" class="public-hero__cta">
                            {{ __('home.hero.primary_cta_empty') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- 2. BRAND STORY & WELLNESS PHILOSOPHY (#about-preview & #wellness-philosophy) --}}
    <section id="about-preview" class="w-full bg-[#FAF7F2] py-20 sm:py-28 lg:py-32 border-b border-brand-border scroll-mt-20">
        <div id="wellness-philosophy"></div>
        <x-public.container size="lg">
            <div class="rounded-3xl bg-white border border-[#C5A880]/20 p-8 sm:p-14 lg:p-20 text-center space-y-8 shadow-xs">
                <div class="w-16 h-16 mx-auto rounded-full bg-[#5B1121]/5 border border-[#C5A880]/40 flex items-center justify-center text-[#5B1121] font-serif text-2xl font-bold">
                    VH
                </div>

                <span class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-[#B3956B]">
                    {{ __('home.editorial.eyebrow') }}
                </span>

                <h2 class="font-serif text-2xl sm:text-4xl lg:text-5xl font-bold text-[#5B1121] leading-tight max-w-3xl mx-auto break-words [text-wrap:balance]">
                    {{ __('home.editorial.title') }}
                </h2>

                <p class="text-base sm:text-lg text-brand-text-secondary leading-relaxed max-w-3xl mx-auto break-words">
                    {{ __('home.editorial.content') }}
                </p>

                <!-- Core Pillars -->
                <div class="pt-6 grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-4xl mx-auto text-left">
                    <div class="p-6 rounded-2xl bg-[#FAF7F2] border border-[#E8DFC8]/60 space-y-2">
                        <div class="font-serif font-bold text-lg text-[#5B1121]">Trị Liệu Chuẩn Hàn</div>
                        <p class="text-xs text-brand-text-secondary leading-relaxed">Kỹ thuật massage và dưỡng sinh chuyên sâu được đào tạo bài bản.</p>
                    </div>
                    <div class="p-6 rounded-2xl bg-[#FAF7F2] border border-[#E8DFC8]/60 space-y-2">
                        <div class="font-serif font-bold text-lg text-[#5B1121]">Thảo Mộc Thanh Khiết</div>
                        <p class="text-xs text-brand-text-secondary leading-relaxed">Sử dụng tinh chất thảo mộc tự nhiên, an lành cho mọi làn da.</p>
                    </div>
                    <div class="p-6 rounded-2xl bg-[#FAF7F2] border border-[#E8DFC8]/60 space-y-2">
                        <div class="font-serif font-bold text-lg text-[#5B1121]">Tâm An Tĩnh Tại</div>
                        <p class="text-xs text-brand-text-secondary leading-relaxed">Không gian riêng tư, thư thái xoa dịu những căng thẳng đời thường.</p>
                    </div>
                </div>
            </div>
        </x-public.container>
    </section>

    {{-- 3. FEATURED SERVICES SECTION (#services-preview & #featured-services) --}}
    <section id="services-preview" class="w-full bg-[#181312] text-white py-20 sm:py-28 lg:py-32 border-b border-[#C5A880]/15 scroll-mt-20">
        <div id="featured-services"></div>
        <x-public.container>
            <div class="text-center space-y-4 max-w-3xl mx-auto mb-14 sm:mb-20">
                <span class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-[#C5A880]">
                    {{ __('home.services.eyebrow') }}
                </span>
                <h2 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold text-white tracking-tight break-words [text-wrap:balance]">
                    {{ __('home.services.title') }}
                </h2>
                <p class="text-base text-white/70 font-light leading-relaxed break-words">
                    {{ __('home.services.subtitle') }}
                </p>
            </div>

            @if(!empty($featuredServices))
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($featuredServices as $service)
                        <article class="flex flex-col rounded-2xl border border-[#C5A880]/20 bg-[#211B19] overflow-hidden shadow-lg hover:border-[#C5A880]/50 transition duration-300 group">
                            @if(!empty($service['media']['url']))
                                <div class="aspect-16/10 w-full overflow-hidden bg-[#181312]">
                                    <img src="{{ $service['media']['url'] }}" alt="{{ $service['media']['alt'] }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
                                </div>
                            @else
                                <div class="aspect-16/10 w-full bg-[#2A0810] flex items-center justify-center text-[#C5A880] font-serif text-2xl font-bold">
                                    {{ $service['name'] }}
                                </div>
                            @endif

                            <div class="p-6 sm:p-8 flex-1 flex flex-col justify-between space-y-4">
                                <div class="space-y-2">
                                    @if(!empty($service['category_name']))
                                        <div class="text-xs font-semibold uppercase tracking-widest text-[#C5A880]">
                                            {{ $service['category_name'] }}
                                        </div>
                                    @endif

                                    <h3 class="font-serif text-xl sm:text-2xl font-bold text-white group-hover:text-[#C5A880] transition leading-snug break-words">
                                        {{ $service['name'] }}
                                    </h3>

                                    @if(!empty($service['excerpt']))
                                        <p class="text-sm text-white/70 font-light leading-relaxed break-words">
                                            {{ $service['excerpt'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <!-- Intentional Serene Empty-State Placeholder -->
                <div class="rounded-3xl border border-[#C5A880]/20 bg-[#211B19] p-8 sm:p-12 text-center max-w-2xl mx-auto space-y-4">
                    <div class="w-12 h-12 mx-auto rounded-full bg-[#C5A880]/10 flex items-center justify-center text-[#C5A880]">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <p class="text-sm sm:text-base text-white/80 font-light leading-relaxed">
                        {{ __('home.services.empty_notice') }}
                    </p>
                    <div class="pt-2">
                        <a href="#contact-preview" class="inline-flex items-center text-xs font-semibold tracking-wider uppercase text-[#C5A880] hover:underline">
                            {{ __('home.cta.contact_btn') }} &rarr;
                        </a>
                    </div>
                </div>
            @endif
        </x-public.container>
    </section>

    {{-- 4. FEATURED TRAINING SECTION (#training-preview & #training) --}}
    <section id="training-preview" class="w-full bg-[#FAF7F2] py-20 sm:py-28 lg:py-32 border-b border-brand-border scroll-mt-20">
        <div id="training"></div>
        <x-public.container>
            <div class="text-center space-y-4 max-w-3xl mx-auto mb-14 sm:mb-20">
                <span class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-[#B3956B]">
                    {{ __('home.training.eyebrow') }}
                </span>
                <h2 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold text-[#5B1121] tracking-tight break-words [text-wrap:balance]">
                    {{ __('home.training.title') }}
                </h2>
                <p class="text-base text-brand-text-secondary leading-relaxed break-words">
                    {{ __('home.training.subtitle') }}
                </p>
            </div>

            @if(!empty($featuredCourses))
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($featuredCourses as $course)
                        <article class="flex flex-col rounded-2xl border border-brand-border bg-white overflow-hidden shadow-xs hover:shadow-md transition duration-200">
                            @if(!empty($course['media']['url']))
                                <div class="aspect-16/10 w-full overflow-hidden bg-brand-warm">
                                    <img src="{{ $course['media']['url'] }}" alt="{{ $course['media']['alt'] }}" class="w-full h-full object-cover transition duration-300 hover:scale-105" loading="lazy">
                                </div>
                            @endif

                            <div class="p-6 sm:p-8 flex-1 flex flex-col justify-between space-y-4">
                                <div class="space-y-2">
                                    <h3 class="font-serif text-xl sm:text-2xl font-bold text-[#5B1121] leading-snug break-words">
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
                                            <span class="text-[#5B1121] font-bold text-sm">{{ number_format($course['tuition_fee'], 0, ',', '.') }} ₫</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-3xl border border-brand-border bg-white p-8 sm:p-12 text-center max-w-2xl mx-auto space-y-4 shadow-xs">
                    <p class="text-sm sm:text-base text-brand-text-secondary leading-relaxed">
                        {{ __('home.training.empty_notice') }}
                    </p>
                    <div>
                        <a href="#contact-preview" class="inline-flex items-center text-xs font-semibold tracking-wider uppercase text-[#5B1121] hover:underline">
                            {{ __('home.cta.contact_btn') }} &rarr;
                        </a>
                    </div>
                </div>
            @endif
        </x-public.container>
    </section>

    {{-- 5. SANCTUARY ATMOSPHERE SHOWCASE (Zen-Style Visual Spaces) --}}
    <section id="atmosphere" class="w-full bg-[#120D0C] text-white py-20 sm:py-28 lg:py-32 border-b border-[#C5A880]/15">
        <x-public.container>
            <div class="text-center space-y-4 max-w-3xl mx-auto mb-14 sm:mb-20">
                <span class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-[#C5A880]">
                    {{ __('home.atmosphere.eyebrow') }}
                </span>
                <h2 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold text-white tracking-tight break-words [text-wrap:balance]">
                    {{ __('home.atmosphere.title') }}
                </h2>
                <p class="text-base text-white/70 font-light leading-relaxed break-words">
                    {{ __('home.atmosphere.subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="group relative rounded-2xl overflow-hidden border border-[#C5A880]/20 bg-[#181312] p-8 space-y-4 hover:border-[#C5A880]/50 transition duration-300 shadow-xl">
                    <div class="w-12 h-12 rounded-full bg-[#C5A880]/10 flex items-center justify-center text-[#C5A880] font-serif font-bold text-lg">
                        01
                    </div>
                    <h3 class="font-serif text-xl font-bold text-white group-hover:text-[#C5A880] transition">
                        {{ __('home.atmosphere.space_1_title') }}
                    </h3>
                    <p class="text-sm text-white/70 font-light leading-relaxed">
                        {{ __('home.atmosphere.space_1_desc') }}
                    </p>
                </div>

                <div class="group relative rounded-2xl overflow-hidden border border-[#C5A880]/20 bg-[#181312] p-8 space-y-4 hover:border-[#C5A880]/50 transition duration-300 shadow-xl">
                    <div class="w-12 h-12 rounded-full bg-[#C5A880]/10 flex items-center justify-center text-[#C5A880] font-serif font-bold text-lg">
                        02
                    </div>
                    <h3 class="font-serif text-xl font-bold text-white group-hover:text-[#C5A880] transition">
                        {{ __('home.atmosphere.space_2_title') }}
                    </h3>
                    <p class="text-sm text-white/70 font-light leading-relaxed">
                        {{ __('home.atmosphere.space_2_desc') }}
                    </p>
                </div>

                <div class="group relative rounded-2xl overflow-hidden border border-[#C5A880]/20 bg-[#181312] p-8 space-y-4 hover:border-[#C5A880]/50 transition duration-300 shadow-xl">
                    <div class="w-12 h-12 rounded-full bg-[#C5A880]/10 flex items-center justify-center text-[#C5A880] font-serif font-bold text-lg">
                        03
                    </div>
                    <h3 class="font-serif text-xl font-bold text-white group-hover:text-[#C5A880] transition">
                        {{ __('home.atmosphere.space_3_title') }}
                    </h3>
                    <p class="text-sm text-white/70 font-light leading-relaxed">
                        {{ __('home.atmosphere.space_3_desc') }}
                    </p>
                </div>
            </div>
        </x-public.container>
    </section>

    {{-- 6. LATEST BLOG / JOURNAL SECTION (#blog-preview & #journal) --}}
    <section id="blog-preview" class="w-full bg-[#FAF7F2] py-20 sm:py-28 lg:py-32 border-b border-brand-border scroll-mt-20">
        <div id="journal"></div>
        <x-public.container>
            <div class="text-center space-y-4 max-w-3xl mx-auto mb-14 sm:mb-20">
                <span class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-[#B3956B]">
                    {{ __('home.journal.eyebrow') }}
                </span>
                <h2 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold text-[#5B1121] tracking-tight break-words [text-wrap:balance]">
                    {{ __('home.journal.title') }}
                </h2>
                <p class="text-base text-brand-text-secondary leading-relaxed break-words">
                    {{ __('home.journal.subtitle') }}
                </p>
            </div>

            @if(!empty($latestPosts))
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($latestPosts as $post)
                        <article class="flex flex-col rounded-2xl border border-brand-border bg-white overflow-hidden shadow-xs hover:shadow-md transition duration-200">
                            @if(!empty($post['media']['url']))
                                <div class="aspect-16/10 w-full overflow-hidden bg-brand-warm">
                                    <img src="{{ $post['media']['url'] }}" alt="{{ $post['media']['alt'] }}" class="w-full h-full object-cover transition duration-300 hover:scale-105" loading="lazy">
                                </div>
                            @endif

                            <div class="p-6 sm:p-8 flex-1 flex flex-col justify-between space-y-4">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-xs text-brand-text-muted">
                                        @if(!empty($post['category_name']))
                                            <span class="font-semibold uppercase tracking-widest text-[#B3956B]">{{ $post['category_name'] }}</span>
                                        @endif

                                        @if(!empty($post['published_date']))
                                            <span>{{ $post['published_date'] }}</span>
                                        @endif
                                    </div>

                                    <h3 class="font-serif text-xl font-bold text-[#5B1121] leading-snug break-words">
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
            @else
                <div class="rounded-3xl border border-brand-border bg-white p-8 sm:p-12 text-center max-w-2xl mx-auto space-y-4 shadow-xs">
                    <p class="text-sm sm:text-base text-brand-text-secondary leading-relaxed">
                        {{ __('home.journal.empty_notice') }}
                    </p>
                </div>
            @endif
        </x-public.container>
    </section>

    {{-- 7. CLOSING CTA & SANCTUARY INVITATION (#contact-preview) --}}
    <section id="contact-preview" class="w-full bg-gradient-to-br from-[#3D0B16] via-[#2A0810] to-[#181312] text-white py-20 sm:py-28 lg:py-32 scroll-mt-20">
        <x-public.container size="lg">
            <div class="rounded-3xl border border-[#C5A880]/30 bg-[#181312]/60 backdrop-blur-md p-8 sm:p-14 lg:p-20 text-center space-y-8 shadow-2xl">
                <span class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-[#C5A880]">
                    {{ __('home.cta.eyebrow') }}
                </span>

                <h2 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-white leading-tight max-w-3xl mx-auto break-words [text-wrap:balance]">
                    {{ __('home.cta.title') }}
                </h2>

                <p class="text-base sm:text-lg text-white/80 font-light leading-relaxed max-w-2xl mx-auto break-words">
                    {{ __('home.cta.subtitle') }}
                </p>

                <div class="pt-4 flex flex-wrap items-center justify-center gap-4">
                    @if(!empty($featuredServices))
                        <x-public.button as="a" href="#services-preview" variant="gold" size="lg" class="rounded-full px-8 py-4 bg-gradient-to-r from-[#C5A880] to-[#B3956B] text-[#181312] font-bold shadow-xl hover:scale-105 transition duration-200 border-0">
                            {{ __('home.cta.button') }}
                        </x-public.button>
                    @else
                        <x-public.button as="a" href="#about-preview" variant="gold" size="lg" class="rounded-full px-8 py-4 bg-gradient-to-r from-[#C5A880] to-[#B3956B] text-[#181312] font-bold shadow-xl hover:scale-105 transition duration-200 border-0">
                            {{ __('home.cta.button_empty') }}
                        </x-public.button>
                    @endif
                </div>

                @inject('settings', 'App\Services\Settings\SiteSettings')
                @php
                    $contactPhone = $settings->getPublic('contact.phone', '090 123 4567');
                    $contactEmail = $settings->getPublic('contact.email', 'info@viethanauhanspa.com');
                @endphp
                <div class="pt-8 border-t border-white/10 flex flex-wrap items-center justify-center gap-6 text-xs sm:text-sm text-white/70">
                    @if($contactPhone)
                        <div>
                            <span class="text-[#C5A880] font-semibold">Hotline:</span>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}" class="text-white hover:text-[#C5A880] transition ml-1">{{ $contactPhone }}</a>
                        </div>
                    @endif
                    @if($contactEmail)
                        <div>
                            <span class="text-[#C5A880] font-semibold">Email:</span>
                            <a href="mailto:{{ $contactEmail }}" class="text-white hover:text-[#C5A880] transition ml-1">{{ $contactEmail }}</a>
                        </div>
                    @endif
                </div>
            </div>
        </x-public.container>
    </section>
</x-layouts.public>
