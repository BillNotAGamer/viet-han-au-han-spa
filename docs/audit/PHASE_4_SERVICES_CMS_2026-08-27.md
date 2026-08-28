# PHASE 4 SERVICES CMS AUDIT REPORT — 2026-08-27

**Date:** 2026-08-27  
**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Workspace Root:** `F:\Coding\Web development\Viet Han Spa`  
**Phase:** Phase 4 — Services CMS  
**Phase Readiness:** **READY FOR PHASE 5**  

---

## A. Starting State
- **Phase 0:** CLOSED (`PHP 8.4.24`, `Laravel 13.26.1`, `Filament 5.7.6`, `Node 24.19.0`, `Vite 8.2.2`).
- **Phase 1 (1A & 1B):** COMPLETE (`23 business tables`, `19 business models`, `19 factories`, `4 PHP backed enums`).
- **Phase 2:** CLOSED (`Bilingual Localization Foundation`, 55 tests, 166 assertions, 0 failures).
- **Phase 3:** CLOSED (`Filament Admin Foundation & Access Control`, 66 tests, 188 assertions, 0 failures).

---

## B. Resources Implemented
- `App\Filament\Resources\ServiceCategories\ServiceCategoryResource`:
  - `Schemas/ServiceCategoryForm.php`
  - `Tables/ServiceCategoriesTable.php`
  - `Pages/ListServiceCategories.php`, `CreateServiceCategory.php`, `EditServiceCategory.php`
- `App\Filament\Resources\Services\ServiceResource`:
  - `Schemas/ServiceForm.php`
  - `Tables/ServicesTable.php`
  - `Pages/ListServices.php`, `CreateService.php`, `EditService.php`

---

## C. Navigation Organization
- **Navigation Group:** `Dịch vụ`
- **Items:**
  1. `Danh mục dịch vụ` (Icon: `heroicon-o-folder`, sort order: 1)
  2. `Dịch vụ` (Icon: `heroicon-o-sparkles`, sort order: 2)

---

## D. Category Form & Table Implementation
- **Form:** Separates core attributes (`status`, `sort_order`) from bilingual tabs (`Tiếng Việt` vs `English`).
- **Validation:** Vietnamese name and slug are required; English translation is optional. Slugs automatically generate from localized names if left blank.
- **Table:** Displays Vietnamese category name, status badge, sort order, services count (`counts('services')`), and formatted timestamp. Search queries match against translated names and slugs.

---

## E. Service Form & Table Implementation
- **Form:** Core section (`service_category_id`, `status`, `sort_order`, `is_featured`, `hero_media_id`, `gallery_media_ids`), bilingual tabs (`name`, `slug`, `excerpt`, `content` RichEditor, repeaters for `benefits`, `process_steps`, `faqs`, and SEO metadata), and Price Tiers repeater.
- **Table:** Displays Vietnamese service name, category name, price range in VND, status badge, featured flag, sort order, and updated date. Filters by `status`, `category`, and `is_featured`. Includes row actions for Edit, Archive, Restore, and Delete.

---

## F. Translation Architecture & Stable ID Preservation
- Localized attributes are persisted strictly in `service_category_translations` and `service_translations`.
- No anti-pattern columns (e.g. `name_vi`, `name_en`).
- Admin updates preserve existing `service_translations.id` in-place without destructive recreation.
- Admin forms edit exact language records without fallback interpolation.

---

## G. Slug Policy & Locale Uniqueness
- Slugs are generated using `Str::slug()` when blank and preserved when manually entered.
- Uniqueness is validated per locale (`UNIQUE(locale, slug)`), allowing identical slugs across different languages while preventing collisions within the same locale.
- Editing an existing record without changing its slug passes validation cleanly without self-collision.

---

## H. Price Tier Implementation & VND Integer Storage
- `price_amount` is stored as an integer VND (`>= 0`).
- `duration_minutes` is stored as positive integer minutes (`>= 1`).
- Optional localized labels are persisted into `service_price_translations` for `vi` and `en`.

---

## I. Stable Price ID Preservation Strategy
- During updates, existing tiers retain their `service_prices.id`.
- If an omitted tier is referenced by a historical `Booking`, it is **deactivated (`is_active = false`) rather than hard-deleted**, preserving historical booking referential integrity. Unreferenced omitted tiers are safely deleted.

---

## J. Structured JSON Content & Empty-State Policy
- Repeaters for `benefits` (`title`, `description`), `process_steps` (`title`, `description`), and `faqs` (`question`, `answer`) persist as clean JSON on `service_translations`.
- **Locked Empty-State Policy:** Unauthored or empty repeaters strictly persist as `NULL`.

---

## K. Multi-Table Writer Services & Transaction Atomicity
- Multi-table mutations are encapsulated in `ServiceCategoryWriter` and `ServiceWriter`.
- All operations execute within `DB::transaction(...)`. Any failure (e.g. duplicate localized slug) triggers an immediate rollback, ensuring zero orphan records.

---

## L. Service Hard-Delete Invariant
- A Service may be hard-deleted **ONLY when `status = DRAFT` AND `bookings_count = 0`**.
- Domain logic in `ServiceWriter::delete()` and UI visibility in Filament reject deletion under any other state:
  - `DRAFT + 0 bookings` -> ALLOWED.
  - `DRAFT + bookings > 0` -> REJECTED (`DomainException`).
  - `PUBLISHED` (with or without bookings) -> REJECTED (`DomainException`).
  - `ARCHIVED` (with or without bookings) -> REJECTED (`DomainException`).
- Categories with services cannot be deleted (`services` foreign key `ON DELETE RESTRICT`).

---

## M. Hero & Gallery Media Behavior
- Hero media selects an existing `media.id`.
- Gallery associates existing media via the `service_media` pivot table with order index.
- Duplicate media selections in gallery are sanitized to enforce `UNIQUE(service_id, media_id)`.
- Reordering gallery items updates `service_media.sort_order` accurately.
- Media upload and physical file deletion are strictly prohibited in Phase 4 and remain deferred to Phase 7.

---

## N. Authorization & Policy Protection
- `ServiceCategoryPolicy` and `ServicePolicy` explicitly gate access with `$user->is_admin`.
- Guests are redirected to `/admin/login`; non-admin users receive HTTP 403 Forbidden.

---

## O. Database Boundary & Regression
- **New Migrations:** `0`
- **Schema Changes:** `NONE`
- **Business Table Count:** Exactly **23 tables** (Unchanged).
- **Migration Lifecycle:** `php artisan migrate:fresh` and `php artisan migrate:refresh` pass cleanly.

---

## P. Automated Test Suite (27 Tests Added in Phase 4, 93 Total Tests)
1. `Tests\Feature\Admin\Services\ServiceCategoryResourceTest`:
   - Guest & non-admin access denial
   - Admin access to list/create
   - Atomic creation of core & bilingual translations
   - Update preserves existing translation IDs
   - Category with services cannot be deleted
   - Category without services deletes cleanly
2. `Tests\Feature\Admin\Services\ServiceResourceTest`:
   - Guest & non-admin access denial
   - Admin access to list/create
   - Atomic service creation with prices & gallery
   - ServiceTranslation stable-ID preservation across updates
   - Archive and restore workflow
   - Hard delete policy: DRAFT with 0 bookings allowed
   - Hard delete policy: DRAFT with bookings denied
   - Hard delete policy: PUBLISHED denied
   - Hard delete policy: ARCHIVED denied
   - Consistent JSON empty-state (`NULL`) persistence
3. `Tests\Feature\Admin\Services\LocalizedSlugValidationTest`:
   - Same-locale slug collision rejected
   - Cross-locale identical slug allowed (`UNIQUE(locale, slug)`)
   - Self-update slug validation passes without collision
   - Slug auto-generates when blank and preserves manual entries
4. `Tests\Feature\Admin\Services\PriceTierSynchronizationTest`:
   - Updating price tier preserves stable `service_prices.id`
   - Omitted price tier with booking is deactivated, not deleted
5. `Tests\Feature\Admin\Services\MediaAssociationTest`:
   - Hero and gallery media assignment
   - Detaching gallery does not delete physical Media records
   - Duplicate gallery media selection prevented
   - Gallery media reordering persists `sort_order`
6. `Tests\Feature\Admin\Services\TransactionRollbackTest`:
   - Transaction failure rolls back all multi-table records atomically

---

## Q. Validation Results
- `php artisan migrate:fresh`: **PASS**
- `php artisan migrate:refresh`: **PASS**
- `php artisan test`: **PASS** (93 tests, 272 assertions, 0 failures in 2.83s)
- `php vendor/bin/pint --test`: **PASS** (0 style violations)
- `npm run build`: **PASS** (Vite built in 508ms, 0 errors)
- `composer validate`: **PASS** (`./composer.json is valid`)
- `git diff --check`: **PASS** (0 whitespace/formatting issues)
- `git status --short`: **PASS** (Clean implementation)

---

## R. Route Inventory & Regression
- Administrative routes for Services CMS are registered strictly under `/admin/...`:
  - `/admin/service-categories`
  - `/admin/service-categories/create`
  - `/admin/service-categories/{record}/edit`
  - `/admin/services`
  - `/admin/services/create`
  - `/admin/services/{record}/edit`
- Public routes (`/`, `/en`, `/vi`) remain deterministic and unaffected. Zero public Service routes added.

---

## S. Production Database Execution Status
- **Production Target:** MySQL 8.0+ / MariaDB 10.4+
- **Execution Status in Phase 4:** **NOT TESTED IN PHASE 4** (SQLite local development only; production parity verification scheduled prior to deployment).

---

## T. Deferred Work Preserved (Hard Boundaries Respected)
- **Phase 5:** Training CMS
- **Phase 6:** Blog CMS
- **Phase 7:** Pages / Settings / Media CMS (including media upload/management)
- **Phase 8:** Public Design System & Global Layout
- **Phase 9:** Homepage
- **Phase 10:** Public Content Pages (including public Service detail pages)
- **Phase 11:** Booking System
- **Phase 12:** Marketing Tracking & Attribution
- **Phase 13:** Technical SEO
- **Phase 14:** Performance / Accessibility / Security
- **Phase 15:** Full QA & Reference Fidelity
- **Phase 16:** Production Deployment
- **Phase 17:** Launch Verification

---

## U. Phase Readiness Declaration

```text
READY FOR PHASE 5
```
