# BLOG CMS ARCHITECTURE & PUBLICATION LIFECYCLE

**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Phase:** Phase 6 — Blog CMS  
**Status:** ACCEPTED (Architect-Approved)  
**Navigation Group:** `Nội dung`  

---

## 1. Scope & Navigation Organization

Phase 6 implements complete editorial content management for the spa's blog through two Filament resources under the navigation group `Nội dung`:
1. `App\Filament\Resources\Posts\PostResource` (Label: `Blog`, Icon: `heroicon-o-newspaper`, sort order: 1)
2. `App\Filament\Resources\PostCategories\PostCategoryResource` (Label: `Danh mục Blog`, Icon: `heroicon-o-folder`, sort order: 2)

Complex multi-table persistence is decoupled from UI components into domain writer services:
* `App\Services\Blog\PostCategoryWriter`
* `App\Services\Blog\PostWriter`

---

## 2. Relational Translation Schema

* **Post Categories:**
  - Core settings stored in `post_categories` (`status`, `sort_order`).
  - Translations stored in `post_category_translations`:
    - **Tiếng Việt (Canonical / Required):** `name`, `slug` (auto-generated if blank), `description`, `seo_title`, `seo_description`.
    - **English (Secondary / Optional):** Localized counterparts; omitted translations are never fabricated.
* **Posts:**
  - Core settings stored in `posts` (`post_category_id`, `author_id`, `hero_media_id`, `status`, `is_featured`, `published_at`).
  - Translations stored in `post_translations`:
    - **Tiếng Việt (Canonical / Required):** `title`, `slug` (auto-generated if blank), `excerpt`, `content` (RichEditor), `seo_title`, `seo_description`.
    - **English (Secondary / Optional):** Localized counterparts.
* **Translation Stable-ID Preservation:** Updates synchronize in-place via `updateOrCreate` keyed by `(post_id, locale)` or `(post_category_id, locale)`, strictly preserving translation primary key IDs.

---

## 3. Localized Slug Policy

* Slugs are auto-generated from localized names/titles via `Str::slug()` when blank.
* Manual slug overrides are preserved across future title edits.
* Uniqueness is compound per locale:
  - `UNIQUE(post_category_id, locale)` and `UNIQUE(locale, slug)` on categories.
  - `UNIQUE(post_id, locale)` and `UNIQUE(locale, slug)` on posts.
* Identical slug text in different locales (e.g. `/vi/blog/spa` vs `/en/blog/spa`) is fully supported.
* Updates ignore the current translation row, preventing self-collision errors.

---

## 4. Author Semantics & Security

* `posts.author_id` references `users.id` (`nullable`, `ON DELETE SET NULL`).
* **Default Author Convention:** When creating a post, `author_id` automatically defaults to the currently authenticated admin user (`auth()->id()`) if not explicitly specified.
* Administrators may choose another existing user as the author.
* Deleting a user decouples the post cleanly (`author_id = null`) without destroying historical articles.
* Selecting an author does not alter admin roles or permissions.

---

## 5. Publication Lifecycle (`published_at`)

* `published_at` is treated as a UTC system timestamp.
* **Lifecycle Transitions:**
  - `DRAFT -> PUBLISHED` with `published_at = null`: sets current UTC timestamp (`now()`).
  - `DRAFT -> PUBLISHED` with future scheduled timestamp: preserves scheduled timestamp.
  - Editing an already published post: preserves existing `published_at`.
  - `PUBLISHED -> ARCHIVED`: preserves historical `published_at`.
  - `ARCHIVED -> DRAFT`: clears `published_at = null` to prevent accidental immediate republishing.

---

## 6. Archive & Conservative Hard-Delete Policy

* **Post Lifecycle:** Managed by `App\Enums\ContentStatus` (`DRAFT`, `PUBLISHED`, `ARCHIVED`).
* **Post Hard-Delete Invariant:** A `Post` may be hard-deleted **ONLY when `status = DRAFT`**.
  - `DRAFT` -> Hard delete permitted with cascade to translations and gallery pivot.
  - `PUBLISHED` -> Forbidden (`DomainException`).
  - `ARCHIVED` -> Forbidden (`DomainException`).
* **Category Delete Invariant:** A `PostCategory` containing associated posts cannot be deleted (`posts` foreign key `ON DELETE RESTRICT`). Attempting deletion throws `DomainException`. Empty categories can be deleted cleanly.

---

## 7. Media Association Boundary

* **Hero Media:** Selected from existing records in the `media` table (`posts.hero_media_id`).
* **Post Media Associations:** Associated and ordered via the `post_media` pivot table.
  - Duplicate selections of the same media item are sanitized, enforcing `UNIQUE(post_id, media_id)`.
  - Ordering persists accurately to `post_media.sort_order`.
  - Detaching an association removes the pivot row only; physical `media` records are never deleted.
* **Hard Phase Boundary:** Phase 6 is association-only. Media upload, file deletion, and image optimization remain deferred to Phase 7.

---

## 8. SEO Metadata vs Technical SEO

* Forms allow editing of `seo_title` and `seo_description`.
* Zero HTML meta tags, OpenGraph tags, sitemaps, canonical links, or hreflang attributes are generated in Phase 6 (deferred to Phase 13).

---

## 9. Performance & N+1 Query Elimination

* `PostCategoriesTable`: Eager loads `translations` and counts `posts`.
* `PostsTable`: Eager loads `translations`, `category.translations`, `author`, and `heroMedia`.
* All listings execute with constant O(1) query overhead.
