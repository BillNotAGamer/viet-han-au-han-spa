# Phase 11 Booking Request MVP Audit

## Starting State

- Starting HEAD: `946462cdb3da052c8d6ef04356003f2aa95cdccb`
- Working tree at start: clean
- Phase 10 public surface: closed

## Existing Booking Domain Findings

The repository already contained `App\Models\Booking`, `App\Enums\BookingStatus`, `database/factories/BookingFactory.php`, and `database/migrations/2026_08_26_000022_create_bookings_table.php`.

Phase 11 reuses the existing `bookings` table instead of creating `booking_requests`.

## Schema and Migration

No Phase 11 migration is added. The existing table includes `reference`, `service_id`, `service_price_id`, snapshot fields, customer contact fields, `preferred_date`, `preferred_time`, `guest_count`, notes, status, locale, lifecycle timestamps, attribution fields, timestamps, and soft deletes.

The existing schema requires `service_id`, so service selection is required.

## Canonical Routes

- `GET /dat-lich` -> `vi.booking.create`
- `POST /dat-lich` -> `vi.booking.store`
- `GET /en/booking` -> `en.booking.create`
- `POST /en/booking` -> `en.booking.store`

No `/vi/dat-lich` route is added. No public booking index/show/edit/update/delete route is added.

## Request Semantics

Public submission is a booking request only. It does not confirm an appointment or check realtime availability. Success copy says staff will contact the customer to confirm.

## Architecture

Flow:

`Route -> App\Http\Controllers\Public\BookingController -> App\Http\Requests\Public\StoreBookingRequest -> App\Services\Booking\BookingRequestCreator -> App\Models\Booking -> Blade`

Supporting services:

- `BookingServiceCatalog`: exact-locale public Service options and validation.
- `BookingWorkflow`: small Filament/staff status actions.

## Validation

`StoreBookingRequest` validates customer name, phone, email, service, preferred date, preferred time, notes, and consent. Preferred date uses `Asia/Ho_Chi_Minh` business wall-clock semantics from ADR-010.

## Service Policy

Selected Services must be `PUBLISHED` and have an exact requested-locale translation. Wrong-locale, draft, archived, and nonexistent service IDs are rejected.

## Persistence

`BookingRequestCreator` creates the booking in a transaction, forces `NEW`, derives locale from the route context, snapshots exact-locale service and price data, trims customer fields, and generates a booking reference.

## CSRF, Rate Limit, PRG

- CSRF: normal Laravel web middleware and `@csrf`.
- Rate limiter: `booking-submissions`, 5 submissions per minute per IP.
- Success flow: POST/Redirect/GET to the locale-specific booking page.

## Filament Admin

`BookingResource` exposes admin list and view pages. It supports viewing customer requests, marking requests contacted/confirmed/cancelled, and editing `admin_note`. It has no create page, no edit page, and no delete action.

`BookingPolicy` restricts access to admin users and prohibits create/delete/force-delete.

## CTA Integration

The active floating booking CTA and mobile drawer CTA target:

- VI: `/dat-lich`
- EN: `/en/booking`

The legacy duplicate public layout's direct booking CTA placeholders were aligned to the same route targets without changing the active layout architecture.

Primary public navigation remains the six-item Phase 10 matrix.

## Boundaries

- Public booking read endpoint: not implemented.
- Realtime availability: not implemented.
- Notifications/email/SMS/Zalo: not implemented.
- Payment: not implemented.
- Contact POST: not implemented.
- Technical SEO: not implemented.

## Responsive and Accessibility Source Review

The booking form uses the active solid public layout, one H1, real labels, required indicators, field-level errors, `aria-invalid`, a session success status region, keyboard-operable controls, and responsive single-column/mobile-safe layout.

## Verification

Final machine verification results are recorded in `docs/audit/evidence/phase-11/`.

- PHPUnit: 306 tests, 1414 assertions, 0 failures, 0 errors, 0 skipped, 9.808966 seconds
- Risky tests: NOT VERIFIED FROM CURRENT JUNIT EVIDENCE
- Pint: PASS
- Vite: PASS; optional `fontaine` warning remains non-blocking
- Composer validate: PASS
- Route list: PASS; only Phase 11 public additions are `GET/POST /dat-lich` and `GET/POST /en/booking`
- Blade DB-query review: PASS for public Booking Blade and modified shared CTA files
- Booking security review: PASS
- Rate-limit review: PASS
- Production DB: NOT TESTED

## Human QA

Human QA:
COMPLETED BY PROJECT OWNER

VI booking submission:
PASS

Admin booking appears:
PASS

Admin workflow:
PASS

English booking:
PASS

Mobile booking:
PASS

Booking CTA VI/EN:
PASS

## Git Scope

Phase 11 changes are intentionally uncommitted pending Human QA.

Executable/source scope includes public booking routes, request validation, booking creation services, Filament booking resource/workflow/policy, booking CTA routing, localized booking copy, and focused tests. No migration or package changes are included.
