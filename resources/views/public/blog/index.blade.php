@php
    $postItems = collect($posts->items());
    $featuredPost = $postItems->first();
    $supportingPosts = $postItems->slice(1);
@endphp

<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null" variant="v2">
    {{-- =========================================================================
       PAGE HERO — Editorial Journal Header
       ========================================================================= --}}
    <section class="v2-page-hero v2-page-hero--intro v2-journal-hero bg-[#F5F0E8] border-b border-[#E2D9CC] py-16 lg:py-24">
        <x-public.v2.container>
            <div class="v2-page-hero__copy max-w-3xl">
                <div class="flex items-center gap-3 mb-4">
                    <x-public.v2.eyebrow>{{ __('blog.index.eyebrow') }}</x-public.v2.eyebrow>
                    <span class="w-8 h-px bg-[#C9A24D]/60" aria-hidden="true"></span>
                    <span class="font-serif italic text-xs text-[#C9A24D]">The Wellness Journal</span>
                </div>
                <x-public.v2.display-heading level="1" size="xl" class="v2-primary-page-hero__title font-serif text-[#181311]">
                    {{ __('blog.index.title') }}
                </x-public.v2.display-heading>
                <div class="w-12 h-px bg-[#C9A24D] my-6"></div>
                <x-public.v2.editorial-copy size="lg" class="text-[#594B42] leading-relaxed">
                    {{ __('blog.index.intro') }}
                </x-public.v2.editorial-copy>
            </div>
        </x-public.v2.container>
    </section>

    {{-- =========================================================================
       JOURNAL INDEX & EDITORIAL ARTICLES
       ========================================================================= --}}
    <x-public.v2.section surface="paper" class="v2-journal-index py-16 lg:py-24">
        <x-public.v2.container>
            @if($featuredPost)
                {{-- Cover Feature Article --}}
                <article @class([
                    'v2-journal-feature group border border-[#E2D9CC] bg-[#FBF9F4] p-6 sm:p-10 lg:p-12 mb-16 lg:mb-20 hover:border-[#C9A24D]/70 transition-all duration-500 rounded-none shadow-none',
                    'v2-journal-feature--without-media' => empty($featuredPost['media']['url'])
                ]) data-reveal>
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                        <div class="@if(!empty($featuredPost['media']['url'])) lg:col-span-7 @else lg:col-span-12 @endif v2-journal-feature__body flex flex-col justify-between">
                            <div>
                                <div class="v2-journal-meta flex items-center gap-3 font-mono text-xs uppercase tracking-widest text-[#C9A24D] mb-4">
                                    @if(!empty($featuredPost['category_name']))
                                        <span class="font-semibold text-[#6A141A]">{{ $featuredPost['category_name'] }}</span>
                                    @endif
                                    @if(!empty($featuredPost['category_name']) && !empty($featuredPost['published_at_display']))
                                        <span class="text-[#E2D9CC]">&middot;</span>
                                    @endif
                                    @if(!empty($featuredPost['published_at_display']))
                                        <time datetime="{{ $featuredPost['published_at_iso'] }}">{{ $featuredPost['published_at_display'] }}</time>
                                    @endif
                                </div>

                                <x-public.v2.display-heading level="2" size="display-lg" class="font-serif text-3xl sm:text-4xl text-[#181311] leading-tight mb-4 group-hover:text-[#6A141A] transition-colors">
                                    <a href="{{ $featuredPost['url'] }}" class="hover:text-[#6A141A] transition-colors">{{ $featuredPost['title'] }}</a>
                                </x-public.v2.display-heading>

                                @if(!empty($featuredPost['excerpt']))
                                    <p class="v2-type-body-lg text-[#594B42] text-base lg:text-lg leading-relaxed mb-6 max-w-2xl">
                                        {{ $featuredPost['excerpt'] }}
                                    </p>
                                @endif
                            </div>

                            <div class="pt-4 border-t border-[#E2D9CC]">
                                <x-public.v2.button :href="$featuredPost['url']" variant="text" class="text-xs uppercase tracking-widest font-semibold text-[#181311] hover:text-[#6A141A]">
                                    {{ __('blog.index.details') }} <span aria-hidden="true">&rarr;</span>
                                </x-public.v2.button>
                            </div>
                        </div>

                        @if(!empty($featuredPost['media']['url']))
                            <div class="lg:col-span-5">
                                <a href="{{ $featuredPost['url'] }}" class="v2-journal-feature__media block overflow-hidden border border-[#E2D9CC]/80" aria-label="{{ $featuredPost['title'] }}">
                                    <x-public.v2.media-frame
                                        :src="$featuredPost['media']['url']"
                                        :alt="$featuredPost['media']['alt']"
                                        role="landscape"
                                        class="aspect-[16/11] w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                    />
                                </a>
                            </div>
                        @endif
                    </div>
                </article>

                {{-- Supporting Articles Ledger --}}
                @if($supportingPosts->isNotEmpty())
                    <div class="v2-journal-index__list divide-y divide-[#E2D9CC] border-y border-[#E2D9CC] mb-16" data-reveal-group>
                        @foreach($supportingPosts as $post)
                            <article @class([
                                'v2-journal-index__entry group py-8 lg:py-10 grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-center',
                                'v2-journal-index__entry--without-media' => empty($post['media']['url'])
                            ]) data-reveal>
                                {{-- Metadata Column --}}
                                <div class="lg:col-span-3">
                                    <div class="v2-journal-meta flex flex-col gap-1 font-mono text-xs uppercase tracking-widest text-[#C9A24D]">
                                        @if(!empty($post['category_name']))
                                            <span class="font-semibold text-[#6A141A]">{{ $post['category_name'] }}</span>
                                        @endif
                                        @if(!empty($post['published_at_display']))
                                            <time datetime="{{ $post['published_at_iso'] }}" class="text-[#594B42]">{{ $post['published_at_display'] }}</time>
                                        @endif
                                    </div>
                                </div>

                                {{-- Title & Excerpt Column --}}
                                <div class="@if(!empty($post['media']['url'])) lg:col-span-6 @else lg:col-span-9 @endif">
                                    <x-public.v2.display-heading level="2" size="heading-md" class="font-serif text-xl sm:text-2xl text-[#181311] leading-snug mb-2 group-hover:text-[#6A141A] transition-colors">
                                        <a href="{{ $post['url'] }}" class="hover:text-[#6A141A] transition-colors">{{ $post['title'] }}</a>
                                    </x-public.v2.display-heading>
                                    @if(!empty($post['excerpt']))
                                        <p class="v2-type-body text-[#594B42] text-sm leading-relaxed m-0">{{ $post['excerpt'] }}</p>
                                    @endif
                                </div>

                                {{-- Thumbnail or Read Action --}}
                                @if(!empty($post['media']['url']))
                                    <div class="lg:col-span-3">
                                        <a href="{{ $post['url'] }}" class="v2-journal-index__media block overflow-hidden border border-[#E2D9CC]/80" aria-label="{{ $post['title'] }}">
                                            <x-public.v2.media-frame
                                                :src="$post['media']['url']"
                                                :alt="$post['media']['alt']"
                                                role="landscape"
                                                class="aspect-[16/10] w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                            />
                                        </a>
                                    </div>
                                @else
                                    <div class="lg:col-span-3 text-right">
                                        <x-public.v2.button :href="$post['url']" variant="text" class="v2-journal-index__link text-xs uppercase tracking-widest font-semibold text-[#181311] hover:text-[#6A141A]">
                                            {{ __('blog.index.details') }} <span aria-hidden="true">&rarr;</span>
                                        </x-public.v2.button>
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif

                {{ $posts->links('components.public.v2.pagination') }}
            @else
                <div class="v2-empty-state border border-[#E2D9CC] bg-[#FBF9F4] py-16 px-8 lg:py-24 lg:px-16 text-center max-w-2xl mx-auto shadow-sm" role="status">
                    <div class="w-12 h-12 mx-auto mb-5 flex items-center justify-center rounded-full border border-[#C9A24D]/40 bg-[#F5F0E8] text-[#C9A24D]">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>

                    <p class="font-serif italic text-xs uppercase tracking-[0.2em] text-[#C9A24D] mb-3">The Wellness Journal</p>
                    
                    <h3 class="v2-type-display-md font-serif text-2xl sm:text-3xl text-[#181311] mb-4">
                        {{ __('blog.index.empty') }}
                    </h3>

                    <div class="w-12 h-px bg-[#C9A24D]/60 mx-auto my-5" aria-hidden="true"></div>

                    <p class="v2-type-body text-[#594B42] text-sm sm:text-base leading-relaxed max-w-md mx-auto mb-8">
                        {{ app()->getLocale() === 'en' 
                            ? 'Our wellness journal articles on oriental skin therapy and mindful living are being curated. We invite you to explore our treatment collection.' 
                            : 'Các bài viết chuyên sâu về dưỡng sinh và chăm sóc sắc đẹp chuẩn Hàn đang được đội ngũ Việt Hàn biên soạn. Thân mời quý khách khám phá danh mục dịch vụ.' }}
                    </p>

                    <div class="flex flex-wrap items-center justify-center gap-4">
                        <x-public.v2.button :href="app()->getLocale() === 'en' ? route('en.services.index') : route('vi.services.index')" variant="primary">
                            {{ app()->getLocale() === 'en' ? 'Explore Treatments' : 'Khám phá Dịch vụ' }}
                        </x-public.v2.button>
                        <x-public.v2.button :href="app()->getLocale() === 'en' ? route('en.booking.create') : route('vi.booking.create')" variant="secondary">
                            {{ app()->getLocale() === 'en' ? 'Reserve Appointment' : 'Đặt lịch hẹn' }}
                        </x-public.v2.button>
                    </div>
                </div>
            @endif
        </x-public.v2.container>
    </x-public.v2.section>
</x-layouts.public>
