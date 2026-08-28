# ADR-017: Media Storage, Upload Validation, and Reference-Safe Deletion

**Status:** ACCEPTED  
**Context:**  
Phase 7A requires implementing the central media asset management for Việt Hàn Âu Hàn Spa, including file storage, metadata extraction, bilingual alt text and captions, and reference-safe deletion across Services, Training Courses, Blog Posts, and Pages. We evaluated:
1. Using third-party packages such as `spatie/laravel-medialibrary` or cloud SDKs.
2. Cascading/auto-detaching media from content upon deletion.
3. Building a lightweight, focused domain layer using native Laravel Filesystem, deep raster image inspection, explicit 7-reference validation, and compensation on failure.

**Decision:**  
Adopt **Native Laravel Filesystem Abstraction, Deep Server-Side Validation, and Reference-Safe Deletion**:
- Storage interacts exclusively through `Storage::disk(config('media.disk'))`.
- Supported formats are strictly raster images: JPEG, PNG, WebP (max 10MB, max 10,000px). SVG and executables are rejected.
- Physical stored filenames use random ULIDs (`Str::ulid() . '.' . $ext`) inside `media/YYYY/MM/`.
- All media metadata (`size_bytes`, `width`, `height`, `mime_type`, `uploaded_by`) is system-derived.
- Deletion is prohibited if any reference exists across all 7 relationship categories (`services.hero_media_id`, `training_courses.hero_media_id`, `posts.hero_media_id`, `service_media`, `training_course_media`, `post_media`, `page_media`).
- Physical file cleanup occurs after authoritative database deletion commits.
- Upload failure triggers compensating file deletion.

**Consequences:**  
- **Positives:**
  - Zero external package dependencies.
  - 100% portable storage (local disk in dev, S3/R2 in production).
  - Absolute protection of active website content against broken media links.
  - Zero orphan files on database failures.
  - Zero database schema changes required (table count remains 23).
- **Negatives:**
  - Administrators must explicitly detach media from services/posts before deleting the image.


## Closure Verification
- Confirmed column names: `disk`, `path`, `file_name`, `mime_type`, `extension`, `size_bytes`, `width`, `height`, `uploaded_by`.
- Authoritative content inspection via `getimagesize()` overrides client-supplied extensions.
- Physical delete failure after DB commit does not recreate DB rows and is explicitly surfaced.
