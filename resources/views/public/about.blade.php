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
        <section class="relative pt-32 sm:pt-36 lg:pt-40 pb-16 sm:pb-24 border-b border-[#E8DFC8]/70 bg-gradient-to-b from-[#F5EFEB] to-[#FAF7F2]">
            <x-public.container size="lg">
                <div class="max-w-3xl space-y-4">
                    <span class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold tracking-widest uppercase text-[#9B7B4F]">
                        <span class="w-8 h-px bg-[#C5A880]/60"></span>
                        {{ $content['hero']['eyebrow'] }}
                    </span>
                    <h1 class="font-serif text-3xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-[#5B1121] leading-tight break-words [text-wrap:balance]">
                        {{ $content['hero']['title'] }}
                    </h1>
                    <p class="text-base sm:text-lg text-[#554D4A] leading-relaxed max-w-2xl pt-2">
                        {{ $content['hero']['lead'] }}
                    </p>
                </div>

                {{-- Hero Visual --}}
                <figure class="mt-10 sm:mt-14 overflow-hidden rounded-2xl border border-[#E8DFC8] bg-white shadow-xl">
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
        <section class="py-16 sm:py-24 lg:py-32 border-b border-[#E8DFC8]/60">
            <x-public.container size="lg">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                    {{-- Left Column: Authentic Team Photography --}}
                    <div class="lg:col-span-6 order-2 lg:order-1">
                        <div class="relative">
                            <div class="overflow-hidden rounded-2xl border border-[#E8DFC8] bg-white shadow-lg">
                                <img
                                    src="{{ $teamImg }}"
                                    alt="{{ $content['team']['title'] }}"
                                    width="1600"
                                    height="1067"
                                    class="w-full aspect-[4/3] object-cover"
                                    loading="lazy"
                                >
                            </div>
                            <div class="mt-4 px-2 flex items-center justify-between text-xs sm:text-sm text-[#736965]">
                                <span>{{ $content['team']['title'] }}</span>
                                <span class="font-serif italic text-[#9B7B4F]">Việt Hàn Âu Hàn Spa</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Brand Narrative --}}
                    <div class="lg:col-span-6 order-1 lg:order-2 space-y-6">
                        <div class="space-y-3">
                            <span class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-[#9B7B4F]">
                                {{ $content['story']['badge'] }}
                            </span>
                            <h2 class="font-serif text-2xl sm:text-4xl font-bold tracking-tight text-[#5B1121] leading-snug">
                                {{ $content['story']['title'] }}
                            </h2>
                        </div>

                        <div class="space-y-4 text-base sm:text-lg text-[#554D4A] leading-relaxed">
                            @foreach($content['story']['paragraphs'] as $paragraph)
                                <p class="break-words">{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-public.container>
        </section>

        {{-- 3. VALUES / EXPERIENCE: 4 Restrained Core Principles --}}
        <section class="py-16 sm:py-24 lg:py-32 bg-[#F5EFEB] border-b border-[#E8DFC8]/70">
            <x-public.container size="lg">
                <div class="max-w-3xl text-center mx-auto space-y-4 mb-12 sm:mb-16">
                    <span class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-[#9B7B4F]">
                        {{ $content['values']['badge'] }}
                    </span>
                    <h2 class="font-serif text-2xl sm:text-4xl font-bold tracking-tight text-[#5B1121]">
                        {{ $content['values']['title'] }}
                    </h2>
                    <p class="text-base sm:text-lg text-[#554D4A] leading-relaxed">
                        {{ $content['values']['subtitle'] }}
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                    @foreach($content['values']['items'] as $value)
                        <div class="rounded-xl border border-[#E8DFC8] bg-white p-7 sm:p-8 shadow-xs flex flex-col justify-between transition-all duration-200 hover:shadow-md hover:border-[#C5A880]/70">
                            <div>
                                <span class="font-serif text-2xl sm:text-3xl font-bold text-[#C5A880]">
                                    {{ $value['number'] }}
                                </span>
                                <h3 class="mt-3 font-serif text-lg sm:text-xl font-bold text-[#5B1121]">
                                    {{ $value['title'] }}
                                </h3>
                                <p class="mt-3 text-sm sm:text-base text-[#554D4A] leading-relaxed">
                                    {{ $value['description'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-public.container>
        </section>

        {{-- 4. SPA ATMOSPHERE / GALLERY: Asymmetric Editorial Layout --}}
        <section class="py-16 sm:py-24 lg:py-32 border-b border-[#E8DFC8]/60">
            <x-public.container size="lg">
                <div class="max-w-3xl space-y-4 mb-12 sm:mb-16">
                    <span class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-[#9B7B4F]">
                        {{ $content['atmosphere']['badge'] }}
                    </span>
                    <h2 class="font-serif text-2xl sm:text-4xl font-bold tracking-tight text-[#5B1121]">
                        {{ $content['atmosphere']['title'] }}
                    </h2>
                    <p class="text-base sm:text-lg text-[#554D4A] leading-relaxed">
                        {{ $content['atmosphere']['subtitle'] }}
                    </p>
                </div>

                {{-- Asymmetric Photo Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-10 items-stretch">
                    {{-- Primary Feature: Sound Bath Sanctuary --}}
                    <div class="md:col-span-7 flex flex-col">
                        <figure class="group overflow-hidden rounded-2xl border border-[#E8DFC8] bg-white shadow-md flex-1 flex flex-col">
                            <div class="overflow-hidden flex-1">
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
                                <h3 class="font-serif text-lg sm:text-xl font-bold text-[#5B1121]">
                                    {{ $content['atmosphere']['items'][0]['title'] }}
                                </h3>
                                <p class="mt-2 text-sm text-[#554D4A] leading-relaxed">
                                    {{ $content['atmosphere']['items'][0]['caption'] }}
                                </p>
                            </figcaption>
                        </figure>
                    </div>

                    {{-- Secondary Stack: Head Spa & Facial Care --}}
                    <div class="md:col-span-5 flex flex-col gap-8">
                        {{-- Head Spa --}}
                        <figure class="group overflow-hidden rounded-2xl border border-[#E8DFC8] bg-white shadow-md">
                            <div class="overflow-hidden">
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
                                <h3 class="font-serif text-base sm:text-lg font-bold text-[#5B1121]">
                                    {{ $content['atmosphere']['items'][1]['title'] }}
                                </h3>
                                <p class="mt-1 text-xs sm:text-sm text-[#554D4A] leading-relaxed">
                                    {{ $content['atmosphere']['items'][1]['caption'] }}
                                </p>
                            </figcaption>
                        </figure>

                        {{-- Facial Care --}}
                        <figure class="group overflow-hidden rounded-2xl border border-[#E8DFC8] bg-white shadow-md">
                            <div class="overflow-hidden">
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
                                <h3 class="font-serif text-base sm:text-lg font-bold text-[#5B1121]">
                                    {{ $content['atmosphere']['items'][2]['title'] }}
                                </h3>
                                <p class="mt-1 text-xs sm:text-sm text-[#554D4A] leading-relaxed">
                                    {{ $content['atmosphere']['items'][2]['caption'] }}
                                </p>
                            </figcaption>
                        </figure>
                    </div>
                </div>
            </x-public.container>
        </section>

        {{-- 5. CLOSING CTA: Booking & Inquiries --}}
        <section class="py-16 sm:py-24 bg-gradient-to-br from-[#3D0B16] via-[#2A0810] to-[#181312] text-white">
            <x-public.container size="lg">
                <div class="rounded-2xl border border-[#C5A880]/30 bg-[#181312]/70 backdrop-blur-sm p-8 sm:p-14 text-center max-w-4xl mx-auto space-y-6 shadow-2xl">
                    <span class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-[#C5A880]">
                        Việt Hàn Âu Hàn Spa
                    </span>
                    <h2 class="font-serif text-2xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-white leading-tight break-words [text-wrap:balance]">
                        {{ $content['cta']['title'] }}
                    </h2>
                    <p class="text-base sm:text-lg text-white/80 font-light leading-relaxed max-w-2xl mx-auto">
                        {{ $content['cta']['description'] }}
                    </p>

                    <div class="pt-4 flex flex-wrap items-center justify-center gap-4">
                        <a
                            href="{{ $bookingUrl }}"
                            class="inline-flex min-h-[48px] items-center justify-center rounded-full bg-gradient-to-r from-[#C5A880] to-[#B3956B] px-8 py-3.5 text-sm sm:text-base font-bold text-[#181312] shadow-xl hover:scale-105 transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C5A880] focus-visible:ring-offset-2 focus-visible:ring-offset-[#181312]"
                        >
                            {{ $content['cta']['button_booking'] }}
                        </a>
                        <a
                            href="{{ $contactUrl }}"
                            class="inline-flex min-h-[48px] items-center justify-center rounded-full border border-[#C5A880]/60 px-7 py-3 text-sm sm:text-base font-semibold text-white/90 hover:bg-white/10 transition duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#181312]"
                        >
                            {{ $content['cta']['button_contact'] }}
                        </a>
                    </div>
                </div>
            </x-public.container>
        </section>
    </article>
</x-layouts.public>
