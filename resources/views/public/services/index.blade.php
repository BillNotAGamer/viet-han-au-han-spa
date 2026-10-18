<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null" variant="v2">
    {{-- =========================================================================
       PAGE HEADER — Haute Spa Treatment Menu Header
       ========================================================================= --}}
    <section class="v2-page-hero v2-page-hero--intro bg-[#F5F0E8] border-b border-[#E2D9CC]">
        <x-public.v2.container>
            <div class="v2-page-hero__copy max-w-3xl">
                <div class="flex items-center gap-3 mb-4">
                    <x-public.v2.eyebrow>{{ __('services.index.eyebrow') }}</x-public.v2.eyebrow>
                    <span class="w-8 h-px bg-[#C9A24D]/60" aria-hidden="true"></span>
                    <span class="font-serif italic text-xs text-[#C9A24D]">Menu des Soins</span>
                </div>
                <x-public.v2.display-heading level="1" size="xl" class="v2-primary-page-hero__title font-serif text-[#181311]">
                    {{ $heading }}
                </x-public.v2.display-heading>
                <div class="w-12 h-px bg-[#C9A24D] my-6"></div>
                <x-public.v2.editorial-copy size="lg" class="text-[#594B42] leading-relaxed">
                    {{ $intro }}
                </x-public.v2.editorial-copy>
            </div>
        </x-public.v2.container>
    </section>

    {{-- =========================================================================
       TREATMENT CATALOG LEDGER — Haute Spa Treatment Menu
       ========================================================================= --}}
    <x-public.v2.section surface="paper" class="v2-listing-section py-16 lg:py-24">
        <x-public.v2.container>
            @if($services->isNotEmpty())
                <div class="v2-treatment-menu flex flex-col gap-8 lg:gap-10" data-reveal-group>
                    @foreach($services as $service)
                        <article @class([
                            'v2-treatment-row group relative border border-[#E2D9CC] bg-[#FBF9F4] hover:border-[#C9A24D]/70 transition-all duration-500 rounded-none shadow-none p-6 sm:p-8 lg:p-10',
                            'v2-treatment-row--without-media' => empty($service['media']['url'])
                        ]) data-reveal>
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                                {{-- Index numeral & Main Content --}}
                                <div class="@if(!empty($service['media']['url'])) lg:col-span-7 @else lg:col-span-12 @endif flex flex-col justify-between h-full">
                                    <div>
                                        <div class="flex items-center justify-between gap-4 mb-4">
                                            <p class="v2-treatment-row__number font-serif italic text-2xl lg:text-3xl text-[#C9A24D] m-0" aria-hidden="true">
                                                {{ str_pad((string) (($services->firstItem() ?? 1) + $loop->index), 2, '0', STR_PAD_LEFT) }}
                                            </p>
                                            @if(!empty($service['category_name']))
                                                <span class="v2-type-eyebrow uppercase tracking-[0.2em] text-[#C9A24D] text-xs font-semibold px-2.5 py-0.5 border border-[#C9A24D]/30 bg-[#F5F0E8]">
                                                    {{ $service['category_name'] }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="v2-treatment-row__body">
                                            <x-public.v2.display-heading level="2" size="heading-lg" class="font-serif text-2xl lg:text-3xl text-[#181311] leading-snug mb-3 group-hover:text-[#6A141A] transition-colors">
                                                <a href="{{ $service['url'] }}" class="hover:text-[#6A141A] transition-colors">{{ $service['name'] }}</a>
                                            </x-public.v2.display-heading>

                                            @if(!empty($service['excerpt']))
                                                <p class="v2-type-body text-[#594B42] text-sm lg:text-base leading-relaxed mb-6 max-w-2xl">
                                                    {{ $service['excerpt'] }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Treatment Tariff Bar --}}
                                    <div class="v2-treatment-row__footer border-t border-[#E2D9CC] pt-5 mt-4 flex flex-wrap items-center justify-between gap-4">
                                        @if(!empty($service['price_summary']))
                                            <p class="v2-type-body-sm m-0 text-sm text-[#594B42] flex items-center gap-2">
                                                <span class="text-xs uppercase tracking-wider text-[#594B42]/80">{{ __('services.index.from_price') }}</span>
                                                <strong class="font-serif text-lg lg:text-xl text-[#6A141A] font-semibold">{{ $service['price_summary']['price_display'] }}</strong>
                                                @if(!empty($service['price_summary']['duration_minutes']))
                                                    <span aria-hidden="true" class="text-[#E2D9CC]">&middot;</span>
                                                    <span class="text-xs tracking-wider uppercase font-medium text-[#594B42]">
                                                        {{ __('services.detail.duration', ['minutes' => $service['price_summary']['duration_minutes']]) }}
                                                    </span>
                                                @endif
                                            </p>
                                        @endif

                                        <x-public.v2.button :href="$service['url']" variant="text" class="text-xs uppercase tracking-widest font-semibold text-[#181311] hover:text-[#6A141A]">
                                            {{ __('services.index.details') }} <span aria-hidden="true">&rarr;</span>
                                        </x-public.v2.button>
                                    </div>
                                </div>

                                {{-- Image Frame --}}
                                @if(!empty($service['media']['url']))
                                    <div class="lg:col-span-5">
                                        <a href="{{ $service['url'] }}" class="v2-treatment-row__media block overflow-hidden border border-[#E2D9CC]/80" aria-label="{{ $service['name'] }}">
                                            <x-public.v2.media-frame
                                                :src="$service['media']['url']"
                                                :alt="$service['media']['alt']"
                                                role="landscape"
                                                class="aspect-[16/11] w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                            />
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                {{ $services->links('components.public.v2.pagination') }}
            @else
                <div class="v2-empty-state border border-[#E2D9CC] p-12 lg:p-16 bg-[#FBF9F4] text-center" role="status">
                    <p class="v2-type-body-lg text-[#594B42] m-0">{{ $emptyCopy }}</p>
                </div>
            @endif
        </x-public.v2.container>
    </x-public.v2.section>
</x-layouts.public>
