@php
    $trainingImage = Vite::asset('resources/images/pages/training/training-practical-mastery.webp');
@endphp

<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null" variant="v2">
    <section class="v2-page-hero v2-page-hero--media">
        <x-public.v2.container>
            <div class="v2-page-hero__grid">
                <div class="v2-page-hero__copy">
                    <x-public.v2.eyebrow>{{ __('training.index.eyebrow') }}</x-public.v2.eyebrow>
                    <x-public.v2.display-heading level="1" size="xl" class="v2-primary-page-hero__title">{{ __('training.index.title') }}</x-public.v2.display-heading>
                    <x-public.v2.editorial-copy size="lg">{{ __('training.index.intro') }}</x-public.v2.editorial-copy>
                </div>

                <x-public.v2.media-frame
                    :src="$trainingImage"
                    :alt="__('training.index.title')"
                    role="landscape"
                    position="portrait"
                    priority
                    class="v2-page-hero__media"
                />
            </div>
        </x-public.v2.container>
    </section>

    <x-public.v2.section surface="ivory" class="v2-listing-section">
        <x-public.v2.container>
            @if($courses->isNotEmpty())
                <div class="v2-program-list" data-reveal-group>
                    @foreach($courses as $course)
                        <article @class(['v2-program-row', 'v2-program-row--without-media' => empty($course['media']['url'])]) data-reveal>
                            <p class="v2-program-row__number" aria-hidden="true">{{ str_pad((string) (($courses->firstItem() ?? 1) + $loop->index), 2, '0', STR_PAD_LEFT) }}</p>

                            @if(!empty($course['media']['url']))
                                <a href="{{ $course['url'] }}" class="v2-program-row__media" aria-label="{{ $course['title'] }}">
                                    <x-public.v2.media-frame
                                        :src="$course['media']['url']"
                                        :alt="$course['media']['alt']"
                                        role="landscape"
                                    />
                                </a>
                            @endif

                            <div class="v2-program-row__body">
                                <x-public.v2.display-heading level="2" size="heading-lg">
                                    <a href="{{ $course['url'] }}">{{ $course['title'] }}</a>
                                </x-public.v2.display-heading>
                                @if(!empty($course['excerpt']))
                                    <p class="v2-type-body">{{ $course['excerpt'] }}</p>
                                @endif

                                @if(!empty($course['duration_display']) || !empty($course['schedule_display']) || !empty($course['tuition']))
                                    <dl class="v2-program-row__facts">
                                        @if(!empty($course['duration_display']))
                                            <div>
                                                <dt>{{ __('training.index.duration') }}</dt>
                                                <dd>{{ $course['duration_display'] }}</dd>
                                            </div>
                                        @endif
                                        @if(!empty($course['schedule_display']))
                                            <div>
                                                <dt>{{ __('training.index.schedule') }}</dt>
                                                <dd>{{ $course['schedule_display'] }}</dd>
                                            </div>
                                        @endif
                                        @if(!empty($course['tuition']))
                                            <div>
                                                <dt>{{ __('training.index.tuition') }}</dt>
                                                <dd>{{ $course['tuition']['display'] }}</dd>
                                            </div>
                                        @endif
                                    </dl>
                                @endif

                                <x-public.v2.button :href="$course['url']" variant="text">
                                    {{ __('training.index.details') }} <span aria-hidden="true">&rarr;</span>
                                </x-public.v2.button>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{ $courses->links('components.public.v2.pagination') }}
            @else
                <div class="v2-empty-state" role="status">
                    <p class="v2-type-body-lg">{{ __('training.index.empty') }}</p>
                </div>
            @endif
        </x-public.v2.container>
    </x-public.v2.section>

    <section class="v2-concierge-cta">
        <x-public.v2.container size="reading">
            <x-public.v2.eyebrow inverse>{{ __('training.index.eyebrow') }}</x-public.v2.eyebrow>
            <x-public.v2.display-heading level="2" size="heading-lg">{{ __('training.detail.inquiry_title') }}</x-public.v2.display-heading>
            <p class="v2-type-body-lg">{{ __('training.detail.inquiry_copy') }}</p>
            <x-public.v2.button :href="$contactHref" variant="inverse">{{ __('training.detail.inquiry_cta') }}</x-public.v2.button>
        </x-public.v2.container>
    </section>
</x-layouts.public>
