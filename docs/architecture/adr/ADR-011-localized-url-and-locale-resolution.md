# ADR-011: Localized URL Structure and Locale Resolution Policy

**Status:** ACCEPTED  
**Context:**  
The website requires strict bilingual support (Vietnamese default `vi`, English secondary `en`) optimized for search engines, ad campaign tracking (Google Ads, Meta Ads), and conversion attribution. We evaluated:
1. URL prefix for all languages (`/vi` and `/en`).
2. Prefix-less default language (`/` for `vi`, `/en` for `en`).
3. Session/Cookie/Header-based locale switching without URL changes.

**Decision:**  
Adopt **Prefix-less default language** with explicit `/en` prefix:
- Vietnamese (default): No prefix (`/`, `/dich-vu`, `/blog`, `/gioi-thieu`).
- English: `/en` prefix (`/en`, `/en/services`, `/en/blog`, `/en/about`).
- `/vi` issues a 301 Permanent Redirect to `/`.
- URL is the sole source of truth for public locale resolution; no browser `Accept-Language` or IP auto-redirects.

**Consequences:**  
- **Positives:**
  - Cleanest canonical URL structure for primary Vietnamese domestic market.
  - Deterministic SEO indexing and ad landing page tracking.
  - Zero session/cookie state dependency for public page rendering.
- **Negatives:**
  - Route files require explicit prefix grouping for `/en` and default root routes.
