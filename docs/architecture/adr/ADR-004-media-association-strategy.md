# ADR-004: Native Media Registry with Explicit Foreign Keys and Pivots

**Status:** ACCEPTED  
**Context:**  
Entities require cover imagery and multi-image galleries (e.g. treatment room photos, certification showcases, blog editorial photos, page banners). We evaluated:
1. Generic polymorphic `mediables` table (e.g., Spatie MediaLibrary pattern).
2. Native `media` table with explicit foreign keys (`hero_media_id`) and explicit pivot tables (`service_media`, `training_course_media`, `post_media`, `page_media`).

**Decision:**  
Implement a **Native Media Registry** with explicit foreign keys (`hero_media_id` on `services`, `training_courses`, `posts` with `ON DELETE RESTRICT`) and explicit pivot tables (`service_media`, `training_course_media`, `post_media`, `page_media` with `media_id ON DELETE RESTRICT`).

**Consequences:**  
- **Positives:**
  - Strict database-level foreign key integrity; prevents physical deletion of media in active use.
  - No polymorphic string lookups (`mediable_type = 'App\Models\Service'`).
  - Simple Filament repeater/picker integration without external package dependencies.
- **Negatives:**
  - Requires 4 dedicated pivot tables for gallery relationships.
