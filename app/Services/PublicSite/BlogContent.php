<?php

declare(strict_types=1);

namespace App\Services\PublicSite;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostTranslation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class BlogContent
{
    public function __construct(
        protected PublicMediaResolver $mediaResolver
    ) {}

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function listingForLocale(string $locale, int $perPage = 12): LengthAwarePaginator
    {
        return Post::query()
            ->where(fn (Builder $query) => $this->applyPublicEligibility($query, $locale))
            ->with([
                'translations' => fn ($query) => $query->where('locale', $locale),
                'category.translations' => fn ($query) => $query->where('locale', $locale),
                'heroMedia.translations' => fn ($query) => $query->where('locale', $locale),
                'author',
            ])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Post $post) => $this->presentListingPost($post, $locale));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detailForSlug(string $locale, string $slug): ?array
    {
        $translation = PostTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->whereHas('post', fn (Builder $query) => $this->applyPublicEligibility($query, $locale))
            ->with([
                'post.translations',
                'post.category.translations' => fn ($query) => $query->where('locale', $locale),
                'post.heroMedia.translations' => fn ($query) => $query->where('locale', $locale),
                'post.media' => fn ($query) => $query
                    ->with(['translations' => fn ($translationQuery) => $translationQuery->where('locale', $locale)])
                    ->orderByPivot('sort_order', 'asc'),
                'post.author',
            ])
            ->first();

        if (! $translation) {
            return null;
        }

        $post = $translation->post;

        return [
            'id' => $post->id,
            'title' => $translation->title,
            'slug' => $translation->slug,
            'excerpt' => $translation->excerpt ?: $this->derivePlainText($translation->content, 220),
            'content' => $this->plainTextParagraphs($translation->content),
            'category_name' => $this->categoryName($post, $locale),
            'author_name' => $this->authorName($post),
            'published_at_iso' => $post->published_at?->toAtomString(),
            'published_at_display' => $this->publishedDateDisplay($post),
            'hero_media' => $this->mediaResolver->resolve($post->heroMedia, $locale),
            'gallery' => $this->presentGallery($post->media, $locale),
            'localized_urls' => $this->localizedUrls($post),
            'seo_title' => $translation->seo_title,
            'seo_description' => $translation->seo_description,
            'seo_alternates' => $this->seoAlternates($post),
        ];
    }

    protected function applyPublicEligibility(Builder $query, string $locale): Builder
    {
        return $query
            ->where('status', ContentStatus::PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('translations', fn (Builder $translationQuery) => $translationQuery->where('locale', $locale));
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentListingPost(Post $post, string $locale): array
    {
        $translation = $post->translations->firstWhere('locale', $locale);

        return [
            'id' => $post->id,
            'title' => $translation?->title,
            'excerpt' => $translation?->excerpt ?: $this->derivePlainText($translation?->content, 150),
            'category_name' => $this->categoryName($post, $locale),
            'author_name' => $this->authorName($post),
            'published_at_iso' => $post->published_at?->toAtomString(),
            'published_at_display' => $this->publishedDateDisplay($post),
            'media' => $this->mediaResolver->resolve($post->heroMedia, $locale),
            'url' => route($locale === 'en' ? 'en.blog.show' : 'vi.blog.show', ['slug' => $translation?->slug]),
        ];
    }

    protected function categoryName(Post $post, string $locale): ?string
    {
        if ($post->category?->status !== ContentStatus::PUBLISHED) {
            return null;
        }

        return $post->category->translations->firstWhere('locale', $locale)?->name;
    }

    protected function authorName(Post $post): ?string
    {
        $name = trim((string) $post->author?->name);

        return $name !== '' ? $name : null;
    }

    protected function publishedDateDisplay(Post $post): ?string
    {
        return $post->published_at
            ? $post->published_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y')
            : null;
    }

    /**
     * @param  iterable<int, Media>  $mediaItems
     * @return array<int, array<string, mixed>>
     */
    protected function presentGallery(iterable $mediaItems, string $locale): array
    {
        $resolved = [];

        foreach ($mediaItems as $media) {
            $item = $this->mediaResolver->resolve($media, $locale);
            if ($item !== null) {
                $resolved[] = $item;
            }
        }

        return $resolved;
    }

    /**
     * @return array<int, string>
     */
    protected function plainTextParagraphs(?string $content): array
    {
        $plain = $this->derivePlainText($content, 8000);

        if ($plain === '') {
            return [];
        }

        return array_values(array_filter(
            preg_split('/(?:\r?\n){2,}/', wordwrap($plain, 900, "\n\n", false)) ?: [],
            fn (string $paragraph) => trim($paragraph) !== ''
        ));
    }

    protected function derivePlainText(?string $content, int $limit = 200): string
    {
        if (empty($content)) {
            return '';
        }

        $clean = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean = (string) preg_replace('/<(script|style|iframe|object|embed|noscript)\b[^>]*>.*?<\/\1>/is', '', $clean);
        $clean = strip_tags($clean);
        $clean = (string) preg_replace('/\s+/u', ' ', $clean);
        $clean = trim($clean);

        if (mb_strlen($clean, 'UTF-8') <= $limit) {
            return $clean;
        }

        return mb_substr($clean, 0, $limit, 'UTF-8').'...';
    }

    /**
     * @return array{vi: string, en: string}
     */
    protected function localizedUrls(Post $post): array
    {
        $vi = $post->translations->firstWhere('locale', 'vi');
        $en = $post->translations->firstWhere('locale', 'en');

        return [
            'vi' => $vi ? route('vi.blog.show', ['slug' => $vi->slug]) : route('vi.blog.index'),
            'en' => $en ? route('en.blog.show', ['slug' => $en->slug]) : route('en.blog.index'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function seoAlternates(Post $post): array
    {
        $vi = $post->translations->firstWhere('locale', 'vi');
        $en = $post->translations->firstWhere('locale', 'en');

        return array_filter([
            'vi' => $vi && ! empty($vi->slug) ? route('vi.blog.show', ['slug' => $vi->slug]) : null,
            'en' => $en && ! empty($en->slug) ? route('en.blog.show', ['slug' => $en->slug]) : null,
        ]);
    }
}
