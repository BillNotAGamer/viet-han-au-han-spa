<?php

declare(strict_types=1);

namespace App\Services\Seo;

final readonly class SeoMetadata
{
    /**
     * @param  array<string, string>  $alternateUrls  Map of locale => canonical URL
     * @param  array<string, mixed>|list<array<string, mixed>>|null  $jsonLd
     */
    public function __construct(
        public string $title,
        public ?string $description,
        public string $canonical,
        public string $locale,
        public array $alternateUrls = [],
        public ?string $xDefaultUrl = null,
        public string $robots = 'index, follow',
        public string $ogType = 'website',
        public ?string $ogImage = null,
        public string $twitterCard = 'summary',
        public ?array $jsonLd = null,
    ) {}

    /**
     * Return safe JSON-LD script tag if schema is provided.
     */
    public function jsonLdScript(): ?string
    {
        if (empty($this->jsonLd)) {
            return null;
        }

        $json = json_encode(
            $this->jsonLd,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        );

        if ($json === false) {
            return null;
        }

        return '<script type="application/ld+json">'.$json.'</script>';
    }
}
