# PHASE 2 LOCALIZATION FOUNDATION AUDIT REPORT — 2026-08-26

**Date:** 2026-08-26  
**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Workspace Root:** `F:\Coding\Web development\Viet Han Spa`  
**Phase:** Phase 2 — Bilingual Localization Foundation  
**Phase Readiness:** **READY FOR PHASE 3**  

---

## A. Starting State
- **Phase 0:** CLOSED (`PHP 8.4.24`, `Laravel 13.26.1`, `Filament 5.7.6`, `Node 24.19.0`, `Vite 8.2.2`).
- **Phase 1 (1A & 1B):** COMPLETE (`23 business tables`, `19 business models`, `19 factories`, `4 PHP backed enums`).
- **Pre-Phase 2 Verification:** 40 tests, 113 assertions, 0 failures.

---

## B. Locale Configuration (`config/localization.php`)
- **Supported Locales:** `vi` (Vietnamese, default), `en` (English, secondary).
- **Default Locale:** `vi`
- **Fallback Locale:** `vi`
- **Prefix Configuration:** `vi` => `''` (no prefix), `en` => `'en'` (`/en` prefix).
- **App Defaults:** `config/app.php` and `.env.example` configured to `APP_LOCALE=vi`, `APP_FALLBACK_LOCALE=vi`, `APP_FAKER_LOCALE=vi_VN`.

---

## C. Canonical URL Architecture
- **Vietnamese (Default Canonical):** No prefix (`/`, `/dich-vu`, `/dao-tao-hoc-vien`, `/blog`, `/gioi-thieu`, `/lien-he`).
- **English (Secondary Canonical):** `/en` prefix (`/en`, `/en/services`, `/en/training`, `/en/blog`, `/en/about`, `/en/contact`).
- **No Duplicate Route Tree:** `/vi` issues an HTTP 301 Permanent Redirect to `/`.
- **URL as Deterministic Truth:** No automatic browser-language or GeoIP redirects; URL is the sole authority for public content locale resolution ([ADR-011](file:///F:/Coding/Web%20development/Viet%20Han%20Spa/docs/architecture/adr/ADR-011-localized-url-and-locale-resolution.md)).

---

## D. Middleware Implementation
- **Class:** `App\Http\Middleware\SetLocale`
- **Registration:** Aliased as `'set.locale'` in `bootstrap/app.php`.
- **Security & Integrity:** Validates locale against `Localization::supportedLocaleKeys()`. Unsupported or malicious input (e.g. `fr`, `../../`, `<script>`) throws `NotFoundHttpException` (404). Zero database queries, zero browser guessing.

---

## E. Route Inventory (`php artisan route:list`)
- `GET|HEAD /` -> `vi.home` (locale: `vi`)
- `GET|HEAD /en` -> `en.home` (locale: `en`)
- `GET|HEAD /vi` -> 301 Redirect to `/`
- `GET|HEAD /admin` -> `filament.admin.pages.dashboard` (Completely isolated, no locale prefix).

---

## F. Static Translation Files (`lang/`)
- `lang/vi/navigation.php` & `lang/en/navigation.php`: Core navigation labels (`Home` / `Trang chủ`, `Services` / `Dịch vụ`, `Academy` / `Đào tạo học viên`, `Blog`, `About` / `Giới thiệu`, `Contact` / `Liên hệ`, `Book Appointment` / `Đặt lịch`).
- `lang/vi/common.php` & `lang/en/common.php`: Common UI labels, brand name, development notices, and language switcher metadata.
- **Strict Separation Rule:** UI interface strings live in PHP language files; all business/editorial content resides strictly in database translation tables.

---

## G. Translation Model Foundation (`App\Models\Concerns\HasTranslations`)
- Implemented across all 8 translatable business models (`ServiceCategory`, `Service`, `ServicePrice`, `TrainingCourse`, `PostCategory`, `Post`, `Page`, `Media`).
- **Exact Translation:** `$model->translationFor(string $locale)` returns the exact translation record or `null`. Reuses eager-loaded `translations` collection to eliminate N+1 queries.
- **Explicit Fallback:** `$model->translationOrFallback(string $locale, ?string $fallback = null)` returns the requested translation or falls back to `vi` strictly for non-routing application contexts.
- **Non-Masquerading Policy:** Public content routes require exact translations; missing secondary translations never silently render default language content under secondary canonical URLs ([ADR-012](file:///F:/Coding/Web%20development/Viet%20Han%20Spa/docs/architecture/adr/ADR-012-translation-resolution-and-fallback.md)).

---

## H. Localized Slug Lookup Foundation & Column Security
- **Explicit Slug Query Scope:** `scopeWhereSlug(Builder $query, string $locale, string $slug)` constructs exact `whereHas('translations', ...)` matching on `locale` and `slug`.
- **API Safety & Sanitization:** Generic translation column lookup taking arbitrary column strings (`scopeWhereTranslation`) was removed to prevent arbitrary SQL column identifier injection.
- Enforces compound index `UNIQUE (locale, slug)` lookups without raw SQL.

---

## I. Language Switch Foundation (`App\Support\Localization`)
- `Localization::switchLocaleUrl(string $targetLocale, ?string $currentRouteName, array $parameters)` safely maps named route pairs (e.g. `vi.home` <-> `en.home`).
- Fallback safe target URL generation with zero open redirect vulnerability.

---

## J. Database Changes
- **New Business Migrations:** `0`
- **Database Schema Changes:** `NONE`
- **Business Table Count:** Exactly **23 tables** (Unchanged from Phase 1).

---

## K. Automated Test Suite (15 Tests Added, 55 Total Tests)
1. `Tests\Feature\Localization\RouteLocaleTest`: Tests `/` (vi, 200), `/en` (en, 200), and `/vi` (301 redirect).
2. `Tests\Feature\Localization\UnsupportedLocaleTest`: Tests rejection of unsupported locales and 404 behavior.
3. `Tests\Feature\Localization\LanguageSwitchTest`: Tests bidirectional language switcher URL mapping.
4. `Tests\Feature\Localization\TranslatableModelTest`: Tests exact translation, missing translation null return, explicit fallback, and eager-loading N+1 query elimination.
5. `Tests\Feature\Localization\LocalizedSlugLookupTest`: Tests localized slug query scope and mismatch rejection.
6. `Tests\Feature\Localization\Utf8IntegrityTest`: Tests clean UTF-8 Vietnamese rendering without mojibake.

---

## L. Validation Results
- `php artisan migrate:fresh`: **PASS** (26 migrations applied in 355ms)
- `php artisan test`: **PASS** (55 tests, 166 assertions, 0 failures in 1.39s)
- `php vendor/bin/pint --test`: **PASS** (0 style violations)
- `npm run build`: **PASS** (Built in 573ms, 0 errors)
- `composer validate`: **PASS** (`./composer.json is valid`)
- `git diff --check`: **PASS** (0 whitespace/formatting issues)
- `git status --short`: **PASS** (Clean implementation)

---

## M. Files Created & Modified
### Files Created:
- `config/localization.php`
- `app/Support/Localization.php`
- `app/Http/Middleware/SetLocale.php`
- `app/Models/Concerns/HasTranslations.php`
- `lang/vi/navigation.php`
- `lang/en/navigation.php`
- `lang/vi/common.php`
- `lang/en/common.php`
- `tests/Feature/Localization/RouteLocaleTest.php`
- `tests/Feature/Localization/UnsupportedLocaleTest.php`
- `tests/Feature/Localization/LanguageSwitchTest.php`
- `tests/Feature/Localization/TranslatableModelTest.php`
- `tests/Feature/Localization/LocalizedSlugLookupTest.php`
- `tests/Feature/Localization/Utf8IntegrityTest.php`
- `docs/architecture/LOCALIZATION_ARCHITECTURE.md`
- `docs/architecture/adr/ADR-011-localized-url-and-locale-resolution.md`
- `docs/architecture/adr/ADR-012-translation-resolution-and-fallback.md`
- `docs/audit/PHASE_2_LOCALIZATION_FOUNDATION_2026-08-26.md`

### Files Modified:
- `config/app.php` (Default locale updated to `vi`)
- `.env.example` (Default locale updated to `vi`)
- `bootstrap/app.php` (Registered `set.locale` middleware alias)
- `routes/web.php` (Configured `/`, `/en`, `/vi` 301 redirect)
- `resources/views/welcome.blade.php` (Dynamic `<html lang="{{ app()->getLocale() }}">` and language switcher)
- `app/Models/ServiceCategory.php`, `Service.php`, `ServicePrice.php`, `TrainingCourse.php`, `PostCategory.php`, `Post.php`, `Page.php`, `Media.php` (Attached `HasTranslations` trait)
- `AGENTS.md` (Updated with permanent localization governance standards)

---

## N. Master Roadmap Alignment & Deferred Work
Official Master Roadmap structure preserved:
- **Phase 3:** Filament Admin Foundation
- **Phase 4:** Services CMS
- **Phase 5:** Training CMS
- **Phase 6:** Blog CMS
- **Phase 7:** Pages / Settings / Media CMS
- **Phase 8:** Public Design System & Global Layout
- **Phase 9:** Homepage
- **Phase 10:** Public Content Pages
- **Phase 11:** Booking System
- **Phase 12:** Marketing Tracking & Attribution
- **Phase 13:** Technical SEO
- **Phase 14:** Performance / Accessibility / Security
- **Phase 15:** Full QA & Reference Fidelity
- **Phase 16:** Production Deployment
- **Phase 17:** Launch Verification

---

## O. Final API Safety & Closure Summary
1. **Translation Column Safety:** Generic `$column` lookup scope was removed. Translation querying strictly uses explicit `scopeWhereSlug` with immutable `locale` and `slug` parameters, preventing arbitrary SQL column identifier construction.
2. **Roadmap Hygiene:** Corrected deferred-work descriptions in alignment with the locked 18-phase roadmap (Phases 8–9 designated as public design & homepage).
3. **Database Regression:** 0 migrations added; exactly 23 business tables verified.
4. **Route Regression:** Public `/` (vi.home), `/en` (en.home), `/vi` (301 redirect), and `/admin` verified completely isolated.
5. **Quality Checks:** Tests (55/55 passed), Pint (0 violations), Build (0 errors), Composer (valid).

---

## P. Phase Readiness Declaration

```text
READY FOR PHASE 3
```
