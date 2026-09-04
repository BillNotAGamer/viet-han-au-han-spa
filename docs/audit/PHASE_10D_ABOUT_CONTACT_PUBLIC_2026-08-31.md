# Phase 10D About And Contact Public Audit

## Objective

Implement public About and Contact pages using the existing Pages CMS and public Site Settings architecture, without schema changes, packages, browser automation, Booking, public contact submission, tracking, technical SEO, or Phase 10E work.

## Starting Git State

- Starting HEAD: `0326220d5a605d5a6fa14707c5f4750ea939f761`
- Starting working tree: clean
- Phase 10A Services: CLOSED
- Phase 10B Training: CLOSED
- Phase 10C Blog: CLOSED

## Actual Page Schema Findings

The implementation consumes the existing Pages schema:

- `pages`: `key`, `status`, timestamps
- `page_translations`: `page_id`, `locale`, `title`, nullable `slug`, `content`, `seo_title`, `seo_description`
- `page_media`: `page_id`, `media_id`, `sort_order`, `created_at`

Pages have no `published_at` scheduling column. `page_translations.slug` is nullable editorial data and is not used for Phase 10D public routing.

## Actual SiteSetting Keys Reused

Phase 10D reuses established public-safe SiteSetting keys:

- `contact.phone`
- `contact.email`
- `contact.address`
- `business.hours`
- `social.facebook_url`
- `social.zalo_url`
- `social.youtube_url`

The Contact page does not dump all public settings and does not render unrelated settings automatically.

## Routes

Phase 10D adds:

- `GET /gioi-thieu` -> `vi.about`
- `GET /lien-he` -> `vi.contact`
- `GET /en/about` -> `en.about`
- `GET /en/contact` -> `en.contact`

Vietnamese static URLs remain prefixless. `/vi/gioi-thieu` and `/vi/lien-he` are not registered. No dynamic public Page slug route is added.

## Fixed Page Keys

About resolves fixed Page key `about`. Contact resolves fixed Page key `contact`. The public routes do not depend on `PageTranslation.slug`, and persisted arbitrary slugs do not alter canonical static URLs.

## Page Eligibility

Public About and Contact visibility requires:

- required fixed `pages.key`
- `pages.status = PUBLISHED`
- exact requested-locale `page_translations` row

Missing Page records, `DRAFT`, `ARCHIVED`, and missing requested-locale translations return public `404`. No Page scheduling rule was invented.

## Controller And Read Service

Request flow:

`Route -> App\Http\Controllers\Public\StaticPageController -> App\Services\PublicSite\StaticPageContent -> Eloquent/domain -> Blade`

`StaticPageController` remains thin: it resolves the active locale, selects the fixed Page key, delegates composition, registers localized URLs, and returns the Blade view.

`StaticPageContent` owns Page lookup, exact-locale enforcement, publication filtering, safe rich-content preparation, ordered Page media preparation, localized route pairs, and Contact setting composition.

## About Behavior

The About page renders exact-locale Page title, safe plain-text Page content, the first resolved ordered Page media item as a lead editorial image when available, remaining resolved media as a simple gallery, and a safe CTA to Services. It does not fabricate business claims, awards, metrics, credentials, founding dates, guarantees, or staff data.

## Contact Behavior

The Contact page renders exact-locale Page title/content, optional Page media, and only explicitly allow-listed public operational settings. It renders gracefully when no public contact settings exist and does not fabricate phone, email, address, or hours.

## Contact Public-Setting Allow-List

Contact operational details are read one key at a time through `SiteSettings::getPublic(...)`. Private settings cannot pass that API. Unrelated public settings are not requested or dumped.

## Private Setting Protection

The Contact page never calls `SiteSettings::publicSettings()` and never iterates over every public setting. Private or secret-like settings are not rendered.

## Unsafe URL Protection

Contact links are validated before becoming active links:

- email must pass `FILTER_VALIDATE_EMAIL`
- phone is normalized to `+` and digits and must match a conservative numeric pattern
- social/external URLs must use `http` or `https`

Unsafe schemes such as `javascript:`, `data:`, and `file:` are omitted.

## Rich Content Strategy

`PageTranslation.content` is RichEditor-authored long text. Phase 10D uses the accepted launch-safe fallback: remove script-like blocks, strip tags, normalize whitespace, and render escaped plain-text paragraphs. No raw `{!! $content !!}` output or sanitizer package was added.

## Page Media

Page media is resolved through `PublicMediaResolver` before Blade rendering. Ordered `page_media.sort_order` is preserved. Missing physical files are omitted safely. Exact requested-locale MediaTranslation alt/caption is used; missing exact-locale alt renders `alt=""`, and wrong-locale alt text does not leak.

## Language Switch

About and Contact use fixed route-pair language switching:

- `/gioi-thieu` -> `/en/about` when English About exists
- `/en/about` -> `/gioi-thieu` when Vietnamese About exists
- `/lien-he` -> `/en/contact` when English Contact exists
- `/en/contact` -> `/lien-he` when Vietnamese Contact exists

If the target locale translation is missing, the switch target falls back to the target-locale Homepage.

## Header And Footer Route Integration

Header links now point About and Contact to canonical Phase 10D routes:

- VI About: `/gioi-thieu`
- VI Contact: `/lien-he`
- EN About: `/en/about`
- EN Contact: `/en/contact`

Existing Services, Training, and Blog route targets remain intact. Footer quick-navigation links were updated to the same canonical routes. Header visual geometry was not changed.

## Responsive Source Review

About and Contact use the existing public layout, solid inner-page header mode, constrained public containers, responsive grid tracks, wrapping text, and responsive media aspect ratios. No browser automation was performed or claimed.

## Accessibility

Each page uses the shared single `main` landmark and one H1. Contact details use address/contact semantics where available, links are keyboard-operable, focus-visible states are present, and image alt text is meaningful or empty based on exact-locale media data.

## Mutation Boundary

Phase 10D adds no public POST, PUT, PATCH, or DELETE routes for About, Contact, Booking, lead submission, or email dispatch. Contact is informational/navigation only.

## Route Boundary

Allowed public content routes after Phase 10D are Homepage, Services, Training, Blog, About, and Contact GET routes only. Booking, tracking, technical SEO, public contact submission, and Phase 10E routes remain out of scope.

## Migrations And Packages

- New migrations: none
- Schema changes: none
- Composer packages added: none
- npm packages added: none

## Verification

- PHPUnit: 284 tests, 1203 assertions, 0 failures, 0 errors, 0 skipped
- Risky state: NOT VERIFIED FROM CURRENT JUNIT EVIDENCE
- Pint: PASS
- Vite production build: PASS with existing optional `fontaine` warning
- Composer validate: PASS
- `git diff --check`: PASS; CRLF normalization warning recorded for `AGENTS.md`
- Blade DB-query review: PASS
- SiteSettings public-safety review: PASS
- Rich-content safety review: PASS

## Git Scope

Phase 10D dirty scope is limited to About/Contact implementation, tests, architecture documentation, audit report, and machine evidence. No local SQLite database or runtime QA data is included.

## Implementation Commit

Implementation commit:
`dfb94d596574ac03f9dd56e3fdc0ad2b6213466c`

Implementation commit message:
`feat: add public about and contact pages for phase 10d`

Owner browser verification:
About VI/EN desktop PASS
Contact VI/EN desktop PASS

Owner authorized progression to Phase 10E:
YES

Implementation tree after commit:
CLEAN

## Production DB Status

NOT TESTED.

## Known Debt

- Duplicate legacy public layout remains deferred.
- Header/logo upperscale remains deferred.
- Optical nav spacing remains deferred.
- Services chevron refinement remains deferred.
- Full RichEditor HTML public renderer/sanitizer remains deferred.
- Real production About/Contact content and media remain data-dependent.

## Phase 10E Deferred

Booking, contact form submission, tracking, technical SEO, and any Phase 10E work remain out of scope.
