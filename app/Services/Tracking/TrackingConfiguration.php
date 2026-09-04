<?php

declare(strict_types=1);

namespace App\Services\Tracking;

use App\Services\Settings\SiteSettings;

class TrackingConfiguration
{
    public const KEY_ENABLED = 'tracking.enabled';

    public const KEY_GTM_CONTAINER_ID = 'tracking.gtm_container_id';

    public const KEY_GA4_MEASUREMENT_ID = 'tracking.ga4_measurement_id';

    public const KEY_META_PIXEL_ID = 'tracking.meta_pixel_id';

    public function __construct(
        protected SiteSettings $siteSettings
    ) {}

    public function isEnabled(): bool
    {
        $value = $this->siteSettings->getPublic(self::KEY_ENABLED, false);

        return $value === true;
    }

    public function getGtmContainerId(): ?string
    {
        $raw = $this->siteSettings->getPublic(self::KEY_GTM_CONTAINER_ID);
        if (! is_string($raw)) {
            return null;
        }

        $trimmed = trim($raw);
        if (preg_match('/^GTM-[A-Z0-9]+$/i', $trimmed) === 1) {
            return strtoupper($trimmed);
        }

        return null;
    }

    public function getGa4MeasurementId(): ?string
    {
        $raw = $this->siteSettings->getPublic(self::KEY_GA4_MEASUREMENT_ID);
        if (! is_string($raw)) {
            return null;
        }

        $trimmed = trim($raw);
        if (preg_match('/^G-[A-Z0-9]+$/i', $trimmed) === 1) {
            return strtoupper($trimmed);
        }

        return null;
    }

    public function getMetaPixelId(): ?string
    {
        $raw = $this->siteSettings->getPublic(self::KEY_META_PIXEL_ID);
        if (! is_string($raw)) {
            return null;
        }

        $trimmed = trim($raw);
        if (preg_match('/^[0-9]{5,25}$/', $trimmed) === 1) {
            return $trimmed;
        }

        return null;
    }

    /**
     * Determine delivery mode based on strict priority:
     * 1. If tracking not enabled -> 'none'
     * 2. If valid GTM container ID exists -> 'gtm' (exclusive, suppresses direct GA4/Meta)
     * 3. Else if valid GA4 or Meta Pixel ID exists -> 'direct'
     * 4. Else -> 'none'
     */
    public function getDeliveryMode(): string
    {
        if (! $this->isEnabled()) {
            return 'none';
        }

        if ($this->getGtmContainerId() !== null) {
            return 'gtm';
        }

        if ($this->getGa4MeasurementId() !== null || $this->getMetaPixelId() !== null) {
            return 'direct';
        }

        return 'none';
    }

    /**
     * @return array{enabled: bool, mode: string, gtmId: ?string, ga4Id: ?string, metaPixelId: ?string}
     */
    public function getViewData(): array
    {
        $mode = $this->getDeliveryMode();

        return [
            'enabled' => $this->isEnabled(),
            'mode' => $mode,
            'gtmId' => $mode === 'gtm' ? $this->getGtmContainerId() : null,
            'ga4Id' => $mode === 'direct' ? $this->getGa4MeasurementId() : null,
            'metaPixelId' => $mode === 'direct' ? $this->getMetaPixelId() : null,
        ];
    }
}
