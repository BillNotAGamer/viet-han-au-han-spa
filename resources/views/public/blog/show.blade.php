<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null" variant="v2">
    <article class="v2-article">
        <header @class(['v2-article-hero', 'v2-article-hero--without-media' => empty($post['hero_media']['url'])])>
            @if(!empty($post['hero_media']['url']))
                <img src="{{ $post['hero_media']['url'] }}" alt="{{ $post['hero_media']['alt'] }}" class="v2-article-hero__image" loading="eager" fetchpriority="high">
                <div class="v2-article-hero__veil"></div>
            @endif
            <x-public.v2.container>
                <div class="v2-article-hero__copy">
                    <div class="v2-journal-meta v2-journal-meta--inverse">
                        @if(!empty($post['category_name']))
                            <span>{{ $post['category_name'] }}</span>
                        @else
                            <span>{{ __('blog.detail.article') }}</span>
                        @endif
                        @if(!empty($post['published_at_display']))
                            <time datetime="{{ $post['published_at_iso'] }}">{{ $post['published_at_display'] }}</time>
                        @endif
                    </div>
                    <x-public.v2.display-heading level="1" size="xl">{{ $post['title'] }}</x-public.v2.display-heading>
                    @if(!empty($post['excerpt']))
                        <p class="v2-type-body-lg">{{ $post['excerpt'] }}</p>
                    @endif
                </div>
            </x-public.v2.container>
        </header>

        <x-public.v2.section surface="paper" class="v2-article-reading">
            <x-public.v2.container size="reading">
                @if(!empty($post['published_at_display']) || !empty($post['author_name']))
                    <dl class="v2-article-byline">
                        @if(!empty($post['published_at_display']))
                            <div>
                                <dt>{{ __('blog.detail.published_on') }}</dt>
                                <dd><time datetime="{{ $post['published_at_iso'] }}">{{ $post['published_at_display'] }}</time></dd>
                            </div>
                        @endif
                        @if(!empty($post['author_name']))
                            <div>
                                <dt>{{ __('blog.detail.by_author') }}</dt>
                                <dd>{{ $post['author_name'] }}</dd>
                            </div>
                        @endif
                    </dl>
                @endif

                @if(!empty($post['content']))
                    <div class="v2-article-body">
                        @foreach($post['content'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                @endif
            </x-public.v2.container>
        </x-public.v2.section>

        @if(!empty($post['gallery']))
            <x-public.v2.section surface="ivory" class="v2-article-gallery">
                <x-public.v2.container size="wide">
                    <div class="v2-section-heading v2-section-heading--compact">
                        <div>
                            <x-public.v2.eyebrow>{{ __('blog.detail.gallery') }}</x-public.v2.eyebrow>
                            <x-public.v2.display-heading level="2" size="heading-lg">{{ __('blog.detail.gallery') }}</x-public.v2.display-heading>
                        </div>
                    </div>
                    <div class="v2-narrative-gallery">
                        @foreach($post['gallery'] as $image)
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

        <section class="v2-article-footer">
            <x-public.v2.container size="reading">
                <div>
                    <x-public.v2.eyebrow>{{ __('blog.detail.article') }}</x-public.v2.eyebrow>
                    <x-public.v2.display-heading level="2" size="heading-md">{{ __('blog.detail.cta_title') }}</x-public.v2.display-heading>
                    <p class="v2-type-body">{{ __('blog.detail.cta_copy') }}</p>
                </div>
                <div class="v2-article-footer__actions">
                    <x-public.v2.button :href="$contactHref" variant="primary">{{ __('blog.detail.cta') }}</x-public.v2.button>
                    <x-public.v2.button :href="$locale === 'en' ? route('en.blog.index') : route('vi.blog.index')" variant="text">{{ __('blog.detail.back_to_blog') }} <span aria-hidden="true">&rarr;</span></x-public.v2.button>
                </div>
            </x-public.v2.container>
        </section>
    </article>
</x-layouts.public>
