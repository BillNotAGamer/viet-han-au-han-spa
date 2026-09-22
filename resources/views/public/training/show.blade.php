<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null" variant="v2">
    <article>
        <section @class(['v2-detail-hero', 'v2-detail-hero--without-media' => empty($course['hero_media']['url'])])>
            @if(!empty($course['hero_media']['url']))
                <img
                    src="{{ $course['hero_media']['url'] }}"
                    alt="{{ $course['hero_media']['alt'] }}"
                    class="v2-detail-hero__image"
                    loading="eager"
                    fetchpriority="high"
                >
                <div class="v2-detail-hero__veil"></div>
            @endif

            <x-public.v2.container>
                <div class="v2-detail-hero__copy">
                    <x-public.v2.eyebrow inverse>{{ __('training.index.eyebrow') }}</x-public.v2.eyebrow>
                    <x-public.v2.display-heading level="1" size="xl">{{ $course['title'] }}</x-public.v2.display-heading>
                    @if(!empty($course['excerpt']))
                        <p class="v2-type-body-lg">{{ $course['excerpt'] }}</p>
                    @endif
                </div>
            </x-public.v2.container>
        </section>

        @if(!empty($course['duration_display']) || !empty($course['schedule_display']) || !empty($course['target_audience']) || !empty($course['tuition']))
            <section class="v2-detail-facts" aria-labelledby="course-information-heading">
                <x-public.v2.container>
                    <h2 id="course-information-heading" class="v2-detail-facts__title">{{ __('training.detail.course_info') }}</h2>
                    <dl class="v2-detail-facts__list">
                        @if(!empty($course['duration_display']))
                            <div><dt>{{ __('training.detail.duration') }}</dt><dd>{{ $course['duration_display'] }}</dd></div>
                        @endif
                        @if(!empty($course['schedule_display']))
                            <div><dt>{{ __('training.detail.schedule') }}</dt><dd>{{ $course['schedule_display'] }}</dd></div>
                        @endif
                        @if(!empty($course['target_audience']))
                            <div><dt>{{ __('training.detail.target_audience') }}</dt><dd>{{ $course['target_audience'] }}</dd></div>
                        @endif
                        @if(!empty($course['tuition']))
                            <div><dt>{{ __('training.detail.tuition') }}</dt><dd>{{ $course['tuition']['display'] }}</dd></div>
                        @endif
                    </dl>
                </x-public.v2.container>
            </section>
        @endif

        @if(!empty($course['content']))
            <x-public.v2.section surface="paper">
                <x-public.v2.container size="reading" class="v2-reading-section">
                    <x-public.v2.eyebrow>{{ __('training.detail.overview') }}</x-public.v2.eyebrow>
                    <x-public.v2.display-heading level="2" size="heading-lg">{{ __('training.detail.overview') }}</x-public.v2.display-heading>
                    <div class="v2-prose">
                        @foreach($course['content'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                </x-public.v2.container>
            </x-public.v2.section>
        @endif

        @if(!empty($course['curriculum_modules']))
            <x-public.v2.section surface="ivory">
                <x-public.v2.container>
                    <div class="v2-section-heading v2-section-heading--compact">
                        <div>
                            <x-public.v2.eyebrow>{{ __('training.detail.curriculum') }}</x-public.v2.eyebrow>
                            <x-public.v2.display-heading level="2" size="heading-lg">{{ __('training.detail.curriculum') }}</x-public.v2.display-heading>
                        </div>
                    </div>
                    <ol class="v2-curriculum-list">
                        @foreach($course['curriculum_modules'] as $module)
                            <li>
                                <span aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <div>
                                    @if($module['title'] !== '')
                                        <h3>{{ $module['title'] }}</h3>
                                    @endif
                                    @if($module['description'] !== '')
                                        <p>{{ $module['description'] }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </x-public.v2.container>
            </x-public.v2.section>
        @endif

        @if(!empty($course['benefits']))
            <x-public.v2.section surface="paper">
                <x-public.v2.container>
                    <div class="v2-section-heading v2-section-heading--compact">
                        <div>
                            <x-public.v2.eyebrow>{{ __('training.detail.benefits') }}</x-public.v2.eyebrow>
                            <x-public.v2.display-heading level="2" size="heading-lg">{{ __('training.detail.benefits') }}</x-public.v2.display-heading>
                        </div>
                    </div>
                    <div class="v2-narrative-list">
                        @foreach($course['benefits'] as $benefit)
                            <article>
                                <span aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <div>
                                    @if($benefit['title'] !== '')
                                        <h3>{{ $benefit['title'] }}</h3>
                                    @endif
                                    @if($benefit['description'] !== '')
                                        <p>{{ $benefit['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </x-public.v2.container>
            </x-public.v2.section>
        @endif

        @if(!empty($course['gallery']))
            <x-public.v2.section surface="muted">
                <x-public.v2.container size="wide">
                    <div class="v2-section-heading v2-section-heading--compact">
                        <div>
                            <x-public.v2.eyebrow>{{ __('training.detail.gallery') }}</x-public.v2.eyebrow>
                            <x-public.v2.display-heading level="2" size="heading-lg">{{ __('training.detail.gallery') }}</x-public.v2.display-heading>
                        </div>
                    </div>
                    <div class="v2-narrative-gallery">
                        @foreach($course['gallery'] as $image)
                            <x-public.v2.media-frame
                                :src="$image['url']"
                                :alt="$image['alt']"
                                :caption="$image['caption'] ?? null"
                                :role="$loop->first ? 'landscape' : 'gallery'"
                            />
                        @endforeach
                    </div>
                </x-public.v2.container>
            </x-public.v2.section>
        @endif

        @if(!empty($course['faqs']))
            <x-public.v2.section surface="paper">
                <x-public.v2.container size="reading" class="v2-faq-section">
                    <x-public.v2.eyebrow>{{ __('training.detail.faqs') }}</x-public.v2.eyebrow>
                    <x-public.v2.display-heading level="2" size="heading-lg">{{ __('training.detail.faqs') }}</x-public.v2.display-heading>
                    <div class="v2-service-faq__list">
                        @foreach($course['faqs'] as $faq)
                            <details>
                                <summary><span>{{ $faq['question'] }}</span><span aria-hidden="true">+</span></summary>
                                <p>{{ $faq['answer'] }}</p>
                            </details>
                        @endforeach
                    </div>
                </x-public.v2.container>
            </x-public.v2.section>
        @endif

        <section class="v2-concierge-cta">
            <x-public.v2.container size="reading">
                <x-public.v2.eyebrow inverse>{{ __('training.index.eyebrow') }}</x-public.v2.eyebrow>
                <x-public.v2.display-heading level="2" size="heading-lg">{{ __('training.detail.inquiry_title') }}</x-public.v2.display-heading>
                <p class="v2-type-body-lg">{{ __('training.detail.inquiry_copy') }}</p>
                <x-public.v2.button :href="$contactHref" variant="inverse">{{ __('training.detail.inquiry_cta') }}</x-public.v2.button>
            </x-public.v2.container>
        </section>
    </article>
</x-layouts.public>
