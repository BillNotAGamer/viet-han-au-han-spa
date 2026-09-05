<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Services\Settings\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class SeoManager
{
    public function __construct(
        protected SiteSettings $siteSettings
    ) {}

    public function canonicalBase(): string
    {
        return rtrim((string) config('app.canonical_url', 'https://viethanauhanspa.com'), '/');
    }

    public function canonicalUrl(string $urlOrPath, ?int $page = null): string
    {
        $base = $this->canonicalBase();

        if (str_starts_with($urlOrPath, 'http://') || str_starts_with($urlOrPath, 'https://')) {
            $path = parse_url($urlOrPath, PHP_URL_PATH) ?: '/';
        } else {
            $path = '/'.ltrim($urlOrPath, '/');
        }

        $normalizedPath = $path === '/' ? '/' : rtrim($path, '/');
        $canonical = $base.$normalizedPath;

        if ($page !== null && $page > 1) {
            $canonical .= '?page='.$page;
        }

        return $canonical;
    }

    public function computeTitle(?string $seoTitle, ?string $fallbackTitle, string $locale): string
    {
        $seoTitle = trim((string) $seoTitle);
        if ($seoTitle !== '') {
            return $seoTitle;
        }

        $fallbackTitle = trim((string) $fallbackTitle);
        $brandName = (string) __('common.brand_name', [], $locale);

        if ($fallbackTitle !== '') {
            return $fallbackTitle.' — '.$brandName;
        }

        $tagline = (string) __('common.tagline', [], $locale);

        return $brandName.' — '.$tagline;
    }

    public function computeDescription(?string $seoDescription, ?string $fallbackDescription): ?string
    {
        $seoDescription = trim((string) $seoDescription);
        if ($seoDescription !== '') {
            return $this->cleanText($seoDescription, 255);
        }

        $fallbackDescription = trim((string) $fallbackDescription);
        if ($fallbackDescription !== '') {
            return $this->cleanText($fallbackDescription, 255);
        }

        return null;
    }

    public function cleanText(?string $text, int $limit = 255): string
    {
        if (empty($text)) {
            return '';
        }

        $clean = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean = (string) preg_replace('/<(script|style|iframe|object|embed|noscript)\b[^>]*>.*?<\/\1>/is', '', $clean);
        $clean = strip_tags($clean);
        $clean = (string) preg_replace('/\s+/u', ' ', $clean);
        $clean = trim($clean);

        if (mb_strlen($clean, 'UTF-8') <= $limit) {
            return $clean;
        }

        return mb_substr($clean, 0, $limit, 'UTF-8');
    }

    public function ogLocale(string $locale): string
    {
        return $locale === 'en' ? 'en_US' : 'vi_VN';
    }

    /**
     * @param  array<string, string|null>  $routeAlternates  Raw alternate paths/routes by locale
     * @return array{alternates: array<string, string>, xDefault: ?string}
     */
    public function resolveAlternates(array $routeAlternates, ?int $page = null): array
    {
        $valid = [];
        foreach ($routeAlternates as $loc => $urlOrPath) {
            if (! empty($urlOrPath)) {
                $valid[$loc] = $this->canonicalUrl((string) $urlOrPath, $page);
            }
        }

        // Only emit alternates if at least one alternate locale exists or to provide x-default
        $xDefault = null;
        if (isset($valid['vi'])) {
            $xDefault = $valid['vi'];
        } elseif (count($valid) === 1) {
            $xDefault = reset($valid);
        }

        return [
            'alternates' => $valid,
            'xDefault' => $xDefault,
        ];
    }

    /**
     * Compose SEO metadata for Homepage.
     */
    public function composeForHome(string $locale, ?array $homePageData = null, ?array $heroMedia = null): SeoMetadata
    {
        $title = $this->computeTitle(
            $homePageData['seo_title'] ?? null,
            $homePageData['title'] ?? null,
            $locale
        );

        $description = $this->computeDescription(
            $homePageData['seo_description'] ?? null,
            null
        );

        $canonical = $this->canonicalUrl($locale === 'en' ? '/en' : '/');

        $alternatesData = $this->resolveAlternates([
            'vi' => '/',
            'en' => '/en',
        ]);

        $ogImage = ! empty($heroMedia['url']) ? (string) $heroMedia['url'] : null;

        return new SeoMetadata(
            title: $title,
            description: $description,
            canonical: $canonical,
            locale: $locale,
            alternateUrls: $alternatesData['alternates'],
            xDefaultUrl: $alternatesData['xDefault'],
            robots: 'index, follow',
            ogType: 'website',
            ogImage: $ogImage,
            twitterCard: $ogImage ? 'summary_large_image' : 'summary',
            jsonLd: $this->buildBeautySalonJsonLd(),
        );
    }

    /**
     * Compose SEO metadata for a fixed static Page (about, contact).
     */
    public function composeForStaticPage(string $key, string $locale, ?array $pageData = null): SeoMetadata
    {
        $path = match ($key) {
            'about' => $locale === 'en' ? '/en/about' : '/gioi-thieu',
            'contact' => $locale === 'en' ? '/en/contact' : '/lien-he',
            default => $locale === 'en' ? '/en' : '/',
        };

        $canonical = $this->canonicalUrl($path);

        $title = $this->computeTitle(
            $pageData['seo_title'] ?? null,
            $pageData['title'] ?? null,
            $locale
        );

        $description = $this->computeDescription(
            $pageData['seo_description'] ?? null,
            ! empty($pageData['content'][0]) ? $pageData['content'][0] : null
        );

        $rawAlternates = $pageData['seo_alternates'] ?? [
            'vi' => $key === 'contact' ? '/lien-he' : '/gioi-thieu',
            'en' => $key === 'contact' ? '/en/contact' : '/en/about',
        ];

        $alternatesData = $this->resolveAlternates($rawAlternates);

        $ogImage = ! empty($pageData['lead_media']['url']) ? (string) $pageData['lead_media']['url'] : null;

        return new SeoMetadata(
            title: $title,
            description: $description,
            canonical: $canonical,
            locale: $locale,
            alternateUrls: $alternatesData['alternates'],
            xDefaultUrl: $alternatesData['xDefault'],
            robots: 'index, follow',
            ogType: 'website',
            ogImage: $ogImage,
            twitterCard: $ogImage ? 'summary_large_image' : 'summary',
            jsonLd: $this->buildBeautySalonJsonLd(),
        );
    }

    /**
     * Compose SEO metadata for public listing pages (services, training, blog, booking).
     */
    public function composeForListing(string $entityType, string $locale, ?int $page = null): SeoMetadata
    {
        $pathPairs = [
            'services' => ['vi' => '/dich-vu', 'en' => '/en/services'],
            'training' => ['vi' => '/dao-tao', 'en' => '/en/training'],
            'blog' => ['vi' => '/blog', 'en' => '/en/blog'],
            'booking' => ['vi' => '/dat-lich', 'en' => '/en/booking'],
        ];

        $paths = $pathPairs[$entityType] ?? ['vi' => '/', 'en' => '/en'];
        $currentPath = $paths[$locale] ?? $paths['vi'];
        $canonical = $this->canonicalUrl($currentPath, $page);

        $titleKey = match ($entityType) {
            'services' => 'services.meta.index_title',
            'training' => 'training.meta.index_title',
            'blog' => 'blog.meta.index_title',
            'booking' => 'booking.meta.title',
            default => 'common.brand_name',
        };

        $listingTitle = (string) __($titleKey, [], $locale);
        $title = $this->computeTitle(null, $listingTitle, $locale);

        $descKey = match ($entityType) {
            'services' => 'services.meta.index_description',
            'training' => 'training.meta.index_description',
            'blog' => 'blog.meta.index_description',
            'booking' => 'booking.meta.description',
            default => '',
        };

        $listingDesc = $descKey !== '' && Lang::has($descKey, $locale)
            ? (string) __($descKey, [], $locale)
            : null;

        $description = $this->computeDescription(null, $listingDesc);

        $alternatesData = $this->resolveAlternates($paths, $page);

        return new SeoMetadata(
            title: $title,
            description: $description,
            canonical: $canonical,
            locale: $locale,
            alternateUrls: $alternatesData['alternates'],
            xDefaultUrl: $alternatesData['xDefault'],
            robots: 'index, follow',
            ogType: 'website',
            ogImage: null,
            twitterCard: 'summary',
            jsonLd: $this->buildBeautySalonJsonLd(),
        );
    }

    /**
     * Compose SEO metadata for detail pages (services, training, blog).
     */
    public function composeForDetail(string $entityType, string $locale, array $entityData): SeoMetadata
    {
        $rawPath = match ($entityType) {
            'services' => $locale === 'en' ? '/en/services/'.$entityData['slug'] : '/dich-vu/'.$entityData['slug'],
            'training' => $locale === 'en' ? '/en/training/'.$entityData['slug'] : '/dao-tao/'.$entityData['slug'],
            'blog' => $locale === 'en' ? '/en/blog/'.$entityData['slug'] : '/blog/'.$entityData['slug'],
            default => '/',
        };

        $canonical = $this->canonicalUrl($rawPath);

        $title = $this->computeTitle(
            $entityData['seo_title'] ?? null,
            $entityData['name'] ?? $entityData['title'] ?? null,
            $locale
        );

        $description = $this->computeDescription(
            $entityData['seo_description'] ?? null,
            $entityData['excerpt'] ?? (! empty($entityData['content'][0]) ? $entityData['content'][0] : null)
        );

        $rawAlternates = $entityData['seo_alternates'] ?? [];
        $alternatesData = $this->resolveAlternates($rawAlternates);

        $ogImage = ! empty($entityData['hero_media']['url']) ? (string) $entityData['hero_media']['url'] : null;
        $isBlog = $entityType === 'blog';

        $jsonLd = $isBlog
            ? $this->buildBlogPostJsonLd($entityData, $canonical, $ogImage)
            : $this->buildBeautySalonJsonLd();

        return new SeoMetadata(
            title: $title,
            description: $description,
            canonical: $canonical,
            locale: $locale,
            alternateUrls: $alternatesData['alternates'],
            xDefaultUrl: $alternatesData['xDefault'],
            robots: 'index, follow',
            ogType: $isBlog ? 'article' : 'website',
            ogImage: $ogImage,
            twitterCard: $ogImage ? 'summary_large_image' : 'summary',
            jsonLd: $jsonLd,
        );
    }

    /**
     * Fallback metadata when no entity/page context is provided.
     */
    public function composeFallback(?string $title = null, ?string $locale = null, ?Request $request = null): SeoMetadata
    {
        $locale = $locale ?: app()->getLocale();
        $path = $request ? $request->path() : ($locale === 'en' ? 'en' : '/');
        $canonical = $this->canonicalUrl($path);

        $finalTitle = $this->computeTitle(null, $title, $locale);

        return new SeoMetadata(
            title: $finalTitle,
            description: null,
            canonical: $canonical,
            locale: $locale,
            alternateUrls: [],
            xDefaultUrl: null,
            robots: 'index, follow',
            ogType: 'website',
            ogImage: null,
            twitterCard: 'summary',
            jsonLd: $this->buildBeautySalonJsonLd(),
        );
    }

    /**
     * Build minimal LocalBusiness / BeautySalon JSON-LD using only allow-listed public SiteSettings.
     *
     * @return array<string, mixed>|null
     */
    public function buildBeautySalonJsonLd(): ?array
    {
        $canonicalBase = $this->canonicalBase();
        $brandName = (string) __('common.brand_name');

        $rawPhone = trim((string) $this->siteSettings->getPublic('contact.phone', ''));
        $normalizedPhone = preg_replace('/[^0-9+]/', '', $rawPhone) ?: '';
        $phone = ($rawPhone !== '' && preg_match('/^\+?[0-9]{6,15}$/', $normalizedPhone)) ? $rawPhone : '';

        $email = trim((string) $this->siteSettings->getPublic('contact.email', ''));
        $address = trim((string) $this->siteSettings->getPublic('contact.address', ''));

        $facebook = $this->safeUrl($this->siteSettings->getPublic('social.facebook_url'));
        $zalo = $this->safeUrl($this->siteSettings->getPublic('social.zalo_url'));
        $youtube = $this->safeUrl($this->siteSettings->getPublic('social.youtube_url'));

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'BeautySalon',
            'name' => $brandName,
            'url' => $canonicalBase,
        ];

        if ($phone !== '') {
            $data['telephone'] = $phone;
        }

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $data['email'] = $email;
        }

        if ($address !== '') {
            $data['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $address,
            ];
        }

        $sameAs = array_values(array_filter([$facebook, $zalo, $youtube]));
        if (! empty($sameAs)) {
            $data['sameAs'] = $sameAs;
        }

        return $data;
    }

    /**
     * Build safe BlogPosting JSON-LD for Blog detail without exposing sensitive author or admin fields.
     *
     * @param  array<string, mixed>  $post
     * @return array<string, mixed>
     */
    public function buildBlogPostJsonLd(array $post, string $canonicalUrl, ?string $heroImageUrl): array
    {
        $blogPost = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => (string) ($post['title'] ?? ''),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $canonicalUrl,
            ],
        ];

        if (! empty($post['published_at_iso'])) {
            $blogPost['datePublished'] = (string) $post['published_at_iso'];
        }

        if (! empty($heroImageUrl)) {
            $blogPost['image'] = $heroImageUrl;
        }

        if (! empty($post['author_name'])) {
            $blogPost['author'] = [
                '@type' => 'Person',
                'name' => (string) $post['author_name'],
            ];
        }

        return $blogPost;
    }

    protected function safeUrl(mixed $url): ?string
    {
        $trimmed = trim((string) $url);
        if ($trimmed === '') {
            return null;
        }

        $scheme = strtolower((string) parse_url($trimmed, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? $trimmed : null;
    }
}
