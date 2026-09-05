<?php

declare(strict_types=1);

namespace App\Services\PublicSite;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Models\ServiceTranslation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ServicesContent
{
    public function __construct(
        protected PublicMediaResolver $mediaResolver
    ) {}

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function listingForLocale(string $locale, int $perPage = 12): LengthAwarePaginator
    {
        return Service::query()
            ->where('status', ContentStatus::PUBLISHED)
            ->whereHas('translations', fn (Builder $query) => $query->where('locale', $locale))
            ->with([
                'translations' => fn ($query) => $query->where('locale', $locale),
                'category.translations' => fn ($query) => $query->where('locale', $locale),
                'heroMedia.translations' => fn ($query) => $query->where('locale', $locale),
                'prices' => fn ($query) => $query
                    ->where('is_active', true)
                    ->with(['translations' => fn ($translationQuery) => $translationQuery->where('locale', $locale)])
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('id', 'asc'),
            ])
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Service $service) => $this->presentListingService($service, $locale));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detailForSlug(string $locale, string $slug): ?array
    {
        $translation = ServiceTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->whereHas('service', fn (Builder $query) => $query->where('status', ContentStatus::PUBLISHED))
            ->with([
                'service.translations',
                'service.category.translations' => fn ($query) => $query->where('locale', $locale),
                'service.heroMedia.translations' => fn ($query) => $query->where('locale', $locale),
                'service.media' => fn ($query) => $query
                    ->with(['translations' => fn ($translationQuery) => $translationQuery->where('locale', $locale)])
                    ->orderByPivot('sort_order', 'asc'),
                'service.prices' => fn ($query) => $query
                    ->where('is_active', true)
                    ->with(['translations' => fn ($translationQuery) => $translationQuery->where('locale', $locale)])
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('id', 'asc'),
            ])
            ->first();

        if (! $translation) {
            return null;
        }

        $service = $translation->service;

        return [
            'id' => $service->id,
            'name' => $translation->name,
            'slug' => $translation->slug,
            'excerpt' => $translation->excerpt ?: $this->derivePlainText($translation->content, 220),
            'content' => $this->plainTextParagraphs($translation->content),
            'benefits' => $this->presentStructuredItems($translation->benefits),
            'process_steps' => $this->presentStructuredItems($translation->process_steps),
            'faqs' => $this->presentFaqs($translation->faqs),
            'category_name' => $service->category?->translations->firstWhere('locale', $locale)?->name,
            'hero_media' => $this->mediaResolver->resolve($service->heroMedia, $locale),
            'gallery' => $this->presentGallery($service->media, $locale),
            'prices' => $this->presentPrices($service->prices, $locale),
            'localized_urls' => $this->localizedUrls($service),
            'seo_title' => $translation->seo_title,
            'seo_description' => $translation->seo_description,
            'seo_alternates' => $this->seoAlternates($service),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function seoAlternates(Service $service): array
    {
        $vi = $service->translations->firstWhere('locale', 'vi');
        $en = $service->translations->firstWhere('locale', 'en');

        return array_filter([
            'vi' => $vi && ! empty($vi->slug) ? route('vi.services.show', ['slug' => $vi->slug]) : null,
            'en' => $en && ! empty($en->slug) ? route('en.services.show', ['slug' => $en->slug]) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentListingService(Service $service, string $locale): array
    {
        $translation = $service->translations->firstWhere('locale', $locale);
        $firstPrice = $service->prices->first();

        return [
            'id' => $service->id,
            'name' => $translation?->name,
            'excerpt' => $translation?->excerpt ?: $this->derivePlainText($translation?->content, 150),
            'category_name' => $service->category?->translations->firstWhere('locale', $locale)?->name,
            'media' => $this->mediaResolver->resolve($service->heroMedia, $locale),
            'price_summary' => $firstPrice ? $this->presentPrice($firstPrice, $locale) : null,
            'url' => route($locale === 'en' ? 'en.services.show' : 'vi.services.show', ['slug' => $translation?->slug]),
        ];
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
     * @param  iterable<int, ServicePrice>  $prices
     * @return array<int, array<string, mixed>>
     */
    protected function presentPrices(iterable $prices, string $locale): array
    {
        $prepared = [];

        foreach ($prices as $price) {
            $prepared[] = $this->presentPrice($price, $locale);
        }

        return $prepared;
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentPrice(ServicePrice $price, string $locale): array
    {
        return [
            'id' => $price->id,
            'label' => $price->translations->firstWhere('locale', $locale)?->label,
            'duration_minutes' => $price->duration_minutes,
            'price_amount' => $price->price_amount,
            'price_display' => number_format((int) $price->price_amount, 0, ',', '.').' ₫',
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $items
     * @return array<int, array{title: string, description: string}>
     */
    protected function presentStructuredItems(?array $items): array
    {
        if (empty($items)) {
            return [];
        }

        $prepared = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $description = trim((string) ($item['description'] ?? $item['desc'] ?? ''));

            if ($title === '' && $description === '') {
                continue;
            }

            $prepared[] = [
                'title' => $title,
                'description' => $description,
            ];
        }

        return $prepared;
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $items
     * @return array<int, array{question: string, answer: string}>
     */
    protected function presentFaqs(?array $items): array
    {
        if (empty($items)) {
            return [];
        }

        $prepared = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $question = trim((string) ($item['question'] ?? $item['q'] ?? ''));
            $answer = trim((string) ($item['answer'] ?? $item['a'] ?? ''));

            if ($question === '' && $answer === '') {
                continue;
            }

            $prepared[] = [
                'question' => $question,
                'answer' => $answer,
            ];
        }

        return $prepared;
    }

    /**
     * @return array<int, string>
     */
    protected function plainTextParagraphs(?string $content): array
    {
        $plain = $this->derivePlainText($content, 5000);

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
    protected function localizedUrls(Service $service): array
    {
        $vi = $service->translations->firstWhere('locale', 'vi');
        $en = $service->translations->firstWhere('locale', 'en');

        return [
            'vi' => $vi ? route('vi.services.show', ['slug' => $vi->slug]) : route('vi.services.index'),
            'en' => $en ? route('en.services.show', ['slug' => $en->slug]) : route('en.services.index'),
        ];
    }
}
