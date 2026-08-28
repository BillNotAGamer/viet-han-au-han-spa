# MEDIA CMS, FILE STORAGE & REFERENCE-SAFE DELETION ARCHITECTURE

**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Phase:** Phase 7A — Media CMS  
**Status:** ACCEPTED (Architect-Approved)  
**Navigation Label:** `Media` (Icon: `heroicon-o-photo`, sort order: 3)  

---

## 1. Scope & Storage Abstraction

Phase 7A provides the application's central media asset repository and reference-safe lifecycle management:
* **Storage Abstraction:** All runtime file persistence, retrieval, and deletion operations interact strictly through `Illuminate\Support\Facades\Storage::disk(...)`.
* **Configurable Media Disk:** Configured in `config/media.php` via `MEDIA_DISK=public` (defaulting to Laravel's standard `public` disk in local environments, portable to object storage without application code alterations).
* **Decoupled Architecture:** Media file operations, metadata extraction, validation, and deletion are encapsulated in:
  - `App\Services\Media\MediaUploadService`
  - `App\Services\Media\MediaReferenceInspector`

---

## 2. Supported File Types & Validation Policy

* **Supported Raster Formats:** Strictly raster images for web presentation:
  - `image/jpeg` (`.jpg`, `.jpeg`)
  - `image/png` (`.png`)
  - `image/webp` (`.webp`)
* **Strict Rejections:** All active or executable content types are strictly rejected:
  - SVG (`image/svg+xml` or files containing SVG tags) due to active XML/script execution risks.
  - Executables, PHP scripts, HTML, archives, and generic documents.
* **Server-Side Inspection:** File extensions and client MIME headers are untrusted. Verification uses `getimagesize()` to inspect true image headers, dimensions, and raster integrity.
* **Dimension & Size Limits:**
  - Maximum upload size: **10 MB** (`10240 KB`).
  - Maximum dimensions: **10,000 × 10,000 px** to protect server memory.

---

## 3. Stored Filename & Directory Convention

* User-controlled original filenames are never used as physical storage paths.
* **Storage Directory:** `media/YYYY/MM/`
* **Safe Stored Filename:** `Str::ulid() . '.' . $extension` (e.g. `media/2026/08/01J6C5...webp`).
* **Original Filename:** Preserved separately in `media.file_name` for administrative display.
* **Directory Traversal Prevention:** Random ULIDs prevent directory traversal (`../`) and filename collisions.

---

## 4. Metadata Derivation & Relational Translations

* **System-Derived Columns:** `disk`, `path`, `file_name`, `mime_type`, `extension`, `size_bytes`, `width`, `height`, `uploaded_by`.
  - Derived server-side from actual stored files and `auth()->id()`; never accepted from client form input.
* **Relational Translations (`media_translations`):**
  - **Tiếng Việt (Canonical):** `alt_text`, `caption`.
  - **English (Secondary / Optional):** `alt_text`, `caption`.
  - **Alt Text Policy:** Alt text is optional to support intentionally decorative assets; omitted translations are never fabricated.
  - **Stable IDs:** Updates synchronize in-place via `updateOrCreate(['media_id' => $id, 'locale' => $locale])`, preserving primary keys.

---

## 5. Upload Compensation Strategy

Storage operations and database transactions do not form a single native atomic transaction. To prevent orphan files:
1. Physical file is written to the configured disk.
2. Database record and translation rows are persisted inside `DB::transaction(...)`.
3. If an exception occurs during database persistence, the catch block triggers an immediate compensating cleanup: `Storage::disk($disk)->delete($storedPath)` before re-throwing the exception.

---

## 6. Complete Seven-Reference Model & Safe Deletion

Media assets can be referenced across exactly **7 approved application relationship categories**:
1. `services.hero_media_id` (Service Hero)
2. `training_courses.hero_media_id` (Training Course Hero)
3. `posts.hero_media_id` (Post Hero)
4. `service_media.media_id` (Service Gallery Association)
5. `training_course_media.media_id` (Training Course Gallery Association)
6. `post_media.media_id` (Post Gallery Association)
7. `page_media.media_id` (Page Gallery Association)

### Reference-Safe Deletion Rule
* **Rule:** A `Media` record may be hard-deleted **ONLY when ZERO references exist across all 7 categories**.
* **Integrity Guard:**
  - `MediaReferenceInspector::hasReferences($media)` checks all 7 relationship counts.
  - If references exist, `MediaUploadService::delete()` throws `DomainException`.
  - The Delete button in Filament UI is visible only when references count is 0.
  - Owning content is **never automatically detached**; administrators must remove associations from the owning content first.
* **Physical Cleanup Strategy:**
  1. Authoritative DB deletion commits inside `DB::transaction(...)`.
  2. Physical file deletion is attempted on `Storage::disk(...)` after commit.
  3. Database integrity is authoritative; a temporary filesystem orphan is safer than leaving a dangling database record pointing to a missing file.

---

## 7. Bulk Delete & Authorization Policy

* **Bulk Delete:** Disabled on `MediaTable` to prevent bypassing individual reference inspection.
* **Authorization:** `MediaPolicy` gates all CRUD access behind `$user->is_admin`.


---

## 8. Authoritative Content Inspection & Failure Semantics (Closure Addendum)

### Exact Schema Column Verification
The `media` table columns are verified against migration `2026_08_26_000001_create_media_table.php` and `DATABASE_SCHEMA.md`:
* `id` (`bigint unsigned auto_increment`)
* `disk` (`string(32)`)
* `path` (`string(500)`)
* `file_name` (`string(255)`)
* `mime_type` (`string(100)`)
* `extension` (`string(16)`)
* `size_bytes` (`unsignedBigInteger`)
* `width` (`unsignedInteger nullable`)
* `height` (`unsignedInteger nullable`)
* `uploaded_by` (`foreignId nullable -> users.id`)
* `created_at`, `updated_at` (`timestamps`)

### Server-Derived File Type Pipeline
1. Client extension and client MIME headers are untrusted.
2. File bytes are inspected via `getimagesize()` and `finfo`.
3. Only raster formats `IMAGETYPE_JPEG`, `IMAGETYPE_PNG`, `IMAGETYPE_WEBP` are permitted.
4. Canonical MIME and extension are derived strictly from byte headers:
   - JPEG: `image/jpeg`, `.jpg`
   - PNG: `image/png`, `.png`
   - WebP: `image/webp`, `.webp`
5. Mismatched client filenames (e.g. real JPEG bytes named `.png`) are canonically detected and stored with their true `.jpg` extension.
6. Non-image files, PHP/HTML scripts disguised as images, and SVGs are rejected immediately.

### Physical Delete Failure Semantics
1. Verification of 0 references across all 7 categories (`services.hero_media_id`, `training_courses.hero_media_id`, `posts.hero_media_id`, `service_media`, `training_course_media`, `post_media`, `page_media`).
2. Database record and translation rows are deleted inside `DB::transaction(...)`.
3. After DB commit, physical storage deletion is attempted.
4. If physical deletion fails:
   - Database record remains permanently deleted (no rollback or auto-recreation).
   - Warning/error is logged containing only disk and path (zero credentials).
   - Service returns `physical_deleted => false`, and Filament surfaces an administrative warning notification.
