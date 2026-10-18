@php
    $locale = app()->getLocale();
@endphp

<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null" variant="v2">
    <article class="v2-article">
        {{-- =====================================================================
           ARTICLE HERO — Editorial Cover Title
           ===================================================================== --}}
        <header @class(['v2-article-hero relative', 'v2-article-hero--without-media' => empty($post['hero_media']['url'])])>
            @if(!empty($post['hero_media']['url']))
                <img src="{{ $post['hero_media']['url'] }}" alt="{{ $post['hero_media']['alt'] }}" class="v2-article-hero__image" loading="eager" fetchpriority="high">
                <div class="v2-article-hero__veil" aria-hidden="true"></div>
            @endif

            <x-public.v2.container class="relative z-10">
                <div class="v2-article-hero__copy max-w-3xl pt-28 pb-16 lg:pb-24">
                    <div class="v2-journal-meta v2-journal-meta--inverse flex items-center gap-3 font-mono text-xs uppercase tracking-widest text-[#C9A24D] mb-4">
                        @if(!empty($post['category_name']))
                            <span class="font-semibold text-[#EFE8DC]">{{ $post['category_name'] }}</span>
                        @else
                            <span>{{ __('blog.detail.article') }}</span>
                        @endif
                        @if(!empty($post['published_at_display']))
                            <span class="text-[#E2D9CC]/60">&middot;</span>
                            <time datetime="{{ $post['published_at_iso'] }}" class="text-[#EFE8DC]/80">{{ $post['published_at_display'] }}</time>
                        @endif
                    </div>

                    <x-public.v2.display-heading level="1" size="xl" class="font-serif text-[#EFE8DC] leading-tight mb-6">
                        {{ $post['title'] }}
                    </x-public.v2.display-heading>

                    @if(!empty($post['excerpt']))
                        <p class="v2-type-body-lg font-serif italic text-lg sm:text-xl text-[#EFE8DC]/90 leading-relaxed border-l-2 border-[#C9A24D] pl-6 my-4">
                            {{ $post['excerpt'] }}
                        </p>
                    @endif
                </div>
            </x-public.v2.container>
        </header>

        {{-- =====================================================================
           READING SANCTUARY — Editorial Monograph Body
           ===================================================================== --}}
        <x-public.v2.section surface="paper" class="v2-article-reading py-16 lg:py-24 border-b border-[#E2D9CC]">
            <x-public.v2.container size="reading" class="max-w-[68ch] mx-auto">
                @if(!empty($post['published_at_display']) || !empty($post['author_name']))
                    <dl class="v2-article-byline flex items-center justify-between pb-6 border-b border-[#E2D9CC] mb-12 text-xs font-mono uppercase tracking-widest text-[#6E675F]">
                        @if(!empty($post['published_at_display']))
                            <div>
                                <dt class="sr-only">{{ __('blog.detail.published_on') }}</dt>
                                <dd class="m-0"><time datetime="{{ $post['published_at_iso'] }}">{{ $post['published_at_display'] }}</time></dd>
                            </div>
                        @endif
                        @if(!empty($post['author_name']))
                            <div class="flex items-center gap-2">
                                <dt class="text-[#C9A24D]">{{ __('blog.detail.by_author') }}:</dt>
                                <dd class="font-semibold text-[#181311] m-0">{{ $post['author_name'] }}</dd>
                            </div>
                        @endif
                    </dl>
                @endif

                @if(!empty($post['content']))
                    <div class="v2-article-body space-y-6 text-[#594B42] text-base lg:text-lg leading-relaxed">
                        @foreach($post['content'] as $paragraph)
                            <p class="first-letter:text-3xl first-letter:font-serif first-letter:text-[#6A141A] first-letter:mr-1">{{ $paragraph }}</p>
                        @endforeach
                    </div>
                @endif
            </x-public.v2.container>
        </x-public.v2.section>

        {{-- =====================================================================
           ARTICLE GALLERY — Visual Exhibition
           ===================================================================== --}}
        @if(!empty($post['gallery']))
            <x-public.v2.section surface="ivory" class="v2-article-gallery py-16 lg:py-24 border-b border-[#E2D9CC]">
                <x-public.v2.container size="wide">
                    <div class="v2-section-heading v2-section-heading--compact mb-12">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <x-public.v2.eyebrow>{{ __('blog.detail.gallery') }}</x-public.v2.eyebrow>
                                <span class="w-6 h-px bg-[#C9A24D]" aria-hidden="true"></span>
                            </div>
                            <x-public.v2.display-heading level="2" size="heading-lg" class="font-serif text-3xl lg:text-4xl text-[#181311]">
                                {{ __('blog.detail.gallery') }}
                            </x-public.v2.display-heading>
                        </div>
                    </div>
                    <div class="v2-narrative-gallery grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($post['gallery'] as $image)
                            <div class="border border-[#E2D9CC] overflow-hidden bg-[#FBF9F4]">
                                <x-public.v2.media-frame
                                    :src="$image['url']"
                                    :alt="$image['alt']"
                                    :caption="$image['caption'] ?? null"
                                    :role="$loop->first ? 'landscape' : 'gallery'"
                                    class="aspect-[4/3] w-full object-cover"
                                />
                            </div>
                        @endforeach
                    </div>
                </x-public.v2.container>
            </x-public.v2.section>
        @endif

        {{-- =====================================================================
           ARTICLE FOOTER & INVITATION CTA
           ===================================================================== --}}
        <section class="v2-article-footer bg-[#F5F0E8] py-16 lg:py-20 border-t border-[#E2D9CC]">
            <x-public.v2.container size="reading" class="max-w-3xl mx-auto flex flex-col sm:flex-row sm:items-center sm:justify-between gap-8">
                <div>
                    <x-public.v2.eyebrow>{{ __('blog.detail.article') }}</x-public.v2.eyebrow>
                    <x-public.v2.display-heading level="2" size="heading-md" class="font-serif text-2xl text-[#181311] mt-2 mb-2">
                        {{ __('blog.detail.cta_title') }}
                    </x-public.v2.display-heading>
                    <p class="v2-type-body text-[#594B42] text-sm leading-relaxed m-0">{{ __('blog.detail.cta_copy') }}</p>
                </div>
                <div class="v2-article-footer__actions flex flex-wrap items-center gap-4 shrink-0">
                    <x-public.v2.button :href="$contactHref" variant="primary">
                        {{ __('blog.detail.cta') }}
                    </x-public.v2.button>
                    <x-public.v2.button :href="$locale === 'en' ? route('en.blog.index') : route('vi.blog.index')" variant="text">
                        {{ __('blog.detail.back_to_blog') }} <span aria-hidden="true">&rarr;</span>
                    </x-public.v2.button>
                </div>
            </x-public.v2.container>
        </section>
    </article>
</x-layouts.public>
