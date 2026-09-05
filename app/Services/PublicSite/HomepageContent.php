<?php

declare(strict_types=1);

namespace App\Services\PublicSite;

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\TrainingCourse;
use Carbon\Carbon;

class HomepageContent
{
    public function __construct(
        protected PublicMediaResolver $mediaResolver
    ) {}

    /**
     * Assemble curated, exact-locale, public-eligible content for the Homepage.
     *
     * @return array<string, mixed>
     */
    public function getForLocale(string $locale): array
    {
        return [
            'locale' => $locale,
            'page' => $this->getHomePage($locale),
            'heroMedia' => $this->getHeroMedia($locale),
            'featuredServices' => $this->getFeaturedServices($locale),
            'featuredCourses' => $this->getFeaturedTrainingCourses($locale),
            'latestPosts' => $this->getLatestPosts($locale),
        ];
    }

    /**
     * Retrieve the published 'home' Page with exact requested locale translation and derived safe excerpt.
     */
    protected function getHomePage(string $locale): ?array
    {
        $page = Page::query()
            ->where('key', 'home')
            ->where('status', ContentStatus::PUBLISHED)
            ->with([
                'translations' => fn ($q) => $q->where('locale', $locale),
            ])
            ->first();

        if (! $page) {
            return null;
        }

        $translation = $page->translations->firstWhere('locale', $locale);
        if (! $translation) {
            return null;
        }

        return [
            'title' => $translation->title,
            'seo_title' => $translation->seo_title,
            'seo_description' => $translation->seo_description,
            'excerpt' => $this->deriveSafeExcerpt($translation->content, 240),
            'content_raw' => $translation->content,
        ];
    }

    /**
     * Resolve the first ordered Media from the published 'home' Page as the Homepage hero visual.
     * Convention: home Page page_media sort_order = 0 -> Homepage hero visual.
     */
    protected function getHeroMedia(string $locale): ?array
    {
        $page = Page::query()
            ->where('key', 'home')
            ->where('status', ContentStatus::PUBLISHED)
            ->with([
                'media' => fn ($q) => $q->with(['translations' => fn ($t) => $t->where('locale', $locale)])
                    ->orderByPivot('sort_order', 'asc'),
            ])
            ->first();

        if (! $page || $page->media->isEmpty()) {
            return null;
        }

        $firstMedia = $page->media->first();

        return $this->mediaResolver->resolve($firstMedia, $locale);
    }

    /**
     * Retrieve up to 6 published, featured services with exact requested locale translation.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getFeaturedServices(string $locale): array
    {
        $services = Service::query()
            ->where('status', ContentStatus::PUBLISHED)
            ->where('is_featured', true)
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale))
            ->with([
                'translations' => fn ($q) => $q->where('locale', $locale),
                'heroMedia.translations' => fn ($q) => $q->where('locale', $locale),
                'category' => fn ($q) => $q->with([
                    'translations' => fn ($t) => $t->where('locale', $locale),
                ]),
            ])
            ->orderBy('sort_order', 'asc')
            ->orderBy('updated_at', 'desc')
            ->limit(6)
            ->get();

        return $services->map(function (Service $service) use ($locale) {
            $trans = $service->translations->firstWhere('locale', $locale);
            $catTrans = $service->category?->translations->firstWhere('locale', $locale);
            $media = $this->mediaResolver->resolve($service->heroMedia, $locale);

            return [
                'id' => $service->id,
                'name' => $trans?->name,
                'excerpt' => $trans?->excerpt ?: $this->deriveSafeExcerpt($trans?->description, 150),
                'category_name' => $catTrans?->name, // null if translation missing
                'media' => $media,
            ];
        })->toArray();
    }

    /**
     * Retrieve up to 3 published, featured training courses with exact requested locale translation.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getFeaturedTrainingCourses(string $locale): array
    {
        $now = Carbon::now();

        $courses = TrainingCourse::query()
            ->where('status', ContentStatus::PUBLISHED)
            ->where('is_featured', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $now)
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale))
            ->with([
                'translations' => fn ($q) => $q->where('locale', $locale),
                'heroMedia.translations' => fn ($q) => $q->where('locale', $locale),
            ])
            ->orderBy('sort_order', 'asc')
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        return $courses->map(function (TrainingCourse $course) use ($locale) {
            $trans = $course->translations->firstWhere('locale', $locale);
            $media = $this->mediaResolver->resolve($course->heroMedia, $locale);

            return [
                'id' => $course->id,
                'title' => $trans?->title,
                'excerpt' => $trans?->excerpt ?: $this->deriveSafeExcerpt($trans?->description, 140),
                'duration_display' => $trans?->duration_display,
                'tuition_fee' => $course->tuition_fee,
                'media' => $media,
            ];
        })->toArray();
    }

    /**
     * Retrieve up to 3 latest published blog posts with exact requested locale translation.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getLatestPosts(string $locale): array
    {
        $now = Carbon::now();

        $posts = Post::query()
            ->where('status', ContentStatus::PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $now)
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale))
            ->with([
                'translations' => fn ($q) => $q->where('locale', $locale),
                'heroMedia.translations' => fn ($q) => $q->where('locale', $locale),
                'category' => fn ($q) => $q->with([
                    'translations' => fn ($t) => $t->where('locale', $locale),
                ]),
            ])
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        return $posts->map(function (Post $post) use ($locale) {
            $trans = $post->translations->firstWhere('locale', $locale);
            $catTrans = $post->category?->translations->firstWhere('locale', $locale);
            $media = $this->mediaResolver->resolve($post->heroMedia, $locale);

            // Display timezone: Asia/Ho_Chi_Minh
            $publishedDate = $post->published_at
                ? $post->published_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y')
                : null;

            return [
                'id' => $post->id,
                'title' => $trans?->title,
                'excerpt' => $trans?->excerpt ?: $this->deriveSafeExcerpt($trans?->content, 140),
                'published_date' => $publishedDate,
                'category_name' => $catTrans?->name,
                'media' => $media,
            ];
        })->toArray();
    }

    /**
     * Derive a safe plain-text presentation excerpt from potential rich HTML content.
     * Note: This performs narrow Homepage plain-text presentation cleaning, not general-purpose HTML sanitization.
     */
    public function deriveSafeExcerpt(?string $content, int $limit = 200): string
    {
        if (empty($content)) {
            return '';
        }

        // 1. Decode HTML entities first to expose any entity-encoded non-editorial blocks
        $clean = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 2. Remove complete non-content blocks and their contents case-insensitively across multiple lines
        $clean = (string) preg_replace('/<(script|style|iframe|object|embed|noscript)\b[^>]*>.*?<\/\1>/is', '', $clean);

        // 3. Strip remaining HTML tags
        $clean = strip_tags($clean);

        // 4. Normalize whitespace
        $clean = (string) preg_replace('/\s+/u', ' ', $clean);
        $clean = trim($clean);

        // 5. Truncate safely
        if (mb_strlen($clean, 'UTF-8') <= $limit) {
            return $clean;
        }

        return mb_substr($clean, 0, $limit, 'UTF-8').'...';
    }
}
