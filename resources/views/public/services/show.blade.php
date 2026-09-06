<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null">
    <article>
        {{-- Cinematic Hero Banner --}}
        <section class="relative bg-[#181312] pt-28 sm:pt-32 lg:pt-36 text-white overflow-hidden">
            @if(!empty($service['hero_media']['url']))
                <div class="absolute inset-0" data-reveal-image>
                    <img src="{{ $service['hero_media']['url'] }}" alt="{{ $service['hero_media']['alt'] }}" class="h-full w-full object-cover" loading="eager" fetchpriority="high">
                    <div class="absolute inset-0 bg-[#181312]/62"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-[#181312]/92 via-[#181312]/70 to-[#181312]/30"></div>
                </div>
            @endif

            <x-public.container size="lg">
                <div class="relative z-10 flex min-h-[440px] sm:min-h-[480px] max-w-3xl flex-col justify-end pb-12 sm:pb-16 lg:pb-18" data-reveal>
                    @if(!empty($service['category_name']))
                        <p class="mb-3.5 inline-flex items-center gap-2 text-[14px] sm:text-[15px] font-semibold uppercase tracking-[0.1em] text-[#C5A880]">
                            <span class="w-6 h-px bg-[#C5A880]/70"></span>
                            {{ $service['category_name'] }}
                        </p>
                    @endif

                    <h1 class="text-3xl sm:text-5xl lg:text-[clamp(2.5rem,4vw,3.85rem)] font-semibold leading-[1.08] tracking-[-0.025em] break-words [text-wrap:balance]">
                        {{ $service['name'] }}
                    </h1>

                    @if(!empty($service['excerpt']))
                        <p class="mt-4 max-w-2xl text-[18px] sm:text-[19px] font-medium leading-[1.66] text-white/90 break-words">
                            {{ $service['excerpt'] }}
                        </p>
                    @endif
                </div>
            </x-public.container>
        </section>

        {{-- Main Editorial Details Section --}}
        <section class="bg-[#FAF7F2] py-12 sm:py-16 lg:py-20">
            <x-public.container size="lg">
                <div class="grid grid-cols-1 gap-12 lg:grid-cols-[minmax(0,1fr)_360px] lg:gap-16">
                    <div class="min-w-0 space-y-10 sm:space-y-12">
                        {{-- Overview --}}
                        @if(!empty($service['content']))
                            <section class="space-y-4" data-reveal>
                                <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold text-[#5B1121] tracking-[-0.02em] leading-[1.10]">
                                    {{ __('services.detail.overview') }}
                                </h2>
                                <div class="space-y-4 text-[18px] sm:text-[19px] font-medium leading-[1.66] text-[#554D4A]">
                                    @foreach($service['content'] as $paragraph)
                                        <p class="break-words">{{ $paragraph }}</p>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        {{-- Benefits --}}
                        @if(!empty($service['benefits']))
                            <section class="space-y-5" data-reveal>
                                <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold text-[#5B1121] tracking-[-0.02em] leading-[1.10]">
                                    {{ __('services.detail.benefits') }}
                                </h2>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5" data-reveal-group>
                                    @foreach($service['benefits'] as $benefit)
                                        <div class="rounded-2xl border border-[#E8DFC8] bg-white p-6 sm:p-7 shadow-xs transition duration-300 hover:border-[#C5A880] hover:shadow-md" data-reveal>
                                            @if($benefit['title'] !== '')
                                                <h3 class="text-xl sm:text-[22px] font-semibold text-[#5B1121] break-words">{{ $benefit['title'] }}</h3>
                                            @endif
                                            @if($benefit['description'] !== '')
                                                <p class="mt-2 text-[17px] leading-[1.65] text-[#554D4A] break-words">{{ $benefit['description'] }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        {{-- Process Steps --}}
                        @if(!empty($service['process_steps']))
                            <section class="space-y-5" data-reveal>
                                <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold text-[#5B1121] tracking-[-0.02em] leading-[1.10]">
                                    {{ __('services.detail.process') }}
                                </h2>
                                <div class="space-y-4" data-reveal-group>
                                    @foreach($service['process_steps'] as $step)
                                        <div class="grid grid-cols-[48px_minmax(0,1fr)] gap-5 rounded-2xl border border-[#E8DFC8] bg-white p-6 shadow-xs transition duration-200 hover:border-[#C5A880]" data-reveal>
                                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#5B1121] text-base font-semibold text-[#C5A880] shadow-sm">
                                                {{ $loop->iteration }}
                                            </div>
                                            <div class="min-w-0 pt-0.5">
                                                @if($step['title'] !== '')
                                                    <h3 class="text-xl sm:text-[22px] font-semibold text-[#5B1121] break-words">{{ $step['title'] }}</h3>
                                                @endif
                                                @if($step['description'] !== '')
                                                    <p class="mt-2 text-[17px] leading-[1.65] text-[#554D4A] break-words">{{ $step['description'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        {{-- Gallery Showcase --}}
                        @if(!empty($service['gallery']))
                            <section class="space-y-5" data-reveal>
                                <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold text-[#5B1121] tracking-[-0.02em] leading-[1.10]">
                                    {{ __('services.detail.gallery') }}
                                </h2>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5" data-reveal-group>
                                    @foreach($service['gallery'] as $image)
                                        <figure class="overflow-hidden rounded-2xl border border-[#E8DFC8] bg-white shadow-xs group editorial-card" data-reveal>
                                            <div class="aspect-4/3 overflow-hidden editorial-image-zoom">
                                                <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                                            </div>
                                            @if(!empty($image['caption']))
                                                <figcaption class="px-5 py-3 text-[15px] sm:text-[16px] text-[#736965] border-t border-[#E8DFC8]">{{ $image['caption'] }}</figcaption>
                                            @endif
                                        </figure>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        {{-- FAQs Accordion --}}
                        @if(!empty($service['faqs']))
                            <section class="space-y-5" data-reveal>
                                <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold text-[#5B1121] tracking-[-0.02em] leading-[1.10]">
                                    {{ __('services.detail.faqs') }}
                                </h2>
                                <div class="space-y-3" data-reveal-group>
                                    @foreach($service['faqs'] as $faq)
                                        <details class="rounded-2xl border border-[#E8DFC8] bg-white p-6 shadow-xs transition duration-200 group" data-reveal>
                                            <summary class="cursor-pointer text-lg sm:text-xl font-semibold text-[#5B1121] break-words flex items-center justify-between list-none">
                                                <span>{{ $faq['question'] }}</span>
                                                <span class="ml-4 text-sm text-[#C5A880] transition group-open:rotate-180">&darr;</span>
                                            </summary>
                                            @if($faq['answer'] !== '')
                                                <p class="mt-4 text-[17px] leading-[1.65] text-[#554D4A] break-words pt-2 border-t border-[#E8DFC8]/60">{{ $faq['answer'] }}</p>
                                            @endif
                                        </details>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    </div>

                    {{-- Sticky Sidebar --}}
                    <aside class="lg:sticky lg:top-28 self-start space-y-6" data-reveal>
                        @if(!empty($service['prices']))
                            <section class="rounded-2xl border border-[#E8DFC8] bg-white p-6 sm:p-7 shadow-sm">
                                <h2 class="text-2xl font-semibold text-[#5B1121] tracking-tight">
                                    {{ __('services.detail.prices') }}
                                </h2>

                                <div class="mt-5 divide-y divide-[#E8DFC8]">
                                    @foreach($service['prices'] as $price)
                                        <div class="py-3.5 first:pt-0 last:pb-0">
                                            <div class="flex items-start justify-between gap-4">
                                                <div class="min-w-0">
                                                    @if(!empty($price['label']))
                                                        <p class="font-semibold text-brand-text break-words">{{ $price['label'] }}</p>
                                                    @endif
                                                    <p class="text-[14px] sm:text-[15px] text-[#736965] mt-0.5">
                                                        {{ __('services.detail.duration', ['minutes' => $price['duration_minutes']]) }}
                                                    </p>
                                                </div>
                                                <p class="shrink-0 font-semibold text-[#5B1121] text-[17px]">{{ $price['price_display'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        <section class="rounded-2xl border border-[#C5A880]/40 bg-[#211B19] p-6 sm:p-7 text-white shadow-xl space-y-3.5">
                            <span class="inline-block text-xs font-semibold uppercase tracking-widest text-[#C5A880]">Việt Hàn Âu Hàn Spa</span>
                            <h2 class="text-2xl font-semibold tracking-tight text-white break-words">
                                {{ __('services.detail.inquiry_title') }}
                            </h2>
                            <p class="text-[16px] leading-[1.6] text-white/85 break-words">
                                {{ __('services.detail.inquiry_copy') }}
                            </p>
                            <div class="pt-2">
                                <a href="{{ $contactHref }}" class="btn-editorial-primary w-full text-center">
                                    {{ __('services.detail.inquiry_cta') }}
                                </a>
                            </div>
                        </section>
                    </aside>
                </div>
            </x-public.container>
        </section>
    </article>
</x-layouts.public>
