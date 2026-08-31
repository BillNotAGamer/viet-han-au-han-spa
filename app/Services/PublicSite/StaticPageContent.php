<?php

declare(strict_types=1);

namespace App\Services\PublicSite;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\Page;
use App\Services\Settings\SiteSettings;
use Illuminate\Database\Eloquent\Builder;

class StaticPageContent
{
    public function __construct(
        protected PublicMediaResolver $mediaResolver,
        protected SiteSettings $siteSettings
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function pageForKey(string $key, string $locale): ?array
    {
        $page = Page::query()
            ->where('key', $key)
            ->where('status', ContentStatus::PUBLISHED)
            ->whereHas('translations', fn (Builder $query) => $query->where('locale', $locale))
            ->with([
                'translations',
                'media' => fn ($query) => $query
                    ->with(['translations' => fn ($translationQuery) => $translationQuery->where('locale', $locale)])
                    ->orderByPivot('sort_order', 'asc'),
            ])
            ->first();

        if (! $page) {
            return null;
        }

        $translation = $page->translations->firstWhere('locale', $locale);
        if (! $translation) {
            return null;
        }

        $media = $this->presentMedia($page->media, $locale);

        return [
            'key' => $page->key,
            'title' => $translation->title,
            'content' => $this->plainTextParagraphs($translation->content),
            'lead_media' => $media[0] ?? null,
            'gallery' => array_slice($media, 1),
            'localized_urls' => $this->localizedUrls($page->key, $page),
        ];
    }

    /**
     * @return array{items: array<int, array<string, string|null>>, links: array<int, array<string, string>>}
     */
    public function contactSettings(): array
    {
        return [
            'items' => array_values(array_filter([
                $this->phoneItem(),
                $this->emailItem(),
                $this->textItem('address', __('pages.contact.address'), $this->siteSettings->getPublic('contact.address')),
                $this->textItem('hours', __('pages.contact.hours'), $this->siteSettings->getPublic('business.hours')),
            ])),
            'links' => array_values(array_filter([
                $this->externalLink('facebook', 'Facebook', $this->siteSettings->getPublic('social.facebook_url')),
                $this->externalLink('zalo', 'Zalo', $this->siteSettings->getPublic('social.zalo_url')),
                $this->externalLink('youtube', 'YouTube', $this->siteSettings->getPublic('social.youtube_url')),
            ])),
        ];
    }

    /**
     * @param  iterable<int, Media>  $mediaItems
     * @return array<int, array<string, mixed>>
     */
    protected function presentMedia(iterable $mediaItems, string $locale): array
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

    protected function phoneItem(): ?array
    {
        $phone = trim((string) $this->siteSettings->getPublic('contact.phone', ''));
        $normalized = preg_replace('/[^0-9+]/', '', $phone) ?: '';

        if ($phone === '' || ! preg_match('/^\+?[0-9]{6,15}$/', $normalized)) {
            return null;
        }

        return [
            'type' => 'phone',
            'label' => __('pages.contact.phone'),
            'value' => $phone,
            'href' => 'tel:'.$normalized,
        ];
    }

    protected function emailItem(): ?array
    {
        $email = trim((string) $this->siteSettings->getPublic('contact.email', ''));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return [
            'type' => 'email',
            'label' => __('pages.contact.email'),
            'value' => $email,
            'href' => 'mailto:'.$email,
        ];
    }

    protected function textItem(string $type, string $label, mixed $value): ?array
    {
        $text = trim((string) $value);

        if ($text === '') {
            return null;
        }

        return [
            'type' => $type,
            'label' => $label,
            'value' => $text,
            'href' => null,
        ];
    }

    protected function externalLink(string $type, string $label, mixed $value): ?array
    {
        $url = $this->safeHttpUrl($value);

        if ($url === null) {
            return null;
        }

        return [
            'type' => $type,
            'label' => $label,
            'url' => $url,
        ];
    }

    protected function safeHttpUrl(mixed $value): ?string
    {
        $url = trim((string) $value);

        if ($url === '') {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? $url : null;
    }

    /**
     * @return array<int, string>
     */
    protected function plainTextParagraphs(?string $content): array
    {
        $plain = $this->derivePlainText($content, 10000);

        if ($plain === '') {
            return [];
        }

        return array_values(array_filter(
            preg_split('/(?:\r?\n){2,}/', wordwrap($plain, 900, "\n\n", false)) ?: [],
            fn (string $paragraph) => trim($paragraph) !== ''
        ));
    }

    protected function derivePlainText(?string $content, int $limit): string
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
    protected function localizedUrls(string $key, Page $page): array
    {
        $viRoute = $key === 'contact' ? 'vi.contact' : 'vi.about';
        $enRoute = $key === 'contact' ? 'en.contact' : 'en.about';

        return [
            'vi' => $page->translations->firstWhere('locale', 'vi') ? route($viRoute) : route('vi.home'),
            'en' => $page->translations->firstWhere('locale', 'en') ? route($enRoute) : route('en.home'),
        ];
    }
}
