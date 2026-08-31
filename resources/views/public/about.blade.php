<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref">
    <article>
        <section class="bg-brand-ivory pt-32 sm:pt-36 lg:pt-40 pb-14 sm:pb-20 border-b border-brand-border">
            <x-public.container size="lg">
                <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,0.9fr)_minmax(360px,0.75fr)] gap-10 lg:gap-16 items-end">
                    <div class="max-w-3xl space-y-5">
                        <span class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-[#B3956B]">
                            {{ __('pages.about.eyebrow') }}
                        </span>
                        <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl font-bold tracking-normal text-[#5B1121] leading-tight break-words [text-wrap:balance]">
                            {{ $page['title'] }}
                        </h1>
                    </div>

                    @if(!empty($page['lead_media']['url']))
                        <figure class="overflow-hidden border border-[#E8DFC8] bg-white shadow-sm">
                            <img src="{{ $page['lead_media']['url'] }}" alt="{{ $page['lead_media']['alt'] }}" class="aspect-4/3 h-full w-full object-cover" loading="eager">
                            @if(!empty($page['lead_media']['caption']))
                                <figcaption class="px-4 py-3 text-sm text-brand-text-muted">{{ $page['lead_media']['caption'] }}</figcaption>
                            @endif
                        </figure>
                    @endif
                </div>
            </x-public.container>
        </section>

        <section class="bg-[#FAF7F2] py-14 sm:py-20 lg:py-24">
            <x-public.container size="lg">
                <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_320px] gap-12 lg:gap-16">
                    <div class="min-w-0 space-y-12">
                        @if(!empty($page['content']))
                            <section class="space-y-5">
                                <div class="space-y-5 text-base sm:text-lg leading-relaxed text-brand-text-secondary">
                                    @foreach($page['content'] as $paragraph)
                                        <p class="break-words">{{ $paragraph }}</p>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @if(!empty($page['gallery']))
                            <section class="space-y-6">
                                <h2 class="font-serif text-3xl sm:text-4xl font-bold text-[#5B1121] tracking-normal">
                                    {{ __('pages.about.gallery') }}
                                </h2>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    @foreach($page['gallery'] as $image)
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
                    </div>

                    <aside class="self-start border border-[#C5A880]/45 bg-[#211B19] p-7 text-white shadow-md">
                        <h2 class="font-serif text-2xl font-bold tracking-normal break-words">
                            {{ __('pages.about.cta_title') }}
                        </h2>
                        <p class="mt-3 text-sm leading-relaxed text-white/76 break-words">
                            {{ __('pages.about.cta_copy') }}
                        </p>
                        <a href="{{ $locale === 'en' ? route('en.services.index') : route('vi.services.index') }}" class="mt-6 inline-flex h-12 items-center justify-center rounded-sm bg-[#C5A880] px-7 text-sm font-semibold uppercase tracking-widest text-[#181312] transition hover:bg-[#B3956B] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C5A880] focus-visible:ring-offset-4 focus-visible:ring-offset-[#211B19]">
                            {{ __('pages.about.cta') }}
                        </a>
                    </aside>
                </div>
            </x-public.container>
        </section>
    </article>
</x-layouts.public>
