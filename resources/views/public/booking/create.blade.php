@php
    $bookingRitualImg = Vite::asset('resources/images/pages/booking/booking-welcome-lounge.webp');
@endphp

<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null" variant="v2">
    <section class="v2-page-hero v2-page-hero--intro v2-booking-page__hero">
        <x-public.v2.container>
            <div class="v2-page-hero__copy">
                <x-public.v2.eyebrow>{{ __('booking.eyebrow') }}</x-public.v2.eyebrow>
                <x-public.v2.display-heading level="1" size="xl">{{ __('booking.title') }}</x-public.v2.display-heading>
                <x-public.v2.editorial-copy size="lg">{{ __('booking.intro') }}</x-public.v2.editorial-copy>
            </div>
        </x-public.v2.container>
    </section>

    <x-public.v2.section surface="ivory" class="v2-booking-page">
        <x-public.v2.container>
            <div class="v2-booking-page__grid">
                <aside class="v2-booking-page__concierge">
                    <x-public.v2.media-frame
                        :src="$bookingRitualImg"
                        :alt="__('booking.process_title')"
                        role="portrait"
                    />
                    <div class="v2-booking-page__note">
                        <x-public.v2.eyebrow>{{ __('booking.eyebrow') }}</x-public.v2.eyebrow>
                        <x-public.v2.display-heading level="2" size="heading-md">{{ __('booking.process_title') }}</x-public.v2.display-heading>
                        <p class="v2-type-body">{{ __('booking.process_copy') }}</p>
                    </div>
                    <div class="v2-booking-page__confirmation">
                        <h2>{{ __('booking.confirmation_title') }}</h2>
                        <p>{{ __('booking.confirmation_copy') }}</p>
                    </div>
                </aside>

                <div class="v2-booking-page__form">
                    <x-public.booking-form
                        :action="$action"
                        :services="$services"
                        :min-date="$minDate"
                        id-prefix="page"
                        variant="v2-page"
                    />
                </div>
            </div>
        </x-public.v2.container>
    </x-public.v2.section>
</x-layouts.public>
