# PHASE 1A ARCHITECTURE CLOSURE AUDIT REPORT — 2026-08-25

**Date:** 2026-08-25  
**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Workspace Root:** `F:\Coding\Web development\Viet Han Spa`  
**Phase:** Phase 1A — Domain Model & Database Architecture Design (Closure Patch)  
**Phase Readiness:** **READY FOR PHASE 1B**  

---

## A. Starting State
- Phase 1A initial design was generated with 20 proposed business tables (`docs/audit/PHASE_1A_DOMAIN_SCHEMA_DESIGN_2026-08-25.md`).
- The project architect reviewed the design and provided explicit architectural corrections to enforce strict relational integrity, translation symmetry, media protection, timezone consistency, and PII data hygiene.

---

## B. Architecture Corrections Applied in Closure Patch
1. **Final Business Table Count:** Formally expanded from 20 to **23 tables** (added `service_price_translations`, `post_media`, `page_media`).
2. **Service Price Localization:** Removed inline `label_vi` / `label_en` from `service_prices`. Created dedicated `service_price_translations` table (`service_price_id`, `locale`, `label`).
3. **Soft Delete Schema Alignment:** Added `deleted_at` nullable timestamp to `bookings` and `training_inquiries`. Explicitly documented that SoftDeletes is an accidental-deletion recovery mechanism, not the legal PII retention policy.
4. **Historical Booking Snapshots:** Added `service_name_snapshot`, `service_price_label_snapshot`, `duration_minutes_snapshot`, and `price_amount_snapshot` to `bookings` ([ADR-009](file:///F:/Coding/Web%20development/Viet%20Han%20Spa/docs/architecture/adr/ADR-009-booking-historical-snapshot.md)).
5. **Foreign Key Delete Integrity:**
   - `bookings.service_id` -> `services.id` changed to `ON DELETE RESTRICT` (`service_id` NOT NULL).
   - `bookings.service_price_id` -> `service_prices.id` changed to `ON DELETE RESTRICT`.
   - `training_inquiries.training_course_id` -> `training_courses.id` changed to `ON DELETE RESTRICT`.
6. **Media Delete Safety & Pivot Expansion:**
   - Hero media foreign keys (`services.hero_media_id`, `training_courses.hero_media_id`, `posts.hero_media_id`) set to `ON DELETE RESTRICT`.
   - Added `post_media` and `page_media` explicit pivot tables (`media_id ON DELETE RESTRICT`).
   - Documented complete 7-point reference check policy for physical media deletion.
7. **Time Type & Timezone Architecture:**
   - `bookings.preferred_time` typed as native `TIME` (`bookings.preferred_date` as `DATE`).
   - Established UTC for persisted system timestamps, `Asia/Ho_Chi_Minh` for display, and local wall-clock semantics for booking dates/times ([ADR-010](file:///F:/Coding/Web%20development/Viet%20Han%20Spa/docs/architecture/adr/ADR-010-timezone-and-business-time.md)).
8. **Page Slug Uniqueness:** Enforced `UNIQUE (locale, slug)` on `page_translations` (allowing multiple NULLs for homepage).
9. **Phone Normalization:** Standardized `phone_normalized` as canonical E.164 (`+84901234567`). Closed from open questions.
10. **Site Settings Security & Caching:** Set `site_settings.is_public` default to `false`. Documented caching via configured Laravel Cache store and prohibited secret tokens in settings.
11. **PII Data Retention Hygiene:** Explicitly established `PII retention duration: TO BE DEFINED BY BUSINESS`. No default retention period (indefinite, 12-month, 24-month) is assumed, and no automatic purge or anonymization schedule is implemented in MVP.
12. **ADR Statuses:** Updated all 10 ADRs (`ADR-001` through `ADR-010`) to **ACCEPTED**.

---

## C. Final Table Inventory (23 Business Tables)
1. `service_categories`
2. `service_category_translations`
3. `services`
4. `service_translations`
5. `service_prices`
6. `service_price_translations`
7. `service_media`
8. `training_courses`
9. `training_course_translations`
10. `training_course_media`
11. `training_inquiries`
12. `post_categories`
13. `post_category_translations`
14. `posts`
15. `post_translations`
16. `post_media`
17. `pages`
18. `page_translations`
19. `page_media`
20. `media`
21. `media_translations`
22. `bookings`
23. `site_settings`

---

## D. Runtime & Database Code Changes
- **Runtime Application Code Changes:** `NONE`
- **Business Migrations Created:** `NONE`
- **Business Models Created:** `NONE`

---

## E. Baseline Validation Results
- `php artisan migrate:fresh`: **PASS** (Baseline tables `users`, `cache`, `jobs` migrated successfully)
- `php artisan test`: **PASS** (4 tests, 6 assertions, 0 failures)
- `npm run build`: **PASS** (CSS: 24.94 kB, JS: 52.89 kB built cleanly with 0 errors)
- `php vendor/bin/pint --test`: **PASS** (0 style violations)
- `git diff --check`: **PASS** (0 whitespace/formatting errors)
- `git status --short`: **PASS** (Changes strictly confined to documentation & governance)

---

## F. Remaining Owner Decisions
- **Decision 1:** Customer Lead & PII Data Retention Duration (`TO BE DEFINED BY BUSINESS`). No automatic purge or anonymization schedule is implemented in MVP until a formal business/legal policy is established.

---

## G. Phase Readiness Declaration

```text
READY FOR PHASE 1B
```
