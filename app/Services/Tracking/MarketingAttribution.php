<?php

declare(strict_types=1);

namespace App\Services\Tracking;

use Illuminate\Http\Request;

class MarketingAttribution
{
    public const SESSION_LANDING_PAGE = 'marketing_attribution.landing_page';

    public const SESSION_REFERRER = 'marketing_attribution.referrer';

    public const SESSION_CAMPAIGN = 'marketing_attribution.campaign';

    public const SESSION_META_COOKIES = 'marketing_attribution.meta_cookies';

    /**
     * Recognized campaign and click identifier parameter names.
     */
    public const CAMPAIGN_PARAMETERS = [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'gclid',
        'gbraid',
        'wbraid',
        'fbclid',
    ];

    /**
     * Column length bounds based on authoritative migration schema.
     */
    public const COLUMN_LIMITS = [
        'utm_source' => 100,
        'utm_medium' => 100,
        'utm_campaign' => 150,
        'utm_content' => 150,
        'utm_term' => 150,
        'gclid' => 150,
        'gbraid' => 100,
        'wbraid' => 100,
        'fbclid' => 150,
        'fbp' => 100,
        'fbc' => 100,
        'landing_page' => 500,
        'referrer' => 500,
    ];

    /**
     * Capture attribution from an incoming HTTP request into the session.
     */
    public function captureFromRequest(Request $request): void
    {
        if (! $request->hasSession()) {
            return;
        }

        $session = $request->session();

        // 1. Initial landing page and referrer (captured once per session)
        if (! $session->has(self::SESSION_LANDING_PAGE)) {
            $landingPage = $this->sanitizeString($request->fullUrl(), self::COLUMN_LIMITS['landing_page']);
            $session->put(self::SESSION_LANDING_PAGE, $landingPage);

            $rawReferer = $request->headers->get('referer');
            $referrer = $rawReferer !== null ? $this->sanitizeString($rawReferer, self::COLUMN_LIMITS['referrer']) : null;
            $session->put(self::SESSION_REFERRER, $referrer);
        }

        // 2. Latest campaign touch within session
        $campaignData = $this->extractCampaignFromRequest($request);
        if ($campaignData !== null) {
            $session->put(self::SESSION_CAMPAIGN, $campaignData);
        }

        // 3. Meta browser cookies if present
        $metaCookies = $this->extractMetaCookiesFromRequest($request);
        if (! empty($metaCookies)) {
            $existing = $session->get(self::SESSION_META_COOKIES, []);
            $session->put(self::SESSION_META_COOKIES, array_merge($existing, $metaCookies));
        }
    }

    /**
     * Retrieve all sanitized attribution fields for persisting to a Booking.
     *
     * @return array<string, ?string>
     */
    public function getAttributionData(?Request $request = null): array
    {
        $session = $request?->hasSession() ? $request->session() : null;

        // Landing page: from session first, then current request URL
        $landingPage = $session?->get(self::SESSION_LANDING_PAGE);
        if ($landingPage === null && $request !== null) {
            $landingPage = $this->sanitizeString($request->fullUrl(), self::COLUMN_LIMITS['landing_page']);
        }

        // Referrer: from session first, then current request header
        $referrer = $session?->get(self::SESSION_REFERRER);
        if ($referrer === null && $request !== null) {
            $rawReferer = $request->headers->get('referer');
            $referrer = $rawReferer !== null ? $this->sanitizeString($rawReferer, self::COLUMN_LIMITS['referrer']) : null;
        }

        // Campaign: from session first, then inspect request directly
        $campaign = $session?->get(self::SESSION_CAMPAIGN);
        if ($campaign === null && $request !== null) {
            $campaign = $this->extractCampaignFromRequest($request);
        }
        $campaign = $campaign ?? [];

        // Meta cookies: from session or current request cookies
        $metaCookies = $session?->get(self::SESSION_META_COOKIES, []);
        if ($request !== null) {
            $requestMeta = $this->extractMetaCookiesFromRequest($request);
            $metaCookies = array_merge($metaCookies, $requestMeta);
        }

        return [
            'landing_page' => $landingPage,
            'referrer' => $referrer,
            'utm_source' => $campaign['utm_source'] ?? null,
            'utm_medium' => $campaign['utm_medium'] ?? null,
            'utm_campaign' => $campaign['utm_campaign'] ?? null,
            'utm_content' => $campaign['utm_content'] ?? null,
            'utm_term' => $campaign['utm_term'] ?? null,
            'gclid' => $campaign['gclid'] ?? null,
            'gbraid' => $campaign['gbraid'] ?? null,
            'wbraid' => $campaign['wbraid'] ?? null,
            'fbclid' => $campaign['fbclid'] ?? null,
            'fbp' => $metaCookies['fbp'] ?? null,
            'fbc' => $metaCookies['fbc'] ?? null,
        ];
    }

    /**
     * Extract campaign parameters from request if any recognized parameter is present.
     *
     * @return array<string, ?string>|null
     */
    protected function extractCampaignFromRequest(Request $request): ?array
    {
        $hasAny = false;
        $extracted = [];

        foreach (self::CAMPAIGN_PARAMETERS as $param) {
            $val = $request->query($param);
            if ($val !== null && is_scalar($val)) {
                $sanitized = $this->sanitizeString((string) $val, self::COLUMN_LIMITS[$param]);
                if ($sanitized !== null) {
                    $hasAny = true;
                    $extracted[$param] = $sanitized;

                    continue;
                }
            }
            $extracted[$param] = null;
        }

        return $hasAny ? $extracted : null;
    }

    /**
     * Extract Meta browser cookies (_fbp, _fbc) from request.
     *
     * @return array<string, ?string>
     */
    protected function extractMetaCookiesFromRequest(Request $request): array
    {
        $result = [];

        $rawFbp = $request->cookie('_fbp');
        if ($rawFbp !== null && is_scalar($rawFbp)) {
            $sanitized = $this->sanitizeString((string) $rawFbp, self::COLUMN_LIMITS['fbp']);
            if ($sanitized !== null) {
                $result['fbp'] = $sanitized;
            }
        }

        $rawFbc = $request->cookie('_fbc');
        if ($rawFbc !== null && is_scalar($rawFbc)) {
            $sanitized = $this->sanitizeString((string) $rawFbc, self::COLUMN_LIMITS['fbc']);
            if ($sanitized !== null) {
                $result['fbc'] = $sanitized;
            }
        }

        return $result;
    }

    /**
     * Sanitize scalar string: trim, strip control characters, bound length, empty -> null.
     */
    public function sanitizeString(string $value, int $maxLength): ?string
    {
        $cleaned = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
        if ($cleaned === null) {
            $cleaned = '';
        }

        $trimmed = trim($cleaned);
        if ($trimmed === '') {
            return null;
        }

        return mb_substr($trimmed, 0, $maxLength, 'UTF-8');
    }
}
