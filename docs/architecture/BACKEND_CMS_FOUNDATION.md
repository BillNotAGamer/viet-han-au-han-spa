# BACKEND CMS FOUNDATION — PHASES 0–7 ARCHITECTURAL CLOSURE

**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Phase Closure:** Phases 0, 1A, 1B, 2, 3, 4, 5, 6, 7A, 7B, 7C COMPLETE  
**Status:** ACCEPTED & CLOSED (Backend CMS Complete)  

---

## 1. Executive Summary & Verification Baseline

The backend CMS foundation for Việt Hàn Âu Hàn Spa is fully constructed, secured, and regression-tested.
* **Framework Stack:** Laravel 13.26.1, Filament 5.7.6, Livewire 4.4.1, PHP 8.4.24.
* **Database Baseline:** Exactly `23 business tables`, `0` temporary or dangling migrations.
* **Automated Regression Suite:** `213 tests`, `688 assertions`, `0 failures`, `0 risky tests`.
* **Style & Code Quality:** Laravel Pint compliant (0 errors), Vite production build passing, Composer valid.

---

## 2. Business Resource Inventory (9 Core Resources)

| Resource | Navigation Group | Label | Responsibilities |
| :--- | :--- | :--- | :--- |
| `ServiceCategoryResource` | `Dịch vụ` | `Danh mục dịch vụ` | Service categories, bilingual metadata |
| `ServiceResource` | `Dịch vụ` | `Dịch vụ` | Multi-tier pricing, duration, hero/gallery media, draft/publish lifecycle |
| `TrainingCourseResource` | `Đào tạo` | `Khóa học` | Academy courses, syllabus, pricing, target audience, bilingual content |
| `TrainingInquiryResource` | `Đào tạo` | `Yêu cầu tư vấn` | Lead management, status progression (`NEW` -> `CONTACTED` -> `ENROLLED` -> `CLOSED`), admin notes |
| `PostCategoryResource` | `Nội dung` | `Danh mục Blog` | Editorial categories, localized slugs |
| `PostResource` | `Nội dung` | `Blog` | Articles, author attribution, publication scheduling (`published_at`), gallery |
| `MediaResource` | `Media` | `Thư viện Media` | Centralized upload owner, byte-level raster inspection, safe ULID storage, 7-reference deletion inspection |
| `PageResource` | `Nội dung` | `Trang` | Logical site pages, immutable machine keys (`home`, `about`, `contact`), nullable localized slugs |
| `SiteSettingResource` | `Hệ thống` | `Cài đặt` | Global operational configuration, typed serialization, secret guards, cache invalidation |

---

## 3. Universal Architectural Invariants

### A. Localization & Relational Translations
* All translatable domains (`services`, `training_courses`, `posts`, `pages`, `media`, categories) utilize separate relational translation child tables (`*_translations`).
* Compound unique constraints ensure per-locale slug uniqueness (`UNIQUE(locale, slug)`).
* Normal updates synchronize in-place via `updateOrCreate`, preserving translation primary key IDs.

### B. Media Ownership Boundary
* `MediaResource` is the **sole owner** of file uploads.
* Domain resources (`Services`, `TrainingCourses`, `Posts`, `Pages`) associate existing `Media` records only.
* Deletion of Media is denied if referenced across any of the 7 approved relationships (`services.hero_media_id`, `training_courses.hero_media_id`, `posts.hero_media_id`, `service_media`, `training_course_media`, `post_media`, `page_media`).

### C. Destructive Action Policies
* **Services:** Hard delete allowed ONLY when `status = DRAFT` AND `bookings_count = 0`.
* **Training Courses:** Hard delete allowed ONLY when `status = DRAFT` AND `inquiries_count = 0`.
* **Posts & Pages:** Hard delete allowed ONLY when `status = DRAFT`.
* **Training Inquiries:** Soft delete with restoration; force deletion prohibited.
* **Media:** Permanent deletion permitted ONLY when total references = 0.
* **Site Settings:** Explicit single record deletion; bulk delete disabled.

### D. Security & Authentication
* Admin access is restricted to authenticated users with `users.is_admin === true`.
* Registration endpoints are completely disabled (`/admin/register`, `/register` return 404).
* Secret credentials are prohibited in database storage (`site_settings`), logs, and CLI arguments.

---

## 4. Production Database Parity Limitation

* **Local Verification:** SQLite (`database/database.sqlite`).
* **Production Parity Status:** `NOT TESTED THROUGH PHASE 7` (Target: MySQL 8.0+ / MariaDB 10.4+). Schema contains zero database-specific raw SQL and follows standard ANSI SQL portable definitions.
