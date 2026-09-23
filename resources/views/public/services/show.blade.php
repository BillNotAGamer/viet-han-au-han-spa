@php
    $serviceFallbackImage = Vite::asset('resources/images/pages/services/services-signature-therapy.webp');
    $heroImage = !empty($service['hero_media']['url']) ? $service['hero_media']['url'] : $serviceFallbackImage;
    $heroAlt = $service['hero_media']['alt'] ?? '';
@endphp

<x-layouts.public variant="v2" :title="$title" header-mode="overlay" :contact-href="$contactHref" :seo="$seo ?? null">
    <article class="v2-service-detail">
        <header class="v2-service-hero" aria-labelledby="service-title">
            <img src="{{ $heroImage }}" alt="{{ $heroAlt }}" class="v2-service-hero__image" loading="eager" fetchpriority="high">
            <div class="v2-service-hero__veil" aria-hidden="true"></div>
            <div class="v2-container v2-service-hero__inner">
                <div class="v2-service-hero__copy" data-reveal>
                    @if(!empty($service['category_name']))
                        <x-public.v2.eyebrow inverse>{{ $service['category_name'] }}</x-public.v2.eyebrow>
                    @endif
                    <x-public.v2.display-heading level="1" size="xl" id="service-title">{{ $service['name'] }}</x-public.v2.display-heading>
                    @if(!empty($service['excerpt']))
                        <p>{{ $service['excerpt'] }}</p>
                    @endif
                    <a href="{{ $contactHref }}" x-data="{}" data-booking-modal-trigger @click.prevent="$dispatch('open-booking-modal', { trigger: $el })" class="v2-button v2-button--primary">
                        {{ __('services.detail.inquiry_cta') }}
                    </a>
                </div>
            </div>
        </header>

        @if(!empty($service['prices']))
            <section class="v2-service-essentials" aria-labelledby="service-prices-title">
                <div class="v2-container">
                    <h2 id="service-prices-title" class="v2-service-essentials__title">{{ __('services.detail.prices') }}</h2>
                    <div class="v2-service-price-list">
                        @foreach($service['prices'] as $price)
                            <div class="v2-service-price">
                                <div>
                                    @if(!empty($price['label']))<h3>{{ $price['label'] }}</h3>@endif
                                    @if($price['duration_minutes'] !== null)
                                        <p>{{ __('services.detail.duration', ['minutes' => $price['duration_minutes']]) }}</p>
                                    @endif
                                </div>
                                <strong>{{ $price['price_display'] }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <div class="v2-service-content v2-surface--paper">
            @if(!empty($service['content']))
                <section class="v2-section v2-service-overview" aria-labelledby="service-overview-title">
                    <div class="v2-container v2-service-reading-grid">
                        <x-public.v2.eyebrow>{{ __('services.detail.overview') }}</x-public.v2.eyebrow>
                        <div>
                            <x-public.v2.display-heading id="service-overview-title" size="heading-lg">{{ $service['name'] }}</x-public.v2.display-heading>
                            <div class="v2-service-prose">
                                @foreach($service['content'] as $paragraph)
                                    <p>{{ $paragraph }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            @if(!empty($service['benefits']))
                <section class="v2-section v2-surface--ivory" aria-labelledby="service-benefits-title">
                    <div class="v2-container">
                        <header class="v2-section-heading v2-section-heading--compact">
                            <div>
                                <x-public.v2.eyebrow>{{ __('services.detail.benefits') }}</x-public.v2.eyebrow>
                                <x-public.v2.display-heading id="service-benefits-title" size="heading-lg">{{ __('services.detail.benefits') }}</x-public.v2.display-heading>
                            </div>
                        </header>
                        <div class="v2-service-benefits" data-reveal-group>
                            @foreach($service['benefits'] as $benefit)
                                <article data-reveal>
                                    <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    @if($benefit['title'] !== '')<h3>{{ $benefit['title'] }}</h3>@endif
                                    @if($benefit['description'] !== '')<p>{{ $benefit['description'] }}</p>@endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            @if(!empty($service['process_steps']))
                <section class="v2-section v2-service-process" aria-labelledby="service-process-title">
                    <div class="v2-container v2-service-reading-grid">
                        <div>
                            <x-public.v2.eyebrow>{{ __('services.detail.process') }}</x-public.v2.eyebrow>
                            <x-public.v2.display-heading id="service-process-title" size="heading-lg">{{ __('services.detail.process') }}</x-public.v2.display-heading>
                        </div>
                        <ol class="v2-service-process__list" data-reveal-group>
                            @foreach($service['process_steps'] as $step)
                                <li data-reveal>
                                    <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <div>
                                        @if($step['title'] !== '')<h3>{{ $step['title'] }}</h3>@endif
                                        @if($step['description'] !== '')<p>{{ $step['description'] }}</p>@endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </section>
            @endif

            @if(!empty($service['gallery']))
                <section class="v2-section v2-surface--ivory" aria-labelledby="service-gallery-title">
                    <div class="v2-container">
                        <header class="v2-section-heading v2-section-heading--compact">
                            <div>
                                <x-public.v2.eyebrow>{{ __('services.detail.gallery') }}</x-public.v2.eyebrow>
                                <x-public.v2.display-heading id="service-gallery-title" size="heading-lg">{{ __('services.detail.gallery') }}</x-public.v2.display-heading>
                            </div>
                        </header>
                        <div class="v2-service-gallery" data-reveal-group>
                            @foreach($service['gallery'] as $image)
                                <x-public.v2.media-frame :src="$image['url']" :alt="$image['alt']" role="gallery" :caption="$image['caption'] ?? null" data-reveal />
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            @if(!empty($service['faqs']))
                <section class="v2-section v2-service-faq" aria-labelledby="service-faq-title">
                    <div class="v2-container v2-service-reading-grid">
                        <div>
                            <x-public.v2.eyebrow>{{ __('services.detail.faqs') }}</x-public.v2.eyebrow>
                            <x-public.v2.display-heading id="service-faq-title" size="heading-lg">{{ __('services.detail.faqs') }}</x-public.v2.display-heading>
                        </div>
                        <div class="v2-service-faq__list">
                            @foreach($service['faqs'] as $faq)
                                <details>
                                    <summary><span>{{ $faq['question'] }}</span><span aria-hidden="true">+</span></summary>
                                    @if($faq['answer'] !== '')<p>{{ $faq['answer'] }}</p>@endif
                                </details>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        </div>

        <section class="v2-service-cta v2-surface--deep" aria-labelledby="service-cta-title">
            <div class="v2-container v2-service-cta__inner" data-reveal>
                <x-public.v2.eyebrow inverse>{{ __('common.brand_name') }}</x-public.v2.eyebrow>
                <x-public.v2.display-heading id="service-cta-title" size="heading-lg">{{ __('services.detail.inquiry_title') }}</x-public.v2.display-heading>
                <p>{{ __('services.detail.inquiry_copy') }}</p>
                <a href="{{ $contactHref }}" x-data="{}" data-booking-modal-trigger @click.prevent="$dispatch('open-booking-modal', { trigger: $el })" class="v2-button v2-button--inverse">
                    {{ __('services.detail.inquiry_cta') }}
                </a>
            </div>
        </section>
    </article>
</x-layouts.public>
