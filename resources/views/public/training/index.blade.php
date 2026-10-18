@php
    $isVi = app()->getLocale() === 'vi';
    $trainingImage = Vite::asset('resources/images/pages/training/training-practical-mastery.webp');
@endphp

<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null" variant="v2">
    {{-- =========================================================================
       PAGE HERO — Academic Academy Header
       ========================================================================= --}}
    <section class="v2-page-hero v2-page-hero--media bg-[#F5F0E8] border-b border-[#E2D9CC] py-16 lg:py-24">
        <x-public.v2.container>
            <div class="v2-page-hero__grid">
                <div class="v2-page-hero__copy">
                    <div class="flex items-center gap-3 mb-4">
                        <x-public.v2.eyebrow>{{ __('training.index.eyebrow') }}</x-public.v2.eyebrow>
                        <span class="w-8 h-px bg-[#C9A24D]/60" aria-hidden="true"></span>
                        <span class="font-serif italic text-xs text-[#C9A24D]">Académie de Beauté</span>
                    </div>
                    <x-public.v2.display-heading level="1" size="xl" class="v2-primary-page-hero__title font-serif text-[#181311]">
                        {{ __('training.index.title') }}
                    </x-public.v2.display-heading>
                    <div class="w-12 h-px bg-[#C9A24D] my-6"></div>
                    <x-public.v2.editorial-copy size="lg" class="text-[#594B42] leading-relaxed">
                        {{ __('training.index.intro') }}
                    </x-public.v2.editorial-copy>
                </div>

                <div class="v2-page-hero__media-frame">
                    <div class="border border-[#E2D9CC] p-2 bg-[#FBF9F4] shadow-sm">
                        <x-public.v2.media-frame
                            :src="$trainingImage"
                            :alt="__('training.index.title')"
                            role="landscape"
                            position="portrait"
                            priority
                            class="v2-page-hero__media aspect-[4/3] w-full object-cover"
                        />
                    </div>
                </div>
            </div>
        </x-public.v2.container>
    </section>

    {{-- =========================================================================
       ACADEMIC PROGRAM LEDGER — Course Showcase
       ========================================================================= --}}
    <x-public.v2.section surface="ivory" class="v2-listing-section py-16 lg:py-24">
        <x-public.v2.container>
            @if($courses->isNotEmpty())
                <div class="v2-program-list flex flex-col gap-8 lg:gap-10" data-reveal-group>
                    @foreach($courses as $course)
                        <article @class([
                            'v2-program-row group relative border border-[#E2D9CC] bg-[#FBF9F4] hover:border-[#C9A24D]/70 transition-all duration-500 rounded-none shadow-none p-6 sm:p-8 lg:p-10',
                            'v2-program-row--without-media' => empty($course['media']['url'])
                        ]) data-reveal>
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                                {{-- Course Content & Program Specs --}}
                                <div class="@if(!empty($course['media']['url'])) lg:col-span-7 @else lg:col-span-12 @endif flex flex-col justify-between h-full">
                                    <div>
                                        <div class="flex items-center justify-between gap-4 mb-4">
                                            <p class="v2-program-row__number font-serif italic text-2xl lg:text-3xl text-[#C9A24D] m-0" aria-hidden="true">
                                                {{ str_pad((string) (($courses->firstItem() ?? 1) + $loop->index), 2, '0', STR_PAD_LEFT) }}
                                            </p>
                                            <span class="v2-type-eyebrow uppercase tracking-[0.2em] text-[#C9A24D] text-xs font-semibold px-2.5 py-0.5 border border-[#C9A24D]/30 bg-[#F5F0E8]">
                                                {{ $isVi ? 'Chứng Chỉ Chuyên Nghiệp' : 'Professional Certification' }}
                                            </span>
                                        </div>

                                        <div class="v2-program-row__body">
                                            <x-public.v2.display-heading level="2" size="heading-lg" class="font-serif text-2xl lg:text-3xl text-[#181311] leading-snug mb-3 group-hover:text-[#6A141A] transition-colors">
                                                <a href="{{ $course['url'] }}" class="hover:text-[#6A141A] transition-colors">{{ $course['title'] }}</a>
                                            </x-public.v2.display-heading>

                                            @if(!empty($course['excerpt']))
                                                <p class="v2-type-body text-[#594B42] text-sm lg:text-base leading-relaxed mb-6 max-w-2xl">
                                                    {{ $course['excerpt'] }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Program Spec Strip --}}
                                    @if(!empty($course['duration_display']) || !empty($course['schedule_display']) || !empty($course['tuition']))
                                        <dl class="v2-program-row__facts grid grid-cols-1 sm:grid-cols-3 border border-[#E2D9CC] bg-[#F5F0E8] divide-y sm:divide-y-0 sm:divide-x divide-[#E2D9CC] my-6">
                                            @if(!empty($course['duration_display']))
                                                <div class="p-3 sm:p-4">
                                                    <dt class="text-[11px] uppercase tracking-widest text-[#C9A24D] font-mono font-medium block">{{ __('training.index.duration') }}</dt>
                                                    <dd class="font-serif text-sm sm:text-base text-[#181311] font-semibold mt-1 m-0">{{ $course['duration_display'] }}</dd>
                                                </div>
                                            @endif
                                            @if(!empty($course['schedule_display']))
                                                <div class="p-3 sm:p-4">
                                                    <dt class="text-[11px] uppercase tracking-widest text-[#C9A24D] font-mono font-medium block">{{ __('training.index.schedule') }}</dt>
                                                    <dd class="font-serif text-sm sm:text-base text-[#181311] font-semibold mt-1 m-0">{{ $course['schedule_display'] }}</dd>
                                                </div>
                                            @endif
                                            @if(!empty($course['tuition']))
                                                <div class="p-3 sm:p-4">
                                                    <dt class="text-[11px] uppercase tracking-widest text-[#C9A24D] font-mono font-medium block">{{ __('training.index.tuition') }}</dt>
                                                    <dd class="font-serif text-sm sm:text-base text-[#6A141A] font-semibold mt-1 m-0">{{ $course['tuition']['display'] }}</dd>
                                                </div>
                                            @endif
                                        </dl>
                                    @endif

                                    <div class="pt-2">
                                        <x-public.v2.button :href="$course['url']" variant="text" class="text-xs uppercase tracking-widest font-semibold text-[#181311] hover:text-[#6A141A]">
                                            {{ __('training.index.details') }} <span aria-hidden="true">&rarr;</span>
                                        </x-public.v2.button>
                                    </div>
                                </div>

                                {{-- Image Frame --}}
                                @if(!empty($course['media']['url']))
                                    <div class="lg:col-span-5">
                                        <a href="{{ $course['url'] }}" class="v2-program-row__media block overflow-hidden border border-[#E2D9CC]/80" aria-label="{{ $course['title'] }}">
                                            <x-public.v2.media-frame
                                                :src="$course['media']['url']"
                                                :alt="$course['media']['alt']"
                                                role="landscape"
                                                class="aspect-[16/11] w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                            />
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                {{ $courses->links('components.public.v2.pagination') }}
            @else
                <div class="v2-empty-state border border-[#E2D9CC] p-12 lg:p-16 bg-[#FBF9F4] text-center" role="status">
                    <p class="v2-type-body-lg text-[#594B42] m-0">{{ __('training.index.empty') }}</p>
                </div>
            @endif
        </x-public.v2.container>
    </x-public.v2.section>

    {{-- =========================================================================
       ACADEMY CONCIERGE CTA — Private Training Advisory
       ========================================================================= --}}
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
</x-layouts.public>
