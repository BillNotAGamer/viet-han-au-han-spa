# TRAINING CMS & TRAINING INQUIRY ADMIN WORKFLOW ARCHITECTURE

**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Phase:** Phase 5 — Training CMS & Training Inquiry Admin Workflow  
**Status:** ACCEPTED (Architect-Approved)  
**Navigation Group:** `Đào tạo`  

---

## 1. Architectural Scope & Navigation

Phase 5 introduces two dedicated Filament resources under the admin navigation group `Đào tạo`:
1. `App\Filament\Resources\TrainingCourses\TrainingCourseResource` (Label: `Khóa học`, Icon: `heroicon-o-academic-cap`, sort order: 1)
2. `App\Filament\Resources\TrainingInquiries\TrainingInquiryResource` (Label: `Yêu cầu tư vấn`, Icon: `heroicon-o-inbox-arrow-down`, sort order: 2)

Complex multi-table course writes and state transitions are decoupled from UI components into domain services:
- `App\Services\Training\TrainingCourseWriter`
- `App\Services\Training\TrainingInquiryWorkflow`

---

## 2. Bilingual Course Translation Architecture

* **Core Settings:** Persisted in `training_courses` (`status`, `is_featured`, `sort_order`, `tuition_fee`, `published_at`, `hero_media_id`).
* **Bilingual Translation Schema:** Localized content is persisted strictly in `training_course_translations`:
  - **Tiếng Việt (Canonical / Required):** `title` (required), `slug` (required, auto-generated from title if blank), `duration_display`, `schedule_display`, `target_audience`, `excerpt`, `content` (RichEditor), repeaters for structured curriculum data (`curriculum_modules`, `benefits`, `faqs`), and SEO metadata (`seo_title`, `seo_description`).
  - **English (Secondary / Optional):** Localized counterparts. English translation is strictly optional and is not fabricated or machine-translated when omitted.
* **Translation Stable-ID Preservation:** Updates synchronize in-place via `updateOrCreate` keyed by `(training_course_id, locale)`, preserving existing primary key IDs across edits.

---

## 3. Localized Slug Policy

* Localized slugs are auto-generated from the corresponding language's title using `Str::slug()` if left blank.
* Manual slug overrides are strictly preserved across later title edits.
* Uniqueness is validated per locale (`UNIQUE(locale, slug)`), allowing the same slug across different languages while preventing collisions within the same locale.

---

## 4. Tuition Fee & Published At Semantics

* **Tuition Fee Storage:** `tuition_fee` is stored as an integer VND (`>= 0`). Display formatting (e.g. `8.500.000 ₫`) is applied only in presentation layers.
* **Published At Lifecycle:**
  - `published_at` is treated as a UTC system timestamp.
  - When publishing a course with `published_at = null`, it is automatically set to `now()` (UTC).
  - Explicit scheduled future timestamps are preserved.
  - Editing an already published course preserves its existing `published_at`.
  - When archiving, historical `published_at` is preserved.
  - Restoring an archived course to `DRAFT` clears `published_at = null` to prevent accidental immediate republishing.

---

## 5. Structured Curriculum JSON & Empty-State Policy

* Structured curriculum modules (`curriculum_modules`), student benefits (`benefits`), and frequently asked questions (`faqs`) are stored as JSON on `training_course_translations`.
* **Consistent Empty-State Policy:** In alignment with Phase 4, repeaters containing empty or unauthored data strictly persist as **`NULL`** (never alternating between `[]` or nested empty keys).

---

## 6. Course Lifecycle & Conservative Hard-Delete Policy

* **Lifecycle Actions:** Governed by `App\Enums\ContentStatus` (`DRAFT`, `PUBLISHED`, `ARCHIVED`).
  - `DRAFT` / `PUBLISHED` -> `ARCHIVED`
  - `ARCHIVED` -> `DRAFT`
* **Hard-Delete Policy:** A `TrainingCourse` may be hard-deleted **ONLY when `status = DRAFT` AND `inquiries_count = 0`**.
  - `DRAFT + 0 inquiries` -> Hard delete allowed with cascade to translations and gallery pivot.
  - `DRAFT + inquiries > 0` -> Forbidden (`DomainException`; backed by `ON DELETE RESTRICT` FK on `training_inquiries.training_course_id`).
  - `PUBLISHED` (any inquiries count) -> Forbidden (`DomainException`).
  - `ARCHIVED` (any inquiries count) -> Forbidden (`DomainException`).

---

## 7. Media Association Boundary

* **Hero Media:** Searchable selection of existing records from the `media` table (`training_courses.hero_media_id`).
* **Gallery Media:** Associated and ordered via the `training_course_media` pivot table.
  - Duplicate media selections are sanitized, enforcing `UNIQUE(training_course_id, media_id)`.
  - Gallery ordering persists accurately to `training_course_media.sort_order`.
  - Detaching an association removes the pivot record only; physical `media` records are never deleted.
* **Hard Boundary:** Phase 5 is association-only. Zero file uploads, file deletions, or image conversions exist in Phase 5.

---

## 8. Training Inquiry Lead Model & Controlled Workflow

* **Lead Immutability:** `TrainingInquiry` represents customer lead submissions. To protect lead integrity:
  - Zero admin `CreateAction` is exposed.
  - Zero generic Resource edit forms are registered. Original customer submission fields (`reference`, `customer_name`, `phone`, `email`, `message`, `training_course_id`, `locale`, `created_at`) and attribution parameters are completely read-only.
* **Status Workflow Service:** Status changes are governed exclusively by `App\Services\Training\TrainingInquiryWorkflow` using `App\Enums\TrainingInquiryStatus`:
  - `NEW` -> `CONTACTED` (sets `contacted_at = now()`).
  - `CONTACTED` -> `ENROLLED` (sets `enrolled_at = now()`).
  - `NEW` or `CONTACTED` -> `CLOSED` (sets `closed_at = now()`).
  - `ENROLLED` and `CLOSED` are terminal states; transitions away from terminal states are strictly rejected.
  - Invalid transitions (e.g. `NEW -> ENROLLED`) throw `DomainException`.
* **Admin Note:** Authorized administrators may append or update internal `admin_note` without altering status, contact timestamps, or attribution data.
* **Soft Deletes:** `TrainingInquiry` implements `SoftDeletes` for accidental-deletion recovery only.
  - Trashed filter and restore actions are provided.
  - Force delete is permanently disabled (`forceDelete` policy returns `false`).
  - PII retention duration is `TO BE DEFINED BY BUSINESS`.

---

## 9. Marketing Attribution & PII Security

* Marketing attribution (`utm_*`, `gclid`, `gbraid`, `wbraid`, `fbclid`, `fbp`, `fbc`, `landing_page`, `referrer`) is displayed in a collapsed, read-only infolist section.
* Attribution capture remains deferred to Phase 12; Phase 5 provides display-only inspection.
* All inquiry data is protected behind admin authorization (`TrainingInquiryPolicy`); no public routes or unauthenticated access points exist.

---

## 10. Performance & N+1 Query Elimination

* `TrainingCoursesTable`: Eager loads `translations` and `heroMedia`, and uses `withCount('inquiries')`.
* `TrainingInquiriesTable`: Eager loads `course.translations`.
* Index listings execute with constant O(1) database queries.
