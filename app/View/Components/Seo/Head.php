<?php

declare(strict_types=1);

namespace App\View\Components\Seo;

use App\Services\Seo\SeoManager;
use App\Services\Seo\SeoMetadata;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Head extends Component
{
    public SeoMetadata $metadata;

    public function __construct(
        ?SeoMetadata $seo = null,
        ?string $title = null,
        ?SeoManager $seoManager = null,
    ) {
        if ($seo !== null) {
            $this->metadata = $seo;
        } else {
            $manager = $seoManager ?? app(SeoManager::class);
            $this->metadata = $manager->composeFallback($title, app()->getLocale(), request());
        }
    }

    public function render(): View
    {
        return view('components.seo.head', [
            'seo' => $this->metadata,
        ]);
    }
}
