@php
    $locale = app()->getLocale();
    $bookingUrl = $locale === 'en' ? route('en.booking.create') : route('vi.booking.create');
    $lobbyImg = Vite::asset('resources/images/pages/contact/contact-reception-lobby.webp');
    $mapEmbedUrl = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1162.253074056725!2d106.6994668582927!3d10.791759610991093!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317528b57e3038f9%3A0xdabf60e42628bc2f!2zMTE1IE5ndXnhu4VuIELhu4luaCBLaGnDqm0sIFTDom4gxJDhu4tuaCwgSOG7kyBDaMOtIE1pbmgsIFZp4buHdCBOYW0!5e1!3m2!1svi!2s!4v1790164676789!5m2!1svi!2s';
@endphp

<x-layouts.public :title="$title" header-mode="solid" :contact-href="$bookingUrl" :seo="$seo ?? null" variant="v2">
    <article>
        <section class="v2-page-hero v2-page-hero--media">
            <x-public.v2.container>
                <div class="v2-page-hero__grid">
                    <div class="v2-page-hero__copy">
                        <x-public.v2.eyebrow>{{ $content['hero']['eyebrow'] }}</x-public.v2.eyebrow>
                        <x-public.v2.display-heading level="1" size="xl" class="v2-primary-page-hero__title">{{ $content['hero']['title'] }}</x-public.v2.display-heading>
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
                        <p class="v2-type-eyebrow">{{ $content['cards']['address']['label'] }}</p>
                        <address class="v2-contact-methods__value v2-contact-methods__address">
                            @foreach($content['cards']['address']['lines'] as $line)
                                <span>{{ $line }}</span>
                            @endforeach
                        </address>
                        <p class="v2-type-body">{{ $content['cards']['address']['subtext'] }}</p>
                    </article>

                    <article>
                        <p class="v2-type-eyebrow">{{ $content['cards']['social']['label'] }}</p>
                        <div class="v2-contact-methods__social">
                            <a href="{{ $content['cards']['social']['zalo']['href'] }}" target="_blank" rel="noopener noreferrer" class="v2-contact-methods__value">{{ $content['cards']['social']['zalo']['label'] }}</a>
                            <a href="{{ $content['cards']['social']['facebook']['href'] }}" target="_blank" rel="noopener noreferrer" class="v2-contact-methods__value">{{ $content['cards']['social']['facebook']['label'] }}</a>
                        </div>
                        <p class="v2-type-body">{{ $content['cards']['social']['subtext'] }}</p>
                    </article>
                </div>
            </x-public.v2.container>
        </x-public.v2.section>

        <x-public.v2.section surface="ivory">
            <x-public.v2.container>
                <div class="v2-editorial-split v2-editorial-split--reverse">
                    <div class="v2-editorial-split__media v2-contact-map-frame" data-image-reveal>
                        <iframe
                            src="{{ $mapEmbedUrl }}"
                            title="{{ $content['atmosphere']['map_title'] }}"
                            width="600"
                            height="450"
                            style="border:0;"
                            allowfullscreen
                            loading="lazy"
                            referrerpolicy="strict-origin-when-cross-origin"
                        ></iframe>
                    </div>
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
