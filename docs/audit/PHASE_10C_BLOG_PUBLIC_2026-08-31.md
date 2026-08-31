# Phase 10C Blog Public Audit

## Objective

Implement public Blog listing and article detail pages using the existing Phase 6 Blog CMS domain, without schema changes, packages, browser automation, comments, search, newsletter, technical SEO, Booking, About, Contact, or Phase 10D work.

## Starting Git State

- Starting HEAD: `d09d1df1caa515dc2e89ac598381eae531aa79c7`
- Starting working tree: clean
- Phase 10A Services: CLOSED
- Phase 10B Training: CLOSED

## Routes

Phase 10C adds:

- `GET /blog` -> `vi.blog.index`
- `GET /blog/{slug}` -> `vi.blog.show`
- `GET /en/blog` -> `en.blog.index`
- `GET /en/blog/{slug}` -> `en.blog.show`

Vietnamese Blog URLs remain prefixless. `/vi/blog` is not registered. Category archives and search routes are not implemented.

## Controller And Read Service

Request flow:

`Route -> App\Http\Controllers\Public\BlogController -> App\Services\PublicSite\BlogContent -> Eloquent/domain -> Blade`

`BlogController` remains thin: it resolves locale through middleware state, delegates listing/detail composition to `BlogContent`, registers localized switch URLs, and returns Blade views.

`BlogContent` owns listing queries, detail lookup, publication eligibility, exact-locale filtering, category presentation, author presentation, media preparation, localized URLs, and safe content preparation.

## Actual Blog Schema And Domain Findings

The implementation consumes the existing schema:

- `posts`: `post_category_id`, `author_id`, `hero_media_id`, `status`, `is_featured`, `published_at`
- `post_translations`: `post_id`, `locale`, `title`, `slug`, `excerpt`, `content`, `seo_title`, `seo_description`
- `post_categories`: `status`, `sort_order`
- `post_category_translations`: `post_category_id`, `locale`, `name`, `slug`, `description`, `seo_title`, `seo_description`
- `post_media`: `post_id`, `media_id`, `sort_order`, `created_at`

`PostWriter` establishes publication lifecycle behavior: publishing with null `published_at` sets `now()`, future timestamps schedule publication, archived posts preserve timestamps, and restoring to draft clears `published_at`.

## Publication Eligibility

Public Blog visibility requires:

- `posts.status = PUBLISHED`
- `posts.published_at IS NOT NULL`
- `posts.published_at <= now()`
- exact requested-locale `post_translations` row

`DRAFT`, `ARCHIVED`, future-scheduled `PUBLISHED`, and missing requested-locale posts are excluded from listing and return `404` on direct detail requests.

## Published At Semantics

`published_at` is a UTC publication lifecycle and scheduling timestamp. It is used for public eligibility, display date, and listing order. It is not a booking date, service date, class date, or generic content update date.

## Listing

The listing pages return HTTP 200 for empty data and render localized empty states without fake articles. Eligible posts are paginated at 12 per page in the database and ordered by `published_at DESC, id DESC`.

Prepared listing data includes exact-locale title, excerpt or plain-text content excerpt, optional exact-locale category name, safe author name, publication date, resolved hero media, and current-locale detail URL.

## Detail

Detail lookup uses exact requested locale plus exact requested slug through `post_translations`. Cross-locale slugs return `404`.

Detail pages render a media-led article header, category metadata when exact locale exists, one H1, publication date, safe author name when present, escaped plain-text article body, ordered gallery media, contact CTA, and a Blog index link.

## Exact Locale And Slug Policy

Vietnamese content never falls back under `/en/blog`. English Blog detail requires the exact English translation slug. Vietnamese Blog detail requires the exact Vietnamese translation slug. No slug text is translated programmatically.

## Category

Category is public metadata only in Phase 10C. It renders only when the category is `PUBLISHED` and has an exact requested-locale translation. Missing requested-locale category labels are omitted without hiding an otherwise valid post.

## Author Privacy

Public output may render only `users.name`. The implementation does not expose author email, user ID, admin flag, password hash, remember token, or authentication metadata.

## Rich Content Safety

`post_translations.content` is stored as rich text in a `longText` column authored by Filament RichEditor. No verified project-level sanitizer or public RichEditor renderer exists. Phase 10C therefore uses an escaped/plain-text fallback: script-like blocks are removed, tags are stripped, whitespace is normalized, and Blade renders escaped paragraphs. SEO fields are not rendered as visible article content.

## Media And Gallery

Hero media uses `posts.hero_media_id`. Gallery media uses `post_media` ordered by `sort_order ASC`. `BlogContent` resolves media through `PublicMediaResolver` before rendering. Missing physical media does not crash public pages. Media alt and captions use exact requested-locale `media_translations`; wrong-locale alt text does not leak.

## Language Switching

Listing switches between `/blog` and `/en/blog`.

Detail switching is entity-based:

- VI detail -> exact EN detail slug when the EN translation exists
- EN detail -> exact VI detail slug when the VI translation exists
- Missing target translation -> target-locale Blog index fallback

## Header Blog Route Update

The shared header now points Blog to:

- VI: `/blog`
- EN: `/en/blog`

Services remain `/dich-vu` and `/en/services`. Training remains `/dao-tao` and `/en/training`. Header visual geometry was not changed.

## Human QA

Blog listing VI:
HUMAN FUNCTIONALLY AND VISUALLY VERIFIED

Blog listing EN:
HUMAN FUNCTIONALLY AND VISUALLY VERIFIED

Blog detail VI:
HUMAN FUNCTIONALLY AND VISUALLY VERIFIED

Blog detail EN:
HUMAN FUNCTIONALLY AND VISUALLY VERIFIED

VI -> EN exact translated slug:
HUMAN FUNCTIONALLY VERIFIED

EN -> VI exact translated slug:
HUMAN FUNCTIONALLY VERIFIED

Cross-locale slug 404:
HUMAN FUNCTIONALLY VERIFIED

Future-scheduled listing exclusion:
HUMAN FUNCTIONALLY VERIFIED

Future-scheduled detail 404:
HUMAN FUNCTIONALLY VERIFIED

Mobile listing/detail:
HUMAN VISUALLY VERIFIED

Human QA:
COMPLETED BY PROJECT OWNER

## Responsive Source Review

The Blog views use the existing public layout, solid inner-page header mode, constrained public containers, responsive grid tracks, wrapping text, and responsive media aspect ratios. No browser automation was performed or claimed.

## Accessibility

Blog index and detail preserve a single main landmark via the shared layout. Each page has one H1, semantic article markup where applicable, time elements for publication dates, keyboard-operable links, focus-visible states, and meaningful or empty alt values from resolved media.

## Route Boundary

Allowed public content routes after Phase 10C are Homepage, Services, Training, and Blog GET routes only. No About, Contact, Booking, comments, search, newsletter, mutation, tracking, or technical SEO routes were added.

## Migrations

New migrations: none.

Schema changes: none.

## Verification

- PHPUnit: 272 tests, 1129 assertions, 0 failures, 0 errors, 0 skipped
- Risky state: NOT VERIFIED FROM CURRENT JUNIT EVIDENCE
- Pint: PASS
- Vite production build: PASS with existing optional `fontaine` warning
- Composer validate: PASS
- `git diff --check`: PASS; CRLF normalization warning recorded for `AGENTS.md`
- Blade DB-query review: PASS
- Rich-content safety review: PASS

Phase 10C implementation is ready for commit after final machine verification.

## Evidence

Evidence directory:

`docs/audit/evidence/phase-10c/`

Includes PHPUnit JUnit XML, console test output, Pint, Vite, Composer validate, route list, Git diff/status evidence, Blade query review, rich-content safety review, and invariant map.

## Production DB Status

NOT TESTED.

## Known Debt

- Duplicate legacy public layout debt remains from earlier phases.
- Future logo/header upperscale remains deferred.
- Future optical nav spacing refinement remains deferred.
- Services chevron review remains deferred.
- Real production Blog photography/content remains data-dependent.
- Full rich HTML rendering requires a future verified sanitizer or approved RichEditor public rendering policy.

## Phase 10D Deferred

About, Contact, Booking, comments, search, newsletter, tracking, and technical SEO remain out of scope.
