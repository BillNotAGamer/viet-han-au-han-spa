# Phase 12 Marketing Tracking & Attribution Audit Report

**Date:** 2026-09-03  
**Implementation Agent:** ANTIGRAVITY / GEMINI 3.8 FLASH  
**Starting HEAD:** `51097ca75a064b34b19d582356e0178c1376e417`  
**Phase State:** CLOSED
**Verification Verdict:** PASS (All 16 invariants verified, 325 tests passing)

---

## 1. Executive Summary

Phase 12 implements launch-critical marketing tracking (Google Tag Manager, Google Analytics 4, Meta Pixel) and end-to-end server-side lead attribution for Việt Hàn Âu Hàn Spa.

Key Architectural Guarantees Delivered:
1. **Zero Database Migrations:** Fully reused pre-existing schema columns in `bookings` (`Phase 12 migrations = 0`).
2. **Zero Dependencies Added:** Zero composer or npm packages installed.
3. **Strict Priority Tag Delivery:** GTM container ID takes strict priority when present and valid, completely suppressing direct GA4 and Meta Pixel tags to avoid duplicate event collection. Direct tags fire only when GTM is absent.
4. **Absolute Zero PII Emission:** Customer personal data (name, phone, email, notes) is strictly excluded from client-side scripts, dataLayer, and events.
5. **Post-Redirect-Get (PRG) Conversion Guarantees:** Conversions fire strictly once via session flash following successful database commit. Conversions never trigger on validation errors or page refreshes.
6. **Server-Side Attribution Pipeline:** Full capture of first-touch context (`landing_page`, `referrer`) and latest-touch marketing parameters (`utm_*`, `gclid`, `gbraid`, `wbraid`, `fbclid`, `fbp`, `fbc`).
7. **Strict Admin & Livewire Isolation:** Tracking scripts and middleware are excluded from `/admin` and `/livewire` endpoints.

---

## 2. Evidence Files Checklist

All evidence files have been generated under `docs/audit/evidence/phase-12/`:
- `git-status.txt`: Working tree status against starting HEAD.
- `git-diff-stat.txt`: Diff stat summary against starting HEAD.
- `git-diff-name-status.txt`: Changed file status list against starting HEAD.
- `environment.txt`: Host environment versions (PHP 8.4.24, Node, NPM, Composer).
- `composer-validate.txt`: Composer schema validation (`./composer.json is valid`).
- `npm-build.txt`: Vite production build log (`built in 1.12s`).
- `pint.txt`: Laravel Pint code style verification (`result: passed`).
- `tests.txt`: PHPUnit test execution log (`325 passed, 1552 assertions, 0 failures`).
- `route-list.txt`: Route list confirming no unauthorized endpoints or leaks.
- `tracking-matrix-review.txt`: Full review of tag delivery precedence and event schemas.
- `pii-review.txt`: Static and dynamic audit proving zero PII leakage.
- `attribution-storage-review.txt`: Schema reuse, truncation, and sanitization verification.
- `phase12-invariant-map.md`: 16/16 invariant verification mapping table.

---

## 3. Architecture & Code Changes

### New Components & Services
1. `app/Services/Tracking/MarketingAttribution.php`: Handles attribution sanitization, initial-touch/latest-touch lifecycle, and cookie mapping.
2. `app/Http/Middleware/CaptureMarketingAttribution.php`: Captures marketing parameters on public web routes, skipping admin and livewire paths.
3. `app/Services/Tracking/TrackingConfiguration.php`: Resolves public tracking settings, enforces regex validation, and determines delivery mode (`none`, `gtm`, `direct`).
4. `app/View/Components/Tracking/Head.php`: View component for `<head>` tracking tags.
5. `app/View/Components/Tracking/Body.php`: View component for `<body>` GTM noscript iframe.
6. `resources/views/components/tracking/head.blade.php`: Blade template for GTM/GA4/Meta script injection and PRG conversion events.
7. `resources/views/components/tracking/body.blade.php`: Blade template for GTM noscript iframe.

### Modifications to Existing Systems
1. `bootstrap/app.php`: Appended `CaptureMarketingAttribution` to the web middleware group.
2. `app/Services/Booking/BookingRequestCreator.php`: Injected `MarketingAttribution` to populate all 13 attribution columns upon booking creation.
3. `app/Http/Controllers/Public/BookingController.php`: Flashed non-PII `booking_conversion` payload upon successful PRG redirect.
4. `resources/views/components/layouts/public.blade.php`: Embedded `<x-tracking.head />` and `<x-tracking.body />`.
5. `app/Filament/Resources/Bookings/Schemas/BookingInfolist.php`: Added read-only "Nguồn tiếp thị & Attribution" infolist section for staff visibility.
6. `AGENTS.md`: Added Section 16 governing Phase 12 Marketing Tracking & Attribution Standards.
7. `docs/architecture/adr/ADR-028-marketing-tracking-and-attribution.md`: Accepted architectural decision record.
8. `docs/architecture/MARKETING_TRACKING_ATTRIBUTION.md`: Comprehensive technical architecture documentation.

---

## 4. Test Suite Results

```text
Tests: 325
Passed: 325
Assertions: 1552
Failures: 0
Errors: 0
Duration: 19.85s
```

Dedicated Phase 12 test coverage: `tests/Feature/Public/TrackingAndAttributionTest.php` with 19 comprehensive feature tests covering:
- Default disabled state (zero tags).
- GTM priority delivery and direct tag suppression.
- Direct GA4 and Meta Pixel delivery modes.
- Identifier regex validation and script injection prevention.
- Admin and Livewire exclusion.
- PRG-only conversion event firing (zero events on validation failure or reload).
- Zero PII in dataLayer / tracking payloads.
- First-touch immutability vs latest-touch updates.
- Parameter length truncation and character sanitization.
- Meta cookie mapping (`_fbp`, `_fbc`).
- Filament read-only infolist display.
- Public navigation and CTA regression prevention.

---

## 5. Verification Gate Summary

- `php artisan test`: **PASS** (325 tests, 1552 assertions, 0 failures)
- `vendor/bin/pint --test`: **PASS** (0 style violations)
- `npm run build`: **PASS** (Vite built in 1.12s)
- `composer validate`: **PASS** (`./composer.json is valid`)

---

## 6. Human QA Verification

```text
Human QA:
COMPLETED BY PROJECT OWNER

Tracking disabled:
PASS

GTM precedence:
PASS

Attribution persistence:
PASS

One-time conversion:
PASS

Admin exclusion:
PASS
```

---

## 7. Git History Anomaly & Tracking Recovery Record

Phase 12 implementation source entered repository history in:

`9036b8ff35dd42189b503ccdd7b1a4058955f202`

Commit subject:
`First commit`

That commit also accidentally changed repository tracking policy and removed `docs/`, `tests/`, and `phpunit.xml` from Git tracking.

No history rewrite was performed because the commit had already been synchronized with origin/main.

Corrective tracking recovery commit:

`1875dfcc20f071f20a4b056ea675a7e26ebdbe1c`

Recovery message:

`fix: restore project tracking after deployment packaging`

Recovery executable source modifications:

`NONE`

Canonical docs/tests/phpunit.xml tracking:

`RESTORED`

Deployment artifacts:

`MOVED OUTSIDE REPOSITORY`

Production provider delivery:
`NOT VERIFIED`

Meta CAPI:
`NOT IMPLEMENTED`

Production DB:
`NOT TESTED`
