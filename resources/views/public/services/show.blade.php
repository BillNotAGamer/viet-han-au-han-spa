@php
    $isVi = app()->getLocale() === 'vi';
    $serviceFallbackImage = Vite::asset('resources/images/pages/services/services-signature-therapy.webp');
    $heroImage = !empty($service['hero_media']['url']) ? $service['hero_media']['url'] : $serviceFallbackImage;
    $heroAlt = $service['hero_media']['alt'] ?? '';
@endphp

<x-layouts.public variant="v2" :title="$title" header-mode="overlay" :contact-href="$contactHref" :seo="$seo ?? null">
    <article class="v2-service-detail">
        {{-- =====================================================================
           CINEMATIC SERVICE HERO
           ===================================================================== --}}
        <header class="v2-service-hero relative" aria-labelledby="service-title">
            <img src="{{ $heroImage }}" alt="{{ $heroAlt }}" class="v2-service-hero__image" loading="eager" fetchpriority="high">
            <div class="v2-service-hero__veil" aria-hidden="true"></div>
            
            <div class="v2-container v2-service-hero__inner flex flex-col justify-end pb-16 lg:pb-24">
                <div class="v2-service-hero__copy max-w-3xl" data-reveal>
                    @if(!empty($service['category_name']))
                        <div class="flex items-center gap-3 mb-4">
                            <x-public.v2.eyebrow inverse>{{ $service['category_name'] }}</x-public.v2.eyebrow>
                            <span class="w-8 h-px bg-[#C9A24D]/60" aria-hidden="true"></span>
                            <span class="font-serif italic text-xs text-[#C9A24D]">Signature Treatment</span>
                        </div>
                    @endif

                    <x-public.v2.display-heading level="1" size="xl" id="service-title" class="font-serif text-[#EFE8DC]">
                        {{ $service['name'] }}
                    </x-public.v2.display-heading>

                    @if(!empty($service['excerpt']))
                        <p class="v2-home-hero__copy text-base lg:text-lg text-[#EFE8DC]/85 mt-4 max-w-2xl leading-relaxed">
                            {{ $service['excerpt'] }}
                        </p>
                    @endif

                    <div class="mt-8">
                        <a href="{{ $contactHref }}" x-data="{}" data-booking-modal-trigger @click.prevent="$dispatch('open-booking-modal', { trigger: $el })" class="v2-button v2-button--primary">
                            {{ __('services.detail.inquiry_cta') }}
                        </a>
                    </div>
                </div>
            </div>
        </header>

        {{-- =====================================================================
           MAIN EDITORIAL CONTENT & STICKY TARIFF SIDEBAR
           ===================================================================== --}}
        <div class="v2-service-content v2-surface--paper py-16 lg:py-24 border-b border-[#E2D9CC]">
            <div class="v2-container">
                <div class="grid grid-cols-1 @if(!empty($service['prices'])) lg:grid-cols-12 @endif gap-12 lg:gap-16 items-start">
                    
                    {{-- Main Treatment Dossier (Overview, Process, Benefits) --}}
                    <div class="@if(!empty($service['prices'])) lg:col-span-7 xl:col-span-8 @else w-full max-w-4xl @endif space-y-16 lg:space-y-24">
                        
                        {{-- Overview Narrative --}}
                        @if(!empty($service['content']))
                            <section class="v2-section v2-service-overview p-0" aria-labelledby="service-overview-title">
                                <div class="space-y-4">
                                    <div class="flex items-center gap-3 mb-2">
                                        <x-public.v2.eyebrow>{{ __('services.detail.overview') }}</x-public.v2.eyebrow>
                                        <span class="w-6 h-px bg-[#C9A24D]" aria-hidden="true"></span>
                                    </div>
                                    <x-public.v2.display-heading id="service-overview-title" size="heading-lg" class="font-serif text-3xl lg:text-4xl text-[#181311]">
                                        {{ $service['name'] }}
                                    </x-public.v2.display-heading>
                                    <div class="v2-service-prose space-y-6 pt-4 text-[#594B42] text-base lg:text-lg leading-relaxed">
                                        @foreach($service['content'] as $paragraph)
                                            <p class="first-letter:text-3xl first-letter:font-serif first-letter:text-[#6A141A] first-letter:mr-1">{{ $paragraph }}</p>
                                        @endforeach
                                    </div>
                                </div>
                            </section>
                        @endif

                        {{-- Sensory Ritual Timeline (Process Steps) --}}
                        @if(!empty($service['process_steps']))
                            <section class="v2-section v2-service-process p-0 pt-8 border-t border-[#E2D9CC]" aria-labelledby="service-process-title">
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <x-public.v2.eyebrow>{{ __('services.detail.process') }}</x-public.v2.eyebrow>
                                        <span class="w-6 h-px bg-[#C9A24D]" aria-hidden="true"></span>
                                        <span class="font-serif italic text-xs text-[#C9A24D]">Sensory Ritual</span>
                                    </div>
                                    <x-public.v2.display-heading id="service-process-title" size="heading-lg" class="font-serif text-2xl lg:text-3xl text-[#181311] mb-8">
                                        {{ __('services.detail.process') }}
                                    </x-public.v2.display-heading>
                                </div>
                                <ol class="v2-service-process__list relative border-l border-[#C9A24D]/40 ml-4 sm:ml-6 space-y-8 pl-6 sm:pl-8 list-none" data-reveal-group>
                                    @foreach($service['process_steps'] as $step)
                                        <li class="relative group" data-reveal>
                                            <span class="absolute -left-[35px] sm:-left-[43px] top-0 flex items-center justify-center w-8 h-8 rounded-none border border-[#C9A24D] bg-[#FBF9F4] text-[#C9A24D] font-serif text-sm italic shadow-sm">
                                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                            </span>
                                            <div class="border border-[#E2D9CC] bg-[#FBF9F4] p-5 sm:p-6 group-hover:border-[#C9A24D]/60 transition-colors">
                                                @if($step['title'] !== '')
                                                    <h3 class="font-serif text-lg sm:text-xl text-[#181311] font-medium mb-2">{{ $step['title'] }}</h3>
                                                @endif
                                                @if($step['description'] !== '')
                                                    <p class="text-[#594B42] text-sm leading-relaxed m-0">{{ $step['description'] }}</p>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            </section>
                        @endif

                        {{-- Therapeutic Benefits Grid --}}
                        @if(!empty($service['benefits']))
                            <section class="v2-section v2-service-benefits-section p-0 pt-8 border-t border-[#E2D9CC]" aria-labelledby="service-benefits-title">
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <x-public.v2.eyebrow>{{ __('services.detail.benefits') }}</x-public.v2.eyebrow>
                                        <span class="w-6 h-px bg-[#C9A24D]" aria-hidden="true"></span>
                                    </div>
                                    <x-public.v2.display-heading id="service-benefits-title" size="heading-lg" class="font-serif text-2xl lg:text-3xl text-[#181311] mb-8">
                                        {{ __('services.detail.benefits') }}
                                    </x-public.v2.display-heading>
                                </div>
                                <div class="v2-service-benefits grid grid-cols-1 sm:grid-cols-2 gap-6" data-reveal-group>
                                    @foreach($service['benefits'] as $benefit)
                                        <article class="border border-[#E2D9CC] bg-[#FBF9F4] p-6 flex flex-col justify-between" data-reveal>
                                            <span class="font-serif italic text-2xl text-[#C9A24D] mb-4 block">
                                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                            </span>
                                            <div>
                                                @if($benefit['title'] !== '')
                                                    <h3 class="font-serif text-lg text-[#181311] font-medium mb-2 leading-snug">{{ $benefit['title'] }}</h3>
                                                @endif
                                                @if($benefit['description'] !== '')
                                                    <p class="text-[#594B42] text-sm leading-relaxed m-0">{{ $benefit['description'] }}</p>
                                                @endif
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    </div>

                    {{-- Sticky 5-Star Spa Tariff Card --}}
                    @if(!empty($service['prices']))
                        <div class="lg:col-span-5 xl:col-span-4 lg:sticky lg:top-28">
                            <section class="v2-service-essentials border border-[#C9A24D]/50 bg-[#F5F0E8] p-6 sm:p-8 rounded-none shadow-none" aria-labelledby="service-prices-title">
                                <div class="flex items-center justify-between pb-4 border-b border-[#E2D9CC] mb-6">
                                    <h2 id="service-prices-title" class="v2-service-essentials__title font-serif text-xl sm:text-2xl text-[#181311] m-0">
                                        {{ __('services.detail.prices') }}
                                    </h2>
                                    <span class="text-[11px] uppercase tracking-widest text-[#C9A24D] font-mono font-medium">Tarif Spa</span>
                                </div>

                                <div class="v2-service-price-list flex flex-col divide-y divide-[#E2D9CC]/80">
                                    @foreach($service['prices'] as $price)
                                        <div class="v2-service-price py-4 flex items-baseline justify-between gap-4">
                                            <div>
                                                @if(!empty($price['label']))
                                                    <h3 class="font-serif text-base text-[#181311] font-medium m-0">{{ $price['label'] }}</h3>
                                                @endif
                                                @if($price['duration_minutes'] !== null)
                                                    <p class="text-xs text-[#594B42] uppercase tracking-wider mt-1 m-0">
                                                        {{ __('services.detail.duration', ['minutes' => $price['duration_minutes']]) }}
                                                    </p>
                                                @endif
                                            </div>
                                            <strong class="font-serif text-lg sm:text-xl text-[#6A141A] font-semibold whitespace-nowrap">
                                                {{ $price['price_display'] }}
                                            </strong>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-8 pt-6 border-t border-[#E2D9CC]">
                                    <a href="{{ $contactHref }}" x-data="{}" data-booking-modal-trigger @click.prevent="$dispatch('open-booking-modal', { trigger: $el })" class="v2-button v2-button--primary w-full text-center justify-center">
                                        {{ __('services.detail.inquiry_cta') }}
                                    </a>
                                    <p class="text-center text-xs text-[#594B42] mt-3 mb-0">
                                        {{ $isVi ? 'Tư vấn phác đồ riêng & giữ chỗ ưu tiên' : 'Tailored consultation & priority reservation' }}
                                    </p>
                                </div>
                            </section>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- =====================================================================
           TREATMENT GALLERY — Curated Visual Exhibition
           ===================================================================== --}}
        @if(!empty($service['gallery']))
            <section class="v2-section v2-surface--ivory py-16 lg:py-24 border-b border-[#E2D9CC]" aria-labelledby="service-gallery-title">
                <div class="v2-container">
                    <header class="v2-section-heading v2-section-heading--compact mb-12">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <x-public.v2.eyebrow>{{ __('services.detail.gallery') }}</x-public.v2.eyebrow>
                                <span class="w-6 h-px bg-[#C9A24D]" aria-hidden="true"></span>
                            </div>
                            <x-public.v2.display-heading id="service-gallery-title" size="heading-lg" class="font-serif text-2xl lg:text-3xl text-[#181311]">
                                {{ __('services.detail.gallery') }}
                            </x-public.v2.display-heading>
                        </div>
                    </header>
                    <div class="v2-service-gallery grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" data-reveal-group>
                        @foreach($service['gallery'] as $image)
                            <div class="border border-[#E2D9CC] overflow-hidden bg-[#FBF9F4]">
                                <x-public.v2.media-frame :src="$image['url']" :alt="$image['alt']" role="gallery" :caption="$image['caption'] ?? null" data-reveal class="aspect-[4/3] w-full object-cover" />
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- =====================================================================
           FREQUENTLY ASKED QUESTIONS — Serene Accordion
           ===================================================================== --}}
        @if(!empty($service['faqs']))
            <section class="v2-section v2-service-faq v2-surface--paper py-16 lg:py-24 border-b border-[#E2D9CC]" aria-labelledby="service-faq-title">
                <div class="v2-container max-w-4xl">
                    <div class="mb-10 text-center">
                        <div class="flex items-center justify-center gap-3 mb-2">
                            <x-public.v2.eyebrow>{{ __('services.detail.faqs') }}</x-public.v2.eyebrow>
                        </div>
                        <x-public.v2.display-heading id="service-faq-title" size="heading-lg" class="font-serif text-2xl lg:text-3xl text-[#181311]">
                            {{ __('services.detail.faqs') }}
                        </x-public.v2.display-heading>
                    </div>
                    <div class="v2-service-faq__list divide-y divide-[#E2D9CC] border-y border-[#E2D9CC]">
                        @foreach($service['faqs'] as $faq)
                            <details class="group py-5">
                                <summary class="flex items-center justify-between gap-6 cursor-pointer list-none font-serif text-lg sm:text-xl text-[#181311] group-open:text-[#6A141A] transition-colors">
                                    <span>{{ $faq['question'] }}</span>
                                    <span class="w-6 h-6 flex items-center justify-center text-[#C9A24D] font-mono text-xl transition-transform group-open:rotate-45" aria-hidden="true">+</span>
                                </summary>
                                @if($faq['answer'] !== '')
                                    <p class="text-[#594B42] text-sm lg:text-base leading-relaxed mt-4 mb-2 pr-8">{{ $faq['answer'] }}</p>
                                @endif
                            </details>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- =====================================================================
           CONCIERGE INQUIRY CTA — Private Reservation Invitation
           ===================================================================== --}}
        <section class="v2-service-cta v2-surface--deep py-20 lg:py-28 text-center" aria-labelledby="service-cta-title">
            <div class="v2-container v2-service-cta__inner max-w-2xl mx-auto" data-reveal>
                <x-public.v2.eyebrow inverse>{{ __('common.brand_name') }}</x-public.v2.eyebrow>
                <x-public.v2.display-heading id="service-cta-title" size="heading-lg" class="font-serif text-3xl lg:text-4xl text-[#EFE8DC] mt-4">
                    {{ __('services.detail.inquiry_title') }}
                </x-public.v2.display-heading>
                <div class="w-12 h-px bg-[#C9A24D] mx-auto my-6"></div>
                <p class="text-[#EFE8DC]/80 text-base lg:text-lg leading-relaxed mb-8">{{ __('services.detail.inquiry_copy') }}</p>
                <a href="{{ $contactHref }}" x-data="{}" data-booking-modal-trigger @click.prevent="$dispatch('open-booking-modal', { trigger: $el })" class="v2-button v2-button--inverse">
                    {{ __('services.detail.inquiry_cta') }}
                </a>
            </div>
        </section>
    </article>
</x-layouts.public>
