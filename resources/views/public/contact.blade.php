@php
    $locale = app()->getLocale();
    $bookingUrl = $locale === 'en' ? route('en.booking.create') : route('vi.booking.create');
    $lobbyImg = Vite::asset('resources/images/pages/contact/contact-reception-lobby.webp');
    $mapEmbedUrl = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1162.253074056725!2d106.6994668582927!3d10.791759610991093!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317528b57e3038f9%3A0xdabf60e42628bc2f!2zMTE1IE5ndXnhu4VuIELhu4luaCBLaGnDqm0sIFTDom4gxJDhu4tuaCwgSOG7kyBDaMOtIE1pbmgsIFZp4buHdCBOYW0!5e1!3m2!1svi!2s!4v1790164676789!5m2!1svi!2s';
@endphp

<x-layouts.public :title="$title" header-mode="solid" :contact-href="$bookingUrl" :seo="$seo ?? null" variant="v2">
    <article>
        {{-- =====================================================================
           PAGE HERO — Sanctuary Concierge & Location Header
           ===================================================================== --}}
        <section class="v2-page-hero v2-page-hero--media bg-[#F5F0E8] border-b border-[#E2D9CC] py-16 lg:py-24">
            <x-public.v2.container>
                <div class="v2-page-hero__grid">
                    <div class="v2-page-hero__copy">
                        <div class="flex items-center gap-3 mb-4">
                            <x-public.v2.eyebrow>{{ $content['hero']['eyebrow'] }}</x-public.v2.eyebrow>
                            <span class="w-8 h-px bg-[#C9A24D]/60" aria-hidden="true"></span>
                            <span class="font-serif italic text-xs text-[#C9A24D]">Concierge & Localisation</span>
                        </div>
                        <x-public.v2.display-heading level="1" size="xl" class="v2-primary-page-hero__title font-serif text-[#181311]">
                            {{ $content['hero']['title'] }}
                        </x-public.v2.display-heading>
                        <div class="w-12 h-px bg-[#C9A24D] my-6"></div>
                        <x-public.v2.editorial-copy size="lg" class="text-[#594B42] leading-relaxed">
                            {{ $content['hero']['lead'] }}
                        </x-public.v2.editorial-copy>
                    </div>

                    <div class="v2-page-hero__media-frame">
                        <div class="border border-[#E2D9CC] p-2 bg-[#FBF9F4] shadow-sm">
                            <x-public.v2.media-frame
                                :src="$lobbyImg"
                                :alt="$content['hero']['title']"
                                role="landscape"
                                priority
                                class="v2-page-hero__media aspect-[4/3] w-full object-cover"
                            />
                        </div>
                    </div>
                </div>
            </x-public.v2.container>
        </section>

        {{-- =====================================================================
           ARCHITECTURAL CONTACT LEDGER — 3 Column Directory
           ===================================================================== --}}
        <x-public.v2.section surface="paper" class="v2-contact-section py-16 lg:py-24 border-b border-[#E2D9CC]">
            <x-public.v2.container>
                <div class="v2-contact-methods grid grid-cols-1 md:grid-cols-3 border border-[#E2D9CC] bg-[#FBF9F4] divide-y md:divide-y-0 md:divide-x divide-[#E2D9CC]">
                    
                    {{-- 01. Phone Concierge --}}
                    <article class="p-8 lg:p-10 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-3 mb-5">
                                <span class="font-serif italic text-2xl text-[#C9A24D]">01</span>
                                <p class="v2-type-eyebrow uppercase tracking-[0.14em] text-[11px] font-medium text-[#6E675F] m-0">{{ $content['cards']['phone']['label'] }}</p>
                            </div>
                            <a href="{{ $content['cards']['phone']['href'] }}" class="v2-contact-methods__value font-serif text-2xl sm:text-3xl text-[#181311] hover:text-[#6A141A] transition-colors font-medium block my-3">
                                {{ $content['cards']['phone']['value'] }}
                            </a>
                        </div>
                        <p class="v2-type-body text-[#594B42] text-xs sm:text-sm leading-relaxed mt-6 m-0 border-t border-[#E2D9CC]/60 pt-4">
                            {{ $content['cards']['phone']['subtext'] }}
                        </p>
                    </article>

                    {{-- 02. Sanctuary Address --}}
                    <article class="p-8 lg:p-10 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-3 mb-5">
                                <span class="font-serif italic text-2xl text-[#C9A24D]">02</span>
                                <p class="v2-type-eyebrow uppercase tracking-[0.14em] text-[11px] font-medium text-[#6E675F] m-0">{{ $content['cards']['address']['label'] }}</p>
                            </div>
                            <address class="v2-contact-methods__address not-italic my-3">
                                @foreach($content['cards']['address']['lines'] as $line)
                                    <span class="block">{{ $line }}</span>
                                @endforeach
                            </address>
                        </div>
                        <p class="v2-type-body text-[#594B42] text-xs sm:text-sm leading-relaxed mt-6 m-0 border-t border-[#E2D9CC]/60 pt-4">
                            {{ $content['cards']['address']['subtext'] }}
                        </p>
                    </article>

                    {{-- 03. Digital Concierge --}}
                    <article class="p-8 lg:p-10 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-3 mb-5">
                                <span class="font-serif italic text-2xl text-[#C9A24D]">03</span>
                                <p class="v2-type-eyebrow uppercase tracking-[0.14em] text-[11px] font-medium text-[#6E675F] m-0">{{ $content['cards']['social']['label'] }}</p>
                            </div>
                            <div class="v2-contact-methods__social flex flex-col gap-2.5 my-3">
                                <a href="{{ $content['cards']['social']['zalo']['href'] }}" target="_blank" rel="noopener noreferrer" class="group">
                                    <span class="font-normal">{{ $content['cards']['social']['zalo']['label'] }}</span>
                                    <span class="text-xs text-[#C9A24D] font-mono group-hover:translate-x-1.5 transition-transform" aria-hidden="true">&rarr;</span>
                                </a>
                                <a href="{{ $content['cards']['social']['facebook']['href'] }}" target="_blank" rel="noopener noreferrer" class="group">
                                    <span class="font-normal">{{ $content['cards']['social']['facebook']['label'] }}</span>
                                    <span class="text-xs text-[#C9A24D] font-mono group-hover:translate-x-1.5 transition-transform" aria-hidden="true">&rarr;</span>
                                </a>
                            </div>
                        </div>
                        <p class="v2-type-body text-[#594B42] text-xs sm:text-sm leading-relaxed mt-6 m-0 border-t border-[#E2D9CC]/60 pt-4">
                            {{ $content['cards']['social']['subtext'] }}
                        </p>
                    </article>

                </div>
            </x-public.v2.container>
        </x-public.v2.section>

        {{-- =====================================================================
           SANCTUARY LOCATION MAP & ATMOSPHERE
           ===================================================================== --}}
        <x-public.v2.section surface="ivory" class="py-16 lg:py-24 border-b border-[#E2D9CC]">
            <x-public.v2.container>
                <div class="v2-editorial-split v2-editorial-split--reverse grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                    <div class="v2-editorial-split__media v2-contact-map-frame lg:col-span-7 border border-[#E2D9CC] bg-[#FBF9F4] p-2" data-image-reveal>
                        <iframe
                            src="{{ $mapEmbedUrl }}"
                            title="{{ $content['atmosphere']['map_title'] }}"
                            width="600"
                            height="450"
                            style="border:0;"
                            allowfullscreen
                            loading="lazy"
                            referrerpolicy="strict-origin-when-cross-origin"
                            class="w-full h-80 sm:h-96"
                        ></iframe>
                    </div>
                    <div class="v2-editorial-split__copy lg:col-span-5">
                        <div class="flex items-center gap-3 mb-2">
                            <x-public.v2.eyebrow>{{ $content['atmosphere']['badge'] }}</x-public.v2.eyebrow>
                            <span class="w-6 h-px bg-[#C9A24D]" aria-hidden="true"></span>
                        </div>
                        <x-public.v2.display-heading level="2" size="heading-lg" class="font-serif text-3xl lg:text-4xl text-[#181311] mb-6">
                            {{ $content['atmosphere']['title'] }}
                        </x-public.v2.display-heading>
                        <div class="w-12 h-px bg-[#C9A24D] my-6"></div>
                        <p class="v2-type-body-lg text-[#594B42] text-base lg:text-lg leading-relaxed m-0">
                            {{ $content['atmosphere']['description'] }}
                        </p>
                    </div>
                </div>
            </x-public.v2.container>
        </x-public.v2.section>

        {{-- =====================================================================
           CONCIERGE CTA
           ===================================================================== --}}
        <section class="v2-concierge-cta v2-surface--deep py-20 lg:py-28 text-center bg-[#181311] text-[#EFE8DC]">
            <x-public.v2.container size="reading" class="max-w-2xl mx-auto">
                <x-public.v2.eyebrow inverse>{{ $content['cta']['badge'] }}</x-public.v2.eyebrow>
                <x-public.v2.display-heading level="2" size="heading-lg" class="font-serif text-3xl lg:text-4xl text-[#EFE8DC] mt-4">
                    {{ $content['cta']['title'] }}
                </x-public.v2.display-heading>
                <div class="w-12 h-px bg-[#C9A24D] mx-auto my-6"></div>
                <p class="v2-type-body-lg text-[#EFE8DC]/80 text-base lg:text-lg leading-relaxed mb-8">
                    {{ $content['cta']['description'] }}
                </p>
                <x-public.v2.button
                    :href="$bookingUrl"
                    variant="inverse"
                    x-data="{}"
                    data-booking-modal-trigger
                    x-on:click.prevent="$dispatch('open-booking-modal', { trigger: $el })"
                >{{ $content['cta']['button'] }}</x-public.v2.button>
            </x-public.v2.container>
        </section>
    </article>
</x-layouts.public>
