<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null">
    {{-- Hero Header --}}
    <section class="bg-gradient-to-b from-[#F5EFEB] to-[#FAF7F2] pt-28 sm:pt-32 lg:pt-36 pb-12 sm:pb-14 border-b border-[#E8DFC8]/70">
        <x-public.container size="lg">
            <div class="max-w-3xl space-y-3.5" data-reveal>
                <span class="inline-flex items-center gap-2 text-[14px] sm:text-[15px] font-semibold tracking-[0.1em] uppercase text-[#9B7B4F]">
                    <span class="w-6 h-px bg-[#C5A880]/70"></span>
                    {{ __('blog.index.eyebrow') }}
                </span>
                <h1 class="text-3xl sm:text-5xl lg:text-[clamp(2.5rem,4vw,3.85rem)] font-semibold tracking-[-0.025em] text-[#5B1121] leading-[1.08] break-words [text-wrap:balance]">
                    {{ __('blog.index.title') }}
                </h1>
                <p class="text-[18px] sm:text-[19px] font-medium text-[#554D4A] leading-[1.66] break-words max-w-2xl pt-1">
                    {{ __('blog.index.intro') }}
                </p>
            </div>
        </x-public.container>
    </section>

    {{-- Blog Articles Listing Grid --}}
    <section class="bg-[#FAF7F2] py-12 sm:py-16 lg:py-20">
        <x-public.container>
            @if($posts->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8 lg:gap-9" data-reveal-group>
                    @foreach($posts as $post)
                        <article class="group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-[#E8DFC8] bg-white shadow-xs transition duration-300 hover:border-[#C5A880] hover:shadow-lg editorial-card" data-reveal>
                            <a href="{{ $post['url'] }}" class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5B1121] focus-visible:ring-offset-4 focus-visible:ring-offset-[#FAF7F2]">
                                @if(!empty($post['media']['url']))
                                    <div class="aspect-16/10 overflow-hidden bg-brand-warm editorial-image-zoom">
                                        <img src="{{ $post['media']['url'] }}" alt="{{ $post['media']['alt'] }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                                    </div>
                                @else
                                    <div class="aspect-16/10 bg-[#211B19] flex items-center justify-center px-8 text-center relative overflow-hidden">
                                        <div class="absolute inset-0 bg-radial from-[#5B1121]/30 to-transparent"></div>
                                        <span class="text-xl sm:text-2xl font-semibold text-[#C5A880] break-words relative z-10">{{ $post['title'] }}</span>
                                    </div>
                                @endif
                            </a>

                            <div class="flex flex-1 flex-col justify-between p-6 sm:p-7">
                                <div class="space-y-3">
                                    @if(!empty($post['category_name']))
                                        <p class="text-[14px] sm:text-[15px] font-semibold uppercase tracking-widest text-[#9B7B4F]">
                                            {{ $post['category_name'] }}
                                        </p>
                                    @endif

                                    <h2 class="text-xl sm:text-[22px] lg:text-[24px] font-semibold leading-[1.2] tracking-tight text-[#5B1121] break-words">
                                        <a href="{{ $post['url'] }}" class="hover:text-[#B3956B] transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5B1121] focus-visible:ring-offset-4">
                                            {{ $post['title'] }}
                                        </a>
                                    </h2>

                                    @if(!empty($post['excerpt']))
                                        <p class="text-[17px] leading-[1.65] text-[#554D4A] break-words">
                                            {{ $post['excerpt'] }}
                                        </p>
                                    @endif
                                </div>

                                <div class="mt-6 flex items-center justify-between gap-4 border-t border-[#E8DFC8] pt-5">
                                    @if(!empty($post['published_at_display']))
                                        <time datetime="{{ $post['published_at_iso'] }}" class="text-[15px] sm:text-[16px] text-[#736965]">
                                            {{ $post['published_at_display'] }}
                                        </time>
                                    @endif

                                    <a href="{{ $post['url'] }}" class="ml-auto inline-flex items-center gap-1 text-[15px] font-semibold uppercase tracking-widest text-[#5B1121] hover:text-[#B3956B] transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5B1121] focus-visible:ring-offset-4">
                                        <span>{{ __('blog.index.details') }}</span>
                                        <span aria-hidden="true">&rarr;</span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-12" data-reveal>
                    {{ $posts->links() }}
                </div>
            @else
                <div class="mx-auto max-w-2xl rounded-2xl border border-[#E8DFC8] bg-white px-8 py-14 text-center shadow-xs" data-reveal>
                    <div class="w-14 h-14 mx-auto rounded-full bg-[#FAF7F2] border border-[#E8DFC8] flex items-center justify-center text-[#9B7B4F] mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                    </div>
                    <p class="text-2xl font-semibold text-[#5B1121] break-words">
                        {{ __('blog.index.empty') }}
                    </p>
                </div>
            @endif
        </x-public.container>
    </section>
</x-layouts.public>
