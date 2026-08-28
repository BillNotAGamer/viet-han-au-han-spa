# PHASE 8 INVARIANT EVIDENCE MAP

| Invariant ID | Requirement Description | Verification Method | Evidence Artifact | Status |
| :--- | :--- | :--- | :--- | :--- |
| **P8-LOC-01** | `GET /` resolves with `lang="vi"` and renders Vietnamese public shell | Automated Runtime Test | `tests.txt` (`PublicLayoutTest::test_vi_root_uses_vi_lang_and_renders_public_layout_markers`), `phpunit.xml` | **RUNTIME-VERIFIED** |
| **P8-LOC-02** | `GET /en` resolves with `lang="en"` and renders English public shell | Automated Runtime Test | `tests.txt` (`PublicLayoutTest::test_en_root_uses_en_lang_and_renders_public_layout_markers`), `phpunit.xml` | **RUNTIME-VERIFIED** |
| **P8-LOC-03** | `GET /vi` performs HTTP 301 redirect to `/` | Automated Runtime Test | `tests.txt` (`PublicLayoutTest::test_vi_prefix_redirects_301_to_root`), `phpunit.xml` | **RUNTIME-VERIFIED** |
| **P8-NAV-01** | Header navigation labels are fully localized for VI and EN | Automated Runtime Test | `tests.txt` (`PublicLayoutTest::test_header_navigation_labels_are_localized`), `phpunit.xml` | **RUNTIME-VERIFIED** |
| **P8-NAV-02** | Language switcher maps `/` to `/en` and `/en` to `/` without generating `/vi` | Automated Runtime Test | `tests.txt` (`PublicLayoutTest::test_language_switcher_targets_correct_urls_without_vi_prefix`), `phpunit.xml` | **RUNTIME-VERIFIED** |
| **P8-A11Y-01** | Skip link exists targeting `#main-content` with exactly one `<main>` landmark | Automated Runtime Test | `tests.txt` (`PublicLayoutTest::test_accessibility_markup_invariants`), `phpunit.xml` | **RUNTIME-VERIFIED** |
| **P8-A11Y-02** | Navigation landmarks and accessible mobile trigger button exist with `aria-expanded` | Automated Runtime Test | `tests.txt` (`PublicLayoutTest::test_accessibility_markup_invariants`), `phpunit.xml` | **RUNTIME-VERIFIED** |
| **P8-SET-01** | Public layout consumes Site Settings via `getPublic()`; private settings never leak | Automated Runtime Test | `tests.txt` (`PublicLayoutTest::test_site_settings_public_consumption_and_private_safety`), `phpunit.xml` | **RUNTIME-VERIFIED** |
| **P8-ZERO-01** | Public shell renders cleanly with zero business records in database | Automated Runtime Test | `tests.txt` (`PublicLayoutTest::test_rendering_succeeds_with_zero_business_records`), `phpunit.xml` | **RUNTIME-VERIFIED** |
| **P8-BLADE-DB-01** | Public Blade templates contain zero direct Eloquent/DB query calls | Static AST & Regex Scan | `blade-db-query-review.txt` (search across `resources/views/`) | **SOURCE-REVIEWED** |
| **P8-ROUTE-01** | No premature Phase 10 public content routes registered in route table | Static Route Inventory Scan | `route-boundary-review.txt` (inspection of `route-list.txt`) | **SOURCE-REVIEWED** |
| **P8-DB-01** | Zero new migrations added in Phase 8; business table count remains 23 | Database Migration Lifecycle | `migrate-fresh.txt`, `migrate-refresh.txt` | **RUNTIME-VERIFIED** |
| **P8-STYLE-01** | Code style conforms 100% to Laravel Pint standards | Automated Pint Linter | `pint.txt` | **RUNTIME-VERIFIED** |
| **P8-BUILD-01** | Production assets compile cleanly via Vite 8 and Tailwind CSS 4 | Automated Build Pipeline | `npm-build.txt` | **RUNTIME-VERIFIED** |
| **P8-COMPOSER-01** | Composer configuration and dependencies remain valid | Composer Validator | `composer-validate.txt` | **RUNTIME-VERIFIED** |
| **P8-GIT-01** | Working tree diff contains zero syntax/whitespace errors | Git CLI Check | `git-diff-check.txt`, `git-status.txt` | **RUNTIME-VERIFIED** |
| **P8-TYPO-01** | Heading word-boundary wrapping and Vietnamese diacritics integrity | Automated Runtime Test & Source Review | `tests.txt` (`PublicLayoutTest::test_typography_invariants_and_heading_word_safety`), `resources/css/app.css` | **RUNTIME-VERIFIED / SOURCE-REVIEWED** |
