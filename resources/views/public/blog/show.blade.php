<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null">
    <article>
        {{-- Cinematic Hero Banner --}}
        <section class="relative bg-[#181312] pt-28 sm:pt-32 lg:pt-36 text-white overflow-hidden">
            @if(!empty($post['hero_media']['url']))
                <div class="absolute inset-0" data-reveal-image>
                    <img src="{{ $post['hero_media']['url'] }}" alt="{{ $post['hero_media']['alt'] }}" class="h-full w-full object-cover" loading="eager" fetchpriority="high">
                    <div class="absolute inset-0 bg-[#181312]/62"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-[#181312]/92 via-[#181312]/70 to-[#181312]/30"></div>
                </div>
            @endif

            <x-public.container size="lg">
                <div class="relative z-10 flex min-h-[440px] sm:min-h-[480px] max-w-3xl flex-col justify-end pb-12 sm:pb-16 lg:pb-18" data-reveal>
                    <div class="mb-3.5 flex flex-wrap items-center gap-3 text-[14px] sm:text-[15px] font-semibold uppercase tracking-[0.1em] text-[#C5A880]">
                        @if(!empty($post['category_name']))
                            <span>{{ $post['category_name'] }}</span>
                        @else
                            <span>{{ __('blog.detail.article') }}</span>
                        @endif
                        @if(!empty($post['published_at_display']))
                            <span class="text-white/40">&bull;</span>
                            <time datetime="{{ $post['published_at_iso'] }}">{{ $post['published_at_display'] }}</time>
                        @endif
                    </div>

                    <h1 class="text-3xl sm:text-5xl lg:text-[clamp(2.5rem,4vw,3.85rem)] font-semibold leading-[1.08] tracking-[-0.025em] break-words [text-wrap:balance]">
                        {{ $post['title'] }}
                    </h1>

                    @if(!empty($post['excerpt']))
                        <p class="mt-4 max-w-2xl text-[18px] sm:text-[19px] font-medium leading-[1.66] text-white/90 break-words">
                            {{ $post['excerpt'] }}
                        </p>
                    @endif
                </div>
            </x-public.container>
        </section>

        {{-- Main Article Section --}}
        <section class="bg-[#FAF7F2] py-12 sm:py-16 lg:py-20">
            <x-public.container size="lg">
                <div class="grid grid-cols-1 gap-12 lg:grid-cols-[minmax(0,1fr)_340px] lg:gap-16">
                    <div class="min-w-0 space-y-10 sm:space-y-12">
                        {{-- Article Content --}}
                        @if(!empty($post['content']))
                            <section class="space-y-4" data-reveal>
                                <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold text-[#5B1121] tracking-[-0.02em] leading-[1.10] mb-5">
                                    {{ __('blog.detail.overview') }}
                                </h2>
                                <div class="space-y-5 text-[18px] sm:text-[19px] font-medium leading-[1.66] text-[#554D4A]">
                                    @foreach($post['content'] as $paragraph)
                                        <p class="break-words">{{ $paragraph }}</p>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        {{-- Article Gallery --}}
                        @if(!empty($post['gallery']))
                            <section class="space-y-5" data-reveal>
                                <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold text-[#5B1121] tracking-[-0.02em] leading-[1.10] mb-5">
                                    {{ __('blog.detail.gallery') }}
                                </h2>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5" data-reveal-group>
                                    @foreach($post['gallery'] as $image)
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
                    </div>

                    {{-- Sticky Sidebar --}}
                    <aside class="lg:sticky lg:top-28 self-start space-y-6" data-reveal>
                        <section class="rounded-2xl border border-[#E8DFC8] bg-white p-6 sm:p-7 shadow-sm">
                            <h2 class="text-xl sm:text-2xl font-semibold text-[#5B1121] tracking-tight">
                                {{ __('blog.detail.article') }}
                            </h2>

                            <dl class="mt-5 divide-y divide-[#E8DFC8]">
                                @if(!empty($post['published_at_display']))
                                    <div class="py-3.5 first:pt-0 last:pb-0">
                                        <dt class="text-xs font-semibold uppercase tracking-widest text-[#9B7B4F]">{{ __('blog.detail.published_on') }}</dt>
                                        <dd class="mt-1 text-[15px] sm:text-[16px] text-[#554D4A] break-words">
                                            <time datetime="{{ $post['published_at_iso'] }}">{{ $post['published_at_display'] }}</time>
                                        </dd>
                                    </div>
                                @endif

                                @if(!empty($post['author_name']))
                                    <div class="py-3.5 first:pt-0 last:pb-0">
                                        <dt class="text-xs font-semibold uppercase tracking-widest text-[#9B7B4F]">{{ __('blog.detail.by_author') }}</dt>
                                        <dd class="mt-1 text-[15px] sm:text-[16px] text-[#554D4A] break-words">{{ $post['author_name'] }}</dd>
                                    </div>
                                @endif

                                @if(!empty($post['category_name']))
                                    <div class="py-3.5 first:pt-0 last:pb-0">
                                        <dt class="sr-only">{{ __('blog.detail.article') }}</dt>
                                        <dd class="text-xs font-semibold uppercase tracking-widest text-[#5B1121] break-words">{{ $post['category_name'] }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </section>

                        <section class="rounded-2xl border border-[#C5A880]/40 bg-[#211B19] p-6 sm:p-7 text-white shadow-xl space-y-3.5">
                            <h2 class="text-xl sm:text-2xl font-semibold tracking-tight text-white break-words">
                                {{ __('blog.detail.cta_title') }}
                            </h2>
                            <p class="text-[16px] leading-[1.6] text-white/85 break-words">
                                {{ __('blog.detail.cta_copy') }}
                            </p>
                            <div class="mt-5 flex flex-col gap-3">
                                <a href="{{ $contactHref }}" class="btn-editorial-primary w-full text-center">
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
