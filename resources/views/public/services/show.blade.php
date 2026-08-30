<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref">
    <article>
        <section class="relative bg-[#181312] pt-28 sm:pt-32 lg:pt-36 text-white">
            @if(!empty($service['hero_media']['url']))
                <div class="absolute inset-0">
                    <img src="{{ $service['hero_media']['url'] }}" alt="{{ $service['hero_media']['alt'] }}" class="h-full w-full object-cover" loading="eager">
                    <div class="absolute inset-0 bg-[#181312]/58"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-[#181312]/90 via-[#181312]/62 to-[#181312]/18"></div>
                </div>
            @endif

            <x-public.container size="lg">
                <div class="relative z-10 flex min-h-[520px] max-w-3xl flex-col justify-end pb-16 sm:pb-20 lg:pb-24">
                    @if(!empty($service['category_name']))
                        <p class="mb-4 text-xs sm:text-sm font-semibold uppercase tracking-widest text-[#C5A880]">
                            {{ $service['category_name'] }}
                        </p>
                    @endif

                    <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight tracking-normal break-words [text-wrap:balance]">
                        {{ $service['name'] }}
                    </h1>

                    @if(!empty($service['excerpt']))
                        <p class="mt-5 max-w-2xl text-base sm:text-lg leading-relaxed text-white/82 break-words">
                            {{ $service['excerpt'] }}
                        </p>
                    @endif
                </div>
            </x-public.container>
        </section>

        <section class="bg-[#FAF7F2] py-14 sm:py-20 lg:py-24">
            <x-public.container size="lg">
                <div class="grid grid-cols-1 gap-12 lg:grid-cols-[minmax(0,1fr)_360px] lg:gap-16">
                    <div class="min-w-0 space-y-12">
                        @if(!empty($service['content']))
                            <section class="space-y-5">
                                <h2 class="font-serif text-3xl sm:text-4xl font-bold text-[#5B1121] tracking-normal">
                                    {{ __('services.detail.overview') }}
                                </h2>
                                <div class="space-y-5 text-base sm:text-lg leading-relaxed text-brand-text-secondary">
                                    @foreach($service['content'] as $paragraph)
                                        <p class="break-words">{{ $paragraph }}</p>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @if(!empty($service['benefits']))
                            <section class="space-y-6">
                                <h2 class="font-serif text-3xl sm:text-4xl font-bold text-[#5B1121] tracking-normal">
                                    {{ __('services.detail.benefits') }}
                                </h2>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    @foreach($service['benefits'] as $benefit)
                                        <div class="border border-[#E8DFC8] bg-white p-6 shadow-xs">
                                            @if($benefit['title'] !== '')
                                                <h3 class="font-serif text-xl font-bold text-[#5B1121] break-words">{{ $benefit['title'] }}</h3>
                                            @endif
                                            @if($benefit['description'] !== '')
                                                <p class="mt-2 text-sm leading-relaxed text-brand-text-secondary break-words">{{ $benefit['description'] }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @if(!empty($service['process_steps']))
                            <section class="space-y-6">
                                <h2 class="font-serif text-3xl sm:text-4xl font-bold text-[#5B1121] tracking-normal">
                                    {{ __('services.detail.process') }}
                                </h2>
                                <div class="space-y-4">
                                    @foreach($service['process_steps'] as $step)
                                        <div class="grid grid-cols-[44px_minmax(0,1fr)] gap-4 border-b border-[#E8DFC8] pb-5">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#5B1121] text-sm font-semibold text-white">
                                                {{ $loop->iteration }}
                                            </div>
                                            <div class="min-w-0">
                                                @if($step['title'] !== '')
                                                    <h3 class="font-serif text-xl font-bold text-[#5B1121] break-words">{{ $step['title'] }}</h3>
                                                @endif
                                                @if($step['description'] !== '')
                                                    <p class="mt-2 text-sm sm:text-base leading-relaxed text-brand-text-secondary break-words">{{ $step['description'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @if(!empty($service['gallery']))
                            <section class="space-y-6">
                                <h2 class="font-serif text-3xl sm:text-4xl font-bold text-[#5B1121] tracking-normal">
                                    {{ __('services.detail.gallery') }}
                                </h2>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    @foreach($service['gallery'] as $image)
                                        <figure class="overflow-hidden border border-[#E8DFC8] bg-white">
                                            <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" class="aspect-4/3 h-full w-full object-cover" loading="lazy">
                                            @if(!empty($image['caption']))
                                                <figcaption class="px-4 py-3 text-sm text-brand-text-muted">{{ $image['caption'] }}</figcaption>
                                            @endif
                                        </figure>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @if(!empty($service['faqs']))
                            <section class="space-y-6">
                                <h2 class="font-serif text-3xl sm:text-4xl font-bold text-[#5B1121] tracking-normal">
                                    {{ __('services.detail.faqs') }}
                                </h2>
                                <div class="space-y-4">
                                    @foreach($service['faqs'] as $faq)
                                        <details class="border border-[#E8DFC8] bg-white p-5">
                                            <summary class="cursor-pointer font-semibold text-[#5B1121] break-words">{{ $faq['question'] }}</summary>
                                            @if($faq['answer'] !== '')
                                                <p class="mt-3 text-sm sm:text-base leading-relaxed text-brand-text-secondary break-words">{{ $faq['answer'] }}</p>
                                            @endif
                                        </details>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    </div>

                    <aside class="lg:sticky lg:top-28 self-start space-y-6">
                        @if(!empty($service['prices']))
                            <section class="border border-[#E8DFC8] bg-white p-6 sm:p-7 shadow-xs">
                                <h2 class="font-serif text-2xl font-bold text-[#5B1121] tracking-normal">
                                    {{ __('services.detail.prices') }}
                                </h2>

                                <div class="mt-5 divide-y divide-[#E8DFC8]">
                                    @foreach($service['prices'] as $price)
                                        <div class="py-4 first:pt-0 last:pb-0">
                                            <div class="flex items-start justify-between gap-4">
                                                <div class="min-w-0">
                                                    @if(!empty($price['label']))
                                                        <p class="font-semibold text-brand-text break-words">{{ $price['label'] }}</p>
                                                    @endif
                                                    <p class="text-sm text-brand-text-muted">
                                                        {{ __('services.detail.duration', ['minutes' => $price['duration_minutes']]) }}
                                                    </p>
                                                </div>
                                                <p class="shrink-0 font-semibold text-[#5B1121]">{{ $price['price_display'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        <section class="border border-[#C5A880]/45 bg-[#211B19] p-7 text-white shadow-md">
                            <h2 class="font-serif text-2xl font-bold tracking-normal break-words">
                                {{ __('services.detail.inquiry_title') }}
                            </h2>
                            <p class="mt-3 text-sm leading-relaxed text-white/76 break-words">
                                {{ __('services.detail.inquiry_copy') }}
                            </p>
                            <a href="{{ $contactHref }}" class="mt-6 inline-flex h-12 items-center justify-center rounded-sm bg-[#C5A880] px-7 text-sm font-semibold uppercase tracking-widest text-[#181312] transition hover:bg-[#B3956B] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C5A880] focus-visible:ring-offset-4 focus-visible:ring-offset-[#211B19]">
                                {{ __('services.detail.inquiry_cta') }}
                            </a>
                        </section>
                    </aside>
                </div>
            </x-public.container>
        </section>
    </article>
</x-layouts.public>
