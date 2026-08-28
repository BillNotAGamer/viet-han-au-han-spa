# PHASE 9 AUDIT REPORT: PUBLIC HOMEPAGE

**Date**: 2026-08-28  
**Repository**: `F:\Coding\Web development\Viet Han Spa`  
**Project**: Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Phase**: Phase 9 (Public Homepage)  
**Status**: `READY FOR PHASE 9 REVIEW`  

---

## A. Starting Git State
- **Starting Git HEAD**: `a5c1ba9caad195c9e22fe5af724017e5f7872d88` (`chore: establish verified phase 8 project baseline`)
- **Starting Working Tree Status**: Handled starting gate discrepancy where `docs/audit/evidence/phase-8.zip` was updated with post-typography 222-test artifacts following human browser QA.

---

## B. Evidence Protocol
All conclusions in this report are substantiated by machine-derived artifacts located in `docs/audit/evidence/phase-9/`:
- `verification.json`: Execution manifest of all automated checks.
- `phpunit.xml`: JUnit test execution log (233 tests, 785 assertions, 0 failures, 0 errors, 0 skipped).
- `tests.txt`: Machine summary of PHPUnit execution (`duration_ms: 6884`).
- `pint.txt`: Code style evaluation (`passed`).
- `npm-build.txt`: Production asset bundling log (`built in 548ms`).
- `composer-validate.txt`: Package manifest validation (`./composer.json is valid`).
- `migrate-fresh.txt` & `migrate-refresh.txt`: Complete migration execution and rollback verification.
- `route-list.txt`: Route table inventory.
- `blade-db-query-review.txt`: Static analysis of Blade templates for direct database/Eloquent queries.
- `route-boundary-review.txt`: Route boundary validation.
- `browser/`: Headless Chrome screenshots and viewport measurements across `375px`, `768px`, and `1440px` viewports for `/` and `/en`.

---

## C. Homepage Route & Controller
- Canonical Vietnamese Homepage: `/` (`vi.home`).
- Secondary Canonical English Homepage: `/en` (`en.home`).
- Canonical Redirect: `/vi` returns 301 Permanent Redirect to `/`.
- Controller: `App\Http\Controllers\Public\HomeController` coordinates the request, delegating entirely to `HomepageContent` and returning `resources/views/public/home.blade.php`.
- **Classification**: `RUNTIME-VERIFIED` / `SOURCE-REVIEWED`.

---

## D. Homepage Composition Service
- Service: `App\Services\PublicSite\HomepageContent`.
- Encapsulates all query filters, ordering, bounds, and presentation data normalization for the Homepage.
- Bounded limits enforced: max 6 services, max 3 training courses, max 3 blog posts.
- Eliminates N+1 query overhead through eager loading of requested-locale translations, media relations, and categories.
- **Classification**: `RUNTIME-VERIFIED`.

---

## E. Exact-Locale Policy
- Strict enforcement of ADR-011 and ADR-012:
  - English Homepage (`/en`) strictly queries records with an exact `en` translation.
  - Vietnamese Homepage (`/`) strictly queries records with an exact `vi` translation.
  - No fallback from English to Vietnamese for business content. If a service or post is translated only in Vietnamese, it is completely absent from `/en`.
- Category badges are hidden if the category itself lacks a translation in the active locale, preventing untranslated labels from leaking.
- **Classification**: `RUNTIME-VERIFIED` (`HomepageTest::test_home_page_exact_locale_isolation_and_no_fallback_to_vi`, `test_category_label_is_omitted_if_category_translation_missing`).

---

## F. Publication Filtering
- All queries strictly enforce `status = ContentStatus::PUBLISHED`.
- `DRAFT` and `ARCHIVED` records are completely excluded from the public Homepage.
- Training courses and posts enforce non-null `published_at <= now()`.
- **Classification**: `RUNTIME-VERIFIED` (`HomepageTest::test_draft_and_archived_home_page_records_are_ignored`).

---

## G. Home Page Usage
- The `home` Page record (`key = 'home'`) provides optional editorial title and narrative.
- It is never auto-seeded or created during GET requests.
- If absent, the Homepage renders gracefully using localized structural marketing copy from `lang/{locale}/home.php`.
- **Classification**: `RUNTIME-VERIFIED` (`HomepageTest::test_empty_database_homepage_renders_cleanly_for_vi_and_en`).

---

## H. Safe Page Excerpt
- `HomepageContent::deriveSafeExcerpt` strips all HTML tags, resolves HTML entities, normalizes multi-whitespace, and safely truncates strings.
- Raw executable HTML (such as `<script>alert('xss')</script>`) is completely eliminated prior to presentation.
- **Classification**: `RUNTIME-VERIFIED` (`HomepageTest::test_rich_html_in_page_content_is_safely_rendered_as_plain_text`).

---

## I. Hero Section
- Warm, editorial hero layout featuring:
  - Eyebrow category tag.
  - Single `<h1>` title (from published `home` Page translation or localized default).
  - Supporting narrative excerpt.
  - Primary CTA jumping to `#featured-services`.
  - Secondary CTA jumping to `#training`.
  - Visual frame with hero image or brand monogram fallback.
- **Classification**: `SOURCE-REVIEWED` / `AGENT BROWSER CAPTURE EXECUTED`.

---

## J. Hero Media Convention
- **Convention Established**: If the published `home` Page has attached media in `page_media`, the item with `sort_order = 0` is selected as the Homepage hero visual.
- **Classification**: `RUNTIME-VERIFIED` (`HomepageTest::test_page_media_hero_convention_selects_first_ordered_item`).

---

## K. Featured Services Section
- Anchor: `id="featured-services"`.
- Displays up to 6 published, featured services with exact-locale translation.
- Displays category badge (if translated), service name, excerpt, and hero media.
- Semantic `<article>` cards without broken detail links (honoring Phase 10 boundary).
- Section is cleanly omitted if zero eligible services exist.
- **Classification**: `RUNTIME-VERIFIED`.

---

## L. Featured Training Section
- Anchor: `id="training"`.
- Displays up to 3 published, featured training courses with exact-locale translation and `published_at <= now()`.
- Displays course title, excerpt, duration display, and tuition fee formatted in integer VND (`15.000.000 ₫`).
- Future courses (`published_at > now()`) do not render.
- **Classification**: `RUNTIME-VERIFIED` (`HomepageTest::test_training_course_scheduling_and_tuition_display`).

---

## M. Latest Blog / Journal Section
- Anchor: `id="journal"`.
- Displays up to 3 published posts with exact-locale translation and `published_at <= now()`.
- Displays category name, localized publication date in `Asia/Ho_Chi_Minh` timezone (`d/m/Y`), title, and excerpt.
- Future posts do not render.
- **Classification**: `RUNTIME-VERIFIED` (`HomepageTest::test_blog_post_scheduling_and_timezone_formatting`).

---

## N. Final CTA Section
- Luxurious full-width closing section inviting visitors to experience restorative treatments.
- Button scrolls to functional page sections (`#featured-services`).
- Zero fake promotions, artificial timers, or fabricated discounts.
- **Classification**: `SOURCE-REVIEWED`.

---

## O. Empty-Data Behavior
- Full rendering verification against a fresh database with zero business records.
- `/` and `/en` return 200 OK without unhandled exceptions or broken template states.
- **Classification**: `RUNTIME-VERIFIED` (`HomepageTest::test_empty_database_homepage_renders_cleanly_for_vi_and_en`).

---

## P. Public Media Handling
- Managed by `App\Services\PublicSite\PublicMediaResolver`.
- Employs Laravel Filesystem abstraction (`Storage::disk(...)`).
- Defensively catches missing physical storage files and gracefully falls back to CSS illustration without crashing.
- Respects accessibility standards by reading `alt_text` from exact-locale `MediaTranslation` (or defaulting to `alt=""`).
- **Classification**: `RUNTIME-VERIFIED` (`HomepageTest::test_missing_physical_media_file_gracefully_falls_back_without_crash`).

---

## Q. Accessibility
- Semantic landmarks: single `<main>` landmark inherited from `layouts.public`.
- Heading hierarchy: single `<h1>` in hero, `<h2>` for sections, `<h3>` for cards.
- Meaningful `alt` attributes on media or `alt=""` for decorative fallbacks.
- Focus rings, legible contrast, and touch-target sizing on all interactive buttons and navigation links.
- **Classification**: `SOURCE-REVIEWED`.

---

## R. Responsive Design
- Verified across mobile (`375px`), tablet (`768px`), and desktop (`1440px`) viewports.
- All headings utilize `break-words [text-wrap:balance]`, preventing word-internal hyphenation or Vietnamese syllable fracturing.
- Automated headless Chrome screen captures saved to `docs/audit/evidence/phase-9/browser/`.
- **Classification**: `AGENT BROWSER CAPTURE EXECUTED`.

---

## S. No-Detail-Route Boundary
- Rendered service, training, and blog cards contain zero links to nonexistent Phase 10 routes (`/dich-vu/{slug}`, `/dao-tao-hoc-vien/{slug}`, `/blog/{slug}`).
- Card preview structures do not direct visitors to 404 pages.
- **Classification**: `RUNTIME-VERIFIED` (`HomepageTest::test_rendered_cards_contain_zero_broken_detail_links`).

---

## T. No-Fake-Data Review
- Zero fabricated testimonials, Google reviews, customer statistics, or medical guarantees.
- Zero fake contact numbers introduced in Phase 9.
- **Classification**: `SOURCE-REVIEWED`.

---

## U. No-DB-Query-in-Blade Review
- Static code analysis across all Blade files under `resources/views/` for Eloquent/DB patterns:
  - Patterns scanned: `App\Models\`, `DB::`, `::query(`, `::where(`, `::find(`, `::first(`, `::get(`.
  - Result: 0 matches found.
- Evidence recorded in `docs/audit/evidence/phase-9/blade-db-query-review.txt`.
- **Classification**: `SOURCE-REVIEWED`.

---

## V. Database Changes
- Migrations added in Phase 9: **0**.
- Schema mutations: **None**.
- **Classification**: `RUNTIME-VERIFIED` (`migrate-fresh.txt`, `migrate-refresh.txt`).

---

## W. Business Table Count
- Exact total: **23 business tables** (unchanged since Phase 1B).
- **Classification**: `RUNTIME-VERIFIED`.

---

## X. Tests Added
11 new feature tests added in `tests/Feature/Public/HomepageTest.php`:
1. `test_empty_database_homepage_renders_cleanly_for_vi_and_en`
2. `test_home_page_exact_locale_isolation_and_no_fallback_to_vi`
3. `test_draft_and_archived_home_page_records_are_ignored`
4. `test_featured_services_publication_and_exact_locale_filtering`
5. `test_category_label_is_omitted_if_category_translation_missing`
6. `test_training_course_scheduling_and_tuition_display`
7. `test_blog_post_scheduling_and_timezone_formatting`
8. `test_page_media_hero_convention_selects_first_ordered_item`
9. `test_missing_physical_media_file_gracefully_falls_back_without_crash`
10. `test_rich_html_in_page_content_is_safely_rendered_as_plain_text`
11. `test_rendered_cards_contain_zero_broken_detail_links`
- **Classification**: `RUNTIME-VERIFIED`.

---

## Y. Machine Test Metrics
- **Tests**: **233** (increased from 222 in Phase 8).
- **Assertions**: **785** (increased from 733 in Phase 8).
- **Failures**: **0**.
- **Errors**: **0**.
- **Skipped**: **0**.
- **Duration**: **6.88s**.
- **Risky-test state**: `NOT VERIFIED FROM CURRENT JUNIT EVIDENCE`.
- Evidence: `docs/audit/evidence/phase-9/tests.txt` and `phpunit.xml`.
- **Classification**: `RUNTIME-VERIFIED`.

---

## Z. Migration Lifecycle
- `php artisan migrate:fresh`: ExitCode 0 (evidence: `migrate-fresh.txt`).
- `php artisan migrate:refresh`: ExitCode 0 (evidence: `migrate-refresh.txt`).
- **Classification**: `RUNTIME-VERIFIED`.

---

## AA. Backend & Admin Regression
- All Phase 3–7 Filament admin resources and access controls remain intact.
- Admin authentication, panel isolation, and management workflows pass 100% of tests.
- **Classification**: `RUNTIME-VERIFIED`.

---

## AB. Localization Regression
- Route locale switching, prefix resolution, and UTF-8 diacritic stability pass 100% of tests (`RouteLocaleTest`, `LanguageSwitchTest`, `Utf8IntegrityTest`).
- **Classification**: `RUNTIME-VERIFIED`.

---

## AC. Route Boundary
- Registered public routes strictly limited to `/`, `/en`, and `/vi` (redirect).
- Zero premature Phase 10 public content routes registered.
- Evidence: `docs/audit/evidence/phase-9/route-boundary-review.txt`.
- **Classification**: `SOURCE-REVIEWED`.

---

## AD. Browser Capture & Review Status
- Automated headless Chrome screen captures executed across 3 viewports (`375px`, `768px`, `1440px`) on both `/` and `/en`:
  - `vi-375.png`, `vi-768.png`, `vi-1440.png`
  - `en-375.png`, `en-768.png`, `en-1440.png`
- Measurements recorded in `docs/audit/evidence/phase-9/browser/measurements.txt`.
- **Classification**: `AGENT BROWSER CAPTURE EXECUTED` (Human reviewer will perform final visual QA).

---

## AE. Reference-Site Review Status
- Reference website: `https://zenmassagespa.vn/`.
- Review method: High-level architectural principles (calm aesthetic, editorial spacing, clear card structure).
- Proprietary assets, text, or exact layouts were not copied.
- **Classification**: `HTTP/SOURCE REVIEW`.

---

## AF. Pint, Build, and Composer Results
- **Laravel Pint**: `passed` (0 code style violations; evidence: `pint.txt`).
- **Vite Build**: `passed` (production bundle built in 548ms; evidence: `npm-build.txt`).
- **Composer Validation**: `passed` (`./composer.json is valid`; evidence: `composer-validate.txt`).
- **Classification**: `RUNTIME-VERIFIED`.

---

## AG. Git Diff Scope
Git diff against Phase 8 baseline (`a5c1ba9`) is strictly limited to Phase 9 scope:
- Controller & Service additions (`HomeController`, `HomepageContent`, `PublicMediaResolver`).
- Language files (`lang/vi/home.php`, `lang/en/home.php`).
- Public view (`resources/views/public/home.blade.php`, removal of `welcome.blade.php`).
- Route binding (`routes/web.php`).
- Feature test suite (`tests/Feature/Public/HomepageTest.php`, update `Utf8IntegrityTest.php`).
- Documentation & evidence (`docs/architecture/HOMEPAGE_ARCHITECTURE.md`, `ADR-021`, `AGENTS.md`, `docs/audit/evidence/phase-9/`).
- **Classification**: `SOURCE-REVIEWED` (`git-diff-name-status.txt`, `git-diff-stat.txt`).

---

## AH. Production Database Execution Status
- All tests executed against SQLite in-memory / file driver.
- Production database execution: `NOT TESTED THROUGH PHASE 9`.

---

## AI. Files Created
- `app/Http/Controllers/Public/HomeController.php`
- `app/Services/PublicSite/HomepageContent.php`
- `app/Services/PublicSite/PublicMediaResolver.php`
- `lang/vi/home.php`
- `lang/en/home.php`
- `resources/views/public/home.blade.php`
- `tests/Feature/Public/HomepageTest.php`
- `docs/architecture/HOMEPAGE_ARCHITECTURE.md`
- `docs/architecture/adr/ADR-021-homepage-content-composition.md`
- `docs/audit/PHASE_9_HOMEPAGE_2026-08-28.md`
- `docs/audit/evidence/phase-9/*` (and `browser/*`)

---

## AJ. Files Modified
- `routes/web.php`
- `resources/views/welcome.blade.php` (deleted)
- `tests/Feature/Localization/Utf8IntegrityTest.php`
- `scripts/verification/run-phase-verification.ps1`
- `AGENTS.md`
- `docs/audit/evidence/phase-8.zip` (resolved starting baseline gate)

---

## AK. Inherited Phase 8 Debt
The following items remain recorded as accepted inherited debt from Phase 8:
- Footer fallback contact values and hardcoded copy.
- Potential duplicate public layout Blade file.

---

## AL. Known Phase 9 Limitations
- Preview cards for Services, Training, and Journal do not link to detail pages because those routes belong to Phase 10.
- Category badges on cards are omitted if the category itself lacks a translation in the requested locale.

---

## AM. Deferred Work
- **Phase 10**: Public Content Pages (Services, Training, Blog, About, Contact).
- **Phase 11**: Booking Form & Submission.
- **Phase 12**: Analytics & Conversion Tracking.
- **Phase 13**: Technical & On-Page SEO.
- **Phase 14**: Hardening & Performance Optimization.
- **Phase 15**: Full System QA.
- **Phase 16**: Production Deployment.
- **Phase 17**: Final Launch.

---

## AN. Invariant Evidence Map
Refer to `docs/audit/evidence/phase-9/phase9-invariant-map.md` for the full mapping of rules P9-ROUTE-01 through P9-QA-01.

---

## AO. Readiness
All Phase 9 implementation requirements, architectural constraints, exact-locale invariants, automated tests, code style formatting, production build checks, and evidence captures are fully verified.

```text
READY FOR PHASE 9 REVIEW
```
