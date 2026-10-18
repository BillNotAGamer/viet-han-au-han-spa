@php
    $isVi = app()->getLocale() === 'vi';
@endphp

<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null" variant="v2">
    <article>
        {{-- =====================================================================
           ACADEMY COURSE HERO
           ===================================================================== --}}
        <section @class(['v2-detail-hero relative', 'v2-detail-hero--without-media' => empty($course['hero_media']['url'])])>
            @if(!empty($course['hero_media']['url']))
                <img
                    src="{{ $course['hero_media']['url'] }}"
                    alt="{{ $course['hero_media']['alt'] }}"
                    class="v2-detail-hero__image"
                    loading="eager"
                    fetchpriority="high"
                >
                <div class="v2-detail-hero__veil" aria-hidden="true"></div>
            @endif

            <x-public.v2.container class="relative z-10">
                <div class="v2-detail-hero__copy max-w-3xl pt-28 pb-16 lg:pb-24">
                    <div class="flex items-center gap-3 mb-4">
                        <x-public.v2.eyebrow inverse>{{ __('training.index.eyebrow') }}</x-public.v2.eyebrow>
                        <span class="w-8 h-px bg-[#C9A24D]/60" aria-hidden="true"></span>
                        <span class="font-serif italic text-xs text-[#C9A24D]">Programme Académique</span>
                    </div>

                    <x-public.v2.display-heading level="1" size="xl" class="font-serif text-[#EFE8DC]">
                        {{ $course['title'] }}
                    </x-public.v2.display-heading>

                    @if(!empty($course['excerpt']))
                        <p class="v2-type-body-lg text-base lg:text-lg text-[#EFE8DC]/85 mt-4 max-w-2xl leading-relaxed">
                            {{ $course['excerpt'] }}
                        </p>
                    @endif

                    <div class="mt-8">
                        <a href="{{ $contactHref }}" class="v2-button v2-button--primary">
                            {{ __('training.detail.inquiry_cta') }}
                        </a>
                    </div>
                </div>
            </x-public.v2.container>
        </section>

        {{-- =====================================================================
           ACADEMIC DOSSIER SPEC STRIP — Key Program Facts
           ===================================================================== --}}
        @if(!empty($course['duration_display']) || !empty($course['schedule_display']) || !empty($course['target_audience']) || !empty($course['tuition']))
            <section class="v2-detail-facts border-b border-[#E2D9CC] bg-[#F5F0E8] py-8 lg:py-12" aria-labelledby="course-information-heading">
                <x-public.v2.container>
                    <div class="flex items-center justify-between pb-4 border-b border-[#E2D9CC] mb-6">
                        <h2 id="course-information-heading" class="v2-detail-facts__title font-serif text-xl sm:text-2xl text-[#181311] m-0">
                            {{ __('training.detail.course_info') }}
                        </h2>
                        <span class="text-xs uppercase tracking-widest text-[#C9A24D] font-mono font-medium">Spécifications</span>
                    </div>

                    <dl class="v2-detail-facts__list grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 border border-[#E2D9CC] bg-[#FBF9F4] divide-y sm:divide-y-0 sm:divide-x divide-[#E2D9CC] m-0">
                        @if(!empty($course['duration_display']))
                            <div class="p-5 lg:p-6">
                                <dt class="text-[11px] uppercase tracking-widest text-[#C9A24D] font-mono font-medium block">{{ __('training.detail.duration') }}</dt>
                                <dd class="font-serif text-lg text-[#181311] font-semibold mt-1.5 m-0">{{ $course['duration_display'] }}</dd>
                            </div>
                        @endif
                        @if(!empty($course['schedule_display']))
                            <div class="p-5 lg:p-6">
                                <dt class="text-[11px] uppercase tracking-widest text-[#C9A24D] font-mono font-medium block">{{ __('training.detail.schedule') }}</dt>
                                <dd class="font-serif text-lg text-[#181311] font-semibold mt-1.5 m-0">{{ $course['schedule_display'] }}</dd>
                            </div>
                        @endif
                        @if(!empty($course['target_audience']))
                            <div class="p-5 lg:p-6">
                                <dt class="text-[11px] uppercase tracking-widest text-[#C9A24D] font-mono font-medium block">{{ __('training.detail.target_audience') }}</dt>
                                <dd class="font-serif text-base text-[#181311] font-semibold mt-1.5 m-0">{{ $course['target_audience'] }}</dd>
                            </div>
                        @endif
                        @if(!empty($course['tuition']))
                            <div class="p-5 lg:p-6">
                                <dt class="text-[11px] uppercase tracking-widest text-[#C9A24D] font-mono font-medium block">{{ __('training.detail.tuition') }}</dt>
                                <dd class="font-serif text-xl text-[#6A141A] font-semibold mt-1.5 m-0">{{ $course['tuition']['display'] }}</dd>
                            </div>
                        @endif
                    </dl>
                </x-public.v2.container>
            </section>
        @endif

        {{-- =====================================================================
           COURSE OVERVIEW PROSE
           ===================================================================== --}}
        @if(!empty($course['content']))
            <x-public.v2.section surface="paper" class="py-16 lg:py-24 border-b border-[#E2D9CC]">
                <x-public.v2.container size="reading" class="v2-reading-section max-w-3xl mx-auto">
                    <div class="flex items-center gap-3 mb-2">
                        <x-public.v2.eyebrow>{{ __('training.detail.overview') }}</x-public.v2.eyebrow>
                        <span class="w-6 h-px bg-[#C9A24D]" aria-hidden="true"></span>
                    </div>
                    <x-public.v2.display-heading level="2" size="heading-lg" class="font-serif text-3xl lg:text-4xl text-[#181311] mb-6">
                        {{ __('training.detail.overview') }}
                    </x-public.v2.display-heading>
                    <div class="v2-prose space-y-6 text-[#594B42] text-base lg:text-lg leading-relaxed">
                        @foreach($course['content'] as $paragraph)
                            <p class="first-letter:text-3xl first-letter:font-serif first-letter:text-[#6A141A] first-letter:mr-1">{{ $paragraph }}</p>
                        @endforeach
                    </div>
                </x-public.v2.container>
            </x-public.v2.section>
        @endif

        {{-- =====================================================================
           ACADEMIC SYLLABUS LEDGER — Curriculum Modules
           ===================================================================== --}}
        @if(!empty($course['curriculum_modules']))
            <x-public.v2.section surface="ivory" class="py-16 lg:py-24 border-b border-[#E2D9CC]">
                <x-public.v2.container>
                    <div class="v2-section-heading v2-section-heading--compact mb-12">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <x-public.v2.eyebrow>{{ __('training.detail.curriculum') }}</x-public.v2.eyebrow>
                                <span class="w-6 h-px bg-[#C9A24D]" aria-hidden="true"></span>
                                <span class="font-serif italic text-xs text-[#C9A24D]">Syllabus Académique</span>
                            </div>
                            <x-public.v2.display-heading level="2" size="heading-lg" class="font-serif text-3xl lg:text-4xl text-[#181311]">
                                {{ __('training.detail.curriculum') }}
                            </x-public.v2.display-heading>
                        </div>
                    </div>
                    <ol class="v2-curriculum-list flex flex-col gap-6 list-none p-0">
                        @foreach($course['curriculum_modules'] as $module)
                            <li class="border border-[#E2D9CC] bg-[#FBF9F4] hover:border-[#C9A24D]/60 transition-colors p-6 sm:p-8 flex flex-col sm:flex-row sm:items-start gap-6">
                                <span class="font-serif italic text-3xl sm:text-4xl text-[#C9A24D] leading-none shrink-0" aria-hidden="true">
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <div class="flex-1">
                                    @if($module['title'] !== '')
                                        <h3 class="font-serif text-xl sm:text-2xl text-[#181311] font-medium mb-3 leading-snug">
                                            {{ $module['title'] }}
                                        </h3>
                                    @endif
                                    @if($module['description'] !== '')
                                        <p class="text-[#594B42] text-sm sm:text-base leading-relaxed m-0">
                                            {{ $module['description'] }}
                                        </p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </x-public.v2.container>
            </x-public.v2.section>
        @endif

        {{-- =====================================================================
           STUDENT BENEFITS — Career & Practical Advantages
           ===================================================================== --}}
        @if(!empty($course['benefits']))
            <x-public.v2.section surface="paper" class="py-16 lg:py-24 border-b border-[#E2D9CC]">
                <x-public.v2.container>
                    <div class="v2-section-heading v2-section-heading--compact mb-12">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <x-public.v2.eyebrow>{{ __('training.detail.benefits') }}</x-public.v2.eyebrow>
                                <span class="w-6 h-px bg-[#C9A24D]" aria-hidden="true"></span>
                            </div>
                            <x-public.v2.display-heading level="2" size="heading-lg" class="font-serif text-3xl lg:text-4xl text-[#181311]">
                                {{ __('training.detail.benefits') }}
                            </x-public.v2.display-heading>
                        </div>
                    </div>
                    <div class="v2-narrative-list grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($course['benefits'] as $benefit)
                            <article class="border border-[#E2D9CC] bg-[#FBF9F4] p-6 sm:p-8 flex flex-col justify-between">
                                <span class="font-serif italic text-2xl text-[#C9A24D] mb-4 block" aria-hidden="true">
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <div>
                                    @if($benefit['title'] !== '')
                                        <h3 class="font-serif text-lg text-[#181311] font-medium mb-2 leading-snug">{{ $benefit['title'] }}</h3>
                                    @endif
                                    @if($benefit['description'] !== '')
                                        <p class="text-[#594B42] text-sm leading-relaxed m-0">{{ $benefit['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </x-public.v2.container>
            </x-public.v2.section>
        @endif

        {{-- =====================================================================
           ACADEMY GALLERY — Practical Classrooms & Facilities
           ===================================================================== --}}
        @if(!empty($course['gallery']))
            <x-public.v2.section surface="muted" class="py-16 lg:py-24 border-b border-[#E2D9CC]">
                <x-public.v2.container size="wide">
                    <div class="v2-section-heading v2-section-heading--compact mb-12">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <x-public.v2.eyebrow>{{ __('training.detail.gallery') }}</x-public.v2.eyebrow>
                                <span class="w-6 h-px bg-[#C9A24D]" aria-hidden="true"></span>
                            </div>
                            <x-public.v2.display-heading level="2" size="heading-lg" class="font-serif text-3xl lg:text-4xl text-[#181311]">
                                {{ __('training.detail.gallery') }}
                            </x-public.v2.display-heading>
                        </div>
                    </div>
                    <div class="v2-narrative-gallery grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($course['gallery'] as $image)
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
           FREQUENTLY ASKED QUESTIONS
           ===================================================================== --}}
        @if(!empty($course['faqs']))
            <x-public.v2.section surface="paper" class="py-16 lg:py-24 border-b border-[#E2D9CC]">
                <x-public.v2.container size="reading" class="v2-faq-section max-w-3xl mx-auto">
                    <div class="mb-10 text-center">
                        <div class="flex items-center justify-center gap-3 mb-2">
                            <x-public.v2.eyebrow>{{ __('training.detail.faqs') }}</x-public.v2.eyebrow>
                        </div>
                        <x-public.v2.display-heading level="2" size="heading-lg" class="font-serif text-2xl lg:text-3xl text-[#181311]">
                            {{ __('training.detail.faqs') }}
                        </x-public.v2.display-heading>
                    </div>
                    <div class="v2-service-faq__list divide-y divide-[#E2D9CC] border-y border-[#E2D9CC]">
                        @foreach($course['faqs'] as $faq)
                            <details class="group py-5">
                                <summary class="flex items-center justify-between gap-6 cursor-pointer list-none font-serif text-lg sm:text-xl text-[#181311] group-open:text-[#6A141A] transition-colors">
                                    <span>{{ $faq['question'] }}</span>
                                    <span class="w-6 h-6 flex items-center justify-center text-[#C9A24D] font-mono text-xl transition-transform group-open:rotate-45" aria-hidden="true">+</span>
                                </summary>
                                <p class="text-[#594B42] text-sm lg:text-base leading-relaxed mt-4 mb-2 pr-8">{{ $faq['answer'] }}</p>
                            </details>
                        @endforeach
                    </div>
                </x-public.v2.container>
            </x-public.v2.section>
        @endif

        {{-- =====================================================================
           ENROLLMENT CONCIERGE CTA
           ===================================================================== --}}
        <section class="v2-concierge-cta v2-surface--deep py-20 lg:py-28 text-center">
            <x-public.v2.container size="reading" class="max-w-2xl mx-auto">
                <x-public.v2.eyebrow inverse>{{ __('training.index.eyebrow') }}</x-public.v2.eyebrow>
                <x-public.v2.display-heading level="2" size="heading-lg" class="font-serif text-3xl lg:text-4xl text-[#EFE8DC] mt-4">
                    {{ __('training.detail.inquiry_title') }}
                </x-public.v2.display-heading>
                <div class="w-12 h-px bg-[#C9A24D] mx-auto my-6"></div>
                <p class="v2-type-body-lg text-[#EFE8DC]/80 text-base lg:text-lg leading-relaxed mb-8">
                    {{ __('training.detail.inquiry_copy') }}
                </p>
                <x-public.v2.button :href="$contactHref" variant="inverse">
                    {{ __('training.detail.inquiry_cta') }}
                </x-public.v2.button>
            </x-public.v2.container>
        </section>
    </article>
</x-layouts.public>
