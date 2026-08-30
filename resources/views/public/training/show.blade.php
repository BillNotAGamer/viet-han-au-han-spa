<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref">
    <article>
        <section class="relative bg-[#181312] pt-28 sm:pt-32 lg:pt-36 text-white">
            @if(!empty($course['hero_media']['url']))
                <div class="absolute inset-0">
                    <img src="{{ $course['hero_media']['url'] }}" alt="{{ $course['hero_media']['alt'] }}" class="h-full w-full object-cover" loading="eager">
                    <div class="absolute inset-0 bg-[#181312]/58"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-[#181312]/90 via-[#181312]/62 to-[#181312]/18"></div>
                </div>
            @endif

            <x-public.container size="lg">
                <div class="relative z-10 flex min-h-[520px] max-w-3xl flex-col justify-end pb-16 sm:pb-20 lg:pb-24">
                    <p class="mb-4 text-xs sm:text-sm font-semibold uppercase tracking-widest text-[#C5A880]">
                        {{ __('training.index.eyebrow') }}
                    </p>

                    <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight tracking-normal break-words [text-wrap:balance]">
                        {{ $course['title'] }}
                    </h1>

                    @if(!empty($course['excerpt']))
                        <p class="mt-5 max-w-2xl text-base sm:text-lg leading-relaxed text-white/82 break-words">
                            {{ $course['excerpt'] }}
                        </p>
                    @endif
                </div>
            </x-public.container>
        </section>

        <section class="bg-[#FAF7F2] py-14 sm:py-20 lg:py-24">
            <x-public.container size="lg">
                <div class="grid grid-cols-1 gap-12 lg:grid-cols-[minmax(0,1fr)_360px] lg:gap-16">
                    <div class="min-w-0 space-y-12">
                        @if(!empty($course['content']))
                            <section class="space-y-5">
                                <h2 class="font-serif text-3xl sm:text-4xl font-bold text-[#5B1121] tracking-normal">
                                    {{ __('training.detail.overview') }}
                                </h2>
                                <div class="space-y-5 text-base sm:text-lg leading-relaxed text-brand-text-secondary">
                                    @foreach($course['content'] as $paragraph)
                                        <p class="break-words">{{ $paragraph }}</p>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @if(!empty($course['curriculum_modules']))
                            <section class="space-y-6">
                                <h2 class="font-serif text-3xl sm:text-4xl font-bold text-[#5B1121] tracking-normal">
                                    {{ __('training.detail.curriculum') }}
                                </h2>
                                <div class="space-y-4">
                                    @foreach($course['curriculum_modules'] as $module)
                                        <div class="grid grid-cols-[44px_minmax(0,1fr)] gap-4 border-b border-[#E8DFC8] pb-5">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#5B1121] text-sm font-semibold text-white">
                                                {{ $loop->iteration }}
                                            </div>
                                            <div class="min-w-0">
                                                @if($module['title'] !== '')
                                                    <h3 class="font-serif text-xl font-bold text-[#5B1121] break-words">{{ $module['title'] }}</h3>
                                                @endif
                                                @if($module['description'] !== '')
                                                    <p class="mt-2 text-sm sm:text-base leading-relaxed text-brand-text-secondary break-words">{{ $module['description'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @if(!empty($course['benefits']))
                            <section class="space-y-6">
                                <h2 class="font-serif text-3xl sm:text-4xl font-bold text-[#5B1121] tracking-normal">
                                    {{ __('training.detail.benefits') }}
                                </h2>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    @foreach($course['benefits'] as $benefit)
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

                        @if(!empty($course['gallery']))
                            <section class="space-y-6">
                                <h2 class="font-serif text-3xl sm:text-4xl font-bold text-[#5B1121] tracking-normal">
                                    {{ __('training.detail.gallery') }}
                                </h2>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    @foreach($course['gallery'] as $image)
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

                        @if(!empty($course['faqs']))
                            <section class="space-y-6">
                                <h2 class="font-serif text-3xl sm:text-4xl font-bold text-[#5B1121] tracking-normal">
                                    {{ __('training.detail.faqs') }}
                                </h2>
                                <div class="space-y-4">
                                    @foreach($course['faqs'] as $faq)
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
                        <section class="border border-[#E8DFC8] bg-white p-6 sm:p-7 shadow-xs">
                            <h2 class="font-serif text-2xl font-bold text-[#5B1121] tracking-normal">
                                {{ __('training.detail.course_info') }}
                            </h2>

                            <dl class="mt-5 divide-y divide-[#E8DFC8]">
                                @if(!empty($course['tuition']))
                                    <div class="py-4 first:pt-0 last:pb-0">
                                        <dt class="text-xs font-semibold uppercase tracking-widest text-[#B3956B]">{{ __('training.detail.tuition') }}</dt>
                                        <dd class="mt-1 font-semibold text-[#5B1121] break-words">{{ $course['tuition']['display'] }}</dd>
                                    </div>
                                @endif

                                @if(!empty($course['duration_display']))
                                    <div class="py-4 first:pt-0 last:pb-0">
                                        <dt class="text-xs font-semibold uppercase tracking-widest text-[#B3956B]">{{ __('training.detail.duration') }}</dt>
                                        <dd class="mt-1 text-brand-text-secondary break-words">{{ $course['duration_display'] }}</dd>
                                    </div>
                                @endif

                                @if(!empty($course['schedule_display']))
                                    <div class="py-4 first:pt-0 last:pb-0">
                                        <dt class="text-xs font-semibold uppercase tracking-widest text-[#B3956B]">{{ __('training.detail.schedule') }}</dt>
                                        <dd class="mt-1 text-brand-text-secondary break-words">{{ $course['schedule_display'] }}</dd>
                                    </div>
                                @endif

                                @if(!empty($course['target_audience']))
                                    <div class="py-4 first:pt-0 last:pb-0">
                                        <dt class="text-xs font-semibold uppercase tracking-widest text-[#B3956B]">{{ __('training.detail.target_audience') }}</dt>
                                        <dd class="mt-1 text-brand-text-secondary break-words">{{ $course['target_audience'] }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </section>

                        <section class="border border-[#C5A880]/45 bg-[#211B19] p-7 text-white shadow-md">
                            <h2 class="font-serif text-2xl font-bold tracking-normal break-words">
                                {{ __('training.detail.inquiry_title') }}
                            </h2>
                            <p class="mt-3 text-sm leading-relaxed text-white/76 break-words">
                                {{ __('training.detail.inquiry_copy') }}
                            </p>
                            <a href="{{ $contactHref }}" class="mt-6 inline-flex h-12 items-center justify-center rounded-sm bg-[#C5A880] px-7 text-sm font-semibold uppercase tracking-widest text-[#181312] transition hover:bg-[#B3956B] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C5A880] focus-visible:ring-offset-4 focus-visible:ring-offset-[#211B19]">
                                {{ __('training.detail.inquiry_cta') }}
                            </a>
                        </section>
                    </aside>
                </div>
            </x-public.container>
        </section>
    </article>
</x-layouts.public>
