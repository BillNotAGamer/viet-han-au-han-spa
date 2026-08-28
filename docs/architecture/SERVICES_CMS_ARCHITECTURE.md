# SERVICES CMS ARCHITECTURE & MULTI-TABLE PERSISTENCE

**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Phase:** Phase 4 — Services CMS  
**Status:** ACCEPTED (Architect-Approved)  
**Navigation Group:** `Dịch vụ`  

---

## 1. Resource Structure & Organization

In alignment with Filament 5 architectural conventions, the Services domain is managed via two thin, focused Resource classes:
* `App\Filament\Resources\ServiceCategories\ServiceCategoryResource` (Navigation: `Danh mục dịch vụ`, sort order 1)
* `App\Filament\Resources\Services\ServiceResource` (Navigation: `Dịch vụ`, sort order 2)

Each resource delegates persistence and complex orchestration to dedicated domain writer services:
* `App\Services\ServiceCatalog\ServiceCategoryWriter`
* `App\Services\ServiceCatalog\ServiceWriter`

---

## 2. Bilingual Translation Form Architecture

The administrative forms enforce strict separation between core operational data and localized content:
* **Core Settings:** Stored on parent tables (`service_categories`, `services`). Includes `status`, `sort_order`, `service_category_id`, `is_featured`, and `hero_media_id`.
* **Bilingual Translation Tabs:**
  - **Tiếng Việt (Default / Canonical):** `name` (required), `slug` (required, auto-generated from name if blank), `excerpt`, `content` (RichEditor), structured repeaters (`benefits`, `process_steps`, `faqs`), and SEO metadata (`seo_title`, `seo_description`).
  - **English (Secondary):** Localized counterparts. English translation is strictly optional and is not fabricated if omitted.
* **No `name_vi` / `name_en` Anti-Pattern:** Content is stored in dedicated relational tables (`service_category_translations`, `service_translations`).
* **Translation Stable-ID Preservation:** Updates synchronize in-place via `updateOrCreate` keyed by `(service_id, locale)` or `(service_category_id, locale)`, strictly preserving the primary key `id` of translation rows across edits.

---

## 3. Structured JSON Content & Empty-State Policy

* Structured repeater data (`benefits`, `process_steps`, `faqs`) is stored as JSON in `service_translations`.
* **Consistent Empty-State Policy:** Authored content containing no valid repeater entries or empty values strictly persists as **`NULL`** (not empty array `[]` or nested empty keys). This provides a clean semantic boundary between unauthored structured content (`NULL`) and authored items.

---

## 4. Price-Tier Management & ID Preservation

Service price tiers are managed directly within the Service form (`service_prices`, `service_price_translations`):
* **Integer Currency:** `price_amount` is strictly persisted as unsigned integer VND (`>= 0`). No decimal or string currency representations are accepted.
* **Duration:** `duration_minutes` is stored as an unsigned integer representing minutes (`>= 1`).
* **Stable ID Synchronization:**
  - Existing tiers provided with an `id` update the existing `ServicePrice` record in place, preserving stable foreign keys.
  - New tiers without an `id` create a new `ServicePrice` row.
  - **Omitted/Removed Tiers:** The writer verifies if the omitted tier is referenced by any historical `Booking` records (`bookings.service_price_id`). If referenced, it is **never hard-deleted**; instead, it is safely marked `is_active = false`. If unreferenced, it is deleted cleanly.

---

## 5. Archive, Delete & Data Integrity Policies

* **Status Lifecycle:** Governed by `App\Enums\ContentStatus` (`DRAFT`, `PUBLISHED`, `ARCHIVED`).
* **Archive Workflow:** Admin row actions provide instant `archive` (moves to `ARCHIVED`) and `restore` (moves to `DRAFT`).
* **Category Deletion Policy:** Deleting a `ServiceCategory` with associated services is prohibited (`services` foreign key `ON DELETE RESTRICT`). The UI and writer reject the deletion with a clear domain message.
* **Strict Service Hard-Delete Policy:** A `Service` may be hard-deleted **ONLY when `status = DRAFT` AND `bookings_count = 0`**.
  - `DRAFT + 0 bookings` -> Hard delete permitted.
  - `DRAFT + bookings > 0` -> Forbidden (`DomainException`).
  - `PUBLISHED` (with or without bookings) -> Forbidden (`DomainException`).
  - `ARCHIVED` (with or without bookings) -> Forbidden (`DomainException`).

---

## 6. Media Association & Gallery Ordering

* **Hero Media:** Selected from existing records in the `media` table (`services.hero_media_id`).
* **Gallery Associations:** Associated and ordered via the `service_media` pivot table (`UNIQUE(service_id, media_id)`).
* **Order Persistence:** Reordering items in the gallery selection strictly persists the order index to `service_media.sort_order`.
* **Duplicate Prevention:** Duplicate selections of the same media item are sanitized, maintaining pivot table uniqueness.
* **Hard Phase Boundary:** Zero file uploads, file deletions, or physical image processing exist in Phase 4. Detaching or removing a gallery association removes the pivot record only; physical `media` records are never deleted.

---

## 7. Transaction Boundaries & Atomicity

All multi-table mutations (`create`, `update`, `delete`) execute inside atomic database transactions (`DB::transaction`). Any failure during core record creation, translation persistence, price tier synchronization, or gallery pivoting immediately rolls back the entire operation, guaranteeing zero orphan rows.

---

## 8. Authorization & N+1 Prevention

* **Policy Protection:** `ServiceCategoryPolicy` and `ServicePolicy` enforce `$user->is_admin` for all operations.
* **N+1 Query Elimination:** Resource table queries utilize eager loading (`with(['translations', 'category.translations', 'prices'])`) and subquery counts (`withCount('services')`), ensuring constant O(1) database query overhead on index views.
