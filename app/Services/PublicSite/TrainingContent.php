<?php

declare(strict_types=1);

namespace App\Services\PublicSite;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\TrainingCourse;
use App\Models\TrainingCourseTranslation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TrainingContent
{
    public function __construct(
        protected PublicMediaResolver $mediaResolver
    ) {}

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function listingForLocale(string $locale, int $perPage = 12): LengthAwarePaginator
    {
        return $this->publicCourseQuery($locale)
            ->with([
                'translations' => fn ($query) => $query->where('locale', $locale),
                'heroMedia.translations' => fn ($query) => $query->where('locale', $locale),
            ])
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (TrainingCourse $course) => $this->presentListingCourse($course, $locale));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detailForSlug(string $locale, string $slug): ?array
    {
        $translation = TrainingCourseTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->whereHas('course', fn (Builder $query) => $this->applyPublicEligibility($query, $locale))
            ->with([
                'course.translations',
                'course.heroMedia.translations' => fn ($query) => $query->where('locale', $locale),
                'course.media' => fn ($query) => $query
                    ->with(['translations' => fn ($translationQuery) => $translationQuery->where('locale', $locale)])
                    ->orderByPivot('sort_order', 'asc'),
            ])
            ->first();

        if (! $translation) {
            return null;
        }

        $course = $translation->course;

        return [
            'id' => $course->id,
            'title' => $translation->title,
            'slug' => $translation->slug,
            'excerpt' => $translation->excerpt ?: $this->derivePlainText($translation->content, 220),
            'content' => $this->plainTextParagraphs($translation->content),
            'duration_display' => $this->cleanOptionalText($translation->duration_display),
            'schedule_display' => $this->cleanOptionalText($translation->schedule_display),
            'target_audience' => $this->cleanOptionalText($translation->target_audience),
            'curriculum_modules' => $this->presentStructuredItems($translation->curriculum_modules),
            'benefits' => $this->presentStructuredItems($translation->benefits),
            'faqs' => $this->presentFaqs($translation->faqs),
            'tuition' => $this->presentTuition($course->tuition_fee),
            'hero_media' => $this->mediaResolver->resolve($course->heroMedia, $locale),
            'gallery' => $this->presentGallery($course->media, $locale),
            'localized_urls' => $this->localizedUrls($course),
        ];
    }

    protected function publicCourseQuery(string $locale): Builder
    {
        return TrainingCourse::query()
            ->where(fn (Builder $query) => $this->applyPublicEligibility($query, $locale));
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
    protected function presentListingCourse(TrainingCourse $course, string $locale): array
    {
        $translation = $course->translations->firstWhere('locale', $locale);

        return [
            'id' => $course->id,
            'title' => $translation?->title,
            'excerpt' => $translation?->excerpt ?: $this->derivePlainText($translation?->content, 150),
            'duration_display' => $this->cleanOptionalText($translation?->duration_display),
            'schedule_display' => $this->cleanOptionalText($translation?->schedule_display),
            'tuition' => $this->presentTuition($course->tuition_fee),
            'media' => $this->mediaResolver->resolve($course->heroMedia, $locale),
            'url' => route($locale === 'en' ? 'en.training.show' : 'vi.training.show', ['slug' => $translation?->slug]),
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
     * @return array{amount: int, display: string}|null
     */
    protected function presentTuition(?int $tuitionFee): ?array
    {
        if ($tuitionFee === null) {
            return null;
        }

        return [
            'amount' => $tuitionFee,
            'display' => number_format($tuitionFee, 0, ',', '.').' ₫',
        ];
    }

    /**
     * @param  array<int, mixed>|null  $items
     * @return array<int, array{title: string, description: string}>
     */
    protected function presentStructuredItems(?array $items): array
    {
        if (empty($items)) {
            return [];
        }

        $prepared = [];

        foreach ($items as $item) {
            if (is_string($item)) {
                $text = trim($item);
                if ($text !== '') {
                    $prepared[] = ['title' => $text, 'description' => ''];
                }

                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $description = trim((string) ($item['description'] ?? $item['content'] ?? $item['desc'] ?? ''));

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

    protected function cleanOptionalText(?string $value): ?string
    {
        $clean = trim((string) $value);

        return $clean !== '' ? $clean : null;
    }

    /**
     * @return array{vi: string, en: string}
     */
    protected function localizedUrls(TrainingCourse $course): array
    {
        $vi = $course->translations->firstWhere('locale', 'vi');
        $en = $course->translations->firstWhere('locale', 'en');

        return [
            'vi' => $vi ? route('vi.training.show', ['slug' => $vi->slug]) : route('vi.training.index'),
            'en' => $en ? route('en.training.show', ['slug' => $en->slug]) : route('en.training.index'),
        ];
    }
}
