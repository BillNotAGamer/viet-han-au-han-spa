# PHASE 7A MEDIA CMS AUDIT REPORT — 2026-08-27

**Date:** 2026-08-27  
**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Workspace Root:** `F:\Coding\Web development\Viet Han Spa`  
**Phase:** Phase 7A — Media CMS, File Storage & Reference-Safe Deletion  
**Phase Readiness:** **READY FOR PHASE 7A REVIEW**  

---

## A. Starting State
- **Phase 0–6:** COMPLETE & CLOSED.
- **Baseline:** `23 business tables`, `155 tests`, `474 assertions`, `0 failures`.

---

## B. MediaResource
- Implemented `App\Filament\Resources\Media\MediaResource`:
  - `Schemas/MediaForm.php`
  - `Tables/MediaTable.php`
  - `Pages/ListMedia.php`, `CreateMedia.php`, `EditMedia.php`

---

## C. Navigation
- **Navigation Label:** `Media`
- **Icon:** `heroicon-o-photo`
- **Sort Order:** `3`

---

## D. Supported File Types
- **Raster Formats:** `image/jpeg` (`.jpg`, `.jpeg`), `image/png` (`.png`), `image/webp` (`.webp`).
- **Forbidden:** SVG (`image/svg+xml`), executables, scripts, documents, archives.

---

## E. Upload Validation
- Server-side verification via `getimagesize()`.
- Maximum size limit: `10 MB` (`10240 KB`).
- Maximum dimension limit: `10,000 × 10,000 px`.

---

## F. Storage Disk / Config
- Defined in `config/media.php`: `disk = env('MEDIA_DISK', 'public')`.
- Safe example documented in `.env.example`: `MEDIA_DISK=public`.

---

## G. Stored Filename / Path Policy
- Physical path format: `media/YYYY/MM/<ulid>.<ext>`.
- Generated via `Str::ulid()`. Client original filename preserved in `media.file_name`.

---

## H. Metadata Extraction
- All columns (`size_bytes`, `width`, `height`, `mime_type`, `extension`, `uploaded_by`) derived server-side.

---

## I. Media Translation Editing
- Relational translations in `media_translations` (`alt_text`, `caption`).
- Vietnamese is canonical; English is optional.
- Updates preserve `MediaTranslation.id` in-place.

---

## J. Upload Transaction & Compensation Strategy
- Physical write to disk followed by `DB::transaction(...)`.
- On database write failure, physical file is immediately deleted from disk via compensating cleanup.

---

## K. Reference Inspector
- Encapsulated in `App\Services\Media\MediaReferenceInspector`.

---

## L. Seven-Reference Inventory
1. `services.hero_media_id`
2. `training_courses.hero_media_id`
3. `posts.hero_media_id`
4. `service_media.media_id`
5. `training_course_media.media_id`
6. `post_media.media_id`
7. `page_media.media_id`

---

## M. Reference-Safe Delete Behavior
- Hard deletion denied if referenced by any of the 7 relations (`DomainException`).
- Owning content is never automatically detached.

---

## N. Physical Delete Strategy
- Authoritative DB transaction deletion commits first; physical file cleanup follows.

---

## O. Bulk-Delete Policy
- Bulk permanent deletion is disabled to guarantee individual reference inspection.

---

## P. Authorization
- `MediaPolicy` gates all actions behind `$user->is_admin`.

---

## Q. Database Changes
- **New Migrations:** `0`
- **Schema Changes:** `NONE`

---

## R. Business Table Count
- **Expected:** `23`
- **Actual:** `23`

---

## S. Tests Added
- Exactly **20 new tests** in `tests/Feature/Admin/Media/`:
  - `MediaResourceTest` (4 tests)
  - `MediaUploadTest` (7 tests)
  - `MediaReferenceTest` (8 tests)
  - `MediaDeleteTest` (1 test)

---

## T. Total Regression Tests
- **Total Tests:** `175`
- **Total Assertions:** `522`
- **Failures:** `0`
- **Duration:** `5.22s`

---

## U. Migration Lifecycle
- `php artisan migrate:fresh`: **PASS**
- `php artisan migrate:refresh`: **PASS**

---

## V. Services, Training & Blog Regression
- All Phase 4, Phase 5, and Phase 6 tests remain 100% green.

---

## W. Route Inventory
- Media routes registered strictly under `/admin/...`:
  - `/admin/media`
  - `/admin/media/create`
  - `/admin/media/{record}/edit`
- Zero public Media routes.

---

## X. N+1 Review
- `MediaTable` eager loads `translations` and `uploader`.
- Reference checks use fast aggregate counts without loading heavy models into memory.

---

## Y. Pint / Build / Composer
- `php vendor/bin/pint --test`: **PASS** (0 violations).
- `npm run build`: **PASS** (Vite built in 512ms).
- `composer validate`: **PASS** (`./composer.json is valid`).
- `git diff --check`: **PASS** (0 issues).

---

## Z. Production DB Execution
- **Execution Status:** **NOT TESTED IN PHASE 7A** (SQLite local development only; production target: MySQL 8.0+ / MariaDB 10.4+).

---

## AA. Files Created
- `config/media.php`
- `app/Policies/MediaPolicy.php`
- `app/Services/Media/MediaReferenceInspector.php`
- `app/Services/Media/MediaUploadService.php`
- `app/Filament/Resources/Media/MediaResource.php`
- `app/Filament/Resources/Media/Schemas/MediaForm.php`
- `app/Filament/Resources/Media/Tables/MediaTable.php`
- `app/Filament/Resources/Media/Pages/ListMedia.php`
- `app/Filament/Resources/Media/Pages/CreateMedia.php`
- `app/Filament/Resources/Media/Pages/EditMedia.php`
- `tests/Feature/Admin/Media/MediaResourceTest.php`
- `tests/Feature/Admin/Media/MediaUploadTest.php`
- `tests/Feature/Admin/Media/MediaReferenceTest.php`
- `tests/Feature/Admin/Media/MediaDeleteTest.php`
- `docs/architecture/MEDIA_CMS_ARCHITECTURE.md`
- `docs/architecture/adr/ADR-017-media-storage-upload-and-deletion.md`
- `docs/audit/PHASE_7A_MEDIA_CMS_2026-08-27.md`

---

## AB. Files Modified
- `.env.example`
- `AGENTS.md`

---

## AC. Known Limitations
- Automatic image resizing, WebP conversion pipelines, and CDN distribution are deferred to later optimization phases.
- Public site media consumption is deferred to Phase 8–10.

---

## AD. Deferred Work Preserved
- **Phase 7B:** Pages CMS
- **Phase 7C:** Site Settings CMS / Phase 7 Closure
- **Phase 8:** Public Design System & Global Layout
- **Phase 9:** Homepage
- **Phase 10:** Public Content Pages
- **Phase 11:** Booking System
- **Phase 12:** Marketing Tracking & Attribution
- **Phase 13:** Technical SEO
- **Phase 14:** Performance / Accessibility / Security Hardening
- **Phase 15:** Full QA & Reference Fidelity
- **Phase 16:** Production Deployment
- **Phase 17:** Launch Verification

---

## AE. Readiness Declaration

```text
READY FOR PHASE 7A REVIEW
```


---

## AO. Final File-Integrity & Failure-Semantics Closure (Section 58 Verification)

1. **Exact Media Schema Column Names:**
   - Confirmed in database schema `2026_08_26_000001_create_media_table.php` and `docs/architecture/DATABASE_SCHEMA.md`:
     `disk`, `path`, `file_name`, `mime_type`, `extension`, `size_bytes`, `width`, `height`, `uploaded_by`, `created_at`, `updated_at`.
   - Zero column discrepancies exist.

2. **Authoritative MIME-Detection & Canonical Extension Pipeline:**
   - Client-provided MIME headers and extensions are strictly untrusted.
   - `MediaUploadService::inspectAndValidateImage()` inspects magic bytes via `getimagesize()` and `finfo`.
   - Detects actual supported image constant (`IMAGETYPE_JPEG`, `IMAGETYPE_PNG`, `IMAGETYPE_WEBP`).
   - Assigns canonical MIME (`image/jpeg`, `image/png`, `image/webp`) and canonical extension (`jpg`, `png`, `webp`).
   - Stored path is generated as `media/YYYY/MM/<ULID>.<canonical-ext>`.

3. **Mismatched Filename / Content Behavior:**
   - Real JPEG bytes named `.png` are detected as JPEG and stored as canonical `.jpg` with `image/jpeg`.
   - Real PNG bytes named `.jpg` are detected as PNG and stored as canonical `.png` with `image/png`.
   - Verified by test: `test_authoritative_content_detection_stores_canonical_extension_regardless_of_client_extension`.

4. **Fake / Corrupted / Script Rejection:**
   - Plain text disguised as image -> REJECTED (`InvalidArgumentException`).
   - PHP script disguised as image -> REJECTED (`InvalidArgumentException`).
   - HTML disguised as image -> REJECTED (`InvalidArgumentException`).
   - SVG files -> REJECTED (`InvalidArgumentException`).
   - Verified by test: `test_rejection_of_fake_images_and_scripts`.

5. **Path Traversal Resistance:**
   - Traversal patterns such as `../../evil.jpg`, `foo/bar.png`, `..\..\image.webp` cannot affect stored paths.
   - Stored paths strictly adhere to `media/YYYY/MM/<ULID>.<canonical-ext>`.
   - Client-facing `media.file_name` strips directory separators via `basename()`.
   - Verified by test: `test_stored_path_safety_prevents_path_traversal`.

6. **Upload DB-Failure Compensation Result:**
   - If database persistence fails after the file is stored, compensating cleanup immediately deletes the physical file from disk.
   - Verified by test: `test_compensation_cleans_up_stored_file_if_database_write_fails`.

7. **Storage-Write Failure Result:**
   - If writing to disk fails (`Storage::putFileAs` returns false), `DomainException` is thrown before any DB transaction occurs.

8. **Physical Delete Failure Semantics:**
   - Authoritative DB transaction commits first, ensuring database reference integrity.
   - Physical deletion is attempted after commit.
   - If physical deletion fails, the DB record remains deleted (never recreated), the error is logged without leaking credentials, and the service reports `physical_deleted => false`.
   - Verified by test: `test_physical_delete_failure_keeps_database_deleted_and_reports_cleanup_failure`.

9. **Seven-Reference Deletion Regression:**
   - Deletion is strictly denied when referenced as:
     1. `services.hero_media_id`
     2. `training_courses.hero_media_id`
     3. `posts.hero_media_id`
     4. `service_media.media_id`
     5. `training_course_media.media_id`
     6. `post_media.media_id`
     7. `page_media.media_id`
   - In all 7 cases, owning content is never auto-detached.

10. **Missing Physical File Admin UI Result:**
    - If a physical file is missing from disk, Media List and Edit pages render safely (HTTP 200) without crashing.
    - Database record is preserved and not deleted.
    - Verified by test: `test_missing_physical_file_does_not_crash_admin_pages_or_delete_database_record`.

11. **URL Generation Policy:**
    - Preview URLs use `Storage::disk($media->disk)->url($media->path)` or `ImageColumn` disk resolution, never hardcoded `/storage/...` assumptions.

12. **Normal Edit File-Replacement Boundary:**
    - Normal edit modifies strictly `vi.alt_text`, `vi.caption`, `en.alt_text`, `en.caption`.
    - Physical file, size, dimensions, path, and uploader remain identical.
    - Verified by test: `test_normal_edit_does_not_replace_physical_file_or_technical_metadata`.

13. **Bulk-Delete Policy:**
    - Bulk permanent delete remains disabled on `MediaTable` to enforce individual reference inspection.

14. **Final Regression Metrics:**
    - Database migrations added: `0`
    - Business tables: exactly `23`
    - Test suite: `176 tests, 555 assertions, 0 failures, 0 risky`
    - Pint code formatting: `php vendor/bin/pint --test` PASS (0 style violations)
    - Vite production build: `npm run build` PASS (built in 512ms)
    - Composer validation: `composer validate` PASS

---

## AP. Final Readiness Declaration

```text
READY FOR PHASE 7B
```
