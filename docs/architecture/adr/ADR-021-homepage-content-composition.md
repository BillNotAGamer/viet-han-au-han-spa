# ADR-021: Homepage Content Composition and Exact-Locale Read Policy

## Status
ACCEPTED (2026-08-28)

## Context
Phase 9 introduces the public-facing Homepage for Việt Hàn Âu Hàn Spa at `/` (Vietnamese) and `/en` (English).
The backend CMS (Phases 3–7) manages domain records across multiple business tables: Pages, Services, Service Categories, Training Courses, Blog Posts, and Media.
To display these entities on the Homepage, we must define:
1. How public queries are executed and isolated from Blade templates.
2. How translations and fallbacks are handled between `/` and `/en`.
3. How publication status and scheduled future dates are enforced.
4. How hero visuals and rich content are extracted from the optional `home` Page record.
5. How to respect boundaries with Phase 10 without producing broken (404) detail links.

## Decision
1. **Dedicated Composition Service**:
   All public Homepage querying and data transformation is encapsulated in `App\Services\PublicSite\HomepageContent`. The controller `HomeController` remains thin and delegates completely to this service. No direct Eloquent or DB queries are permitted in Blade templates.
2. **Strict Exact-Locale Translation Policy**:
   In accordance with ADR-011 and ADR-012, public Homepage content queries require exact requested-locale translations. English pages (`/en`) must never fall back to Vietnamese content for business entities. If an exact translation is absent, the entity is omitted from that locale.
3. **Publication & Scheduling Filtering**:
   Records must be in `ContentStatus::PUBLISHED`. For `TrainingCourse` and `Post`, `published_at` must be non-null and `<= now()`. Scheduled future items must not leak.
4. **Hero Visual Convention**:
   The `home` Page record (key `home`) is optional. If present and published, its first ordered media attachment (`sort_order = 0`) is used as the Homepage hero image. Missing media or missing physical files gracefully fall back to an elegant CSS brand monogram.
5. **Safe Page Excerpt**:
   Rich HTML content from `Page` is stripped of tags, normalized, truncated, and escaped in presentation to eliminate script injection risks.
6. **No Premature Detail Links**:
   Service, Training, and Blog cards are semantic `<article>` preview cards without detail links, honoring the Phase 10 route boundary. Primary CTAs jump to internal `#featured-services` or `#training` anchors.

## Consequences
### Positive
- Strict separation of concerns: controller and templates remain clean and declarative.
- Complete bilingual integrity: zero Vietnamese content leakage on English canonical pages.
- Resilience: the Homepage renders gracefully even on an empty database or with missing media files.
- Zero broken links: visitors are never directed to 404 pages.
- Performance: bounded queries (max 6 services, 3 courses, 3 posts) with eager loading eliminate N+1 issues.

### Negative / Trade-offs
- Visitors cannot view individual service or course details until Phase 10 introduces public detail routes.
- Category badges are hidden if the category itself lacks a translation in the requested locale, even if the service is translated.
