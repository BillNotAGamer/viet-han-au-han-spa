# PAGES CMS & STATIC PAGE CONTENT ARCHITECTURE

**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Phase:** Phase 7B — Pages CMS  
**Status:** ACCEPTED (Architect-Approved)  
**Navigation Group:** `Nội dung`  
**Navigation Label:** `Trang` (Icon: `heroicon-o-document-text`, sort order: 3)  

---

## 1. Scope & Resource Structure

Phase 7B establishes the editorial content management system for logical static and structural pages of the website:
* **Resource:** `App\Filament\Resources\Pages\PageResource`
* **Domain Service:** `App\Services\Pages\PageWriter`
* **Authorization Policy:** `App\Policies\PagePolicy`

---

## 2. Page Key Semantics & Immutability

* `pages.key` is a unique, stable machine/application identifier (`UNIQUE(key)`).
* **Format & Normalization:**
  - Lowercase ASCII alphanumeric with hyphens or underscores: `/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/`.
  - Normalization trims whitespace and converts to lowercase before validation and storage.
  - Rejects spaces, uppercase accidental variants, non-ASCII characters, and path fragments (`/`, `..`).
* **Immutability Invariant:**
  - **Create:** Editable and mandatory.
  - **Edit:** Disabled in the UI and strictly preserved by `PageWriter::update`. The server-side domain writer ignores/preserves the existing key, preventing accidental route/template decoupling.
* **Primary Product Keys:**
  - Reserved logical keys for Việt Hàn Âu Hàn Spa include `home`, `about`, `contact`. Additional pages may be created intentionally by an authorized administrator.

---

## 3. Relational Translations & The Nullable Slug Policy

* **Relational Schema:** Localized fields are stored in `page_translations` (`title`, `slug`, `content`, `seo_title`, `seo_description`).
* **Vietnamese Requirement:** A Page requires an exact Vietnamese translation with a non-empty `title`.
* **Nullable Slug by Design:**
  - Unlike Services, Training, or Blog, `page_translations.slug` is **nullable** by approved schema design.
  - **Blank Slug Policy:** A blank slug is stored strictly as `NULL`.
  - **No Title Auto-Generation:** Slugs are NOT automatically generated from the title. The homepage legitimately retains `slug = null`.
  - **Multiple NULL Slugs:** Supported by database unique index semantics (`UNIQUE(locale, slug)` where NULL != NULL).
* **Non-Null Slug Uniqueness:**
  - Compound uniqueness per locale: `UNIQUE(locale, slug)`.
  - Identical non-null slugs within the same locale are rejected.
  - Identical non-null slugs across different locales (e.g. `/vi/about` and `/en/about`) are permitted.
  - Updates preserve existing translation primary keys (`PageTranslation.id`) in-place via `updateOrCreate`.
* **English Translation:** Completely optional; never fabricated when omitted.

---

## 4. Separation of Concepts

* **Page Key:** Machine identifier (`home`, `about`, `contact`) used by application logic and template routing. Untranslated.
* **Page Slug:** Optional localized URI segment (`gioi-thieu`, `lien-he`).
* **Canonical Public Routes:** Application-level routes managed in Phase 10 (e.g. `/`, `/en`, `/gioi-thieu`, `/en/about`). Phase 7B introduces zero public controllers or route endpoints.

---

## 5. Media Associations & The Media CMS Boundary

* Associated and ordered through the existing `page_media` pivot table.
* PageResource associates existing `Media` records uploaded via `MediaResource`. Zero ad-hoc file uploaders exist inside PageResource.
* Duplicate media selections are sanitized enforcing `UNIQUE(page_id, media_id)`.
* Pivot ordering persists accurately to `page_media.sort_order`.
* Detaching media deletes the pivot row only; physical `Media` records are never deleted.
* As `page_media` is one of the 7 approved reference relations, Media deletion remains denied while attached to a Page.

---

## 6. Page Lifecycle & Conservative Hard Delete

* Statuses: `ContentStatus::DRAFT`, `PUBLISHED`, `ARCHIVED`. (Pages have no `published_at` scheduling column).
* **Archive / Restore:** Instant transition `DRAFT/PUBLISHED -> ARCHIVED` and `ARCHIVED -> DRAFT`.
* **Conservative Hard Delete:** A Page may be hard-deleted **ONLY when `status = DRAFT`**. Deleting `PUBLISHED` or `ARCHIVED` pages throws `DomainException`.

---

## 7. Performance & Query Optimization

* `PagesTable` eager loads `translations` and counts `media` (`counts('media')`).
* All listing queries execute with constant O(1) query overhead.
