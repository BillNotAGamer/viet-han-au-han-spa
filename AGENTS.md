# AGENTS.md — PROJECT GOVERNANCE & ENGINEERING RULES

**Project:** Việt Hàn Âu Hàn Spa  
**Production Domain:** `viethanauhanspa.com`  
**Repository Root:** `F:\Coding\Web development\Viet Han Spa`  

This document defines the permanent, non-negotiable engineering rules, architectural constraints, security mandates, and quality standards for all developers and AI agents working on this codebase.

---

## 1. Technology Stack (Locked)

* **Backend Framework:** Laravel 13.x
* **Runtime:** PHP 8.3+
* **Admin Panel:** Filament 5.x (Panel Builder)
* **Frontend View Layer:** Laravel Blade Templates
* **Frontend Styling:** Tailwind CSS 4.x
* **Frontend Interactivity:** Alpine.js (via Vite)
* **Build Tooling:** Vite
* **Database (Production):** MySQL 8.x / MariaDB 10.x
* **Database (Local Development):** SQLite (temporary dev/test only) or MySQL/MariaDB

### Forbidden Framework & Architecture Changes
Do **NOT** introduce or install any of the following without explicit architectural approval:
* **Frontend Frameworks:** React, Vue.js, Next.js, Nuxt.js, Angular, Svelte.
* **SPA Adapters:** Inertia.js (Public site is server-rendered Blade + Alpine.js).
* **Legacy Libraries:** jQuery, Bootstrap, Semantic UI.
* **Architecture Deviations:** Microservices, independent REST/GraphQL API for public frontend, decoupled SPA apps.

---

## 2. Public Site Architecture

The public site follows a strict, single-direction flow:

$$\text{Route} \longrightarrow \text{Controller} \longrightarrow \text{Domain Service} \longrightarrow \text{Eloquent Model} \longrightarrow \text{Blade View}$$

### Rules:
1. **Thin Controllers:** Controllers only handle HTTP orchestration: extracting validated request input, invoking domain services, and returning Blade views or redirects.
2. **Form Requests:** All non-trivial form submissions, booking requests, and mutations must use dedicated Laravel `FormRequest` classes with robust validation rules.
3. **Domain Services:** All business logic, lead attribution capture, notification dispatching, and calculations reside in `App\Services` or domain-specific action classes.
4. **Clean Blade Views:**
   * Blade templates must **never** execute database queries (e.g. `Model::all()`, `DB::table()`, or lazy loading queries).
   * Blade templates must **never** execute raw business logic.
   * Views receive prepared ViewModels or clean DTOs/Eloquent instances from controllers.

---

## 3. Filament Admin Panel Architecture

1. **Filament 5 Standard CRUD:** Filament Resources, Pages, and Forms may bind directly to Eloquent models for standard management.
2. **Complex / Multi-Step Operations:** Any non-trivial business logic (e.g., booking confirmation triggers, notification emails, customer SMS/Zalo webhooks, batch updates) must be delegated to the shared `App\Services` domain services to prevent code duplication between Admin and Public layers.
3. **Route Prefix:** The administrative interface is mounted at `/admin` and configured via `app/Providers/Filament/AdminPanelProvider.php`.
4. **No Hardcoded Credentials:** Never commit default or seed passwords in source control.

---

## 4. Database & Persistence Governance

1. **Strict Migration Policy:** Every single database schema addition, modification, index, or foreign key constraint must be created via a dedicated Laravel migration.
2. **No Manual Schema Mutation:** Never execute manual SQL DDL queries directly against production databases.
3. **Referential Integrity & Indexing:** Define explicit foreign keys with cascade/restrict rules and add composite indexes to high-frequency query columns (e.g., slugs, published status, locale, booking dates).
4. **Media Storage:** **Never** store image or video binary blobs in the database. Store files on the filesystem (`storage/app/public`) or S3-compatible object storage, storing only file paths and metadata in the database.

---

## 5. Security & Data Protection

1. **Zero Secrets in Git:** Never commit `.env` files, API keys, webhook secrets, database credentials, or private certificates.
2. **Server-Side Validation:** Never rely solely on client-side HTML5/JavaScript validation. All incoming requests must be strictly validated server-side.
3. **CSRF & Rate Limiting:** All public forms (especially booking requests and contact inquiries) must have CSRF protection (`@csrf`) and Laravel Rate Limiting middleware applied.
4. **Upload Security:** All file uploads must be strictly validated by MIME type, file extension, and file size limit.
5. **Authorization:** Admin endpoints must be secured by Filament's authentication and authorization policies.

---

## 6. Testing & Quality Assurance

1. **Automated Testing:** Critical business paths (Booking submissions, localized routes, service catalog display, admin permission gates) must have automated Pest/PHPUnit tests.
2. **Pre-Completion Checks:** Before declaring any roadmap phase complete, run and pass:
   ```bash
   php artisan test
   npm run build
   vendor/bin/pint --test   # (when Pint is installed)
   ```
3. **No Fabricated Results:** Never report a test or command as `PASS` unless it has actually been executed and succeeded with zero exit code.

---

## 7. Package Management

Do **NOT** add arbitrary Composer or npm packages. Any new dependency must:
1. Solve a real, concrete requirement not reasonably fulfilled by Laravel/Filament built-ins.
2. Be documented in the respective Phase Audit report with rationale.

---

## 8. Documentation & Phase Audits

* All architectural plans and phase records must be stored under `docs/`.
* Every phase completion or major milestone must produce an audit report under `docs/audit/` following the naming convention:
  `docs/audit/PHASE_{X}_{NAME}_{YYYY-MM-DD}.md`

---

## 9. Domain & Data Governance Standards

1. **Phone Normalization:** Customer phone numbers must be preserved raw in phone and normalized to canonical E.164 (+84901234567) in phone_normalized for search and deduplication.
2. **Timezone Policy:** System instant timestamps (created_at, updated_at, published_at, deleted_at, lifecycle timestamps) must be stored in UTC and displayed in Asia/Ho_Chi_Minh. Booking appointment fields (preferred_date, preferred_time) represent local Vietnam business wall-clock time and must never be instant-shifted.
3. **Site Settings Security:** site_settings.is_public defaults to alse. Secret credentials, API tokens (e.g. Meta CAPI tokens), and private keys must **never** be stored in database settings; they reside in .env.
4. **Media Deletion Safety:** A media file must never be deleted from storage if it is referenced by any content hero or gallery pivot.
5. **Content Lifecycle:** Archiving content (status = 'ARCHIVED') is mandatory over hard deletion whenever relational business history exists.

6. **Bilingual Localization Standards:**
   - Default canonical locale is `vi` (no URL prefix). English uses `/en` prefix.
   - There is no `/vi` canonical route tree (`/vi` issues a 301 redirect to `/`).
   - URL is the sole source of truth for public locale resolution. Do not implement automatic browser/IP redirects.
   - Public localized content routes require exact translations in the requested locale; missing secondary translations must not silently render default language content under secondary URLs.
   - Static interface strings reside in `lang/{locale}/`; business and editorial content resides in database translation tables.
   - Public localization middleware must never alter or prefix Filament `/admin` routes.

7. **Administrative & Access Control Standards:**
   - Filament administrative panel is mounted at `/admin` and isolated from public locale prefixes.
   - Panel admission is strictly gated by `users.is_admin` boolean via `FilamentUser::canAccessPanel()`.
   - `is_admin` defaults to `false` and must never be exposed via generic mass assignment.
   - Public user and administrator registration endpoints are prohibited.
   - Hardcoded admin credentials and seeded default passwords in source code or documentation are strictly forbidden.
   - Granular resource permissions in later CMS phases must use native Laravel Policies without heavy third-party RBAC packages.

8. **Services CMS & Persistence Standards:**
   - Multi-table Service and Category writes must execute inside database transactions via dedicated writer services.
   - Vietnamese translation is required for CMS records; English translation is optional and must not be fabricated.
   - Price tiers must preserve stable primary keys (`service_prices.id`) during updates; referenced tiers are deactivated (`is_active = false`) instead of hard-deleted.
   - `price_amount` is strictly integer VND; `duration_minutes` is strictly positive integer minutes.
   - Service categories with services and services with bookings cannot be hard-deleted.
   - Phase 4 media behavior is association-only; file uploads and physical media deletions are strictly forbidden until Phase 7.
   - Public Service routes require exact requested-locale translations.
   - Service detail slugs are locale-specific; Vietnamese slugs must not resolve under `/en`.
   - No Vietnamese Service fallback may render under English public URLs.
   - Only `PUBLISHED` Services are public; `DRAFT` and `ARCHIVED` detail requests return 404.
   - Public Services Blade templates must perform no direct database or Eloquent queries.
   - Missing Service media files must not crash public listing or detail pages.

9. **Training CMS & Lead Governance Standards:**
   - Training courses use relational translation rows with required Vietnamese and optional English.
   - Course multi-table writes and state changes must execute inside transactions via `TrainingCourseWriter`.
   - Training courses referenced by inquiries cannot be hard-deleted; retire them using `ARCHIVED` status.
   - `tuition_fee` is strictly stored as unsigned integer VND.
   - Customer submission and attribution fields in `TrainingInquiry` are immutable lead records; do not expose generic edit forms.
   - Training inquiry status transitions must use `TrainingInquiryWorkflow` with atomic lifecycle timestamps.
   - Permanent force deletion of training inquiries is strictly prohibited; soft delete is for recovery only.
   - Phase 5 media behavior is association-only; file uploads and deletions remain deferred to Phase 7.
   - Public Training routes require exact requested-locale translations.
   - Training detail slugs are locale-specific; Vietnamese slugs must not resolve under `/en`.
   - No Vietnamese Training fallback may render under English public URLs.
   - Only publicly eligible TrainingCourse records render publicly.
   - Public Training Blade templates must perform no direct database or Eloquent queries.
   - Missing Training media files must not crash public listing or detail pages.
   - Do not invent instructor, schedule, certificate, employment, or class-date data absent from schema.

10. **Blog CMS Governance Standards:**
   - Blog posts and categories use relational translation rows with required Vietnamese and optional English.
   - All Blog writes must execute inside database transactions via `PostCategoryWriter` and `PostWriter`.
   - PostTranslation IDs must remain stable across normal updates.
   - Slugs are compound locale-unique (`UNIQUE(locale, slug)`); blank slugs auto-generate from localized title.
   - Post author defaults to current authenticated admin when omitted; author deletion sets `author_id = null`.
   - `published_at` is UTC system time and follows publication lifecycle rules.
   - Posts can be hard-deleted only in `DRAFT` status; Categories containing posts cannot be deleted.
   - Phase 6 media behavior is association-only; file uploads remain deferred to Phase 7.
   - Public blog pages and controllers must not be created before Phase 10.
   - Public Blog routes require exact requested-locale translations.
   - Post detail slugs are locale-specific; Vietnamese slugs must not resolve under `/en`.
   - Future-scheduled Posts are not public.
   - No Vietnamese Blog fallback may render under English public URLs.
   - Public Blog Blade templates must perform no direct database or Eloquent queries.
   - Blog author public output must not expose account-sensitive fields.
   - RichEditor content must use a verified safe renderer or escaped fallback.

11. **Media CMS & Storage Governance Standards:**
   - All media storage operations must interact through `Storage::disk(config('media.disk'))`.
   - Media uploads support raster images only (JPEG, PNG, WebP); SVG and executable content are strictly rejected.
   - Client filenames and MIME headers are untrusted; metadata is verified server-side via `getimagesize()`.
   - Stored filenames use random ULIDs (`media/YYYY/MM/<ulid>.<ext>`) to prevent directory traversal and collisions.
   - Media translations (`alt_text`, `caption`) update in-place, preserving stable translation IDs.
   - Media deletion is strictly prohibited when referenced by any of the 7 approved relationships (Service hero/gallery, Training hero/gallery, Post hero/gallery, Page gallery).
   - Media is never automatically detached from content upon deletion; references must be removed explicitly.
   - Database deletion is authoritative; physical file cleanup occurs only after DB commit.
   - Embedded domain resources (Services, Training, Blog, Pages) select existing Media only; they must not embed ad-hoc file uploaders.

12. **Pages CMS & Key Governance Standards:**
   - `pages.key` is a stable, untranslated machine identifier (`^[a-z0-9]+(?:[-_][a-z0-9]+)*$`).
   - Page keys are strictly immutable across normal edit workflows.
   - `page_translations.slug` is nullable by design; blank slugs persist as `NULL` and are NOT auto-generated.
   - Page translations remain relational with required Vietnamese title and optional English.
   - Page writes execute inside database transactions via `PageWriter`, preserving translation IDs.
   - Pages can be hard-deleted only in `DRAFT` status; `PUBLISHED` and `ARCHIVED` pages must not be destroyed.
   - PageResource associates existing Media only; uploads remain owned by MediaResource.
   - Public page routing remains Phase 10.

13. **Site Settings & Global Configuration Standards:**
   - `site_settings.key` is a stable, untranslated machine identifier (`^[a-z0-9]+(?:[._-][a-z0-9]+)*$`).
   - Setting keys are strictly immutable across normal edit workflows.
   - Site Settings NEVER store secrets (passwords, tokens, API secret keys belong in `.env`).
   - `is_public` defaults to `false`; public reads must explicitly query `is_public = true`.
   - Application access must go through `SiteSettings` typed service with cache integration.
   - Mutations via `SiteSettingWriter` invalidate relevant cache keys immediately after commit.
   - No generic public settings HTTP endpoints.
   - Phase 7 completes the backend CMS foundation only; public website rendering begins in Phase 8.

14. **Public Frontend & Design System Standards:**
   - Public pages use the shared public layout (`layouts/public.blade.php`) with single `#main-content`.
   - Brand styling uses centralized tokens in `resources/css/app.css` (`--color-brand-*`).
   - Public Blade templates must NEVER query Eloquent or the database directly.
   - Public Site Settings access uses `SiteSettings::getPublic(...)` with graceful fallbacks.
   - Navigation labels come from localization files (`lang/{locale}/navigation.php`).
   - Language switching adheres to `/` <-> `/en` mapping; never generate `/vi`.
   - Mobile interactivity is limited to Alpine.js; no secondary JS frameworks.
   - Global components remain domain-content agnostic; homepage is implemented in Phase 9.

### Phase 9 Homepage Invariants
- **Exact-Locale Content**: Public Homepage content requires exact requested locale; Vietnamese content never leaks or falls back to `/en`.
- **Publication & Scheduling**: Only `PUBLISHED` records are queried; future scheduled `published_at > now()` courses and posts must never leak publicly.
- **Home Page Record**: The `home` Page key is optional editorial content; never auto-create or seed it during GET requests.
- **Hero Media Convention**: The first ordered media (`page_media.sort_order = 0`) attached to the `home` Page acts as the Homepage hero visual.
- **Safe Content Excerpt**: Rich HTML from Page records is stripped of tags and normalized to plain text before presentation.
- **Blade Query Isolation**: Public Blade templates must execute zero direct Eloquent or DB queries; all composition belongs in `HomepageContent`.
- **Phase 10 Boundary**: Homepage cards are preview-only semantic `<article>` elements unless a later public-content subphase has implemented the target route with exact-locale routing. They must never link to unimplemented detail routes.
- **Evidence-Driven**: No fake business metrics, fabricated testimonials, or unverified claims.
