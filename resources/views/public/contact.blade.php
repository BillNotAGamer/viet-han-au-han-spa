@php
    $locale = app()->getLocale();
    $bookingUrl = $locale === 'en' ? route('en.booking.create') : route('vi.booking.create');
    $lobbyImg = Vite::asset('resources/images/pages/contact/contact-reception-lobby.webp');
    $loungeImg = Vite::asset('resources/images/pages/contact/contact-consultation-lounge.webp');
@endphp

<x-layouts.public :title="$title" header-mode="solid" :contact-href="$bookingUrl" :seo="$seo ?? null">
    <article class="bg-[#FAF7F2] text-[#211B19]">
        {{-- 1. HERO SECTION --}}
        <section class="relative pt-28 sm:pt-32 lg:pt-36 pb-12 sm:pb-16 border-b border-[#E8DFC8]/70 bg-gradient-to-b from-[#F5EFEB] to-[#FAF7F2]">
            <x-public.container size="lg">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
                    <div class="lg:col-span-7 space-y-3.5" data-reveal>
                        <span class="inline-flex items-center gap-2 text-[14px] sm:text-[15px] font-semibold tracking-[0.1em] uppercase text-[#9B7B4F]">
                            <span class="w-8 h-px bg-[#C5A880]/60"></span>
                            {{ $content['hero']['eyebrow'] }}
                        </span>
                        <h1 class="text-3xl sm:text-5xl lg:text-[clamp(2.5rem,4vw,3.85rem)] font-semibold tracking-[-0.025em] text-[#5B1121] leading-[1.08] break-words [text-wrap:balance]">
                            {{ $content['hero']['title'] }}
                        </h1>
                        <p class="text-[18px] sm:text-[19px] font-medium text-[#554D4A] leading-[1.66] max-w-xl pt-1">
                            {{ $content['hero']['lead'] }}
                        </p>
                    </div>

                    <div class="lg:col-span-5" data-reveal-image>
                        <figure class="overflow-hidden rounded-2xl border border-[#E8DFC8] bg-white shadow-xl">
                            <img
                                src="{{ $lobbyImg }}"
                                alt="{{ $content['hero']['title'] }}"
                                width="1800"
                                height="1200"
                                class="w-full aspect-[4/3] object-cover"
                                loading="eager"
                                fetchpriority="high"
                            >
                        </figure>
                    </div>
                </div>
            </x-public.container>
        </section>

        {{-- 2. CONTACT ACTION AREA: Verified Values Only --}}
        <section class="py-12 sm:py-16 border-b border-[#E8DFC8]/60">
            <x-public.container size="lg">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8" data-reveal-group>
                    {{-- Phone Card --}}
                    <div class="rounded-2xl border border-[#E8DFC8] bg-white p-6 sm:p-7 shadow-xs flex flex-col justify-between transition-all duration-200 hover:shadow-md hover:border-[#C5A880]/70" data-reveal>
                        <div class="space-y-4">
                            <div class="w-12 h-12 rounded-xl bg-[#5B1121]/5 text-[#5B1121] flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                </svg>
                            </div>
                            <div>
                                <span class="text-[14px] font-semibold tracking-[0.08em] uppercase text-[#9B7B4F]">
                                    {{ $content['cards']['phone']['label'] }}
                                </span>
                                <div class="mt-2">
                                    <a
                                        href="{{ $content['cards']['phone']['href'] }}"
                                        class="text-2xl font-semibold text-[#5B1121] hover:text-[#C5A880] transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5B1121] rounded-sm"
                                    >
                                        {{ $content['cards']['phone']['value'] }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <p class="mt-5 text-[15px] sm:text-[16px] text-[#736965] border-t border-[#E8DFC8]/60 pt-3.5">
                            {{ $content['cards']['phone']['subtext'] }}
                        </p>
                    </div>

                    {{-- Email Card --}}
                    <div class="rounded-2xl border border-[#E8DFC8] bg-white p-6 sm:p-7 shadow-xs flex flex-col justify-between transition-all duration-200 hover:shadow-md hover:border-[#C5A880]/70" data-reveal>
                        <div class="space-y-4">
                            <div class="w-12 h-12 rounded-xl bg-[#5B1121]/5 text-[#5B1121] flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                            </div>
                            <div>
                                <span class="text-[14px] font-semibold tracking-[0.08em] uppercase text-[#9B7B4F]">
                                    {{ $content['cards']['email']['label'] }}
                                </span>
                                <div class="mt-2">
                                    <a
                                        href="{{ $content['cards']['email']['href'] }}"
                                        class="text-xl sm:text-2xl font-semibold text-[#5B1121] hover:text-[#C5A880] transition break-all focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5B1121] rounded-sm"
                                    >
                                        {{ $content['cards']['email']['value'] }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <p class="mt-5 text-[15px] sm:text-[16px] text-[#736965] border-t border-[#E8DFC8]/60 pt-3.5">
                            {{ $content['cards']['email']['subtext'] }}
                        </p>
                    </div>

                    {{-- Booking CTA Card --}}
                    <div class="rounded-2xl border border-[#C5A880]/50 bg-gradient-to-br from-[#FAF7F2] to-[#F5EFEB] p-6 sm:p-7 shadow-xs flex flex-col justify-between transition-all duration-200 hover:shadow-md hover:border-[#C5A880]" data-reveal>
                        <div class="space-y-4">
                            <div class="w-12 h-12 rounded-xl bg-[#C5A880]/20 text-[#5B1121] flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.253M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                            </div>
                            <div>
                                <span class="text-[14px] font-semibold tracking-[0.08em] uppercase text-[#9B7B4F]">
                                    {{ $content['cards']['booking']['label'] }}
                                </span>
                                <h3 class="mt-2 text-xl sm:text-[22px] font-semibold text-[#5B1121]">
                                    {{ $content['cards']['booking']['title'] }}
                                </h3>
                            </div>
                        </div>
                        <div class="mt-5 pt-3.5 border-t border-[#E8DFC8]/60">
                            <a
                                href="{{ $bookingUrl }}"
                                class="inline-flex w-full min-h-[46px] items-center justify-center rounded-lg bg-[#5B1121] px-5 py-2.5 text-[17px] sm:text-[18px] font-semibold text-white shadow-sm hover:bg-[#430C18] transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5B1121] focus-visible:ring-offset-2"
                            >
                                {{ $content['cards']['booking']['cta'] }}
                            </a>
                        </div>
                    </div>
                </div>
            </x-public.container>
        </section>

        {{-- 3. ATMOSPHERE / VISUAL SECTION --}}
        <section class="py-12 sm:py-16 border-b border-[#E8DFC8]/60 bg-[#F5EFEB]">
            <x-public.container size="lg">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
                    <div class="lg:col-span-6" data-reveal-image>
                        <figure class="overflow-hidden rounded-2xl border border-[#E8DFC8] bg-white shadow-lg">
                            <img
                                src="{{ $loungeImg }}"
                                alt="{{ $content['atmosphere']['title'] }}"
                                width="1600"
                                height="1067"
                                class="w-full aspect-[4/3] object-cover"
                                loading="lazy"
                            >
                        </figure>
                    </div>

                    <div class="lg:col-span-6 space-y-3.5" data-reveal>
                        <span class="text-[14px] sm:text-[15px] font-semibold tracking-[0.1em] uppercase text-[#9B7B4F]">
                            {{ $content['atmosphere']['badge'] }}
                        </span>
                        <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold tracking-[-0.02em] text-[#5B1121] leading-[1.10]">
                            {{ $content['atmosphere']['title'] }}
                        </h2>
                        <p class="text-[18px] sm:text-[19px] font-medium text-[#554D4A] leading-[1.66]">
                            {{ $content['atmosphere']['description'] }}
                        </p>
                    </div>
                </div>
            </x-public.container>
        </section>

        {{-- 4. BOOKING CTA --}}
        <section class="py-12 sm:py-16 bg-gradient-to-br from-[#3D0B16] via-[#2A0810] to-[#181312] text-white">
            <x-public.container size="lg">
                <div class="rounded-2xl border border-[#C5A880]/30 bg-[#181312]/70 backdrop-blur-sm p-8 sm:p-12 text-center max-w-4xl mx-auto space-y-5 shadow-2xl" data-reveal>
                    <span class="text-[14px] sm:text-[15px] font-semibold tracking-[0.1em] uppercase text-[#C5A880]">
                        {{ $content['cta']['badge'] }}
                    </span>
                    <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold tracking-[-0.02em] text-white leading-[1.10] break-words [text-wrap:balance]">
                        {{ $content['cta']['title'] }}
                    </h2>
                    <p class="text-[18px] sm:text-[19px] font-medium text-white/85 leading-[1.66] max-w-2xl mx-auto">
                        {{ $content['cta']['description'] }}
                    </p>

                    <div class="pt-3 flex items-center justify-center">
                        <a
                            href="{{ $bookingUrl }}"
                            class="inline-flex min-h-[48px] items-center justify-center rounded-full bg-gradient-to-r from-[#C5A880] to-[#B3956B] px-8 py-3.5 text-[17px] sm:text-[18px] font-semibold text-[#181312] shadow-xl hover:scale-105 transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C5A880] focus-visible:ring-offset-2 focus-visible:ring-offset-[#181312]"
                        >
                            {{ $content['cta']['button'] }}
                        </a>
                    </div>
                </div>
            </x-public.container>
        </section>
    </article>
</x-layouts.public>
