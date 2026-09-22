@php
    $postItems = collect($posts->items());
    $featuredPost = $postItems->first();
    $supportingPosts = $postItems->slice(1);
@endphp

<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null" variant="v2">
    <section class="v2-page-hero v2-page-hero--intro v2-journal-hero">
        <x-public.v2.container>
            <div class="v2-page-hero__copy">
                <x-public.v2.eyebrow>{{ __('blog.index.eyebrow') }}</x-public.v2.eyebrow>
                <x-public.v2.display-heading level="1" size="xl">{{ __('blog.index.title') }}</x-public.v2.display-heading>
                <x-public.v2.editorial-copy size="lg">{{ __('blog.index.intro') }}</x-public.v2.editorial-copy>
            </div>
        </x-public.v2.container>
    </section>

    <x-public.v2.section surface="paper" class="v2-journal-index">
        <x-public.v2.container>
            @if($featuredPost)
                <article @class(['v2-journal-feature', 'v2-journal-feature--without-media' => empty($featuredPost['media']['url'])])>
                    @if(!empty($featuredPost['media']['url']))
                        <a href="{{ $featuredPost['url'] }}" class="v2-journal-feature__media" aria-label="{{ $featuredPost['title'] }}">
                            <x-public.v2.media-frame :src="$featuredPost['media']['url']" :alt="$featuredPost['media']['alt']" role="landscape" />
                        </a>
                    @endif
                    <div class="v2-journal-feature__body">
                        <div class="v2-journal-meta">
                            @if(!empty($featuredPost['category_name']))<span>{{ $featuredPost['category_name'] }}</span>@endif
                            @if(!empty($featuredPost['published_at_display']))
                                <time datetime="{{ $featuredPost['published_at_iso'] }}">{{ $featuredPost['published_at_display'] }}</time>
                            @endif
                        </div>
                        <x-public.v2.display-heading level="2" size="display-lg">
                            <a href="{{ $featuredPost['url'] }}">{{ $featuredPost['title'] }}</a>
                        </x-public.v2.display-heading>
                        @if(!empty($featuredPost['excerpt']))
                            <p class="v2-type-body-lg">{{ $featuredPost['excerpt'] }}</p>
                        @endif
                        <x-public.v2.button :href="$featuredPost['url']" variant="text">{{ __('blog.index.details') }} <span aria-hidden="true">&rarr;</span></x-public.v2.button>
                    </div>
                </article>

                @if($supportingPosts->isNotEmpty())
                    <div class="v2-journal-index__list">
                        @foreach($supportingPosts as $post)
                            <article @class(['v2-journal-index__entry', 'v2-journal-index__entry--without-media' => empty($post['media']['url'])])>
                                <div class="v2-journal-meta">
                                    @if(!empty($post['category_name']))<span>{{ $post['category_name'] }}</span>@endif
                                    @if(!empty($post['published_at_display']))
                                        <time datetime="{{ $post['published_at_iso'] }}">{{ $post['published_at_display'] }}</time>
                                    @endif
                                </div>
                                <div>
                                    <x-public.v2.display-heading level="2" size="heading-md">
                                        <a href="{{ $post['url'] }}">{{ $post['title'] }}</a>
                                    </x-public.v2.display-heading>
                                    @if(!empty($post['excerpt']))<p class="v2-type-body">{{ $post['excerpt'] }}</p>@endif
                                </div>
                                @if(!empty($post['media']['url']))
                                    <a href="{{ $post['url'] }}" class="v2-journal-index__media" aria-label="{{ $post['title'] }}">
                                        <x-public.v2.media-frame :src="$post['media']['url']" :alt="$post['media']['alt']" role="landscape" />
                                    </a>
                                @else
                                    <x-public.v2.button :href="$post['url']" variant="text" class="v2-journal-index__link">{{ __('blog.index.details') }} <span aria-hidden="true">&rarr;</span></x-public.v2.button>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif

                {{ $posts->links('components.public.v2.pagination') }}
            @else
                <div class="v2-empty-state" role="status">
                    <p class="v2-type-body-lg">{{ __('blog.index.empty') }}</p>
                </div>
            @endif
        </x-public.v2.container>
    </x-public.v2.section>
</x-layouts.public>
