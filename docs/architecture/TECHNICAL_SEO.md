# Technical SEO & Search Discovery Architecture

**Phase:** Phase 13
**Status:** Closed
**Production Domain:** `https://viethanauhanspa.com`

---

## 1. Metadata Fallback Policy

Metadata composition is centralized in `App\Services\Seo\SeoManager` and rendered via `<x-seo.head>`. Public Blade views perform zero direct database or Eloquent queries.

### Title Policy
Deterministic, exact-locale precedence:
1. `seo_title` from the entity/page translation (if non-empty).
2. Localized entity/page title + ` â€” ` + `common.brand_name`.
3. Site fallback: `common.brand_name` + ` â€” ` + `common.tagline`.

*Exact-locale invariant:* English URLs never receive Vietnamese fallback titles.

### Description Policy
Deterministic, exact-locale precedence:
1. `seo_description` from the entity/page translation (if non-empty, trimmed to 255 chars).
2. Localized entity/page excerpt or first paragraph (trimmed to 255 chars).
3. Omitted entirely: if no legitimate description exists, no `<meta name="description">` is rendered. Zero fabricated marketing descriptions.

---

## 2. Canonical URL Policy

All canonical URLs adhere to:
- Scheme: HTTPS.
- Host: `https://viethanauhanspa.com` (configurable via `config('app.canonical_url')`).
- Normalization: No trailing slash (except root `/`).
- Campaign Stripping: Exclude all marketing query parameters (`utm_*`, `fbclid`, `gclid`, `gbraid`, `wbraid`, etc.) and URL fragments.
- Legitimate Pagination: Preserve legitimate `page=N` if and only if `N > 1` on paginated index routes. If `page=1` or non-integer, omit query parameters.

---

## 3. Hreflang & Alternate Locale Policy

- **Bilingual Pages:** When an exact translation exists in both locales (`vi` and `en`), render:
  - `<link rel="alternate" hreflang="vi" href="...">`
  - `<link rel="alternate" hreflang="en" href="...">`
  - `<link rel="alternate" hreflang="x-default" href="...">` (pointing to the canonical Vietnamese URL).
- **Single-Locale Entities:** If a detail entity exists only in one locale (e.g. Vietnamese), do NOT emit a fake alternate URL for the other locale.
- **Static Pages:** Route pairs map canonical endpoints (`/` <-> `/en`, `/dich-vu` <-> `/en/services`, `/dao-tao` <-> `/en/training`, `/blog` <-> `/en/blog`, `/dat-lich` <-> `/en/booking`). CMS pages (`about`, `contact`) verify translation existence before emitting alternates.

---

## 4. Sitemap Eligibility & Performance

Endpoint: `GET /sitemap.xml` (rendered on-demand, zero external packages, zero file writes).
Content-Type: `application/xml; charset=utf-8`.

### Eligible URLs:
1. **Static Canonical Routes:** `/`, `/en`, `/dich-vu`, `/en/services`, `/dao-tao`, `/en/training`, `/blog`, `/en/blog`, `/dat-lich`, `/en/booking`.
2. **CMS Pages (`about`, `contact`):** Included only when `Page` is `PUBLISHED` and translation exists in the respective locale (`/gioi-thieu`, `/en/about`, `/lien-he`, `/en/contact`).
3. **Services:** Included only when `Service` status is `PUBLISHED`, iterating translations with valid slugs.
4. **Training Courses:** Included only when `TrainingCourse` status is `PUBLISHED` and `published_at <= now()`.
5. **Blog Posts:** Included only when `Post` status is `PUBLISHED` and `published_at <= now()`.

### Excluded URLs:
- Any `DRAFT` or `ARCHIVED` records.
- Future-scheduled courses or posts (`published_at > now()`).
- Untranslated locale paths.
- Admin routes (`/admin*`), Livewire endpoints (`/livewire*`), login/auth routes.
- Tracking query parameters, session states, booking customer data.

---

## 5. Robots Policy & robots.txt

`public/robots.txt` configuration:
```txt
User-agent: *
Allow: /
Disallow: /admin
Disallow: /admin/
Disallow: /livewire/

Sitemap: https://viethanauhanspa.com/sitemap.xml
```

Public canonical pages output `<meta name="robots" content="index, follow">`. Admin, Livewire, and sensitive endpoints are strictly disbarred.

---

## 6. Structured Data (JSON-LD) Boundary

Zero speculative or fabricated rich-result schema:
- **LocalBusiness / BeautySalon:** Emitted on public pages using only allow-listed, real public `SiteSettings` (`contact.phone`, `contact.email`, `contact.address`, `social.*`) and canonical brand information. Missing fields are omitted; zero fabricated ratings, review counts, coordinates, or opening hour schemas.
- **BlogPosting:** Emitted on Blog detail pages with `headline`, `mainEntityOfPage`, `datePublished`, `image` (if hero media exists), and `author` (person name if present). Never exposes author email, internal IDs, or admin metadata.
- **Serialization Safety:** Serialized using `JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP` to completely prevent script breakout and XSS vulnerabilities.

---

## 7. Security & Architecture Boundaries

- **Host Safety:** Canonical and sitemap URLs derive from trusted canonical base configuration, not arbitrary incoming HTTP `Host` headers.
- **Exact-Locale Isolation:** Requested locale strictly bounds metadata resolution; no Vietnamese data leaks under English routes.
- **Tracking Compatibility:** Does not modify Phase 12 tracking configuration, attribution capture, or PRG booking conversion flash.
- **Zero Package / Zero Migration:** Implemented natively using Laravel 13 features without additional Composer/npm dependencies or database schema modifications.
