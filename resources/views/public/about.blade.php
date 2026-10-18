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

<x-layouts.public :title="$title" header-mode="solid" :contact-href="$bookingUrl" :seo="$seo ?? null" variant="v2">
    <article>
        {{-- =====================================================================
           BRAND HERITAGE HERO
           ===================================================================== --}}
        <section class="v2-about-hero relative">
            <img src="{{ $heroImg }}" alt="{{ $content['hero']['title'] }}" class="v2-about-hero__image" width="1800" height="1200" loading="eager" fetchpriority="high">
            <div class="v2-about-hero__veil" aria-hidden="true"></div>
            
            <x-public.v2.container class="relative z-10">
                <div class="v2-about-hero__copy max-w-3xl pt-28 pb-16 lg:pb-24">
                    <div class="flex items-center gap-3 mb-4">
                        <x-public.v2.eyebrow inverse>{{ $content['hero']['eyebrow'] }}</x-public.v2.eyebrow>
                        <span class="w-8 h-px bg-[#C9A24D]/60" aria-hidden="true"></span>
                        <span class="font-serif italic text-xs text-[#C9A24D]">Monographie de Marque</span>
                    </div>

                    <x-public.v2.display-heading level="1" size="xl" class="v2-primary-page-hero__title font-serif text-[#EFE8DC]">
                        {{ $content['hero']['title'] }}
                    </x-public.v2.display-heading>

                    <div class="w-12 h-px bg-[#C9A24D] my-6"></div>

                    <p class="v2-type-body-lg text-base lg:text-lg text-[#EFE8DC]/85 leading-relaxed max-w-2xl m-0">
                        {{ $content['hero']['lead'] }}
                    </p>
                </div>
            </x-public.v2.container>
        </section>

        {{-- =====================================================================
           BRAND MANIFESTO & PHILOSOPHY
           ===================================================================== --}}
        <x-public.v2.section surface="paper" spacing="spacious" class="py-16 lg:py-24 border-b border-[#E2D9CC]">
            <x-public.v2.container>
                <div class="v2-about-manifesto grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                    <div class="v2-about-manifesto__mark lg:col-span-4 flex justify-center p-6 border border-[#E2D9CC] bg-[#FBF9F4]" aria-hidden="true">
                        <x-public.v2.logo variant="colored" decorative loading="lazy" class="w-48 sm:w-56 h-auto" />
                    </div>

                    <div class="v2-about-manifesto__copy lg:col-span-8">
                        <div class="flex items-center gap-3 mb-2">
                            <x-public.v2.eyebrow>{{ $content['story']['badge'] }}</x-public.v2.eyebrow>
                            <span class="w-6 h-px bg-[#C9A24D]" aria-hidden="true"></span>
                        </div>
                        <x-public.v2.display-heading level="2" size="display-lg" class="font-serif text-3xl sm:text-4xl text-[#181311] mb-6">
                            {{ $content['story']['title'] }}
                        </x-public.v2.display-heading>
                        <div class="v2-prose v2-prose--lead space-y-6 text-[#594B42] text-base lg:text-lg leading-relaxed">
                            @foreach($content['story']['paragraphs'] as $paragraph)
                                <p class="first-letter:text-3xl first-letter:font-serif first-letter:text-[#6A141A] first-letter:mr-1">{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-public.v2.container>
        </x-public.v2.section>

        {{-- =====================================================================
           MASTER THERAPIST TEAM
           ===================================================================== --}}
        <x-public.v2.section surface="ivory" class="py-16 lg:py-24 border-b border-[#E2D9CC]">
            <x-public.v2.container>
                <div class="v2-editorial-split grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                    <div class="lg:col-span-6">
                        <div class="border border-[#E2D9CC] p-2 bg-[#FBF9F4]">
                            <x-public.v2.media-frame
                                :src="$teamImg"
                                :alt="$content['team']['title']"
                                role="landscape"
                                class="v2-editorial-split__media aspect-[4/3] w-full object-cover"
                            />
                        </div>
                    </div>

                    <div class="v2-editorial-split__copy lg:col-span-6">
                        <div class="flex items-center gap-3 mb-2">
                            <x-public.v2.eyebrow>{{ $content['team']['badge'] }}</x-public.v2.eyebrow>
                            <span class="w-6 h-px bg-[#C9A24D]" aria-hidden="true"></span>
                            <span class="font-serif italic text-xs text-[#C9A24D]">Maîtres Thérapeutes</span>
                        </div>
                        <x-public.v2.display-heading level="2" size="heading-lg" class="font-serif text-3xl lg:text-4xl text-[#181311] mb-6">
                            {{ $content['team']['title'] }}
                        </x-public.v2.display-heading>
                        <div class="w-12 h-px bg-[#C9A24D] my-6"></div>
                        <p class="v2-type-body-lg text-[#594B42] text-base lg:text-lg leading-relaxed m-0">
                            {{ $content['team']['description'] }}
                        </p>
                    </div>
                </div>
            </x-public.v2.container>
        </x-public.v2.section>

        {{-- =====================================================================
           CORE VALUES — Architectural Pillars
           ===================================================================== --}}
        <x-public.v2.section surface="paper" class="py-16 lg:py-24 border-b border-[#E2D9CC]">
            <x-public.v2.container>
                <div class="v2-section-heading mb-12">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <x-public.v2.eyebrow>{{ $content['values']['badge'] }}</x-public.v2.eyebrow>
                            <span class="w-6 h-px bg-[#C9A24D]" aria-hidden="true"></span>
                        </div>
                        <x-public.v2.display-heading level="2" size="heading-lg" class="font-serif text-3xl lg:text-4xl text-[#181311]">
                            {{ $content['values']['title'] }}
                        </x-public.v2.display-heading>
                    </div>
                    <p class="v2-type-body-lg text-[#594B42] text-base lg:text-lg mt-3">{{ $content['values']['subtitle'] }}</p>
                </div>

                <div class="v2-narrative-list grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($content['values']['items'] as $value)
                        <article class="border border-[#E2D9CC] bg-[#FBF9F4] p-6 sm:p-8 flex flex-col justify-between">
                            <span class="font-serif italic text-3xl text-[#C9A24D] mb-4 block" aria-hidden="true">{{ $value['number'] }}</span>
                            <div>
                                <h3 class="font-serif text-xl text-[#181311] font-medium mb-3 leading-snug">{{ $value['title'] }}</h3>
                                <p class="text-[#594B42] text-sm leading-relaxed m-0">{{ $value['description'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </x-public.v2.container>
        </x-public.v2.section>

        {{-- =====================================================================
           SANCTUARY ATMOSPHERE GALLERY
           ===================================================================== --}}
        <x-public.v2.section surface="espresso" class="v2-about-atmosphere py-20 lg:py-28 bg-[#181311] text-[#EFE8DC] border-b border-[#E2D9CC]/20">
            <x-public.v2.container size="wide">
                <div class="v2-about-atmosphere__heading max-w-3xl mb-12">
                    <div class="flex items-center gap-3 mb-2">
                        <x-public.v2.eyebrow inverse>{{ $content['atmosphere']['badge'] }}</x-public.v2.eyebrow>
                        <span class="w-6 h-px bg-[#C9A24D]" aria-hidden="true"></span>
                    </div>
                    <x-public.v2.display-heading level="2" size="display-lg" class="font-serif text-3xl sm:text-4xl lg:text-5xl text-[#EFE8DC]">
                        {{ $content['atmosphere']['title'] }}
                    </x-public.v2.display-heading>
                    <p class="v2-type-body-lg text-[#EFE8DC]/80 text-base lg:text-lg mt-4 leading-relaxed">{{ $content['atmosphere']['subtitle'] }}</p>
                </div>

                <div class="v2-about-gallery grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="border border-[#E2D9CC]/20 bg-[#1F1917] p-2">
                        <x-public.v2.media-frame :src="$sanctuaryImg" :alt="$content['atmosphere']['items'][0]['title']" :caption="$content['atmosphere']['items'][0]['caption']" role="landscape" class="aspect-[4/3] w-full object-cover" />
                    </div>
                    <div class="border border-[#E2D9CC]/20 bg-[#1F1917] p-2">
                        <x-public.v2.media-frame :src="$headSpaImg" :alt="$content['atmosphere']['items'][1]['title']" :caption="$content['atmosphere']['items'][1]['caption']" role="portrait" class="aspect-[4/3] w-full object-cover" />
                    </div>
                    <div class="border border-[#E2D9CC]/20 bg-[#1F1917] p-2">
                        <x-public.v2.media-frame :src="$facialImg" :alt="$content['atmosphere']['items'][2]['title']" :caption="$content['atmosphere']['items'][2]['caption']" role="portrait" class="aspect-[4/3] w-full object-cover" />
                    </div>
                </div>
            </x-public.v2.container>
        </x-public.v2.section>

        {{-- =====================================================================
           CONCIERGE CTA
           ===================================================================== --}}
        <section class="v2-concierge-cta v2-surface--deep py-20 lg:py-28 text-center bg-[#181311] text-[#EFE8DC]">
            <x-public.v2.container size="reading" class="max-w-2xl mx-auto">
                <x-public.v2.eyebrow inverse>{{ __('common.brand_name') }}</x-public.v2.eyebrow>
                <x-public.v2.display-heading level="2" size="heading-lg" class="font-serif text-3xl lg:text-4xl text-[#EFE8DC] mt-4">
                    {{ $content['cta']['title'] }}
                </x-public.v2.display-heading>
                <div class="w-12 h-px bg-[#C9A24D] mx-auto my-6"></div>
                <p class="v2-type-body-lg text-[#EFE8DC]/80 text-base lg:text-lg leading-relaxed mb-8">
                    {{ $content['cta']['description'] }}
                </p>
                <div class="v2-concierge-cta__actions flex flex-wrap items-center justify-center gap-4">
                    <x-public.v2.button :href="$bookingUrl" variant="inverse" x-data="{}" data-booking-modal-trigger @click.prevent="$dispatch('open-booking-modal', { trigger: $el })">
                        {{ $content['cta']['button_booking'] }}
                    </x-public.v2.button>
                    <x-public.v2.button :href="$contactUrl" variant="text" class="text-xs uppercase tracking-widest text-[#EFE8DC] hover:text-[#C9A24D]">
                        {{ $content['cta']['button_contact'] }} <span aria-hidden="true">&rarr;</span>
                    </x-public.v2.button>
                </div>
            </x-public.v2.container>
        </section>
    </article>
</x-layouts.public>
