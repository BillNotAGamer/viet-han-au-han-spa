@php
    $locale = app()->getLocale();
    $bookingUrl = $locale === 'en' ? route('en.booking.create') : route('vi.booking.create');
    $contactUrl = $locale === 'en' ? route('en.contact') : route('vi.contact');
    $heroImg = Vite::asset('resources/images/pages/about/about-hero-treatment-space.webp');
    $teamImg = Vite::asset('resources/images/pages/about/about-therapist-team.webp');
    $sanctuaryImg = Vite::asset('resources/images/pages/about/about-sound-bath-sanctuary.webp');
    $headSpaImg = Vite::asset('resources/images/pages/about/about-herbal-head-spa.webp');
    $facialImg = Vite::asset('resources/images/pages/about/about-facial-therapy-care.webp');
@endphp

<x-layouts.public :title="$title" header-mode="solid" :contact-href="$bookingUrl" :seo="$seo ?? null">
    <article class="bg-[#FAF7F2] text-[#211B19]">
        {{-- 1. HERO SECTION: Editorial, Photography-First --}}
        <section class="relative pt-28 sm:pt-32 lg:pt-36 pb-12 sm:pb-16 border-b border-[#E8DFC8]/70 bg-gradient-to-b from-[#F5EFEB] to-[#FAF7F2]">
            <x-public.container size="lg">
                <div class="max-w-3xl space-y-3.5" data-reveal>
                    <span class="inline-flex items-center gap-2 text-[14px] sm:text-[15px] font-semibold tracking-[0.1em] uppercase text-[#9B7B4F]">
                        <span class="w-8 h-px bg-[#C5A880]/60"></span>
                        {{ $content['hero']['eyebrow'] }}
                    </span>
                    <h1 class="text-3xl sm:text-5xl lg:text-[clamp(2.5rem,4vw,3.85rem)] font-semibold tracking-[-0.025em] text-[#5B1121] leading-[1.08] break-words [text-wrap:balance]">
                        {{ $content['hero']['title'] }}
                    </h1>
                    <p class="text-[18px] sm:text-[19px] font-medium text-[#554D4A] leading-[1.66] max-w-2xl pt-1">
                        {{ $content['hero']['lead'] }}
                    </p>
                </div>

                {{-- Hero Visual (Connected Composition) --}}
                <figure class="mt-7 sm:mt-9 overflow-hidden rounded-2xl border border-[#E8DFC8] bg-white shadow-xl" data-reveal data-reveal-image>
                    <img
                        src="{{ $heroImg }}"
                        alt="{{ $content['hero']['title'] }}"
                        width="1800"
                        height="1200"
                        class="w-full aspect-[16/9] sm:aspect-[21/9] object-cover object-center"
                        loading="eager"
                        fetchpriority="high"
                    >
                </figure>
            </x-public.container>
        </section>

        {{-- 2. BRAND STORY: Two-Column Editorial Narrative --}}
        <section class="py-14 sm:py-18 lg:py-22 border-b border-[#E8DFC8]/60">
            <x-public.container size="lg">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
                    {{-- Left Column: Authentic Team Photography --}}
                    <div class="lg:col-span-6 order-2 lg:order-1" data-reveal>
                        <div class="relative">
                            <div class="overflow-hidden rounded-2xl border border-[#E8DFC8] bg-white shadow-lg" data-reveal-image>
                                <img
                                    src="{{ $teamImg }}"
                                    alt="{{ $content['team']['title'] }}"
                                    width="1600"
                                    height="1067"
                                    class="w-full aspect-[4/3] object-cover"
                                    loading="lazy"
                                >
                            </div>
                            <div class="mt-3 px-2 flex items-center justify-between text-[15px] sm:text-[16px] text-[#736965]">
                                <span>{{ $content['team']['title'] }}</span>
                                <span class="italic text-[#9B7B4F]">Việt Hàn Âu Hàn Spa</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Brand Narrative --}}
                    <div class="lg:col-span-6 order-1 lg:order-2 space-y-5" data-reveal>
                        <div class="space-y-2.5">
                            <span class="text-[14px] sm:text-[15px] font-semibold tracking-[0.1em] uppercase text-[#9B7B4F]">
                                {{ $content['story']['badge'] }}
                            </span>
                            <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold tracking-[-0.02em] text-[#5B1121] leading-[1.10]">
                                {{ $content['story']['title'] }}
                            </h2>
                        </div>

                        <div class="space-y-4 text-[18px] sm:text-[19px] font-medium text-[#554D4A] leading-[1.66]">
                            @foreach($content['story']['paragraphs'] as $paragraph)
                                <p class="break-words">{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-public.container>
        </section>

        {{-- 3. VALUES / EXPERIENCE: 4 Restrained Core Principles --}}
        <section class="py-14 sm:py-18 lg:py-22 bg-[#F5EFEB] border-b border-[#E8DFC8]/70">
            <x-public.container size="lg">
                <div class="max-w-3xl text-center mx-auto space-y-3 mb-8 sm:mb-12" data-reveal>
                    <span class="text-[14px] sm:text-[15px] font-semibold tracking-[0.1em] uppercase text-[#9B7B4F]">
                        {{ $content['values']['badge'] }}
                    </span>
                    <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold tracking-[-0.02em] text-[#5B1121] leading-[1.10]">
                        {{ $content['values']['title'] }}
                    </h2>
                    <p class="text-[18px] sm:text-[19px] font-medium text-[#554D4A] leading-[1.66]">
                        {{ $content['values']['subtitle'] }}
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8" data-reveal-group>
                    @foreach($content['values']['items'] as $value)
                        <div class="rounded-xl border border-[#E8DFC8] bg-white p-6 sm:p-7 shadow-xs flex flex-col justify-between transition-all duration-200 hover:shadow-md hover:border-[#C5A880]/70" data-reveal>
                            <div>
                                <span class="text-2xl sm:text-3xl font-semibold text-[#C5A880]">
                                    {{ $value['number'] }}
                                </span>
                                <h3 class="mt-3 text-xl sm:text-[22px] font-semibold text-[#5B1121] leading-snug">
                                    {{ $value['title'] }}
                                </h3>
                                <p class="mt-2.5 text-[17px] text-[#554D4A] leading-[1.65]">
                                    {{ $value['description'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-public.container>
        </section>

        {{-- 4. SPA ATMOSPHERE / GALLERY: Asymmetric Editorial Layout --}}
        <section class="py-14 sm:py-18 lg:py-22 border-b border-[#E8DFC8]/60">
            <x-public.container size="lg">
                <div class="max-w-3xl space-y-3 mb-8 sm:mb-12" data-reveal>
                    <span class="text-[14px] sm:text-[15px] font-semibold tracking-[0.1em] uppercase text-[#9B7B4F]">
                        {{ $content['atmosphere']['badge'] }}
                    </span>
                    <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold tracking-[-0.02em] text-[#5B1121] leading-[1.10]">
                        {{ $content['atmosphere']['title'] }}
                    </h2>
                    <p class="text-[18px] sm:text-[19px] font-medium text-[#554D4A] leading-[1.66]">
                        {{ $content['atmosphere']['subtitle'] }}
                    </p>
                </div>

                {{-- Asymmetric Photo Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-10 items-stretch" data-reveal-group>
                    {{-- Primary Feature: Sound Bath Sanctuary --}}
                    <div class="md:col-span-7 flex flex-col" data-reveal>
                        <figure class="group overflow-hidden rounded-2xl border border-[#E8DFC8] bg-white shadow-md flex-1 flex flex-col">
                            <div class="overflow-hidden flex-1" data-reveal-image>
                                <img
                                    src="{{ $sanctuaryImg }}"
                                    alt="{{ $content['atmosphere']['items'][0]['title'] }}"
                                    width="1600"
                                    height="1067"
                                    class="w-full h-full min-h-[300px] object-cover transition duration-300 group-hover:scale-[1.02]"
                                    loading="lazy"
                                >
                            </div>
                            <figcaption class="p-6 bg-white border-t border-[#E8DFC8]/60">
                                <h3 class="text-xl sm:text-[22px] font-semibold text-[#5B1121]">
                                    {{ $content['atmosphere']['items'][0]['title'] }}
                                </h3>
                                <p class="mt-2 text-[17px] text-[#554D4A] leading-[1.65]">
                                    {{ $content['atmosphere']['items'][0]['caption'] }}
                                </p>
                            </figcaption>
                        </figure>
                    </div>

                    {{-- Secondary Stack: Head Spa & Facial Care --}}
                    <div class="md:col-span-5 flex flex-col gap-8" data-reveal>
                        {{-- Head Spa --}}
                        <figure class="group overflow-hidden rounded-2xl border border-[#E8DFC8] bg-white shadow-md">
                            <div class="overflow-hidden" data-reveal-image>
                                <img
                                    src="{{ $headSpaImg }}"
                                    alt="{{ $content['atmosphere']['items'][1]['title'] }}"
                                    width="1600"
                                    height="1067"
                                    class="w-full aspect-[16/10] object-cover transition duration-300 group-hover:scale-[1.02]"
                                    loading="lazy"
                                >
                            </div>
                            <figcaption class="p-5 bg-white border-t border-[#E8DFC8]/60">
                                <h3 class="text-lg sm:text-xl font-semibold text-[#5B1121]">
                                    {{ $content['atmosphere']['items'][1]['title'] }}
                                </h3>
                                <p class="mt-1 text-[16px] text-[#554D4A] leading-relaxed">
                                    {{ $content['atmosphere']['items'][1]['caption'] }}
                                </p>
                            </figcaption>
                        </figure>

                        {{-- Facial Care --}}
                        <figure class="group overflow-hidden rounded-2xl border border-[#E8DFC8] bg-white shadow-md">
                            <div class="overflow-hidden" data-reveal-image>
                                <img
                                    src="{{ $facialImg }}"
                                    alt="{{ $content['atmosphere']['items'][2]['title'] }}"
                                    width="1600"
                                    height="1067"
                                    class="w-full aspect-[16/10] object-cover transition duration-300 group-hover:scale-[1.02]"
                                    loading="lazy"
                                >
                            </div>
                            <figcaption class="p-5 bg-white border-t border-[#E8DFC8]/60">
                                <h3 class="text-lg sm:text-xl font-semibold text-[#5B1121]">
                                    {{ $content['atmosphere']['items'][2]['title'] }}
                                </h3>
                                <p class="mt-1 text-[16px] text-[#554D4A] leading-relaxed">
                                    {{ $content['atmosphere']['items'][2]['caption'] }}
                                </p>
                            </figcaption>
                        </figure>
                    </div>
                </div>
            </x-public.container>
        </section>

        {{-- 5. CLOSING CTA: Booking & Inquiries --}}
        <section class="py-12 sm:py-16 bg-gradient-to-br from-[#3D0B16] via-[#2A0810] to-[#181312] text-white">
            <x-public.container size="lg">
                <div class="rounded-2xl border border-[#C5A880]/30 bg-[#181312]/70 backdrop-blur-sm p-8 sm:p-12 text-center max-w-4xl mx-auto space-y-5 shadow-2xl" data-reveal>
                    <span class="text-[14px] sm:text-[15px] font-semibold tracking-[0.1em] uppercase text-[#C5A880]">
                        Việt Hàn Âu Hàn Spa
                    </span>
                    <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold tracking-[-0.02em] text-white leading-[1.10] break-words [text-wrap:balance]">
                        {{ $content['cta']['title'] }}
                    </h2>
                    <p class="text-[18px] sm:text-[19px] font-medium text-white/85 leading-[1.66] max-w-2xl mx-auto">
                        {{ $content['cta']['description'] }}
                    </p>

                    <div class="pt-3 flex flex-wrap items-center justify-center gap-4">
                        <a
                            href="{{ $bookingUrl }}"
                            class="inline-flex min-h-[48px] items-center justify-center rounded-full bg-gradient-to-r from-[#C5A880] to-[#B3956B] px-8 py-3.5 text-[17px] sm:text-[18px] font-semibold text-[#181312] shadow-xl hover:scale-105 transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C5A880] focus-visible:ring-offset-2 focus-visible:ring-offset-[#181312]"
                        >
                            {{ $content['cta']['button_booking'] }}
                        </a>
                        <a
                            href="{{ $contactUrl }}"
                            class="inline-flex min-h-[48px] items-center justify-center rounded-full border border-[#C5A880]/60 px-7 py-3 text-[17px] sm:text-[18px] font-semibold text-white/90 hover:bg-white/10 transition duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#181312]"
                        >
                            {{ $content['cta']['button_contact'] }}
                        </a>
                    </div>
                </div>
            </x-public.container>
        </section>
    </article>
</x-layouts.public>
