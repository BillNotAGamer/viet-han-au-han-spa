# PHASE 5 TRAINING CMS AUDIT REPORT — 2026-08-27

**Date:** 2026-08-27  
**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Workspace Root:** `F:\Coding\Web development\Viet Han Spa`  
**Phase:** Phase 5 — Training CMS & Training Inquiry Admin Workflow  
**Phase Readiness:** **READY FOR PHASE 5 REVIEW**  

---

## A. Starting State
- **Phase 0:** CLOSED (`PHP 8.4.24`, `Laravel 13.26.1`, `Filament 5.7.6`, `Node 24.19.0`, `Vite 8.2.2`).
- **Phase 1 (1A & 1B):** COMPLETE (`23 business tables`, `19 business models`, `19 factories`, `4 PHP backed enums`).
- **Phase 2:** CLOSED (`Bilingual Localization Foundation`, 55 tests, 166 assertions, 0 failures).
- **Phase 3:** CLOSED (`Filament Admin Foundation & Access Control`, 66 tests, 188 assertions, 0 failures).
- **Phase 4:** CLOSED (`Services CMS`, 93 tests, 272 assertions, 0 failures).

---

## B. Resources Implemented
- `App\Filament\Resources\TrainingCourses\TrainingCourseResource`:
  - `Schemas/TrainingCourseForm.php`
  - `Tables/TrainingCoursesTable.php`
  - `Pages/ListTrainingCourses.php`, `CreateTrainingCourse.php`, `EditTrainingCourse.php`
- `App\Filament\Resources\TrainingInquiries\TrainingInquiryResource`:
  - `Schemas/TrainingInquiryInfolist.php`
  - `Tables/TrainingInquiriesTable.php`
  - `Pages/ListTrainingInquiries.php`, `ViewTrainingInquiry.php`

---

## C. Navigation
- **Navigation Group:** `Đào tạo`
- **Items:**
  1. `Khóa học` (Icon: `heroicon-o-academic-cap`, sort order: 1)
  2. `Yêu cầu tư vấn` (Icon: `heroicon-o-inbox-arrow-down`, sort order: 2)

---

## D. TrainingCourse List & Form
- **Form:** Separates core settings (`status`, `is_featured`, `sort_order`, `tuition_fee`, `published_at`, `hero_media_id`, `gallery_media_ids`) from bilingual tabs (`Tiếng Việt` vs `English`).
- **Table:** Displays Vietnamese course title, tuition fee formatted in VND, status badge, featured icon, sort order, inquiries count (`counts('inquiries')`), published date, and updated date. Searchable by VI/EN title and slug. Eager loads `translations`, `heroMedia`.

---

## E. Translation Architecture
- Localized attributes reside strictly in `training_course_translations`.
- Vietnamese title and slug are required; English translation is optional and not fabricated when omitted.
- Updates preserve existing `training_course_translations.id` in place without destructive recreation.

---

## F. Slug Behavior
- Auto-generates from localized title using `Str::slug()` if left blank.
- Manual slugs are preserved across later title edits.
- Validates compound uniqueness per locale (`UNIQUE(locale, slug)`), permitting identical slugs across different languages while preventing collisions within the same locale.

---

## G. Tuition Behavior
- Persisted strictly as unsigned integer VND (`>= 0`).
- Presentation layers format display as `8.500.000 ₫`.

---

## H. published_at Policy
- UTC system timestamp.
- Setting status to `PUBLISHED` with null timestamp assigns `now()`.
- Future scheduled timestamps are preserved.
- Editing published courses preserves existing timestamp.
- Archiving preserves historical timestamp.
- Restoring from `ARCHIVED` to `DRAFT` clears `published_at` to null.

---

## I. Structured JSON Content
- Repeaters for `curriculum_modules`, `benefits`, and `faqs` persist as JSON on `training_course_translations`.
- Empty or omitted repeaters persist consistently as **`NULL`**.

---

## J. TrainingCourse Writer
- Multi-table mutations are encapsulated in `App\Services\Training\TrainingCourseWriter`.
- All operations execute inside `DB::transaction(...)`.

---

## K. Translation ID Preservation
- `updateOrCreate` by `(training_course_id, locale)` ensures existing translation rows maintain their primary key `id`.

---

## L. Archive & Delete Policy
- Lifecycle transitions: `DRAFT`/`PUBLISHED` -> `ARCHIVED`, `ARCHIVED` -> `DRAFT`.
- Hard delete invariant: permitted **ONLY when `status = DRAFT` AND `inquiries_count = 0`**. Any other state throws `DomainException` and hides the delete action in UI.

---

## M. Hero & Gallery Media
- Hero selects an existing `media.id`.
- Gallery associates existing media via `training_course_media` pivot table.
- Duplicate media selections are sanitized.
- Reordering persists `sort_order`.
- Detaching associations preserves physical `media` records.
- Zero file uploads or deletions exist in Phase 5.

---

## N. TrainingInquiry List & View
- **List:** Displays reference, customer name, phone, course title (VI), status badge, locale badge, created date, and contacted date. Searchable by reference, customer name, phone. Filters by status, course, locale, and trashed.
- **View:** Read-only Infolist displaying lead details, lifecycle timeline, internal admin notes, and marketing attribution.

---

## O. TrainingInquiry Mutable vs Immutable Fields
- **Immutable:** `reference`, `training_course_id`, `customer_name`, `phone`, `phone_normalized`, `email`, `message`, `locale`, `created_at`, and all attribution fields.
- **Mutable:** `admin_note` and workflow status via dedicated modal actions.

---

## P. TrainingInquiry Workflow
- Governed by `App\Services\Training\TrainingInquiryWorkflow` using `TrainingInquiryStatus`.
- No free-form status dropdown.

---

## Q. Allowed Status Transitions
- `NEW` -> `CONTACTED`
- `CONTACTED` -> `ENROLLED` (terminal)
- `NEW` or `CONTACTED` -> `CLOSED` (terminal)
- Invalid transitions throw `DomainException`.

---

## R. Lifecycle Timestamps
- `NEW` -> `CONTACTED`: sets `contacted_at = now()`.
- `CONTACTED` -> `ENROLLED`: sets `enrolled_at = now()`.
- `NEW`/`CONTACTED` -> `CLOSED`: sets `closed_at = now()`.
- Timestamps update atomically with status.

---

## S. Soft-Delete & Restore Behavior
- Uses native `SoftDeletes`.
- Deleted records are excluded from default queries, visible via `TrashedFilter`, and restorable via `RestoreAction`.

---

## T. Force-Delete Policy
- Force delete is permanently disabled (`forceDelete` and `forceDeleteAny` policies return `false`).
- PII retention duration is `TO BE DEFINED BY BUSINESS`.

---

## U. Attribution Handling
- Read-only display of UTM parameters (`source`, `medium`, `campaign`, `content`, `term`), click IDs (`gclid`, `gbraid`, `wbraid`, `fbclid`, `fbp`, `fbc`), landing page, and referrer.
- Attribution capture remains deferred to Phase 12.

---

## V. Authorization
- `TrainingCoursePolicy` and `TrainingInquiryPolicy` enforce `$user->is_admin` for all operations.
- Inquiry creation policy returns `false`.

---

## W. Database Changes
- **New Migrations:** `0`
- **Schema Changes:** `NONE`

---

## X. Business Table Count
- **Expected:** `23`
- **Actual:** `23`

---

## Y. Tests Added
- Exactly 34 new tests added in `tests/Feature/Admin/Training/`:
  - `TrainingCourseResourceTest` (12 tests)
  - `TrainingCoursePublishedAtTest` (3 tests)
  - `TrainingCourseSlugTest` (4 tests)
  - `TrainingCourseMediaTest` (3 tests)
  - `TrainingInquiryResourceTest` (12 tests)

---

## Z. Total Regression Tests
- **Total Tests:** `127`
- **Total Assertions:** `361`
- **Failures:** `0`
- **Duration:** `4.24s`

---

## AA. Migration Lifecycle
- `php artisan migrate:fresh`: **PASS**
- `php artisan migrate:refresh`: **PASS**

---

## AB. Services CMS Regression
- All Phase 4 Services CMS tests pass with 0 regressions.

---

## AC. Localization Regression
- Public routes (`/`, `/en`, `/vi`) pass with 0 regressions.

---

## AD. Admin Security Regression
- Guest/non-admin access blocks and CSRF protection pass with 0 regressions.

---

## AE. Route Inventory
- Training admin routes are registered strictly under `/admin/...`:
  - `/admin/training-courses`
  - `/admin/training-courses/create`
  - `/admin/training-courses/{record}/edit`
  - `/admin/training-inquiries`
  - `/admin/training-inquiries/{record}`
- Zero public training routes or submission endpoints added.

---

## AF. N+1 Review
- `TrainingCoursesTable`: Eager loads `translations`, `heroMedia` and counts `inquiries`.
- `TrainingInquiriesTable`: Eager loads `course.translations`.
- Constant O(1) query execution verified.

---

## AG. Build / Pint / Composer
- `php vendor/bin/pint --test`: **PASS** (0 violations).
- `npm run build`: **PASS** (Vite built in 539ms).
- `composer validate`: **PASS** (`./composer.json is valid`).
- `git diff --check`: **PASS** (0 whitespace/formatting issues).

---

## AH. Production DB Execution
- **Execution Status:** **NOT TESTED IN PHASE 5** (SQLite local development only; production target: MySQL 8.0+ / MariaDB 10.4+).

---

## AI. Files Created
- `app/Policies/TrainingCoursePolicy.php`
- `app/Policies/TrainingInquiryPolicy.php`
- `app/Services/Training/TrainingCourseWriter.php`
- `app/Services/Training/TrainingInquiryWorkflow.php`
- `app/Filament/Resources/TrainingCourses/TrainingCourseResource.php`
- `app/Filament/Resources/TrainingCourses/Schemas/TrainingCourseForm.php`
- `app/Filament/Resources/TrainingCourses/Tables/TrainingCoursesTable.php`
- `app/Filament/Resources/TrainingCourses/Pages/ListTrainingCourses.php`
- `app/Filament/Resources/TrainingCourses/Pages/CreateTrainingCourse.php`
- `app/Filament/Resources/TrainingCourses/Pages/EditTrainingCourse.php`
- `app/Filament/Resources/TrainingInquiries/TrainingInquiryResource.php`
- `app/Filament/Resources/TrainingInquiries/Schemas/TrainingInquiryInfolist.php`
- `app/Filament/Resources/TrainingInquiries/Tables/TrainingInquiriesTable.php`
- `app/Filament/Resources/TrainingInquiries/Pages/ListTrainingInquiries.php`
- `app/Filament/Resources/TrainingInquiries/Pages/ViewTrainingInquiry.php`
- `tests/Feature/Admin/Training/TrainingCourseResourceTest.php`
- `tests/Feature/Admin/Training/TrainingCoursePublishedAtTest.php`
- `tests/Feature/Admin/Training/TrainingCourseSlugTest.php`
- `tests/Feature/Admin/Training/TrainingCourseMediaTest.php`
- `tests/Feature/Admin/Training/TrainingInquiryResourceTest.php`
- `docs/architecture/TRAINING_CMS_ARCHITECTURE.md`
- `docs/architecture/adr/ADR-015-training-cms-and-inquiry-workflow.md`
- `docs/audit/PHASE_5_TRAINING_CMS_2026-08-27.md`

---

## AJ. Files Modified
- `app/Enums/TrainingInquiryStatus.php`
- `AGENTS.md`

---

## AK. Known Limitations
- Media selection relies on pre-seeded `media` rows; admin file uploads remain deferred to Phase 7.
- Lead submissions cannot be manually corrected from the UI in Phase 5 to protect submission integrity.

---

## AL. Deferred Work Preserved
- **Phase 6:** Blog CMS
- **Phase 7:** Pages / Settings / Media CMS
- **Phase 8:** Public Design System & Global Layout
- **Phase 9:** Homepage
- **Phase 10:** Public Content Pages (including public Training detail pages)
- **Phase 11:** Booking System
- **Phase 12:** Marketing Tracking & Attribution
- **Phase 13:** Technical SEO
- **Phase 14:** Performance / Accessibility / Security
- **Phase 15:** Full QA & Reference Fidelity
- **Phase 16:** Production Deployment
- **Phase 17:** Launch Verification

---

## AM. Readiness Declaration

```text
READY FOR PHASE 5 REVIEW
```

---

## AN. Final Workflow Invariant Closure (Section 53 Verification)

1. **TrainingInquiryStatus Backing Values:**
   - Backed enum cases remain exactly 4: `NEW`, `CONTACTED`, `ENROLLED`, `CLOSED`.
   - Persisted database values are identical: string `'NEW'`, `'CONTACTED'`, `'ENROLLED'`, `'CLOSED'`.
   - Verified by test: `test_training_inquiry_status_backing_values_are_exact`.

2. **Reason `TrainingInquiryStatus.php` was Modified:**
   - Modified exclusively to implement Filament 5 contract interfaces `Filament\Support\Contracts\HasLabel` and `Filament\Support\Contracts\HasColor` for UI presentation (badge colors and localized labels).
   - Zero database enum/string values were altered or added.

3. **Admin Note Mutation Isolation Result:**
   - Updating `admin_note` via `TrainingInquiryWorkflow::updateAdminNote` mutates ONLY `admin_note` and standard `updated_at`.
   - All customer submission fields (`reference`, `customer_name`, `phone`, `phone_normalized`, `email`, `message`, `training_course_id`, `locale`, `created_at`) remain 100% unchanged.
   - All attribution fields (`utm_*`, `gclid`, `gbraid`, `wbraid`, `fbclid`, `fbp`, `fbc`, `landing_page`, `referrer`) remain 100% unchanged.
   - Status and lifecycle timestamps (`status`, `contacted_at`, `enrolled_at`, `closed_at`) remain 100% unchanged.
   - Verified by test: `test_admin_note_isolation_preserves_all_lead_and_attribution_fields`.

4. **Workflow Atomicity Result:**
   - Status and lifecycle timestamps update atomically within `DB::transaction(...)`:
     - `NEW -> CONTACTED` sets `status = CONTACTED` and `contacted_at = now()`.
     - `CONTACTED -> ENROLLED` sets `status = ENROLLED` and `enrolled_at = now()`, preserving `contacted_at`.
     - `NEW/CONTACTED -> CLOSED` sets `status = CLOSED` and `closed_at = now()`.

5. **Invalid-Transition Rollback Result:**
   - Any invalid transition (e.g. `NEW -> ENROLLED`) throws `DomainException` and leaves status and all lifecycle timestamps completely untouched (`status` remains `NEW`, timestamps remain `null`).
   - Verified by test: `test_invalid_status_transitions_are_rejected_and_state_remains_unchanged`.

6. **Terminal-State Immutability Result:**
   - `ENROLLED` and `CLOSED` are strictly terminal.
   - Attempting to transition from `ENROLLED` or `CLOSED` throws `DomainException`.
   - No reopen actions or free-form status selects exist in the UI.
   - Verified by tests: `test_terminal_enrolled_transition_is_rejected` and `test_terminal_closed_transition_is_rejected`.

7. **Inquiry Resource CreateAction Status:**
   - Zero admin `CreateAction` is exposed in `TrainingInquiryResource`.
   - `TrainingInquiryPolicy::create` returns `false`.
   - Route `/admin/training-inquiries/create` returns HTTP 404.
   - Verified by tests: `test_no_create_or_generic_edit_route_is_exposed` and `test_policy_strictly_prohibits_creation_and_force_delete`.

8. **Force-Delete Policy Status:**
   - `ForceDeleteAction` is absent from table actions.
   - `TrainingInquiryPolicy::forceDelete` and `forceDeleteAny` return `false`.
   - Soft delete and restore remain active for recovery only.
   - Verified by test: `test_policy_strictly_prohibits_creation_and_force_delete`.

9. **Regression & Parity Verification:**
   - Database migrations added: `0`
   - Business table count: exactly `23`
   - Test suite: `129 tests, 394 assertions, 0 failures` (Duration: 4.36s)
   - Code formatting: `php vendor/bin/pint --test` PASS (0 violations)
   - Build assets: `npm run build` PASS (Vite built in 662ms)
   - Composer: `composer validate` PASS
   - Public route boundary: zero public Training routes or inquiry endpoints exist.

---

## AO. Final Phase Readiness Declaration

```text
READY FOR PHASE 6
```

