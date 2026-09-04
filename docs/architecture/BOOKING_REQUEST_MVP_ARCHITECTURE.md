# Booking Request MVP Architecture

Phase 11 exposes the first public booking-request workflow. It reuses the existing `App\Models\Booking` domain and `bookings` table created during the database foundation phase.

## Semantics

A booking is a customer request for a preferred appointment date/time. It is not an automatically confirmed appointment and does not represent realtime availability. Staff confirms availability manually in Filament.

## Routes

Canonical public routes:

- `GET /dat-lich` -> `vi.booking.create`
- `POST /dat-lich` -> `vi.booking.store`
- `GET /en/booking` -> `en.booking.create`
- `POST /en/booking` -> `en.booking.store`

No `/vi/dat-lich` route exists. No public booking index, show, edit, update, or delete route exists.

## Existing Domain Reused

The existing `bookings` table is authoritative. It includes:

- `reference`
- `service_id`
- `service_price_id`
- service and price snapshot fields
- customer name, phone, normalized phone, email
- preferred local date and time
- `guest_count`
- customer/admin notes
- `status`
- `locale`
- lifecycle timestamps
- attribution fields
- timestamps and soft deletes

No Phase 11 migration is added.

## Status Lifecycle

`App\Enums\BookingStatus` contains `NEW`, `CONTACTED`, `CONFIRMED`, `COMPLETED`, `CANCELLED`, and `NO_SHOW`. Public submissions always create `NEW`. Customers cannot set status, admin note, lifecycle timestamps, locale, reference, or attribution fields directly.

The admin workflow service exposes only small staff actions for marking a request contacted, confirmed, cancelled, or updating the internal note.

## Service Selection

The existing schema requires `service_id`, so Phase 11 requires service selection on the public form. Options are loaded through `BookingServiceCatalog` and include only Services that are:

- `status = PUBLISHED`
- translated in the exact requested locale

The posted `service_id` is revalidated against the same exact-locale public eligibility policy. Wrong-locale, draft, archived, and nonexistent services are rejected.

## Validation

`StoreBookingRequest` validates name, phone, email, service, preferred date, preferred time, notes, and consent. Public preferred date validation uses the established Vietnam business wall-clock timezone `Asia/Ho_Chi_Minh` from ADR-010.

Phone handling is intentionally conservative: validated human-readable phone is stored as entered after trimming, and a simple normalized value is derived for existing `phone_normalized` indexing.

## Persistence

`BookingRequestCreator` creates the record inside a transaction, generates a reference, snapshots exact-locale service/price data, and derives locale from the route context.

## CSRF, Rate Limit, PRG

The form uses normal Laravel web CSRF protection. POST routes use the native named limiter `booking-submissions` at `5` submissions per minute per client IP. Successful submission follows POST/Redirect/GET back to the locale-specific booking page and flashes copy stating that staff will confirm manually.

## Filament Admin

`BookingResource` is the staff surface. It exposes list/view pages, newest-first operational listing, status/date/service filters, lifecycle actions, and admin-note editing. It intentionally has no create page, no edit page, and no delete action. `BookingPolicy` restricts access to `users.is_admin` and prohibits create/delete/force-delete.

## CTA Integration

The shared floating booking CTA and mobile drawer booking CTA now point to canonical booking routes:

- VI -> `/dat-lich`
- EN -> `/en/booking`

Primary navigation remains the six-item Phase 10 matrix; Booking is not added as a seventh primary nav item.

## Boundaries

Phase 11 does not implement realtime availability, notifications, email/SMS/Zalo, calendar sync, payment, booking public read endpoints, Contact POST, or technical SEO.
