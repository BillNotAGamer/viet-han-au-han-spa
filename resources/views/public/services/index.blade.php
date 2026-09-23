<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null" variant="v2">
    <section class="v2-page-hero v2-page-hero--intro">
        <x-public.v2.container>
            <div class="v2-page-hero__copy">
                <x-public.v2.eyebrow>{{ __('services.index.eyebrow') }}</x-public.v2.eyebrow>
                <x-public.v2.display-heading level="1" size="xl">{{ $heading }}</x-public.v2.display-heading>
                <x-public.v2.editorial-copy size="lg">{{ $intro }}</x-public.v2.editorial-copy>
            </div>
        </x-public.v2.container>
    </section>

    <x-public.v2.section surface="paper" class="v2-listing-section">
        <x-public.v2.container>
            @if($services->isNotEmpty())
                <div class="v2-treatment-menu" data-reveal-group>
                    @foreach($services as $service)
                        <article @class(['v2-treatment-row', 'v2-treatment-row--without-media' => empty($service['media']['url'])]) data-reveal>
                            <p class="v2-treatment-row__number" aria-hidden="true">{{ str_pad((string) (($services->firstItem() ?? 1) + $loop->index), 2, '0', STR_PAD_LEFT) }}</p>

                            <div class="v2-treatment-row__body">
                                @if(!empty($service['category_name']))
                                    <p class="v2-type-eyebrow">{{ $service['category_name'] }}</p>
                                @endif
                                <x-public.v2.display-heading level="2" size="heading-lg">
                                    <a href="{{ $service['url'] }}">{{ $service['name'] }}</a>
                                </x-public.v2.display-heading>
                                @if(!empty($service['excerpt']))
                                    <p class="v2-type-body">{{ $service['excerpt'] }}</p>
                                @endif
                                <div class="v2-treatment-row__footer">
                                    @if(!empty($service['price_summary']))
                                        <p class="v2-type-body-sm">
                                            {{ __('services.index.from_price') }}
                                            <strong>{{ $service['price_summary']['price_display'] }}</strong>
                                            @if(!empty($service['price_summary']['duration_minutes']))
                                                <span aria-hidden="true">&middot;</span>
                                                {{ __('services.detail.duration', ['minutes' => $service['price_summary']['duration_minutes']]) }}
                                            @endif
                                        </p>
                                    @endif
                                    <x-public.v2.button :href="$service['url']" variant="text">
                                        {{ __('services.index.details') }} <span aria-hidden="true">&rarr;</span>
                                    </x-public.v2.button>
                                </div>
                            </div>

                            @if(!empty($service['media']['url']))
                                <a href="{{ $service['url'] }}" class="v2-treatment-row__media" aria-label="{{ $service['name'] }}">
                                    <x-public.v2.media-frame
                                        :src="$service['media']['url']"
                                        :alt="$service['media']['alt']"
                                        role="landscape"
                                    />
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>

                {{ $services->links('components.public.v2.pagination') }}
            @else
                <div class="v2-empty-state" role="status">
                    <p class="v2-type-body-lg">{{ $emptyCopy }}</p>
                </div>
            @endif
        </x-public.v2.container>
    </x-public.v2.section>
</x-layouts.public>
