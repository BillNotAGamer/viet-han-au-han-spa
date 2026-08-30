# Services Public Architecture

**Phase:** 10A - Services public listing and detail pages
**Status:** Accepted for implementation review
**Date:** 2026-08-29

## Routes

Vietnamese remains the default canonical locale without a prefix:

- `GET /dich-vu` -> `vi.services.index`
- `GET /dich-vu/{slug}` -> `vi.services.show`

English remains under the `/en` prefix:

- `GET /en/services` -> `en.services.index`
- `GET /en/services/{slug}` -> `en.services.show`

There is no `/vi/dich-vu` route tree. The existing `/vi` redirect to `/` remains the only Vietnamese-prefix compatibility route.

## Controller

`App\Http\Controllers\Public\ServiceController` is a thin HTTP coordinator. It reads the current locale from the locale middleware, delegates all content preparation to `App\Services\PublicSite\ServicesContent`, registers localized switch URLs on the request, and returns Blade views.

## Public Read Service

`App\Services\PublicSite\ServicesContent` owns public Service composition:

- Listing query with pagination.
- Detail lookup through `service_translations.locale + slug`.
- Exact-locale presentation arrays for Blade.
- Publication filtering.
- Category, price, media, gallery, and structured-content preparation.
- Language-switch URL mapping for dynamic detail pages.

Blade receives arrays and paginators only; database access does not occur in the public Services views.

## Exact-Locale Lookup

Public Services never call `translationOrFallback()`. A Service must have a `service_translations` row in the requested locale to appear on listing pages or resolve on detail pages.

Examples:

- `PUBLISHED` Service with VI translation only appears at `/dich-vu`.
- The same record does not appear at `/en/services`.
- `/en/services/{vi-slug}` returns 404.

## Publication Filtering

Only `services.status = PUBLISHED` is public. `DRAFT` and `ARCHIVED` Services are excluded from listings and return 404 for direct detail requests.

## Localized Slug Behavior

Detail slugs are locale-specific database content. The VI slug is resolved only by `vi.services.show`; the EN slug is resolved only by `en.services.show`. Cross-locale slug fallback and silent redirects are prohibited.

## Listing Pagination

The listing page uses Laravel pagination at 12 Services per page. Ordering is deterministic: `sort_order ASC, id ASC`. Pagination is query-based and not performed after loading the entire catalog.

## Media Resolution

Service hero media uses `services.hero_media_id`. Gallery media uses the ordered `service_media` pivot. Media is resolved in `ServicesContent` through `PublicMediaResolver`, which checks the configured storage disk and returns `null` for missing physical files. Missing media never crashes public pages.

Media alt text uses exact-locale `media_translations.alt_text`. If it is absent, the public presentation uses `alt=""`.

## Price Presentation

Active `service_prices` rows are presented in deterministic order. Persisted integer VND amounts are formatted for display. Exact-locale `service_price_translations.label` is used when present. Missing optional labels are omitted; wrong-locale labels are never shown.

## Category Behavior

The Service itself remains public when its own translation is valid even if the category lacks a requested-locale translation. Category labels are displayed only when `service_category_translations.locale` matches the current locale.

## Empty States

`/dich-vu` and `/en/services` return 200 when no eligible records exist. The empty state is localized and does not fabricate business services or expose internal diagnostics.

## Language Switching

Listing pages switch between `/dich-vu` and `/en/services`. Detail pages switch to the exact translated Service slug when the target translation exists. If the target translation is missing, the switch target falls back to the target locale Services index.

## Inner-Page Header

Services pages use the approved Phase 9.5 sticky-style header state from initial render through the shared layout's `header-mode="solid"` prop. This preserves the existing header geometry and classes while avoiding a transparent overlay on non-homepage content.

## Phase 10B+ Boundaries

This architecture does not introduce Training, Blog, About, Contact, Booking, Tracking, structured SEO, sitemap, or OpenGraph routes. Booking remains deferred; Services CTAs point to the existing Homepage contact fragment.
