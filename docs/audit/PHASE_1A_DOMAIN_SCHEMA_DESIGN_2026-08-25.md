# PHASE 1A DESIGN AUDIT REPORT — 2026-08-25

**Date:** 2026-08-25  
**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Workspace Root:** `F:\Coding\Web development\Viet Han Spa`  
**Phase:** Phase 1A — Domain Model & Database Architecture Design  
**Phase Readiness:** **READY FOR PHASE 1A REVIEW**  

---

## A. Starting State Verification
- **Phase 0 Status:** Formally CLOSED and verified via `docs/audit/PHASE_0_CLOSURE_2026-08-25.md`.
- **Runtime Environment:** PHP 8.4.24, Composer 2.10.2, Laravel 13.26.1, Filament 5.7.6, Livewire 4.4.1, Node v24.19.0, Vite 8.2.2, Tailwind CSS 4.3.3.
- **Local Database:** SQLite 3 (`database/database.sqlite`).
- **Production Target:** MySQL 8.x / MariaDB 10.x on Linux Shared Hosting.

---

## B. Inputs Reviewed
The following project governance and specification files were thoroughly analyzed:
1. `AGENTS.md` (Engineering rules and locked stack boundaries)
2. `docs/PROJECT_SPEC.md` (Product scope, navigation, and module specifications)
3. `docs/ARCHITECTURE.md` (Target system topology and request lifecycle)
4. `docs/ROADMAP.md` (18-phase master roadmap, Phase 0 to Phase 17)
5. `docs/TRACKING_REQUIREMENTS.md` (Marketing attribution, GTM, CAPI schema requirements)
6. `docs/DEPLOYMENT_TARGET.md` (Shared hosting constraints and portability limits)
7. `docs/BRAND_UI_DIRECTION.md` (Brand luxury wellness design principles)

---

## C. Proposed Domain Model Summary
The domain model partitions the application into 7 cohesive Bounded Contexts:
1. **Service Catalog Context:** Categories, Services, Pricing Options, and bilingual content.
2. **Training Academy Context:** Courses, Curriculum Modules, Schedules, and Student Inquiries.
3. **Blog Context:** Categories, Articles, Author attribution, and publication workflow.
4. **Page Context:** Static singleton landing pages (`home`, `about`, `contact`, `terms`, `privacy`).
5. **Media Context:** Asset registry with bilingual SEO alt-text/captions and explicit gallery pivots.
6. **Lead Generation Context:** Spa appointment requests and student inquiries with marketing attribution snapshots.
7. **Site Settings Context:** Grouped key-value global runtime configuration.

---

## D. Proposed Table Inventory (20 Business Tables)
1. `service_categories`
2. `service_category_translations`
3. `services`
4. `service_translations`
5. `service_prices`
6. `training_courses`
7. `training_course_translations`
8. `training_inquiries`
9. `post_categories`
10. `post_category_translations`
11. `posts`
12. `post_translations`
13. `pages`
14. `page_translations`
15. `media`
16. `media_translations`
17. `service_media`
18. `training_course_media`
19. `bookings`
20. `site_settings`

---

## E. Major Architecture Decisions (ADRs)
- **ADR-001 (Translation Tables):** Native Eloquent translation tables for strict `(locale, slug)` indexing without third-party packages.
- **ADR-002 (Status & Enums):** `VARCHAR(32)` database columns paired with PHP 8.4 Backed Enums for full SQLite/MySQL portability.
- **ADR-003 (Money Strategy):** Integer VND storage (`unsignedBigInteger`) to prevent floating-point rounding errors.
- **ADR-004 (Media Association):** Native media registry with explicit foreign keys and pivot tables for strict relational integrity.
- **ADR-005 (Editorial JSON):** JSON columns on translation tables for rich editorial repeaters (benefits, procedure steps, FAQs).
- **ADR-006 (Attribution Snapshot):** Direct conversion-time attribution columns on `bookings` and `training_inquiries`.
- **ADR-007 (Soft Deletes):** Applied exclusively to lead tables (`bookings`, `training_inquiries`) to prevent slug collisions on content.
- **ADR-008 (Site Settings):** Grouped key-value settings table cached in memory.

---

## F. SQLite / MySQL / MariaDB Portability Conclusion
- All column types and constraints are 100% portable between SQLite 3 and MySQL 8.x / MariaDB 10.x.
- Zero database-level ENUM types.
- String lengths are explicitly sized to prevent InnoDB index byte limit overflows.
- Nullable unique constraints behave correctly across all engines.

---

## G. Files Created in Phase 1A
- `docs/architecture/DOMAIN_MODEL.md`
- `docs/architecture/DATABASE_SCHEMA.md`
- `docs/architecture/DATABASE_ERD.md`
- `docs/architecture/DATABASE_INDEX_STRATEGY.md`
- `docs/architecture/DATABASE_PORTABILITY.md`
- `docs/architecture/DATA_RETENTION_AND_DELETE_POLICY.md`
- `docs/architecture/SCHEMA_SUMMARY.md`
- `docs/architecture/adr/ADR-001-translation-table-strategy.md`
- `docs/architecture/adr/ADR-002-string-status-with-php-enums.md`
- `docs/architecture/adr/ADR-003-money-as-integer-vnd.md`
- `docs/architecture/adr/ADR-004-media-association-strategy.md`
- `docs/architecture/adr/ADR-005-editorial-json-policy.md`
- `docs/architecture/adr/ADR-006-lead-attribution-snapshot.md`
- `docs/architecture/adr/ADR-007-soft-delete-strategy.md`
- `docs/architecture/adr/ADR-008-site-settings-strategy.md`
- `docs/audit/PHASE_1A_DOMAIN_SCHEMA_DESIGN_2026-08-25.md`

---

## H. Files Modified in Phase 1A
- None (all additions are documentation and architectural specifications).

---

## I. Runtime Code & Migration Changes
- **Runtime Code Changes:** `NONE`
- **Business Migrations Created:** `NONE`
- **Business Models Created:** `NONE`

---

## J. Baseline Validation Results
- `php artisan test`: **PASS** (4 tests, 6 assertions, 0 failures in 320ms)
- `npm run build`: **PASS** (Built in 1.67s, 0 errors)
- `php vendor/bin/pint --test`: **PASS** (0 style violations)

---

## K. Phase Readiness Declaration

```text
READY FOR PHASE 1A REVIEW
```
