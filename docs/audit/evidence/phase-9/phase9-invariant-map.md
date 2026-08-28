# Phase 9 Invariant Evidence Map (Final Closure)

| Invariant ID | Rule Description | Verifier / Evidence File | Status |
|---|---|---|---|
| **P9-ROUTE-01** | Canonical VI at `/`, EN at `/en`, `/vi` 301 redirect | `route-list.txt`, `HomepageTest.php` | RUNTIME-VERIFIED |
| **P9-CTRL-01** | HomeController thin coordinator delegating to HomepageContent | `HomeController.php`, `route-list.txt` | SOURCE-REVIEWED |
| **P9-LOCALE-01**| Exact requested locale required; VI content never falls back to EN | `HomepageTest::test_home_page_exact_locale_isolation_and_no_fallback_to_vi` | RUNTIME-VERIFIED |
| **P9-STATUS-01**| Only `PUBLISHED` content rendered; `DRAFT` and `ARCHIVED` excluded | `HomepageTest::test_draft_and_archived_home_page_records_are_ignored` | RUNTIME-VERIFIED |
| **P9-SCHED-01** | Future `published_at > now()` courses and posts excluded | `HomepageTest::test_training_course_scheduling_and_tuition_display`, `test_blog_post_scheduling_and_timezone_formatting` | RUNTIME-VERIFIED |
| **P9-LIMIT-01** | Curated limits: max 6 services, max 3 courses, max 3 posts | `HomepageContent.php`, `HomepageTest.php` | RUNTIME-VERIFIED |
| **P9-HERO-01**  | Hero visual uses `home` Page `page_media` `sort_order = 0` | `HomepageTest::test_page_media_hero_convention_selects_first_ordered_item` | RUNTIME-VERIFIED |
| **P9-CTA-01**   | Primary CTA targets `#featured-services` when services exist, `#wellness-philosophy` when empty | `HomepageTest::test_rendered_internal_fragment_links_always_resolve_across_representative_data_states` | RUNTIME-VERIFIED |
| **P9-CTA-02**   | Training CTA rendered conditionally only when courses exist | `HomepageTest::test_rendered_internal_fragment_links_always_resolve_across_representative_data_states` | RUNTIME-VERIFIED |
| **P9-CTA-03**   | Closing CTA is exploration-oriented; does not claim Booking exists | `home.blade.php`, `home.php` | SOURCE-REVIEWED |
| **P9-EXCERPT-01**| CMS excerpt rendered in Hero only; Editorial uses complementary copy | `HomepageTest::test_cms_home_page_narrative_excerpt_is_rendered_only_once_and_not_duplicated_in_editorial_section` | RUNTIME-VERIFIED |
| **P9-SEC-01**   | Plain-text excerpt decodes entities first, then strips script/style blocks and tags | `HomepageTest::test_rich_html_in_page_content_is_safely_rendered_as_plain_text` | RUNTIME-VERIFIED |
| **P9-CLAIM-01** | Unsupported structural fallback claims removed/neutralized | `lang/vi/home.php`, `lang/en/home.php` | SOURCE-REVIEWED |
| **P9-MEDIA-01** | Missing physical media file falls back gracefully without crash | `HomepageTest::test_missing_physical_media_file_gracefully_falls_back_without_crash` | RUNTIME-VERIFIED |
| **P9-EMPTY-01** | Homepage renders 200 OK on empty database with zero business records | `HomepageTest::test_empty_database_homepage_renders_cleanly_for_vi_and_en` | RUNTIME-VERIFIED |
| **P9-BOUND-01** | Rendered cards contain zero broken detail links to Phase 10 routes | `HomepageTest::test_rendered_cards_contain_zero_broken_detail_links`, `route-boundary-review.txt` | RUNTIME-VERIFIED |
| **P9-BLADE-01** | Public Blade templates execute zero direct Eloquent or DB queries | `blade-db-query-review.txt` | SOURCE-REVIEWED |
| **P9-TYPO-01**  | Headings enforce safe word-boundary wrapping (`break-words`) without mojibake | `PublicLayoutTest.php`, `Utf8IntegrityTest.php` | RUNTIME-VERIFIED |
| **P9-QA-01**    | Final responsive visual QA across 375px and 1440px viewports | Manual testing by project owner | HUMAN QA REQUIRED |
