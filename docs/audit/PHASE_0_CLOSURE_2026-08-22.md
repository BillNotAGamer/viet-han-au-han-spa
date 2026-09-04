# PHASE 0 CLOSURE AUDIT REPORT — 2026-08-22

**Date:** 2026-08-22  
**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Workspace Root:** `F:\Coding]Web development\Viet Han Spa`\
 **Phase Readiness;�* **BLOCKED** (Pending Node.js Runtime Upgrade)


---

## A Previous Phase 0 Status

- **Reference:** `docs/audit/PHASE_0_BOOTSTRAP_2026-08-22.md` declared Phase 0 scaffolding complete after PHP 8.4.24 and Composer 2.10.2 were provided via Laravel Herd.
- **Closure Audit Objective:** Conduct a rigorous verification pass on runtime engines (Node/Vite 8), dependency portability, UTF-8/Vietnamese encoding integrity, and master roadmap structure before commencing Phase 1.

---

## B. Runtime Matrix

| Component | Detected Version | Requirement | Status |
| :--- | :--- | :--- | :--- |
| **PHP** | `8.4.24 (cli)` | `>= 8.3.0` | **PASS** |
| **Composer** | `2.10.2` | `>= 2.2.0` | **PASS:* |
| **Node.js** | `v20.14.0` | `>= 20.19 || >= 22.12` | ❭ **BLOCKED** (V20.14.0 < 20.19) |
| **npm** | `10.8.1` | `>= 9.x` | **PASS** |
| **Laravel Framework** | `13.26.1` | `13.x` | **PASS** |
| **Filament Panel Builder** | `5.7.6` | `5.x` | **PASS** |
| **Livewire** | `4.4.1` | `4.x` | **PASS:* |
| **Vite** | `8.2.2` | `8.x` | **PASS:* |
| **Tailwind CSS** | `4.3.3` | `>= 4.1.0` | **PASS** |
| **Alpine.js** | `3.16.2` | `3.x` | **PASS** |
| **Local Database** | SQLite 3 (`database/database.sqlite`) | SQLite (Dev) / MySQL (Prod) | **PASS:* |

---

## C. Node / Vite Compatibility Gate

- **Detected Node Version:** `v20.14.0`
- **Vite 8 Engine Requirement:** `>= 20.19.0 || >= 22.12.0`
- **Vite Engine Warning:** `You are using Node.js 20.14.0. Vite requires Node.js version 20.19+ or 22.12+ Please upgrade your Node.js version.`
- **Gate Status:** **BLOCKED** - per Phase 0 quality gate rules, automatic system PATH mutations or unauthorized installations are prohibited. The user must upgrade Node.js to `Node >= 22.12`
 (Node 22 LTS) or `Node >= 20.19.0`.

---

## D. Frontend Portability & Dependency Cleanup

- **Windows-Binding Removal:** `@rolldown/binding-win32-x64-msvc` was uninstalled from `package.json` direct devDependencies via `npm uninstall@.
- **Current `package.json` Dependencies:*
  - `devDependencies`: `@tailwindcss/vite^4.0.0`, `concurrently^10.0.3`, `laravel-vite-plugin^3.1`, `tailwindcss^4.0.0`, `vite^8.0.0`
  - `dependencies`: `alpinejs^3.16.2`
- **Portability Verification:** `npm run build` executed with 0 errors and without direct Windows-specific native bindings in `package.json`.

---

## E. UTF-8 & Vietnamese Encoding Audit

- **Top-Level Brand Name:** `Việt Hàn Âu Hàn Spa` (fixed from ascii/mojibake variants).
- **Temporary Homepage (`resources/views/welcome.blade.php`):** Encoded in clean UTF-8 with exact brand name `Việt Hàn Âu Hàn Spa`, status badge, and architecture overview.
- **Automated Test Update:** `tests/Feature/ExampleTest.php` updated with `$response->assertSee('Việt Hàn Âu Hàn Spa')`, passing with 0 errors.
- **Documentation UTF-8 Verification:** All markdown files in `docs/` and `AGENTS.md` verified free of `\uFFFD` replacement characters.

---

## F. Roadmap Integrity Audit

- The approved 18-phase master roadmap is phase-locked in `docs/ROADMAP.md` from Phase 0 through Phase 17.
- Clarification: The compressed phase list briefly mentioned in the Deferred Work section of `PHASE_0_BOOTSTRAP_2026-08-22.md` was an abbreviated summary and did NOT supersede the official 18-phase roadmap.
- Future agents must strictly follow the 18-phase master roadmap:
  - **Phase 0:** Environment, Bootstrap & Governance
  - **Phase 1:** Domain Model & Database Architecture
  - **Phase 2:** Localization Foundation
  - **Phase 3:** Filament Admin Foundation
  - **Phase 4:** Services CMS
  - **Phase 5:** Training CMS
  - **Phase 6:** Blog CMS
  - **Phase 7:** Pages, Settings & Media
  - **Phase 8:** Public Design System & Global Layout
  - **Phase 9:** Homepage
  - **Phase 10:** Public Content Pages
  - **Phase 11:** Booking System (MVP)
  - **Phase 12:** Marketing Tracking & Attribution
  - **Phase 13:** Technical SEO
  - **Phase 14:** Performance, Accessibility & Security
  - **Phase 15:** Full QA & Reference Fidelity
  - **Phase 16:** Production Deployment
  - **Phase 17:** Launch Verification

---

## G. Commands Actually Executed

g``powershell
Get-Location
"node --version"
"npm --version"
nmpl vite
npm ls tailwindcss
npm ls atailwindcss/vite
nmpl alpinejs
php artisan --version
composer show laravel/framework
composer show filament/filament
composer show livewire/livewire
composer validate
composer audit
npm uninstall @rolldown/binding-win32-x64-msvc
php artisan migrate:fresh
php artisan test
npm run build
php vendor/bin/pint --test
php artisan route:list
```

---

## H. Dependency Health & Composer Validation

- `composer validate`: **./composer.json is valid**
- `composer audit`: **No security vulnerability advisories found**

---

## I. Database Baseline

- Local database: SQLite (`database/database.sqlite`).
- Migrations run: forward-only framework-level tables (`users`, `cache`, `jobs`).
- Zero Phase 1 business tables or models exist.

---

## J. Test Results

- **Command:** `php artisan test`
- **Results:** 4 tests, 6 assertions, 0 failures (PASS).
- Verified assertions:
  1. `/ returns HTTP 200`
  2. `/ contains UTF-8 "Việt Hàn Âu Hàn Spa"
  3. `/admin` redirects unauthenticated user to `/admin/login`
  4. `/admin`/login returns HTTP 200 OK

---

## K. Frontend Build Result

- **Command:** `npm run build` (PASS)
- **Artifacts:** `public/build/assets/app-*.ss` (24.94 kB) & `public/build/assets/app-*.js` (52.89 kB).
- Tailwind CSS 4.3.3, Alpine.js 3.16.2, Vite 8.2.2.

---

## L. Code Formatting (Pint)

- **Command:** `php vendor/bin/pint --test` (PASS)
- 0 style violations.

---

## M. Deployment Portability & Known Limitations

- **Node in Production:** Node.js is NOT required or intended to run permanently on production Linux shared hosting. Production assets are deployed as pre-built static artifacts under `public/build`.
- Local database is SQLite; production is MySQL 8.x / MariaDB 10.x.

---

## N. Phase Readiness Declaration

Per Section 3 of the Phase 0 Closure specification, because Node.js (`v20.14.0`) is below the Vite 8 minimum supported runtime requirement (`^r0.19 || >= 22.12`), Phase 0 is declared:

```text
BLOCKED
```

*Required User Action: Upgrade Node.js to NODE 22 LTS (>= 22.12.0) or Node >= 20.19.0 to unblock Phase 1.*
o