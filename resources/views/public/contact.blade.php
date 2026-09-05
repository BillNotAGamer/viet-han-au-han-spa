<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null">
    <article>
        <section class="bg-brand-ivory pt-32 sm:pt-36 lg:pt-40 pb-14 sm:pb-20 border-b border-brand-border">
            <x-public.container size="lg">
                <div class="max-w-3xl space-y-5">
                    <span class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-[#B3956B]">
                        {{ __('pages.contact.eyebrow') }}
                    </span>
                    <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl font-bold tracking-normal text-[#5B1121] leading-tight break-words [text-wrap:balance]">
                        {{ $page['title'] }}
                    </h1>
                    <p class="text-base sm:text-lg text-brand-text-secondary leading-relaxed break-words max-w-2xl">
                        {{ __('pages.contact.intro') }}
                    </p>
                </div>
            </x-public.container>
        </section>

        <section class="bg-[#FAF7F2] py-14 sm:py-20 lg:py-24">
            <x-public.container size="lg">
                <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,0.9fr)_minmax(360px,0.75fr)] gap-10 lg:gap-16">
                    <div class="min-w-0 space-y-10">
                        <section class="border border-[#E8DFC8] bg-white p-6 sm:p-8 shadow-xs">
                            <h2 class="font-serif text-3xl font-bold text-[#5B1121] tracking-normal">
                                {{ __('pages.contact.details') }}
                            </h2>

                            @if(!empty($contact['items']) || !empty($contact['links']))
                                <address class="not-italic">
                                    @if(!empty($contact['items']))
                                        <dl class="mt-6 divide-y divide-[#E8DFC8]">
                                            @foreach($contact['items'] as $item)
                                                <div class="py-4 first:pt-0 last:pb-0">
                                                    <dt class="text-xs font-semibold uppercase tracking-widest text-[#B3956B]">{{ $item['label'] }}</dt>
                                                    <dd class="mt-1 text-brand-text-secondary break-words">
                                                        @if(!empty($item['href']))
                                                            <a href="{{ $item['href'] }}" class="font-semibold text-[#5B1121] hover:text-[#B3956B] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5B1121] focus-visible:ring-offset-4">
                                                                {{ $item['value'] }}
                                                            </a>
                                                        @else
                                                            {{ $item['value'] }}
                                                        @endif
                                                    </dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    @endif

                                    @if(!empty($contact['links']))
                                        <div class="mt-7 border-t border-[#E8DFC8] pt-6">
                                            <h3 class="text-xs font-semibold uppercase tracking-widest text-[#B3956B]">
                                                {{ __('pages.contact.channels') }}
                                            </h3>
                                            <div class="mt-4 flex flex-wrap gap-3">
                                                @foreach($contact['links'] as $link)
                                                    <a href="{{ $link['url'] }}" class="inline-flex h-10 items-center rounded-sm border border-[#C5A880]/55 px-4 text-sm font-semibold text-[#5B1121] transition hover:border-[#B3956B] hover:text-[#B3956B] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5B1121] focus-visible:ring-offset-4" rel="noopener noreferrer" target="_blank">
                                                        {{ $link['label'] }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </address>
                            @else
                                <p class="mt-5 text-base leading-relaxed text-brand-text-secondary break-words">
                                    {{ __('pages.contact.empty') }}
                                </p>
                            @endif
                        </section>

                        @if(!empty($page['content']))
                            <section class="space-y-5">
                                <h2 class="font-serif text-3xl sm:text-4xl font-bold text-[#5B1121] tracking-normal">
                                    {{ __('pages.contact.content') }}
                                </h2>
                                <div class="space-y-5 text-base sm:text-lg leading-relaxed text-brand-text-secondary">
                                    @foreach($page['content'] as $paragraph)
                                        <p class="break-words">{{ $paragraph }}</p>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    </div>

                    <div class="space-y-5">
                        @if(!empty($page['lead_media']['url']))
                            <figure class="overflow-hidden border border-[#E8DFC8] bg-white shadow-sm">
                                <img src="{{ $page['lead_media']['url'] }}" alt="{{ $page['lead_media']['alt'] }}" class="aspect-4/3 h-full w-full object-cover" loading="eager">
                                @if(!empty($page['lead_media']['caption']))
                                    <figcaption class="px-4 py-3 text-sm text-brand-text-muted">{{ $page['lead_media']['caption'] }}</figcaption>
                                @endif
                            </figure>
                        @endif

                        @if(!empty($page['gallery']))
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-5">
                                @foreach($page['gallery'] as $image)
                                    <figure class="overflow-hidden border border-[#E8DFC8] bg-white">
                                        <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" class="aspect-4/3 h-full w-full object-cover" loading="lazy">
                                        @if(!empty($image['caption']))
                                            <figcaption class="px-4 py-3 text-sm text-brand-text-muted">{{ $image['caption'] }}</figcaption>
                                        @endif
                                    </figure>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </x-public.container>
        </section>
    </article>
</x-layouts.public>
