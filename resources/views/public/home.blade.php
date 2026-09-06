@php
    $defaultHeroUrl = Vite::asset('resources/images/homepage/viet-han-banner-hero.png');
    $heroImageUrl = !empty($heroMedia['url']) ? $heroMedia['url'] : $defaultHeroUrl;
    $heroImageAlt = !empty($heroMedia['alt']) ? $heroMedia['alt'] : (!empty($page['title']) ? $page['title'] : __('home.hero.title'));

    $philosophyImg = Vite::asset('resources/images/pages/homepage/homepage-philosophy-sanctuary.webp');
    $soundBathImg = Vite::asset('resources/images/pages/about/about-sound-bath-sanctuary.webp');
    $headSpaImg = Vite::asset('resources/images/pages/about/about-herbal-head-spa.webp');
    $footRitualImg = Vite::asset('resources/images/pages/homepage/homepage-atmosphere-foot-ritual.webp');
@endphp

<x-layouts.public :seo="$seo ?? null">
    {{-- 1. HERO SECTION (Zen-Style Full-Width Cinematic Hero with Local Banner Image) --}}
    <section id="home" class="public-hero">
        <div class="public-hero__media" data-reveal-image>
            <img src="{{ $heroImageUrl }}" alt="{{ $heroImageAlt }}" class="public-hero__image" loading="eager" fetchpriority="high">
        </div>

        <div class="public-hero__wash"></div>
        <div class="hero-readability-overlay"></div>

        <div class="public-hero__content-wrap">
            <div class="public-hero__content" data-reveal>
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
    <section id="about-preview" class="w-full bg-[#FAF7F2] py-16 sm:py-20 lg:py-24 border-b border-[#E8DFC8]/70 scroll-mt-20">
        <div id="wellness-philosophy"></div>
        <x-public.container size="lg">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
                {{-- Left Column: Authentic Curated Sanctuary Photography --}}
                <div class="lg:col-span-6 order-2 lg:order-1" data-reveal>
                    <div class="relative">
                        <div class="overflow-hidden rounded-2xl border border-[#E8DFC8] bg-white shadow-xl group" data-reveal-image>
                            <img
                                src="{{ $philosophyImg }}"
                                alt="Việt Hàn Âu Hàn Spa — Không Gian Thư Giãn Tinh Tế"
                                width="1800"
                                height="1200"
                                class="w-full aspect-[4/3] object-cover transition duration-700 group-hover:scale-105"
                                loading="lazy"
                            >
                        </div>
                        <div class="mt-3 px-2 flex items-center justify-between text-[15px] sm:text-[16px] text-[#736965]">
                            <span>Việt Hàn Âu Hàn Spa</span>
                            <span class="italic text-[#9B7B4F]">{{ app()->getLocale() === 'vi' ? 'Không Gian Trị Liệu Tĩnh Lặng' : 'Serene Treatment Space' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Brand Narrative & 3 Core Pillars --}}
                <div class="lg:col-span-6 order-1 lg:order-2 space-y-5" data-reveal>
                    <div class="space-y-2.5">
                        <span class="inline-flex items-center gap-2 text-[15px] font-semibold tracking-[0.1em] uppercase text-[#9B7B4F]">
                            <span class="w-6 h-px bg-[#C5A880]/70"></span>
                            {{ __('home.editorial.eyebrow') }}
                        </span>

                        <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold tracking-[-0.02em] text-[#5B1121] leading-[1.10] break-words [text-wrap:balance]">
                            {{ __('home.editorial.title') }}
                        </h2>
                    </div>

                    <p class="text-[18px] sm:text-[19px] font-medium text-brand-text-secondary leading-[1.66] break-words">
                        {{ __('home.editorial.content') }}
                    </p>

                    <!-- Core Pillars -->
                    <div class="pt-3 space-y-3" data-reveal-group>
                        <div class="p-4 sm:p-5 rounded-xl bg-white border border-[#E8DFC8]/70 shadow-xs flex items-start gap-4 transition duration-200 hover:border-[#C5A880]" data-reveal>
                            <span class="font-semibold text-xl text-[#C5A880] mt-0.5 shrink-0">01</span>
                            <div class="space-y-1 min-w-0">
                                <div class="font-semibold text-xl text-[#5B1121]">{{ app()->getLocale() === 'vi' ? 'Trị Liệu Chuẩn Hàn' : 'Korean Standard Therapy' }}</div>
                                <p class="text-[17px] text-brand-text-secondary leading-[1.65]">{{ app()->getLocale() === 'vi' ? 'Kỹ thuật massage và dưỡng sinh chuyên sâu được đào tạo bài bản.' : 'Specialized massage and wellness techniques performed with structured training.' }}</p>
                            </div>
                        </div>

                        <div class="p-4 sm:p-5 rounded-xl bg-white border border-[#E8DFC8]/70 shadow-xs flex items-start gap-4 transition duration-200 hover:border-[#C5A880]" data-reveal>
                            <span class="font-semibold text-xl text-[#C5A880] mt-0.5 shrink-0">02</span>
                            <div class="space-y-1 min-w-0">
                                <div class="font-semibold text-xl text-[#5B1121]">{{ app()->getLocale() === 'vi' ? 'Thảo Mộc Thanh Khiết' : 'Pure Herbal Essence' }}</div>
                                <p class="text-[17px] text-brand-text-secondary leading-[1.65]">{{ app()->getLocale() === 'vi' ? 'Sử dụng tinh chất thảo mộc tự nhiên, an lành cho mọi làn da.' : 'Natural herbal extracts selected for gentle care and wholesome relaxation.' }}</p>
                            </div>
                        </div>

                        <div class="p-4 sm:p-5 rounded-xl bg-white border border-[#E8DFC8]/70 shadow-xs flex items-start gap-4 transition duration-200 hover:border-[#C5A880]" data-reveal>
                            <span class="font-semibold text-xl text-[#C5A880] mt-0.5 shrink-0">03</span>
                            <div class="space-y-1 min-w-0">
                                <div class="font-semibold text-xl text-[#5B1121]">{{ app()->getLocale() === 'vi' ? 'Tâm An Tĩnh Tại' : 'Tranquil Sanctuary' }}</div>
                                <p class="text-[17px] text-brand-text-secondary leading-[1.65]">{{ app()->getLocale() === 'vi' ? 'Không gian riêng tư, thư thái xoa dịu những căng thẳng đời thường.' : 'A peaceful, secluded setting designed to ease everyday stresses.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-public.container>
    </section>

    {{-- 3. FEATURED SERVICES SECTION (#services-preview & #featured-services) --}}
    <section id="services-preview" class="w-full bg-[#181312] text-white py-16 sm:py-20 lg:py-24 border-b border-[#C5A880]/15 scroll-mt-20">
        <div id="featured-services"></div>
        <x-public.container>
            <div class="text-center space-y-3 max-w-3xl mx-auto mb-10 sm:mb-14" data-reveal>
                <span class="text-[15px] font-semibold tracking-[0.1em] uppercase text-[#C5A880]">
                    {{ __('home.services.eyebrow') }}
                </span>
                <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold tracking-[-0.02em] text-white leading-[1.10] break-words [text-wrap:balance]">
                    {{ __('home.services.title') }}
                </h2>
                <p class="text-[18px] sm:text-[19px] font-medium text-white/85 leading-[1.66] break-words">
                    {{ __('home.services.subtitle') }}
                </p>
            </div>

            @if(!empty($featuredServices))
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" data-reveal-group>
                    @foreach($featuredServices as $service)
                        <article class="flex flex-col rounded-2xl border border-[#C5A880]/20 bg-[#211B19] overflow-hidden shadow-lg hover:border-[#C5A880]/50 transition duration-300 group editorial-card" data-reveal>
                            @if(!empty($service['media']['url']))
                                <div class="aspect-16/10 w-full overflow-hidden bg-[#181312] editorial-image-zoom">
                                    <img src="{{ $service['media']['url'] }}" alt="{{ $service['media']['alt'] }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
                                </div>
                            @else
                                <div class="aspect-16/10 w-full bg-[#2A0810] flex items-center justify-center text-[#C5A880] text-2xl font-semibold px-6 text-center">
                                    {{ $service['name'] }}
                                </div>
                            @endif

                            <div class="p-6 sm:p-8 flex-1 flex flex-col justify-between space-y-4">
                                <div class="space-y-2">
                                    @if(!empty($service['category_name']))
                                        <div class="text-[15px] font-semibold uppercase tracking-widest text-[#C5A880]">
                                            {{ $service['category_name'] }}
                                        </div>
                                    @endif

                                    <h3 class="text-xl sm:text-[22px] lg:text-[24px] font-semibold text-white group-hover:text-[#C5A880] transition leading-snug break-words">
                                        {{ $service['name'] }}
                                    </h3>

                                    @if(!empty($service['excerpt']))
                                        <p class="text-[17px] text-white/80 leading-[1.65] break-words">
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
                <div class="rounded-3xl border border-[#C5A880]/20 bg-[#211B19] p-8 sm:p-12 text-center max-w-2xl mx-auto space-y-4" data-reveal>
                    <div class="w-12 h-12 mx-auto rounded-full bg-[#C5A880]/10 flex items-center justify-center text-[#C5A880]">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <p class="text-[17px] text-white/80 leading-relaxed">
                        {{ __('home.services.empty_notice') }}
                    </p>
                    <div class="pt-2">
                        <a href="#contact-preview" class="inline-flex items-center text-sm font-semibold tracking-wider uppercase text-[#C5A880] hover:underline">
                            {{ __('home.cta.contact_btn') }} &rarr;
                        </a>
                    </div>
                </div>
            @endif
        </x-public.container>
    </section>

    {{-- 4. FEATURED TRAINING SECTION (#training-preview & #training) --}}
    <section id="training-preview" class="w-full bg-[#FAF7F2] py-16 sm:py-20 lg:py-24 border-b border-[#E8DFC8]/70 scroll-mt-20">
        <div id="training"></div>
        <x-public.container>
            <div class="text-center space-y-3 max-w-3xl mx-auto mb-10 sm:mb-14" data-reveal>
                <span class="text-[15px] font-semibold tracking-[0.1em] uppercase text-[#B3956B]">
                    {{ __('home.training.eyebrow') }}
                </span>
                <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold tracking-[-0.02em] text-[#5B1121] leading-[1.10] break-words [text-wrap:balance]">
                    {{ __('home.training.title') }}
                </h2>
                <p class="text-[18px] sm:text-[19px] font-medium text-brand-text-secondary leading-[1.66] break-words">
                    {{ __('home.training.subtitle') }}
                </p>
            </div>

            @if(!empty($featuredCourses))
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8" data-reveal-group>
                    @foreach($featuredCourses as $course)
                        <article class="flex flex-col rounded-2xl border border-[#E8DFC8] bg-white overflow-hidden shadow-xs hover:border-[#C5A880] hover:shadow-md transition duration-300 group editorial-card" data-reveal>
                            @if(!empty($course['media']['url']))
                                <div class="aspect-16/10 w-full overflow-hidden bg-brand-warm editorial-image-zoom">
                                    <img src="{{ $course['media']['url'] }}" alt="{{ $course['media']['alt'] }}" class="w-full h-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                                </div>
                            @endif

                            <div class="p-6 sm:p-8 flex-1 flex flex-col justify-between space-y-4">
                                <div class="space-y-2">
                                    <h3 class="text-xl sm:text-[22px] lg:text-[24px] font-semibold text-[#5B1121] group-hover:text-[#B3956B] transition leading-snug break-words">
                                        {{ $course['title'] }}
                                    </h3>

                                    @if(!empty($course['excerpt']))
                                        <p class="text-[17px] text-brand-text-secondary leading-[1.65] break-words">
                                            {{ $course['excerpt'] }}
                                        </p>
                                    @endif
                                </div>

                                <div class="pt-4 border-t border-[#E8DFC8] space-y-2 text-[15px] sm:text-[16px] text-brand-text-muted">
                                    @if(!empty($course['duration_display']))
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-brand-text">{{ __('home.training.duration_prefix') }}</span>
                                            <span class="inline-block px-2.5 py-0.5 rounded-full bg-[#FAF7F2] border border-[#E8DFC8] text-[#5B1121] font-medium">{{ $course['duration_display'] }}</span>
                                        </div>
                                    @endif

                                    @if(!is_null($course['tuition_fee']) && $course['tuition_fee'] > 0)
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-brand-text">{{ __('home.training.tuition_prefix') }}</span>
                                            <span class="text-[#5B1121] font-semibold text-[17px]">{{ number_format($course['tuition_fee'], 0, ',', '.') }} ₫</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-3xl border border-[#E8DFC8] bg-white p-8 sm:p-12 text-center max-w-2xl mx-auto space-y-4 shadow-xs" data-reveal>
                    <p class="text-[17px] sm:text-[18px] text-brand-text-secondary leading-relaxed">
                        {{ __('home.training.empty_notice') }}
                    </p>
                    <div>
                        <a href="#contact-preview" class="inline-flex items-center text-sm font-semibold tracking-wider uppercase text-[#5B1121] hover:underline">
                            {{ __('home.cta.contact_btn') }} &rarr;
                        </a>
                    </div>
                </div>
            @endif
        </x-public.container>
    </section>

    {{-- 5. SANCTUARY ATMOSPHERE SHOWCASE (Zen-Style Visual Spaces with Real Spa Photography) --}}
    <section id="atmosphere" class="w-full bg-[#120D0C] text-white py-16 sm:py-20 lg:py-24 border-b border-[#C5A880]/15">
        <x-public.container>
            <div class="text-center space-y-3 max-w-3xl mx-auto mb-10 sm:mb-14" data-reveal>
                <span class="text-[15px] font-semibold tracking-[0.1em] uppercase text-[#C5A880]">
                    {{ __('home.atmosphere.eyebrow') }}
                </span>
                <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold tracking-[-0.02em] text-white leading-[1.10] break-words [text-wrap:balance]">
                    {{ __('home.atmosphere.title') }}
                </h2>
                <p class="text-[18px] sm:text-[19px] font-medium text-white/85 leading-[1.66] break-words">
                    {{ __('home.atmosphere.subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8" data-reveal-group>
                <div class="overflow-hidden rounded-2xl border border-[#C5A880]/20 bg-[#211B19] group shadow-xl editorial-card" data-reveal>
                    <div class="aspect-4/3 overflow-hidden editorial-image-zoom">
                        <img
                            src="{{ $soundBathImg }}"
                            alt="Việt Hàn Âu Hàn Spa — Chuông Xoay Tây Tạng"
                            width="1800"
                            height="1200"
                            class="w-full h-full object-cover transition duration-700 group-hover:scale-105"
                            loading="lazy"
                        >
                    </div>
                    <div class="p-6 space-y-2">
                        <div class="font-semibold text-white text-xl sm:text-[22px]">{{ app()->getLocale() === 'vi' ? 'Liệu Pháp Chuông Xoay' : 'Singing Bowl Therapy' }}</div>
                        <p class="text-[17px] text-white/80 leading-[1.65]">{{ app()->getLocale() === 'vi' ? 'Sóng âm thanh chữa lành tái tạo năng lượng sâu thẳm.' : 'Healing acoustic soundscapes for deep revitalization.' }}</p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-[#C5A880]/20 bg-[#211B19] group shadow-xl editorial-card" data-reveal>
                    <div class="aspect-4/3 overflow-hidden editorial-image-zoom">
                        <img
                            src="{{ $headSpaImg }}"
                            alt="Việt Hàn Âu Hàn Spa — Gội Đầu Dưỡng Sinh Thảo Mộc"
                            width="1800"
                            height="1200"
                            class="w-full h-full object-cover transition duration-700 group-hover:scale-105"
                            loading="lazy"
                        >
                    </div>
                    <div class="p-6 space-y-2">
                        <div class="font-semibold text-white text-xl sm:text-[22px]">{{ app()->getLocale() === 'vi' ? 'Dưỡng Sinh Thảo Dược' : 'Herbal Head Spa' }}</div>
                        <p class="text-[17px] text-white/80 leading-[1.65]">{{ app()->getLocale() === 'vi' ? 'Nước nấu thảo mộc tươi thơm lành thư giãn tinh thần.' : 'Fresh botanical essences crafted for soothing wellness.' }}</p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-[#C5A880]/20 bg-[#211B19] group shadow-xl editorial-card" data-reveal>
                    <div class="aspect-4/3 overflow-hidden editorial-image-zoom">
                        <img
                            src="{{ $footRitualImg }}"
                            alt="Việt Hàn Âu Hàn Spa — Ngâm Chân Thảo Mộc"
                            width="1800"
                            height="1200"
                            class="w-full h-full object-cover transition duration-700 group-hover:scale-105"
                            loading="lazy"
                        >
                    </div>
                    <div class="p-6 space-y-2">
                        <div class="font-semibold text-white text-xl sm:text-[22px]">{{ app()->getLocale() === 'vi' ? 'Nghi Thức Ngâm Chân' : 'Foot Ritual Sanctuary' }}</div>
                        <p class="text-[17px] text-white/80 leading-[1.65]">{{ app()->getLocale() === 'vi' ? 'Muối khoáng và hoa tươi khai mở nguồn năng lượng an yên.' : 'A calming floral bath ritual restoring inner balance.' }}</p>
                    </div>
                </div>
            </div>
        </x-public.container>
    </section>

    {{-- 6. LATEST BLOG / JOURNAL SECTION (#blog-preview & #journal) --}}
    <section id="blog-preview" class="w-full bg-[#FAF7F2] py-16 sm:py-20 lg:py-24 border-b border-[#E8DFC8]/70 scroll-mt-20">
        <div id="journal"></div>
        <x-public.container>
            <div class="text-center space-y-3 max-w-3xl mx-auto mb-10 sm:mb-14" data-reveal>
                <span class="text-[15px] font-semibold tracking-[0.1em] uppercase text-[#B3956B]">
                    {{ __('home.journal.eyebrow') }}
                </span>
                <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold tracking-[-0.02em] text-[#5B1121] leading-[1.10] break-words [text-wrap:balance]">
                    {{ __('home.journal.title') }}
                </h2>
                <p class="text-[18px] sm:text-[19px] font-medium text-brand-text-secondary leading-[1.66] break-words">
                    {{ __('home.journal.subtitle') }}
                </p>
            </div>

            @if(!empty($latestPosts))
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8" data-reveal-group>
                    @foreach($latestPosts as $post)
                        <article class="flex flex-col rounded-2xl border border-[#E8DFC8] bg-white overflow-hidden shadow-xs hover:border-[#C5A880] hover:shadow-md transition duration-300 group editorial-card" data-reveal>
                            @if(!empty($post['media']['url']))
                                <div class="aspect-16/10 w-full overflow-hidden bg-brand-warm editorial-image-zoom">
                                    <img src="{{ $post['media']['url'] }}" alt="{{ $post['media']['alt'] }}" class="w-full h-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                                </div>
                            @endif

                            <div class="p-6 sm:p-8 flex-1 flex flex-col justify-between space-y-4">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-[15px] sm:text-[16px] text-brand-text-muted">
                                        @if(!empty($post['category_name']))
                                            <span class="font-semibold uppercase tracking-widest text-[#B3956B]">{{ $post['category_name'] }}</span>
                                        @endif

                                        @if(!empty($post['published_date']))
                                            <span class="text-brand-text-muted">{{ $post['published_date'] }}</span>
                                        @endif
                                    </div>

                                    <h3 class="text-xl sm:text-[22px] lg:text-[24px] font-semibold text-[#5B1121] group-hover:text-[#B3956B] transition leading-snug break-words">
                                        {{ $post['title'] }}
                                    </h3>

                                    @if(!empty($post['excerpt']))
                                        <p class="text-[17px] text-brand-text-secondary leading-[1.65] break-words">
                                            {{ $post['excerpt'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-3xl border border-[#E8DFC8] bg-white p-8 sm:p-12 text-center max-w-2xl mx-auto space-y-4 shadow-xs" data-reveal>
                    <p class="text-[17px] sm:text-[18px] text-brand-text-secondary leading-relaxed">
                        {{ __('home.journal.empty_notice') }}
                    </p>
                </div>
            @endif
        </x-public.container>
    </section>

    {{-- 7. CLOSING CTA & SANCTUARY INVITATION (#contact-preview) --}}
    <section id="contact-preview" class="w-full bg-gradient-to-br from-[#3D0B16] via-[#2A0810] to-[#181312] text-white py-16 sm:py-20 lg:py-24 scroll-mt-20">
        <x-public.container size="lg">
            <div class="rounded-3xl border border-[#C5A880]/30 bg-[#181312]/70 backdrop-blur-md p-8 sm:p-12 lg:p-16 text-center space-y-6 shadow-2xl" data-reveal>
                <span class="text-[15px] font-semibold tracking-[0.1em] uppercase text-[#C5A880]">
                    {{ __('home.cta.eyebrow') }}
                </span>

                <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold tracking-[-0.02em] text-white leading-[1.10] max-w-3xl mx-auto break-words [text-wrap:balance]">
                    {{ __('home.cta.title') }}
                </h2>

                <p class="text-[18px] sm:text-[19px] font-medium text-white/90 leading-[1.66] max-w-2xl mx-auto break-words">
                    {{ __('home.cta.subtitle') }}
                </p>

                <div class="pt-2 flex flex-wrap items-center justify-center gap-4">
                    @if(!empty($featuredServices))
                        <x-public.button as="a" href="#services-preview" variant="gold" size="lg" class="rounded-full px-8 py-4 bg-gradient-to-r from-[#C5A880] to-[#B3956B] text-[#181312] font-semibold shadow-xl hover:scale-105 transition duration-200 border-0">
                            {{ __('home.cta.button') }}
                        </x-public.button>
                    @else
                        <x-public.button as="a" href="#about-preview" variant="gold" size="lg" class="rounded-full px-8 py-4 bg-gradient-to-r from-[#C5A880] to-[#B3956B] text-[#181312] font-semibold shadow-xl hover:scale-105 transition duration-200 border-0">
                            {{ __('home.cta.button_empty') }}
                        </x-public.button>
                    @endif
                </div>

                @inject('settings', 'App\Services\Settings\SiteSettings')
                @php
                    $contactPhone = $settings->getPublic('contact.phone', '090 123 4567');
                    $contactEmail = $settings->getPublic('contact.email', 'info@viethanauhanspa.com');
                @endphp
                <div class="pt-6 border-t border-white/10 flex flex-wrap items-center justify-center gap-6 text-[15px] sm:text-[16px] text-white/80">
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
