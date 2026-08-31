<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref">
    <article>
        <section class="relative bg-[#181312] pt-28 sm:pt-32 lg:pt-36 text-white">
            @if(!empty($post['hero_media']['url']))
                <div class="absolute inset-0">
                    <img src="{{ $post['hero_media']['url'] }}" alt="{{ $post['hero_media']['alt'] }}" class="h-full w-full object-cover" loading="eager">
                    <div class="absolute inset-0 bg-[#181312]/58"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-[#181312]/90 via-[#181312]/62 to-[#181312]/18"></div>
                </div>
            @endif

            <x-public.container size="lg">
                <div class="relative z-10 flex min-h-[520px] max-w-3xl flex-col justify-end pb-16 sm:pb-20 lg:pb-24">
                    <div class="mb-4 flex flex-wrap items-center gap-3 text-xs sm:text-sm font-semibold uppercase tracking-widest text-[#C5A880]">
                        @if(!empty($post['category_name']))
                            <span>{{ $post['category_name'] }}</span>
                        @else
                            <span>{{ __('blog.detail.article') }}</span>
                        @endif
                        @if(!empty($post['published_at_display']))
                            <time datetime="{{ $post['published_at_iso'] }}">{{ $post['published_at_display'] }}</time>
                        @endif
                    </div>

                    <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight tracking-normal break-words [text-wrap:balance]">
                        {{ $post['title'] }}
                    </h1>

                    @if(!empty($post['excerpt']))
                        <p class="mt-5 max-w-2xl text-base sm:text-lg leading-relaxed text-white/82 break-words">
                            {{ $post['excerpt'] }}
                        </p>
                    @endif
                </div>
            </x-public.container>
        </section>

        <section class="bg-[#FAF7F2] py-14 sm:py-20 lg:py-24">
            <x-public.container size="lg">
                <div class="grid grid-cols-1 gap-12 lg:grid-cols-[minmax(0,1fr)_340px] lg:gap-16">
                    <div class="min-w-0 space-y-12">
                        @if(!empty($post['content']))
                            <section class="space-y-5">
                                <h2 class="font-serif text-3xl sm:text-4xl font-bold text-[#5B1121] tracking-normal">
                                    {{ __('blog.detail.overview') }}
                                </h2>
                                <div class="space-y-5 text-base sm:text-lg leading-relaxed text-brand-text-secondary">
                                    @foreach($post['content'] as $paragraph)
                                        <p class="break-words">{{ $paragraph }}</p>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @if(!empty($post['gallery']))
                            <section class="space-y-6">
                                <h2 class="font-serif text-3xl sm:text-4xl font-bold text-[#5B1121] tracking-normal">
                                    {{ __('blog.detail.gallery') }}
                                </h2>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    @foreach($post['gallery'] as $image)
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

                    <aside class="lg:sticky lg:top-28 self-start space-y-6">
                        <section class="border border-[#E8DFC8] bg-white p-6 sm:p-7 shadow-xs">
                            <h2 class="font-serif text-2xl font-bold text-[#5B1121] tracking-normal">
                                {{ __('blog.detail.article') }}
                            </h2>

                            <dl class="mt-5 divide-y divide-[#E8DFC8]">
                                @if(!empty($post['published_at_display']))
                                    <div class="py-4 first:pt-0 last:pb-0">
                                        <dt class="text-xs font-semibold uppercase tracking-widest text-[#B3956B]">{{ __('blog.detail.published_on') }}</dt>
                                        <dd class="mt-1 text-brand-text-secondary break-words">
                                            <time datetime="{{ $post['published_at_iso'] }}">{{ $post['published_at_display'] }}</time>
                                        </dd>
                                    </div>
                                @endif

                                @if(!empty($post['author_name']))
                                    <div class="py-4 first:pt-0 last:pb-0">
                                        <dt class="text-xs font-semibold uppercase tracking-widest text-[#B3956B]">{{ __('blog.detail.by_author') }}</dt>
                                        <dd class="mt-1 text-brand-text-secondary break-words">{{ $post['author_name'] }}</dd>
                                    </div>
                                @endif

                                @if(!empty($post['category_name']))
                                    <div class="py-4 first:pt-0 last:pb-0">
                                        <dt class="sr-only">{{ __('blog.detail.article') }}</dt>
                                        <dd class="text-sm font-semibold uppercase tracking-widest text-[#5B1121] break-words">{{ $post['category_name'] }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </section>

                        <section class="border border-[#C5A880]/45 bg-[#211B19] p-7 text-white shadow-md">
                            <h2 class="font-serif text-2xl font-bold tracking-normal break-words">
                                {{ __('blog.detail.cta_title') }}
                            </h2>
                            <p class="mt-3 text-sm leading-relaxed text-white/76 break-words">
                                {{ __('blog.detail.cta_copy') }}
                            </p>
                            <div class="mt-6 flex flex-col gap-3">
                                <a href="{{ $contactHref }}" class="inline-flex h-12 items-center justify-center rounded-sm bg-[#C5A880] px-7 text-sm font-semibold uppercase tracking-widest text-[#181312] transition hover:bg-[#B3956B] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C5A880] focus-visible:ring-offset-4 focus-visible:ring-offset-[#211B19]">
                                    {{ __('blog.detail.cta') }}
                                </a>
                                <a href="{{ $locale === 'en' ? route('en.blog.index') : route('vi.blog.index') }}" class="inline-flex h-12 items-center justify-center rounded-sm border border-white/35 px-7 text-sm font-semibold uppercase tracking-widest text-white transition hover:border-[#C5A880] hover:text-[#C5A880] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C5A880] focus-visible:ring-offset-4 focus-visible:ring-offset-[#211B19]">
                                    {{ __('blog.detail.back_to_blog') }}
                                </a>
                            </div>
                        </section>
                    </aside>
                </div>
            </x-public.container>
        </section>
    </article>
</x-layouts.public>
