@php
    $locale = app()->getLocale();
    $bookingUrl = $locale === 'en' ? route('en.booking.create') : route('vi.booking.create');
    $lobbyImg = Vite::asset('resources/images/pages/contact/contact-reception-lobby.webp');
    $loungeImg = Vite::asset('resources/images/pages/contact/contact-consultation-lounge.webp');
@endphp

<x-layouts.public :title="$title" header-mode="solid" :contact-href="$bookingUrl" :seo="$seo ?? null" variant="v2">
    <article>
        <section class="v2-page-hero v2-page-hero--media">
            <x-public.v2.container>
                <div class="v2-page-hero__grid">
                    <div class="v2-page-hero__copy">
                        <x-public.v2.eyebrow>{{ $content['hero']['eyebrow'] }}</x-public.v2.eyebrow>
                        <x-public.v2.display-heading level="1" size="xl">{{ $content['hero']['title'] }}</x-public.v2.display-heading>
                        <x-public.v2.editorial-copy size="lg">{{ $content['hero']['lead'] }}</x-public.v2.editorial-copy>
                    </div>
                    <x-public.v2.media-frame :src="$lobbyImg" :alt="$content['hero']['title']" role="landscape" priority class="v2-page-hero__media" />
                </div>
            </x-public.v2.container>
        </section>

        <x-public.v2.section surface="paper" class="v2-contact-section">
            <x-public.v2.container>
                <div class="v2-contact-methods">
                    <article>
                        <p class="v2-type-eyebrow">{{ $content['cards']['phone']['label'] }}</p>
                        <a href="{{ $content['cards']['phone']['href'] }}" class="v2-contact-methods__value">{{ $content['cards']['phone']['value'] }}</a>
                        <p class="v2-type-body">{{ $content['cards']['phone']['subtext'] }}</p>
                    </article>

                    <article>
                        <p class="v2-type-eyebrow">{{ $content['cards']['email']['label'] }}</p>
                        <a href="{{ $content['cards']['email']['href'] }}" class="v2-contact-methods__value">{{ $content['cards']['email']['value'] }}</a>
                        <p class="v2-type-body">{{ $content['cards']['email']['subtext'] }}</p>
                    </article>

                    <article>
                        <p class="v2-type-eyebrow">{{ $content['cards']['booking']['label'] }}</p>
                        <h2 class="v2-contact-methods__value">{{ $content['cards']['booking']['title'] }}</h2>
                        <p class="v2-type-body">{{ $content['cards']['booking']['subtext'] }}</p>
                        <x-public.v2.button
                            :href="$bookingUrl"
                            variant="text"
                            x-data="{}"
                            data-booking-modal-trigger
                            x-on:click.prevent="$dispatch('open-booking-modal', { trigger: $el })"
                        >
                            {{ $content['cards']['booking']['cta'] }} <span aria-hidden="true">&rarr;</span>
                        </x-public.v2.button>
                    </article>
                </div>
            </x-public.v2.container>
        </x-public.v2.section>

        <x-public.v2.section surface="ivory">
            <x-public.v2.container>
                <div class="v2-editorial-split v2-editorial-split--reverse">
                    <x-public.v2.media-frame :src="$loungeImg" :alt="$content['atmosphere']['title']" role="landscape" class="v2-editorial-split__media" />
                    <div class="v2-editorial-split__copy">
                        <x-public.v2.eyebrow>{{ $content['atmosphere']['badge'] }}</x-public.v2.eyebrow>
                        <x-public.v2.display-heading level="2" size="heading-lg">{{ $content['atmosphere']['title'] }}</x-public.v2.display-heading>
                        <p class="v2-type-body-lg">{{ $content['atmosphere']['description'] }}</p>
                    </div>
                </div>
            </x-public.v2.container>
        </x-public.v2.section>

        <section class="v2-concierge-cta">
            <x-public.v2.container size="reading">
                <x-public.v2.eyebrow inverse>{{ $content['cta']['badge'] }}</x-public.v2.eyebrow>
                <x-public.v2.display-heading level="2" size="heading-lg">{{ $content['cta']['title'] }}</x-public.v2.display-heading>
                <p class="v2-type-body-lg">{{ $content['cta']['description'] }}</p>
                <x-public.v2.button
                    :href="$bookingUrl"
                    variant="inverse"
                    x-data="{}"
                    data-booking-modal-trigger
                    x-on:click.prevent="$dispatch('open-booking-modal', { trigger: $el })"
                >{{ $content['cta']['button'] }}</x-public.v2.button>
            </x-public.v2.container>
        </section>
    </article>
</x-layouts.public>
