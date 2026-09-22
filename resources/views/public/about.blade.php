@php
    $locale = app()->getLocale();
    $bookingUrl = $locale === 'en' ? route('en.booking.create') : route('vi.booking.create');
    $contactUrl = $locale === 'en' ? route('en.contact') : route('vi.contact');
    $heroImg = Vite::asset('resources/images/pages/about/about-hero-treatment-space.webp');
    $teamImg = Vite::asset('resources/images/pages/about/about-therapist-team.webp');
    $sanctuaryImg = Vite::asset('resources/images/pages/about/about-sound-bath-sanctuary.webp');
    $headSpaImg = Vite::asset('resources/images/pages/about/about-herbal-head-spa.webp');
    $facialImg = Vite::asset('resources/images/pages/about/about-facial-therapy-care.webp');
@endphp

<x-layouts.public :title="$title" header-mode="solid" :contact-href="$bookingUrl" :seo="$seo ?? null" variant="v2">
    <article>
        <section class="v2-about-hero">
            <img src="{{ $heroImg }}" alt="{{ $content['hero']['title'] }}" class="v2-about-hero__image" width="1800" height="1200" loading="eager" fetchpriority="high">
            <div class="v2-about-hero__veil"></div>
            <x-public.v2.container>
                <div class="v2-about-hero__copy">
                    <x-public.v2.eyebrow inverse>{{ $content['hero']['eyebrow'] }}</x-public.v2.eyebrow>
                    <x-public.v2.display-heading level="1" size="xl">{{ $content['hero']['title'] }}</x-public.v2.display-heading>
                    <p class="v2-type-body-lg">{{ $content['hero']['lead'] }}</p>
                </div>
            </x-public.v2.container>
        </section>

        <x-public.v2.section surface="paper" spacing="spacious">
            <x-public.v2.container>
                <div class="v2-about-manifesto">
                    <div class="v2-about-manifesto__mark" aria-hidden="true">
                        <x-public.v2.logo variant="colored" decorative loading="lazy" />
                    </div>
                    <div class="v2-about-manifesto__copy">
                        <x-public.v2.eyebrow>{{ $content['story']['badge'] }}</x-public.v2.eyebrow>
                        <x-public.v2.display-heading level="2" size="display-lg">{{ $content['story']['title'] }}</x-public.v2.display-heading>
                        <div class="v2-prose v2-prose--lead">
                            @foreach($content['story']['paragraphs'] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-public.v2.container>
        </x-public.v2.section>

        <x-public.v2.section surface="ivory">
            <x-public.v2.container>
                <div class="v2-editorial-split">
                    <x-public.v2.media-frame :src="$teamImg" :alt="$content['team']['title']" role="landscape" class="v2-editorial-split__media" />
                    <div class="v2-editorial-split__copy">
                        <x-public.v2.eyebrow>{{ $content['team']['badge'] }}</x-public.v2.eyebrow>
                        <x-public.v2.display-heading level="2" size="heading-lg">{{ $content['team']['title'] }}</x-public.v2.display-heading>
                        <p class="v2-type-body-lg">{{ $content['team']['description'] }}</p>
                    </div>
                </div>
            </x-public.v2.container>
        </x-public.v2.section>

        <x-public.v2.section surface="paper">
            <x-public.v2.container>
                <div class="v2-section-heading">
                    <div>
                        <x-public.v2.eyebrow>{{ $content['values']['badge'] }}</x-public.v2.eyebrow>
                        <x-public.v2.display-heading level="2" size="heading-lg">{{ $content['values']['title'] }}</x-public.v2.display-heading>
                    </div>
                    <p class="v2-type-body-lg">{{ $content['values']['subtitle'] }}</p>
                </div>

                <div class="v2-narrative-list">
                    @foreach($content['values']['items'] as $value)
                        <article>
                            <span aria-hidden="true">{{ $value['number'] }}</span>
                            <div>
                                <h3>{{ $value['title'] }}</h3>
                                <p>{{ $value['description'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </x-public.v2.container>
        </x-public.v2.section>

        <x-public.v2.section surface="espresso" class="v2-about-atmosphere">
            <x-public.v2.container size="wide">
                <div class="v2-about-atmosphere__heading">
                    <x-public.v2.eyebrow inverse>{{ $content['atmosphere']['badge'] }}</x-public.v2.eyebrow>
                    <x-public.v2.display-heading level="2" size="display-lg">{{ $content['atmosphere']['title'] }}</x-public.v2.display-heading>
                    <p class="v2-type-body-lg">{{ $content['atmosphere']['subtitle'] }}</p>
                </div>

                <div class="v2-about-gallery">
                    <x-public.v2.media-frame :src="$sanctuaryImg" :alt="$content['atmosphere']['items'][0]['title']" :caption="$content['atmosphere']['items'][0]['caption']" role="landscape" />
                    <x-public.v2.media-frame :src="$headSpaImg" :alt="$content['atmosphere']['items'][1]['title']" :caption="$content['atmosphere']['items'][1]['caption']" role="portrait" />
                    <x-public.v2.media-frame :src="$facialImg" :alt="$content['atmosphere']['items'][2]['title']" :caption="$content['atmosphere']['items'][2]['caption']" role="portrait" />
                </div>
            </x-public.v2.container>
        </x-public.v2.section>

        <section class="v2-concierge-cta">
            <x-public.v2.container size="reading">
                <x-public.v2.eyebrow inverse>{{ __('common.brand_name') }}</x-public.v2.eyebrow>
                <x-public.v2.display-heading level="2" size="heading-lg">{{ $content['cta']['title'] }}</x-public.v2.display-heading>
                <p class="v2-type-body-lg">{{ $content['cta']['description'] }}</p>
                <div class="v2-concierge-cta__actions">
                    <x-public.v2.button :href="$bookingUrl" variant="inverse" x-data="{}" data-booking-modal-trigger @click.prevent="$dispatch('open-booking-modal', { trigger: $el })">{{ $content['cta']['button_booking'] }}</x-public.v2.button>
                    <x-public.v2.button :href="$contactUrl" variant="text">{{ $content['cta']['button_contact'] }} <span aria-hidden="true">&rarr;</span></x-public.v2.button>
                </div>
            </x-public.v2.container>
        </section>
    </article>
</x-layouts.public>
