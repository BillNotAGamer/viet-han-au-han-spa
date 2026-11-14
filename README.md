# Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)

Production web platform and content management system for **Việt Hàn Âu Hàn Spa**, an operating wellness and therapeutic spa business in Ho Chi Minh City, Vietnam.

---

## 1. Overview

This repository houses the full-stack Laravel 13 application powering `viethanauhanspa.com`. Built from scratch with server-rendered Blade templates, a transactional domain service layer, and a Filament 5 administrative backend, the platform serves bilingual public traffic—Vietnamese as the canonical prefixless locale (`/`) and English under `/en`—while managing service offerings, academy training programs, editorial content, and customer reservation intake.

---

## 2. Key Features & Architecture

The application follows a strict unidirectional public request flow:

```text
Route -> Controller -> Domain Service -> Eloquent Model -> Blade View
```

Public Blade templates perform zero direct Eloquent or database queries; controllers delegate data preparation and SEO metadata composition to dedicated services under `app/Services/`.

### Multi-Step Booking Concierge Workflow
- **Dual Entry Points**: Customers can submit reservation requests via the dedicated localized booking routes (`/dat-lich`, `/en/booking`) or through an accessible Alpine.js Booking Concierge modal (`<x-public.booking-modal />`) available across public pages.
- **Transactional Request Intake (`BookingRequestCreator`)**:
  - Validates service eligibility against the active route locale so cross-locale or unpublished services cannot be booked.
  - Captures immutable snapshots of localized service name, price tier label, duration, and integer VND pricing at submission time.
  - Preserves raw customer phone input alongside canonical E.164 normalization (`+84...` for Vietnam numbers) and stores appointment dates/times in `Asia/Ho_Chi_Minh` wall-clock time.
  - Generates unique booking references (`BK-YYYYMMDD-XXXXXX`) and persists first-touch / latest-touch marketing attribution (`utm_*`, `gclid`, `gbraid`, `wbraid`, `fbclid`, `fbp`, `fbc`, `landing_page`, `referrer`) via `CaptureMarketingAttribution` middleware.
- **Post-Redirect-Get & Operational Sync**:
  - Uses PRG (`booking_status` and `booking_conversion` session flashes) so conversion tracking events fire at most once without leaking PII.
  - Enqueues durable outbox records (`BookingGoogleSheetsOutbox`) processed by the scheduled `bookings:sync-google-sheets` command (every minute with lease locking and retry backoff) and supports optional email reception alerts (`BookingNotificationService`).

### Service & Training Catalogs
- **Bilingual Relational Schema**: Core entities (`ServiceCategory`, `Service`, `ServicePrice`, `TrainingCourse`, `TrainingInquiry`, `Page`) separate untranslated structural/pricing fields from `*_translations` tables (`vi` required, `en` optional).
- **Strict Locale Isolation**: Public routes enforce exact-locale lookups (`vi` slugs do not resolve under `/en`, and Vietnamese content never silently falls back onto English URLs).
- **Transactional Domain Writers**: Multi-table updates, price-tier key preservation (`ServiceWriter`), and slug normalization run inside database transactions via dedicated writer services (`ServiceCategoryWriter`, `ServiceWriter`, `TrainingCourseWriter`, `PageWriter`).

### Wellness Journal CMS & Technical SEO
- **Editorial Publishing (`Post`, `PostCategory`)**: Supports `DRAFT`, `PUBLISHED`, and scheduled future `published_at` visibility rules, author attribution without exposing account-sensitive fields, and localized slug routing (`/blog/{slug}`, `/en/blog/{slug}`).
- **Media Library (`MediaUploadService`, `MediaReferenceInspector`)**: Restricts uploads to verified raster images (`image/jpeg`, `image/png`, `image/webp` validated via `getimagesize()`), stores files under randomized ULID paths (`media/YYYY/MM/<ulid>.<ext>`), and blocks deletion while referenced by any content hero or gallery pivot.
- **SEO & Sitemap (`SeoManager`, `SitemapController`)**: Generates deterministic metadata, canonical URLs stripped of campaign parameters, exact-locale `hreflang` pairs, JSON-LD structured data, and a dynamic `/sitemap.xml` limited to publicly eligible routes.

### Validation, Security & Admin Isolation
- **HTTP Hardening**: Global `SecurityHeaders` middleware (`X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`, and production HTTPS `Strict-Transport-Security`), configurable `TRUSTED_PROXIES` in `bootstrap/app.php`, CSRF protection, and per-IP rate limiting (`throttle:booking-submissions`, 5 requests/minute).
- **Admin Access Boundary**: Filament 5 panel mounted at `/admin`, isolated from public locale and attribution middleware, and gated by `User::canAccessPanel()` checking the non-mass-assignable `is_admin` flag.
- **Secret Hygiene**: `SiteSettingWriter` rejects keys containing secret-bearing patterns (passwords, tokens, private keys); Google Sheets service-account credentials are resolved at runtime from `GOOGLE_SHEETS_CREDENTIALS_FILE` outside `public/` and excluded from cached config.

### Custom Blade + Tailwind CSS + Vite Frontend Architecture
- **Design System**: Centralized `@theme` brand and semantic CSS tokens in `resources/css/app.css` paired with reusable Blade components (`resources/views/components/public/` and `v2/`).
- **Progressive Enhancement**: Alpine.js (`resources/js/app.js`) handles interactive UI states (mobile navigation, booking modal focus/keyboard trapping) alongside a lightweight `IntersectionObserver` scroll-reveal coordinator that respects `prefers-reduced-motion` and preserves native form PRG behavior.
- **Asset & Font Pipeline**: Vite 8 (`vite.config.js`) compiles CSS/JS bundles, eager-loads public image assets, and configures Bunny Fonts (`Montserrat` and `Cormorant Garamond` with `latin` and `vietnamese` subsets).

---

## 3. Tech Stack

| Layer | Technology |
| :--- | :--- |
| **Backend Framework** | Laravel `^13.17` |
| **Language Runtime** | PHP `^8.3` |
| **Admin Panel** | Filament `~5.0` |
| **Templating** | Laravel Blade Components |
| **Styling** | Tailwind CSS `^4.0` (`@tailwindcss/vite`) |
| **Client Interactivity** | Alpine.js `^3.16` |
| **Build Tooling** | Vite `^8.0` & `laravel-vite-plugin` `^3.1` |
| **Database** | MySQL 8.x / MariaDB 10.x (Production); SQLite (`:memory:` / local file) for dev & testing |
| **Integrations** | `google/apiclient` `^2.20` (Google Sheets booking outbox sync) |
| **Testing & Code Style** | PHPUnit `^12.5.12`, Laravel Pint `^1.27` |

---

## 4. Testing & Quality Assurance

The automated test suite runs against an in-memory SQLite database configured in `phpunit.xml`.

- **Verified Test Run**: **432 passing tests** (**2,957 assertions**), 0 failures, executed via:

```bash
php -d memory_limit=512M vendor/bin/phpunit
```

> **Note on PHP Memory Limit:** Exporting and rendering Filament Livewire tables in `BookingXlsxExportTest` requires more than PHP's default `128M` CLI memory limit. Running PHPUnit with `-d memory_limit=512M` ensures the full suite executes cleanly.

### Covered Flows & Test Suites
- **Public Flows (`tests/Feature/Public/`)**: Bilingual homepage rendering, service and training catalogs, blog listing/detail views, static pages, booking page & modal submission, PRG conversion dispatch, attribution capture, Google Sheets outbox durability & credential validation, and technical SEO (`hreflang`, canonicals, JSON-LD, `sitemap.xml`).
- **Admin & CMS (`tests/Feature/Admin/`)**: Filament `/admin` access control, registration lockouts, resource CRUD and transactional writers for Services, Price Tiers, Training Courses, Inquiries, Blog Posts, Pages, Media upload/reference protection, Site Settings type/cache/secret guards, and booking XLSX exports.
- **Localization & Database Integrity (`tests/Feature/Localization/`, `tests/Feature/Database/`)**: Canonical route resolution, `/vi` -> `/` 301 redirect, cross-locale slug isolation, UTF-8 integrity, foreign key delete guards, integer VND money persistence, and `Asia/Ho_Chi_Minh` appointment wall-clock persistence.
- **Security Hardening (`tests/Feature/Security/`)**: Baseline security headers, trusted proxy behavior, rate limiting, and production configuration checks.

---

## 5. Local Development Setup

### Prerequisites
- PHP `>= 8.3` with standard Laravel extensions (`mbstring`, `pdo_sqlite` / `pdo_mysql`, `gd` / `fileinfo`, `xml`, `zip`)
- Composer `>= 2.x`
- Node.js `>= 20.x` and npm

### Installation Steps

```bash
# 1. Clone the repository
git clone <repository-url> viet-han-au-han-spa
cd viet-han-au-han-spa

# 2. Install PHP and Node dependencies
composer install
npm install

# 3. Configure environment variables
cp .env.example .env
php artisan key:generate
```

4. **Review `.env` for local HTTP development**:
   - The default `.env.example` configures `DB_CONNECTION=sqlite` (create `database/database.sqlite` if using file-based SQLite, or configure `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` for MySQL/MariaDB).
   - `.env.example` sets `SESSION_SECURE_COOKIE=true` for HTTPS production deployments. When serving locally over plain HTTP (`http://localhost:8000`), set `SESSION_SECURE_COOKIE=false` in `.env` so CSRF tokens and admin sessions persist across requests.

```bash
# 5. Run database migrations and link public storage
touch database/database.sqlite   # Skip if using MySQL/MariaDB
php artisan migrate
php artisan storage:link

# 6. Build frontend assets (or run Vite dev server)
npm run build
# For hot-reload development:
# npm run dev

# 7. Start the local Laravel server
php artisan serve
```

### Verification Commands

```bash
php -d memory_limit=512M vendor/bin/phpunit
vendor/bin/pint --test
npm run build
```

---

## 6. Active Development Roadmap

The following engineering milestones are scoped for the next phase of active development:

- **Administrative Back-Office Dashboard for Staff Scheduling & Appointment Management**: Expand the Filament `/admin` workspace with therapist/room allocation views, daily appointment calendars, and conflict-aware status workflows beyond initial lead intake.
- **Automated Booking Confirmation Notifications & Reminder Workflows**: Build queued customer confirmation dispatches and scheduled pre-appointment reminder pipelines (email / messaging webhooks) triggered from booking lifecycle state transitions.
- **Service Package & Voucher Tracking Engine with Expanded Integration Tests**: Implement multi-session treatment package balances, prepaid voucher redemption ledgers, and end-to-end feature/integration tests covering issuance, redemption, and expiration rules.
