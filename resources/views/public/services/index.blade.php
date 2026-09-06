<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null">
    {{-- Hero Header --}}
    <section class="bg-gradient-to-b from-[#F5EFEB] to-[#FAF7F2] pt-28 sm:pt-32 lg:pt-36 pb-12 sm:pb-14 border-b border-[#E8DFC8]/70">
        <x-public.container size="lg">
            <div class="max-w-3xl space-y-3.5" data-reveal>
                <span class="inline-flex items-center gap-2 text-[14px] sm:text-[15px] font-semibold tracking-[0.1em] uppercase text-[#9B7B4F]">
                    <span class="w-6 h-px bg-[#C5A880]/70"></span>
                    {{ __('services.index.eyebrow') }}
                </span>
                <h1 class="text-3xl sm:text-5xl lg:text-[clamp(2.5rem,4vw,3.85rem)] font-semibold tracking-[-0.025em] text-[#5B1121] leading-[1.08] break-words [text-wrap:balance]">
                    {{ __('services.index.title') }}
                </h1>
                <p class="text-[18px] sm:text-[19px] font-medium text-[#554D4A] leading-[1.66] break-words max-w-2xl pt-1">
                    {{ __('services.index.intro') }}
                </p>
            </div>
        </x-public.container>
    </section>

    {{-- Services Listing Grid --}}
    <section class="bg-[#FAF7F2] py-12 sm:py-16 lg:py-20">
        <x-public.container>
            @if($services->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8 lg:gap-9" data-reveal-group>
                    @foreach($services as $service)
                        <article class="group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-[#E8DFC8] bg-white shadow-xs transition duration-300 hover:border-[#C5A880] hover:shadow-lg editorial-card" data-reveal>
                            <a href="{{ $service['url'] }}" class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5B1121] focus-visible:ring-offset-4 focus-visible:ring-offset-[#FAF7F2]">
                                @if(!empty($service['media']['url']))
                                    <div class="aspect-16/10 overflow-hidden bg-brand-warm editorial-image-zoom">
                                        <img src="{{ $service['media']['url'] }}" alt="{{ $service['media']['alt'] }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                                    </div>
                                @else
                                    <div class="aspect-16/10 bg-[#211B19] flex items-center justify-center px-8 text-center relative overflow-hidden">
                                        <div class="absolute inset-0 bg-radial from-[#5B1121]/30 to-transparent"></div>
                                        <span class="text-2xl font-semibold text-[#C5A880] break-words relative z-10">{{ $service['name'] }}</span>
                                    </div>
                                @endif
                            </a>

                            <div class="flex flex-1 flex-col justify-between p-6 sm:p-7">
                                <div class="space-y-3">
                                    @if(!empty($service['category_name']))
                                        <p class="text-[14px] sm:text-[15px] font-semibold uppercase tracking-widest text-[#9B7B4F]">
                                            {{ $service['category_name'] }}
                                        </p>
                                    @endif

                                    <h2 class="text-xl sm:text-[22px] lg:text-[24px] font-semibold leading-[1.2] tracking-tight text-[#5B1121] break-words">
                                        <a href="{{ $service['url'] }}" class="hover:text-[#B3956B] transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5B1121] focus-visible:ring-offset-4">
                                            {{ $service['name'] }}
                                        </a>
                                    </h2>

                                    @if(!empty($service['excerpt']))
                                        <p class="text-[17px] leading-[1.65] text-[#554D4A] break-words">
                                            {{ $service['excerpt'] }}
                                        </p>
                                    @endif
                                </div>

                                <div class="mt-6 flex items-center justify-between gap-4 border-t border-[#E8DFC8] pt-5">
                                    @if(!empty($service['price_summary']))
                                        <p class="text-[16px] text-[#736965]">
                                            {{ __('services.index.from_price') }}
                                            <span class="font-semibold text-[#5B1121]">{{ $service['price_summary']['price_display'] }}</span>
                                        </p>
                                    @endif

                                    <a href="{{ $service['url'] }}" class="ml-auto inline-flex items-center gap-1 text-[15px] font-semibold uppercase tracking-widest text-[#5B1121] hover:text-[#B3956B] transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5B1121] focus-visible:ring-offset-4">
                                        <span>{{ __('services.index.details') }}</span>
                                        <span aria-hidden="true">&rarr;</span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-12" data-reveal>
                    {{ $services->links() }}
                </div>
            @else
                <div class="mx-auto max-w-2xl rounded-2xl border border-[#E8DFC8] bg-white px-8 py-14 text-center shadow-xs" data-reveal>
                    <div class="w-14 h-14 mx-auto rounded-full bg-[#FAF7F2] border border-[#E8DFC8] flex items-center justify-center text-[#9B7B4F] mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <p class="text-xl font-semibold text-[#5B1121] break-words">
                        {{ __('services.index.empty') }}
                    </p>
                </div>
            @endif
        </x-public.container>
    </section>
</x-layouts.public>
