# Phase 10A Services Public Audit

Date: 2026-08-30
Repository: `F:\Coding\Web development\Viet Han Spa`
Starting HEAD: `b749ce0dbc8aecd11c9c05d2f6cb58a0a6df34af`

## Objective

Implement Phase 10A only: public Services listing and detail pages for Vietnamese and English canonical routes using the existing Services CMS model, exact-locale localization policy, Blade SSR, Tailwind CSS 4, and Alpine.js.

## Starting Git State

The starting gate was verified clean at:

`b749ce0dbc8aecd11c9c05d2f6cb58a0a6df34af`

`git status --short` returned empty before implementation.

## Routes

Added public Services routes:

- `GET /dich-vu` -> `vi.services.index`
- `GET /dich-vu/{slug}` -> `vi.services.show`
- `GET /en/services` -> `en.services.index`
- `GET /en/services/{slug}` -> `en.services.show`

The existing `/` and `/en` Homepage routes remain. The existing `/vi` 301 redirect to `/` remains. No `/vi/dich-vu` route was added.

## Controller and Read Service

`App\Http\Controllers\Public\ServiceController` is a thin HTTP controller. It resolves the current locale, registers localized switch URLs, delegates read/composition work to `App\Services\PublicSite\ServicesContent`, and renders Blade views.

`App\Services\PublicSite\ServicesContent` owns listing and detail query composition, exact translation enforcement, public publication filtering, media preparation, category presentation, price presentation, safe content extraction, and localized detail URL mapping.

## Listing

`resources/views/public/services/index.blade.php` renders localized page intro copy, a paginated list of public-eligible Services, hero media when available, localized name, excerpt, optional exact-locale category label, price summary when available, and detail links.

Pagination uses 12 Services per page with deterministic ordering: `sort_order ASC, id ASC`.

## Detail

`resources/views/public/services/show.blade.php` renders a media-led inner-page Service detail view with localized name, optional category, safe plain-text content, benefits, process steps, active prices, gallery, FAQs, and an inquiry CTA.

Raw CMS rich HTML is not rendered directly. Service content is stripped to safe plain text in the read service and then escaped by Blade.

FAQ rendering is based on actual existing domain/schema data: the `service_translations.faqs` JSON column. No hardcoded or fabricated FAQ content is rendered.

## Exact Locale

Public listing and detail require exact requested-locale `service_translations` rows. Vietnamese content does not fallback under `/en`.

## Slug Policy

Detail slugs are locale-specific. Lookup uses requested `locale + slug`; cross-locale slug matches return 404.

## Publication Filtering

Only `services.status = PUBLISHED` appears publicly. `DRAFT` and `ARCHIVED` records are excluded from listings and return public 404 on detail requests.

## Prices

Active `service_prices` rows are displayed in persisted order. `price_amount` remains integer VND and is formatted for presentation. Exact-locale `service_price_translations.label` is used only when present; missing labels are omitted and wrong-locale labels do not leak.

## Media

Service hero media and gallery media use existing Media relationships. `PublicMediaResolver` checks the configured disk before producing URLs and returns `null` for missing physical files. Missing media does not crash listing or detail pages. Alt text uses exact-locale `MediaTranslation`; missing alt becomes `alt=""`. Wrong-locale media alt text is not shown.

## Category Handling

Category labels display only when the category has an exact requested-locale translation. A missing category translation does not hide a valid public Service.

## Language Switching

Listing pages switch between `/dich-vu` and `/en/services`. Detail pages use exact translated Service slugs when the target locale translation exists. Missing target detail translation falls back to the target locale Services index.

## Inner-Page Header

Services pages use the approved Phase 9.5 sticky-style header state from initial render through `header-mode="solid"` on the shared layout. Header geometry CSS was not changed. Homepage overlay/sticky behavior remains frozen.

## Responsive Source Review

Services listing uses one column on small screens, two columns at medium width, and three columns at extra-large width. Detail content stacks on small screens and uses a content/sidebar layout at large width. No browser automation was run by instruction.

## Accessibility

Each Services page has one `<h1>`, logical section headings, semantic `<article>` usage, keyboard-focusable links, and media alt text that is meaningful when authored or empty when absent.

## Route Boundary

Phase 10A added only Services public routes. Training, Blog, About, Contact, Booking, Tracking, technical SEO, sitemap, JSON-LD, and OpenGraph routes remain deferred.

## Migrations

No migrations were added. No schema changes were made.

## Tests

Full PHPUnit evidence:

- Tests: 247
- Assertions: 953
- Failures: 0
- Errors: 0
- Skipped: 0
- JUnit duration: 8.239180 seconds

Targeted Phase 10A public Services test was also run before full verification:

- Tests: 12
- Assertions: 72
- Failures: 0
- Errors: 0

## Pint

`vendor/bin/pint --test` passed with exit code 0.

## Vite

`cmd /c npm run build` passed with exit code 0. The existing optional `fontaine` fallback optimization warning appeared during build; it did not fail the build.

## Composer

Composer validate passed with exit code 0.

## Blade DB-Query Review

`resources/views/public/services/*.blade.php` was scanned for prohibited direct DB/Eloquent query patterns. No prohibited patterns were found.

## Git Diff

Phase 10A source changes are uncommitted for architect and human browser review. `git diff --check` passed. Git reports CRLF normalization warnings for `AGENTS.md` and `resources/views/components/layouts/public.blade.php`; no whitespace errors were reported.

## Human QA

Services listing desktop:
HUMAN VISUALLY VERIFIED

Services listing mobile:
HUMAN VISUALLY VERIFIED

Service detail desktop:
HUMAN VISUALLY VERIFIED

Service detail mobile:
HUMAN VISUALLY VERIFIED

Detail locale switching:
HUMAN FUNCTIONALLY VERIFIED

Cross-locale slug 404 behavior:
HUMAN FUNCTIONALLY VERIFIED

## Production DB Status

NOT TESTED.

## Known Debt

The duplicate public layout debt remains from Phase 9.5:

- Active: `resources/views/components/layouts/public.blade.php`
- Legacy/apparently unused: `resources/views/layouts/public.blade.php`

No layout cleanup was performed in Phase 10A.

## Deferred Phase 10B+

Training public pages, Blog public pages, About, Contact, Booking, Tracking, and SEO remain deferred to later phases.
