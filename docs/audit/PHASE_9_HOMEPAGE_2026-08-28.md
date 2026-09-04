# PHASE 9 AUDIT REPORT: PUBLIC HOMEPAGE (FINAL REVIEW CLOSURE)

**Date**: 2026-08-28
**Repository**: `F:\Coding\Web development\Viet Han Spa`
**Project**: Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)
**Phase**: Phase 9 (Public Homepage Closure)
**Status**: `READY FOR PHASE 9 FINAL HUMAN QA`

---

## A. Starting Git State & Governance Disclosure
- **Phase 8 Accepted Baseline**: `a5c1ba9caad195c9e22fe5af724017e5f7872d88` (`chore: establish verified phase 8 project baseline`).
- **Initial Phase 9 Commit**: `fa0c372d3ce2f2b2408dd0608f876b51ccd3f322` (`feat: implement phase 9 homepage`).
- **Cross-Phase Evidence Housekeeping Disclosure**:
  Phase 9 application implementation was strictly Phase 9 scoped. The original Phase 9 commit (`fa0c372`) also contained one cross-phase Phase 8 evidence housekeeping update: `docs/audit/evidence/phase-8.zip`. This discrepancy occurred because the working tree held post-QA repackaged 222-test artifacts. Execution proceeded under an explicit housekeeping exception.

---

## B. Evidence Protocol
All findings in this report are substantiated by machine-derived artifacts in `docs/audit/evidence/phase-9/`:
- `verification.json`: Execution manifest of all automated checks.
- `phpunit.xml`: JUnit test execution log (235 tests, 842 assertions, 0 failures, 0 errors, 0 skipped).
- `tests.txt`: Machine summary of PHPUnit execution (`duration_ms: 7786`).
- `pint.txt`: Code style evaluation (`passed`).
- `npm-build.txt`: Production asset bundling log.
- `composer-validate.txt`: Package manifest validation (`./composer.json is valid`).
- `migrate-fresh.txt` & `migrate-refresh.txt`: Migration lifecycle verification.
- `route-list.txt`: Route table inventory.
- `blade-db-query-review.txt`: Static analysis of Blade templates for direct database/Eloquent queries (0 matches).
- `route-boundary-review.txt`: Route boundary validation (0 unapproved routes).

---

## C. Homepage Route & Controller
- Canonical Vietnamese Homepage: `/` (`vi.home`).
- Secondary Canonical English Homepage: `/en` (`en.home`).
- Canonical Redirect: `/vi` returns 301 Permanent Redirect to `/`.
- Controller: `App\Http\Controllers\Public\HomeController` coordinates the request, delegating entirely to `HomepageContent` and rendering `resources/views/public/home.blade.php`.
- **Classification**: `RUNTIME-VERIFIED` / `SOURCE-REVIEWED`.

---

## D. Homepage Composition Service
- Service: `App\Services\PublicSite\HomepageContent`.
- Encapsulates all query filters, ordering, bounds, and presentation data normalization.
- Enforces limits: max 6 services, max 3 training courses, max 3 blog posts.
- Eliminates N+1 query overhead through eager loading.
- **Classification**: `RUNTIME-VERIFIED`.

---

## E. Exact-Locale Policy
- Strict enforcement of ADR-011 and ADR-012:
  - English Homepage (`/en`) strictly queries records with an exact `en` translation.
  - Vietnamese Homepage (`/`) strictly queries records with an exact `vi` translation.
  - Zero fallback from English to Vietnamese for business content. If a service or post is translated only in Vietnamese, it is completely absent from `/en`.
- Category badges are hidden if the category itself lacks a translation in the active locale.
- **Classification**: `RUNTIME-VERIFIED`.

---

## F. Publication Filtering
- All queries strictly enforce `status = ContentStatus::PUBLISHED`.
- `DRAFT` and `ARCHIVED` records are completely excluded from the public Homepage.
- Training courses and posts enforce non-null `published_at <= now()`.
- **Classification**: `RUNTIME-VERIFIED`.

---

## G. Home Page Usage & Non-Duplication
- The `home` Page record (`key = 'home'`) provides optional editorial title and narrative.
- **Non-Duplication Enforced**: The CMS `home` Page excerpt is rendered in the Hero section only (`$page['excerpt']`).
- The Editorial section (`id="wellness-philosophy"`) renders complementary localized structural copy (`__('home.editorial.content')`).
- Proven by regression test `test_cms_home_page_narrative_excerpt_is_rendered_only_once_and_not_duplicated_in_editorial_section`.
- **Classification**: `RUNTIME-VERIFIED`.

---

## H. Safe Plain-Text Excerpt Derivation
- Scope: **Homepage plain-text excerpt derivation** (not a general-purpose HTML sanitizer).
- `HomepageContent::deriveSafeExcerpt` transformation pipeline:
  1. Decode HTML entities (`html_entity_decode`) first to expose entity-encoded blocks.
  2. Remove complete non-content element blocks and contents case-insensitively and multiline (`script`, `style`, `iframe`, `object`, `embed`, `noscript`).
  3. Strip remaining HTML tags (`strip_tags`).
  4. Normalize whitespace and trim.
  5. Truncate safely with ellipsis using multibyte functions.
- Verified to eliminate both literal and entity-encoded script/style/iframe payloads (`alert(`, `display:none`) while preserving editorial sentences. Normal Blade escaping (`{{ ... }}`) is strictly maintained in views.
- **Classification**: `RUNTIME-VERIFIED`.

---

## I. Hero Section & CTA Target Integrity
- Hero primary CTA is presentation-aware:
  - When `featuredServices` is non-empty: targets `#featured-services` with label `home.hero.primary_cta` ("Khám phá dịch vụ" / "Explore Services").
  - When `featuredServices` is empty: targets permanent `#wellness-philosophy` with label `home.hero.primary_cta_empty` ("Tìm hiểu thêm" / "Our Philosophy").
- Hero secondary CTA (Training):
  - Rendered conditionally **only** when `featuredCourses` is non-empty.
  - When training courses are absent, the button is omitted rather than pointing to a nonexistent anchor.
- Hero Media: First ordered `page_media` item (`sort_order = 0`) attached to published `home` Page acts as hero visual. Missing media falls back to an elegant brand monogram CSS visual.
- **Classification**: `RUNTIME-VERIFIED`.

---

## J. Featured Services Section
- Anchor: `id="featured-services"`.
- Displays up to 6 published, featured services with exact-locale translation.
- Semantic `<article>` preview cards without broken detail links (honoring Phase 10 boundary).
- Section is cleanly omitted if zero eligible services exist.
- **Classification**: `RUNTIME-VERIFIED`.

---

## K. Featured Training Section
- Anchor: `id="training"`.
- Displays up to 3 published, featured training courses with exact-locale translation and `published_at <= now()`.
- Displays duration and tuition fee formatted in integer VND (`15.000.000 ₫`).
- Section is cleanly omitted if zero eligible courses exist.
- **Classification**: `RUNTIME-VERIFIED`.

---

## L. Latest Blog / Journal Section
- Anchor: `id="journal"`.
- Displays up to 3 published posts with exact-locale translation and `published_at <= now()`.
- Displays category, localized date in `Asia/Ho_Chi_Minh` timezone (`d/m/Y`), title, and excerpt.
- Section is cleanly omitted if zero eligible posts exist.
- **Classification**: `RUNTIME-VERIFIED`.

---

## M. Closing CTA Section Semantics
- Presentation-aware closing CTA:
  - When `featuredServices` is non-empty: targets `#featured-services` with label `home.cta.button` ("Khám phá dịch vụ" / "Explore Treatments").
  - When `featuredServices` is empty: targets `#wellness-philosophy` with label `home.cta.button_empty` ("Tìm hiểu thêm" / "Discover More").
- Misleading booking reservation labels ("Đặt lịch hẹn ngay" / "Reserve Treatment") completely removed, as Phase 11 owns booking.
- **Classification**: `RUNTIME-VERIFIED` / `SOURCE-REVIEWED`.

---

## N. Claim-Safe Fallback Copy
- All unverified claims in `lang/vi/home.php` and `lang/en/home.php` have been neutralized:
  - Removed: "sản phẩm thảo mộc organic cao cấp" / "premium organic formulations"
  - Removed: "cam kết tay nghề vững vàng"
  - Removed: "recognized standards"
  - Removed: "career mentorship"
- Replaced with qualitative, non-verifiable marketing copy ("không gian thư giãn", "chăm sóc chỉn chu", "học tập và thực hành nghề spa", "nurture relaxation and holistic well-being").
- **Classification**: `SOURCE-REVIEWED`.

---

## O. Empty-Data Behavior & Internal Fragment Integrity
- Full rendering verified against empty database: `/` and `/en` return 200 OK.
- **Internal Fragment Link Integrity**:
  - Tested across 4 representative data states (empty database; services present & training absent; training present & services absent; both present) on `/` and `/en`.
  - Every rendered internal fragment `href="#..."` resolves to an existing element `id="..."` in the document.
- **Classification**: `RUNTIME-VERIFIED` (`HomepageTest::test_rendered_internal_fragment_links_always_resolve_across_representative_data_states`).

---

## P. Public Media Handling
- Managed by `App\Services\PublicSite\PublicMediaResolver`.
- Employs Laravel Filesystem abstraction (`Storage::disk(...)`).
- Defensively catches missing physical storage files and gracefully falls back without throwing exceptions.
- **Classification**: `RUNTIME-VERIFIED`.

---

## Q. Accessibility
- Single `<h1>` per page, logical `<h2>`/`<h3>` hierarchy.
- Descriptive `alt` attributes on media or `alt=""` for decorative fallbacks.
- Accessible skip link (`#main-content`).
- Focus rings, legible contrast, and touch-target sizing on all interactive buttons and navigation links.
- **Classification**: `SOURCE-REVIEWED`.

---

## R. Browser Verification Status
- **Final Automated Browser Capture**: `NOT EXECUTED`
  - *Reason*: Headless Chrome probe workflow was canceled after hanging; browser automation was intentionally excluded from Phase 9F.2 to ensure stability.
  - Earlier pre-closure automated captures were removed from the final evidence set to prevent false claims of matching current source.
- **Human Browser QA**: `REQUIRED`
  - The project owner will perform manual visual QA at responsive viewports (`375px` and `1440px`) on `/` and `/en`.
- **Classification**: `HUMAN QA REQUIRED`.

---

## S. No-Detail-Route Boundary
- Rendered service, training, and blog cards contain zero links to nonexistent Phase 10 routes (`/dich-vu/{slug}`, `/dao-tao-hoc-vien/{slug}`, `/blog/{slug}`).
- Card preview structures do not direct visitors to 404 pages.
- **Classification**: `RUNTIME-VERIFIED`.

---

## T. No-Fake-Data Review
- Zero fabricated testimonials, Google reviews, customer statistics, or medical guarantees.
- Zero fake contact numbers introduced.
- **Classification**: `SOURCE-REVIEWED`.

---

## U. No-DB-Query-in-Blade Review
- Static code analysis across all Blade files under `resources/views/` returned 0 matches for direct database or Eloquent calls (`blade-db-query-review.txt`).
- **Classification**: `SOURCE-REVIEWED`.

---

## V. Database Changes
- Migrations added in Phase 9: **0**.
- Schema mutations: **None**.
- **Classification**: `RUNTIME-VERIFIED`.

---

## W. Business Table Count
- Exact total: **23 business tables** (unchanged since Phase 1B).
- **Classification**: `RUNTIME-VERIFIED`.

---

## X. Tests Added
13 feature tests verified in `tests/Feature/Public/HomepageTest.php`:
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
12. `test_rendered_internal_fragment_links_always_resolve_across_representative_data_states`
13. `test_cms_home_page_narrative_excerpt_is_rendered_only_once_and_not_duplicated_in_editorial_section`
- **Classification**: `RUNTIME-VERIFIED`.

---

## Y. Machine Test Metrics (Fresh Full Suite)
- **Tests**: **235** (increased from 222 in Phase 8).
- **Assertions**: **842** (increased from 733 in Phase 8).
- **Failures**: **0**.
- **Errors**: **0**.
- **Skipped**: **0**.
- **Duration**: **7.79s**.
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
- Route locale switching, prefix resolution, and UTF-8 diacritic stability pass 100% of tests.
- **Classification**: `RUNTIME-VERIFIED`.

---

## AC. Route Boundary
- Registered public routes strictly limited to `/`, `/en`, and `/vi` (redirect).
- Zero premature Phase 10 public content routes registered (`route-boundary-review.txt`).
- **Classification**: `SOURCE-REVIEWED`.

---

## AD. Pint, Build, and Composer Results
- **Laravel Pint**: `passed` (0 code style violations; evidence: `pint.txt`).
- **Vite Build**: `passed` (production bundle clean; evidence: `npm-build.txt`).
- **Composer Validation**: `passed` (`./composer.json is valid`; evidence: `composer-validate.txt`).
- **Classification**: `RUNTIME-VERIFIED`.

---

## AE. Final Status
All Phase 9F closure requirements, CTA integrity fixes, safe excerpt transformations, test suites, and machine checks are fully verified.

```text
READY FOR PHASE 9 FINAL HUMAN QA
```
