<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null">
    {{-- Hero Header --}}
    <section class="bg-gradient-to-b from-[#F5EFEB] to-[#FAF7F2] pt-28 sm:pt-32 lg:pt-36 pb-12 sm:pb-14 border-b border-[#E8DFC8]/70">
        <x-public.container size="lg">
            <div class="max-w-3xl space-y-3.5" data-reveal>
                <span class="inline-flex items-center gap-2 text-[14px] sm:text-[15px] font-semibold tracking-[0.1em] uppercase text-[#9B7B4F]">
                    <span class="w-6 h-px bg-[#C5A880]/70"></span>
                    {{ __('training.index.eyebrow') }}
                </span>
                <h1 class="text-3xl sm:text-5xl lg:text-[clamp(2.5rem,4vw,3.85rem)] font-semibold tracking-[-0.025em] text-[#5B1121] leading-[1.08] break-words [text-wrap:balance]">
                    {{ __('training.index.title') }}
                </h1>
                <p class="text-[18px] sm:text-[19px] font-medium text-[#554D4A] leading-[1.66] break-words max-w-2xl pt-1">
                    {{ __('training.index.intro') }}
                </p>
            </div>
        </x-public.container>
    </section>

    {{-- Courses Listing Grid / Intentional Empty State --}}
    <section class="bg-[#FAF7F2] py-10 sm:py-14 lg:py-16">
        <x-public.container>
            @if($courses->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8 lg:gap-9" data-reveal-group>
                    @foreach($courses as $course)
                        <article class="group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-[#E8DFC8] bg-white shadow-xs transition duration-300 hover:border-[#C5A880] hover:shadow-lg editorial-card" data-reveal>
                            <a href="{{ $course['url'] }}" class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5B1121] focus-visible:ring-offset-4 focus-visible:ring-offset-[#FAF7F2]">
                                @if(!empty($course['media']['url']))
                                    <div class="aspect-16/10 overflow-hidden bg-brand-warm editorial-image-zoom">
                                        <img src="{{ $course['media']['url'] }}" alt="{{ $course['media']['alt'] }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                                    </div>
                                @else
                                    <div class="aspect-16/10 bg-[#211B19] flex items-center justify-center px-8 text-center relative overflow-hidden">
                                        <div class="absolute inset-0 bg-radial from-[#5B1121]/30 to-transparent"></div>
                                        <span class="text-2xl font-semibold text-[#C5A880] break-words relative z-10">{{ $course['title'] }}</span>
                                    </div>
                                @endif
                            </a>

                            <div class="flex flex-1 flex-col justify-between p-6 sm:p-7">
                                <div class="space-y-3">
                                    <h2 class="text-xl sm:text-[22px] lg:text-[24px] font-semibold leading-[1.2] tracking-tight text-[#5B1121] break-words">
                                        <a href="{{ $course['url'] }}" class="hover:text-[#B3956B] transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5B1121] focus-visible:ring-offset-4">
                                            {{ $course['title'] }}
                                        </a>
                                    </h2>

                                    @if(!empty($course['excerpt']))
                                        <p class="text-[17px] leading-[1.65] text-[#554D4A] break-words">
                                            {{ $course['excerpt'] }}
                                        </p>
                                    @endif

                                    @if(!empty($course['duration_display']) || !empty($course['schedule_display']))
                                        <dl class="flex flex-wrap gap-2 pt-2 text-[14px] sm:text-[15px]">
                                            @if(!empty($course['duration_display']))
                                                <div class="min-w-0">
                                                    <dt class="sr-only">{{ __('training.index.duration') }}</dt>
                                                    <dd class="inline-block px-3 py-1 rounded-full bg-[#FAF7F2] border border-[#E8DFC8] text-[#5B1121] font-medium break-words">{{ $course['duration_display'] }}</dd>
                                                </div>
                                            @endif
                                            @if(!empty($course['schedule_display']))
                                                <div class="min-w-0">
                                                    <dt class="sr-only">{{ __('training.index.schedule') }}</dt>
                                                    <dd class="inline-block px-3 py-1 rounded-full bg-[#FAF7F2] border border-[#E8DFC8] text-[#736965] font-medium break-words">{{ $course['schedule_display'] }}</dd>
                                                </div>
                                            @endif
                                        </dl>
                                    @endif
                                </div>

                                <div class="mt-6 flex items-center justify-between gap-4 border-t border-[#E8DFC8] pt-5">
                                    @if(!empty($course['tuition']))
                                        <p class="text-[16px] text-[#736965]">
                                            {{ __('training.index.tuition') }}
                                            <span class="font-semibold text-[#5B1121]">{{ $course['tuition']['display'] }}</span>
                                        </p>
                                    @endif

                                    <a href="{{ $course['url'] }}" class="ml-auto inline-flex items-center gap-1 text-[15px] font-semibold uppercase tracking-widest text-[#5B1121] hover:text-[#B3956B] transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5B1121] focus-visible:ring-offset-4">
                                        <span>{{ __('training.index.details') }}</span>
                                        <span aria-hidden="true">&rarr;</span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-12" data-reveal>
                    {{ $courses->links() }}
                </div>
            @else
                <div class="mx-auto max-w-xl rounded-2xl border border-[#E8DFC8] bg-white p-8 sm:p-10 text-center shadow-xs" data-reveal>
                    <div class="w-12 h-12 mx-auto rounded-full bg-[#FAF7F2] border border-[#E8DFC8] flex items-center justify-center text-[#9B7B4F] mb-3.5">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h2 class="text-xl sm:text-[22px] font-semibold text-[#5B1121] break-words">
                        {{ __('training.index.empty') }}
                    </h2>
                </div>
            @endif
        </x-public.container>
    </section>
</x-layouts.public>
