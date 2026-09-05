<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null">
    <section class="bg-brand-ivory pt-32 sm:pt-36 lg:pt-40 pb-16 sm:pb-20 border-b border-brand-border">
        <x-public.container size="lg">
            <div class="max-w-3xl space-y-5">
                <span class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-[#B3956B]">
                    {{ __('services.index.eyebrow') }}
                </span>
                <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl font-bold tracking-normal text-[#5B1121] leading-tight break-words [text-wrap:balance]">
                    {{ __('services.index.title') }}
                </h1>
                <p class="text-base sm:text-lg text-brand-text-secondary leading-relaxed break-words max-w-2xl">
                    {{ __('services.index.intro') }}
                </p>
            </div>
        </x-public.container>
    </section>

    <section class="bg-[#FAF7F2] py-14 sm:py-20 lg:py-24">
        <x-public.container>
            @if($services->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-7 lg:gap-8">
                    @foreach($services as $service)
                        <article class="group flex min-w-0 flex-col overflow-hidden rounded-sm border border-[#E8DFC8] bg-white shadow-xs transition duration-200 hover:border-[#C5A880] hover:shadow-md">
                            <a href="{{ $service['url'] }}" class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5B1121] focus-visible:ring-offset-4 focus-visible:ring-offset-[#FAF7F2]">
                                @if(!empty($service['media']['url']))
                                    <div class="aspect-16/10 overflow-hidden bg-brand-warm">
                                        <img src="{{ $service['media']['url'] }}" alt="{{ $service['media']['alt'] }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                                    </div>
                                @else
                                    <div class="aspect-16/10 bg-[#211B19] flex items-center justify-center px-8 text-center">
                                        <span class="font-serif text-2xl font-bold text-[#C5A880] break-words">{{ $service['name'] }}</span>
                                    </div>
                                @endif
                            </a>

                            <div class="flex flex-1 flex-col justify-between p-6 sm:p-7">
                                <div class="space-y-3">
                                    @if(!empty($service['category_name']))
                                        <p class="text-xs font-semibold uppercase tracking-widest text-[#B3956B]">
                                            {{ $service['category_name'] }}
                                        </p>
                                    @endif

                                    <h2 class="font-serif text-2xl font-bold leading-snug tracking-normal text-[#5B1121] break-words">
                                        <a href="{{ $service['url'] }}" class="hover:text-[#B3956B] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5B1121] focus-visible:ring-offset-4">
                                            {{ $service['name'] }}
                                        </a>
                                    </h2>

                                    @if(!empty($service['excerpt']))
                                        <p class="text-sm sm:text-base leading-relaxed text-brand-text-secondary break-words">
                                            {{ $service['excerpt'] }}
                                        </p>
                                    @endif
                                </div>

                                <div class="mt-6 flex items-center justify-between gap-4 border-t border-brand-border pt-5">
                                    @if(!empty($service['price_summary']))
                                        <p class="text-sm text-brand-text-muted">
                                            {{ __('services.index.from_price') }}
                                            <span class="font-semibold text-[#5B1121]">{{ $service['price_summary']['price_display'] }}</span>
                                        </p>
                                    @endif

                                    <a href="{{ $service['url'] }}" class="ml-auto inline-flex items-center text-xs font-semibold uppercase tracking-widest text-[#5B1121] hover:text-[#B3956B] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5B1121] focus-visible:ring-offset-4">
                                        {{ __('services.index.details') }}
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $services->links() }}
                </div>
            @else
                <div class="mx-auto max-w-2xl border border-[#E8DFC8] bg-white px-8 py-12 text-center shadow-xs">
                    <p class="font-serif text-2xl font-bold text-[#5B1121] break-words">
                        {{ __('services.index.empty') }}
                    </p>
                </div>
            @endif
        </x-public.container>
    </section>
</x-layouts.public>
