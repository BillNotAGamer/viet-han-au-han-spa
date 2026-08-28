# HOMEPAGE ARCHITECTURE

## 1. Executive Summary

This document establishes the architecture for the public Homepage of **Việt Hàn Âu Hàn Spa** at `/` (Vietnamese canonical) and `/en` (English secondary canonical).
The Homepage is a read-side public composition layer that curates published, exact-locale business domain entities (Pages, Services, Training Courses, and Journal Posts) into a responsive, accessible, and high-performance server-rendered interface.

---

## 2. Route and Controller Structure

### 2.1 Route Design (`routes/web.php`)
- **Vietnamese Canonical (`/`)**: Handled by `App\Http\Controllers\Public\HomeController@index` under `set.locale:vi` middleware. Named `vi.home`.
- **English Secondary Canonical (`/en`)**: Handled by `App\Http\Controllers\Public\HomeController@index` under prefix `en` and `set.locale:en` middleware. Named `en.home`.
- **Legacy / Redundant Redirect (`/vi`)**: Permanent 301 redirect to `/`.

### 2.2 Controller Responsibility
`App\Http\Controllers\Public\HomeController` is strictly a thin coordinator:
- It resolves the current application locale via `app()->getLocale()`.
- It delegates content assembly to `App\Services\PublicSite\HomepageContent`.
- It renders `resources/views/public/home.blade.php` with the assembled data array.
- It performs zero direct database or Eloquent queries.

---

## 3. Homepage Composition Service (`HomepageContent`)

All query, filtering, data normalization, and excerpt derivation logic resides in `App\Services\PublicSite\HomepageContent`:

### 3.1 Content Queries & Limits
1. **`home` Page**:
   - Query: `Page::where('key', 'home')->where('status', ContentStatus::PUBLISHED)->first()`.
   - Locale Resolution: Only the exact requested locale translation (`$page->translations->firstWhere('locale', $locale)`) is loaded.
   - If missing, the Homepage falls back to qualitative localized structural marketing copy defined in `lang/{locale}/home.php`. The `home` Page is never auto-seeded or required for rendering.
2. **Hero Media Convention**:
   - `Page` has an ordered `page_media` pivot table.
   - **Convention**: The first ordered media (`page_media.sort_order = 0`) attached to the published `home` Page acts as the Homepage hero visual.
   - Resolved via `App\Services\PublicSite\PublicMediaResolver`. If unassigned or the physical file is missing from disk, an elegant brand monogram CSS visual is rendered as fallback.
3. **Featured Services**:
   - Query: Up to 6 records with `status = ContentStatus::PUBLISHED`, `is_featured = true`, and having an exact translation in `$locale`.
   - Ordering: `sort_order ASC, updated_at DESC`.
   - Eager loading: `translations` (for requested locale), `heroMedia.translations`, and `category.translations`.
   - Category Label Policy: If the service's category has no translation in the requested locale, the category badge is omitted cleanly without leaking untranslated text.
4. **Featured Training Courses**:
   - Query: Up to 3 records with `status = ContentStatus::PUBLISHED`, `is_featured = true`, `published_at IS NOT NULL`, `published_at <= now()`, and having an exact translation in `$locale`.
   - Ordering: `sort_order ASC, published_at DESC`.
   - Tuition Display: Stored as integer VND in `tuition_fee`, formatted in presentation (`15.000.000 ₫`). Omitted if null or 0.
5. **Latest Journal Posts**:
   - Query: Up to 3 records with `status = ContentStatus::PUBLISHED`, `published_at IS NOT NULL`, `published_at <= now()`, and having an exact translation in `$locale`.
   - Ordering: `published_at DESC`.
   - Date Display: Formatted in `Asia/Ho_Chi_Minh` timezone (`d/m/Y`).

---

## 4. Exact-Locale Invariant & Translation Policy

In accordance with ADR-011 and ADR-012:
- Public Homepage content strictly requires exact-locale translations (`vi` or `en`).
- `translationOrFallback()` is **never** invoked for public presentation.
- If a Service, Course, or Post only has a Vietnamese translation, it is excluded from `/en` automatically via SQL `whereHas('translations', fn($q) => $q->where('locale', 'en'))`.
- English visitors never see untranslated Vietnamese content leaking into their view.

---

## 5. Safe Page Excerpt Policy

To prevent raw rich HTML or malicious scripts from executing in public Blade templates:
- `HomepageContent::deriveSafeExcerpt(?string $content, int $limit)` processes HTML content:
  1. `strip_tags()` removes all HTML tags (including `<script>`, `<style>`, `<iframe`, etc.).
  2. `html_entity_decode()` safely resolves HTML entities.
  3. `preg_replace('/\s+/u', ' ', ...)` normalizes multi-line spacing.
  4. Truncates safely using `mb_substr()` to the requested length.
- The derived excerpt is rendered with standard Blade escaping (`{{ $page['excerpt'] }}`).

---

## 6. Public Media Resolution (`PublicMediaResolver`)

- Uses Laravel's configured filesystem disk (`config('filesystems.media_disk', 'public')`).
- Accesses `Storage::disk($disk)->url($media->path)`.
- Checks `$disk->exists($media->path)` defensively: if a DB record exists but the underlying physical file was deleted or unmounted, it returns `null` rather than throwing an exception.
- Reads `alt_text` from the exact-locale `MediaTranslation`; returns `alt=""` if empty, preserving accessibility standards.

---

## 7. Phase 10 Boundary & Link Isolation

- **Phase 10 Scope**: Listing and detail pages for Services (`/dich-vu/{slug}`), Training (`/dao-tao-hoc-vien/{slug}`), and Journal (`/blog/{slug}`).
- **Phase 9 Invariant**: Rendered cards on the Homepage are semantic `<article>` cards that do not contain links to nonexistent Phase 10 detail routes.
- The hero primary CTA scrolls internally to `#featured-services`; secondary CTA scrolls to `#training`.
- The closing CTA scrolls to `#featured-services`.

---

## 8. Presentation & Accessibility Invariants

- Exactly one `<h1>` per page.
- Section headings use `<h2>`, and cards use `<h3>`.
- All headings enforce `overflow-wrap: break-word; word-break: normal; text-wrap: balance;`.
- No `word-break: break-all` or internal Vietnamese syllable fracturing.
- Zero Eloquent or direct DB calls in `resources/views/`.
