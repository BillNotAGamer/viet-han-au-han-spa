# Phase 10B Training Public Audit

Date: 2026-08-30
Repository: `F:\Coding\Web development\Viet Han Spa`
Starting HEAD: `80c347b0b1af8a43f88e77800eb62ea641ebc0db`

## Objective

Implement Phase 10B only: public Training listing and detail pages for Vietnamese and English canonical routes using the existing Training CMS model, exact-locale localization policy, Blade SSR, Tailwind CSS 4, and Alpine.js.

## Starting Git State

The starting gate was verified clean at `80c347b0b1af8a43f88e77800eb62ea641ebc0db`.

## Routes

Added public Training routes:

- `GET /dao-tao` -> `vi.training.index`
- `GET /dao-tao/{slug}` -> `vi.training.show`
- `GET /en/training` -> `en.training.index`
- `GET /en/training/{slug}` -> `en.training.show`

The existing `/`, `/en`, `/vi`, Services, and Services detail routes remain. No `/vi/dao-tao` route was added.

## Controller and Read Service

`App\Http\Controllers\Public\TrainingController` is a thin HTTP controller. It resolves the current locale, registers localized switch URLs, delegates read/composition work to `App\Services\PublicSite\TrainingContent`, and renders Blade views.

`App\Services\PublicSite\TrainingContent` owns listing and detail query composition, exact translation enforcement, public publication filtering, media preparation, tuition presentation, safe content extraction, structured content, and localized detail URL mapping.

## Listing

`resources/views/public/training/index.blade.php` renders localized page intro copy, a paginated list of public-eligible courses, hero media when available, localized title, excerpt, optional duration and schedule display, tuition when persisted, and detail links.

Pagination uses 12 courses per page with deterministic ordering: `sort_order ASC, id ASC`.

## Detail

`resources/views/public/training/show.blade.php` renders a media-led inner-page Training detail view with localized title, safe plain-text content, duration, schedule, target audience, tuition, curriculum modules, benefits, gallery, FAQs, and a safe inquiry CTA.

Raw CMS rich HTML is not rendered directly. Training content is stripped to safe plain text in the read service and then escaped by Blade.

FAQ rendering is based on actual existing domain/schema data: the `training_course_translations.faqs` JSON column. No hardcoded or fabricated FAQ content is rendered.

## Exact Locale and Slug Policy

Public listing and detail require exact requested-locale `training_course_translations` rows. Vietnamese content does not fallback under `/en`.

Detail slugs are locale-specific. Lookup uses requested `locale + slug`; cross-locale slug matches return 404.

## Publication Eligibility

Public eligibility requires:

- `training_courses.status = PUBLISHED`
- `training_courses.published_at IS NOT NULL`
- `training_courses.published_at <= now()`
- exact requested-locale translation exists

`DRAFT`, `ARCHIVED`, future-scheduled, and missing-locale records are excluded from listings and return public 404 on detail requests.

## Published At Semantics

`published_at` is a UTC publication lifecycle timestamp. It gates public visibility for scheduled content. It is not displayed as a class date, enrollment date, or course schedule.

## Tuition

`tuition_fee` is nullable integer VND. When present, it is displayed as a numeric persisted amount. `0` remains `0 ₫`; no free/contact/discount/installment semantics are invented.

## Media and Gallery

Training hero media and gallery media use existing Media relationships. `PublicMediaResolver` checks the configured disk before producing URLs and returns `null` for missing physical files. Missing media does not crash listing or detail pages. Alt text uses exact-locale `MediaTranslation`; missing alt becomes `alt=""`. Wrong-locale media alt text is not shown.

## Language Switching

Listing pages switch between `/dao-tao` and `/en/training`. Detail pages use exact translated TrainingCourse slugs when the target locale translation exists. Missing target detail translation falls back to the target locale Training index.

## Public Inquiry Boundary

No public TrainingInquiry submission endpoint was added. CTAs point to `/#contact-preview` for Vietnamese and `/en#contact-preview` for English.

## Inner-Page Header

Training pages use the same Phase 10A inner-page solid header mode. Header visual geometry CSS, logo sizing, nav typography, nav gaps, mobile header, sticky behavior, and floating CTA styling were not changed.

## Responsive Source Review

Training listing uses one column on small screens, two columns at medium width, and three columns at extra-large width. Detail content stacks on small screens and uses a content/sidebar layout at large width. No browser automation was run by instruction.

## Accessibility

Each Training page has one `<h1>`, logical section headings, semantic `<article>` usage, keyboard-focusable links, and media alt text that is meaningful when authored or empty when absent.

## Route Boundary

Phase 10B added only Training public GET routes. Blog, About, Contact, Booking, Tracking, public TrainingInquiry POST, technical SEO, sitemap, JSON-LD, and OpenGraph routes remain deferred.

## Migrations

No migrations were added. No schema changes were made.

## Tests

Full PHPUnit evidence:

- Tests: 259
- Assertions: 1037
- Failures: 0
- Errors: 0
- Skipped: 0
- JUnit duration: 9.129246 seconds

Targeted Phase 10B public Training test was also run before full verification:

- Tests: 12
- Assertions: 84
- Failures: 0
- Errors: 0

## Pint

`vendor/bin/pint --test` passed with exit code 0.

## Vite

`cmd /c npm run build` passed with exit code 0. The existing optional `fontaine` fallback optimization warning appeared during build; it did not fail the build.

## Composer

Composer validate passed with exit code 0.

## Blade DB-Query Review

`resources/views/public/training/*.blade.php` was scanned for prohibited direct DB/Eloquent query patterns. No prohibited patterns were found.

## Git Diff

Phase 10B implementation is ready for commit after final machine verification. Human QA has been completed by the project owner. `git diff --check` passed with only the existing CRLF normalization warning for `AGENTS.md`; no whitespace errors were reported.

Dirty paths are limited to Phase 10B implementation, tests, architecture documentation, audit documentation, and machine evidence.

## Human QA

Training listing desktop:
HUMAN VISUALLY VERIFIED

Training detail desktop:
HUMAN VISUALLY VERIFIED

Training public workflow:
HUMAN QA COMPLETED BY PROJECT OWNER

VI -> EN exact translated detail slug:
HUMAN FUNCTIONALLY VERIFIED

EN -> VI exact translated detail slug:
HUMAN FUNCTIONALLY VERIFIED

Future-scheduled publication exclusion:
HUMAN FUNCTIONALLY VERIFIED

Future-scheduled direct detail 404:
HUMAN FUNCTIONALLY VERIFIED

## Production DB Status

NOT TESTED.

## Known Debt

- Phase 8 duplicate public layout legacy debt remains.
- Future header/logo upperscale remains deferred.
- Future optical nav spacing refinement remains deferred.
- Services header chevron review remains deferred.
- Real production Training photography/content remains data-dependent.

## Deferred Phase 10C+

Blog public pages, About, Contact, Booking, Tracking, SEO, and public Training inquiry submission remain deferred to later phases.
