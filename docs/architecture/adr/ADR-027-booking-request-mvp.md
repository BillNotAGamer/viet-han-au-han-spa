# ADR-027: Booking Request MVP

**Status:** ACCEPTED

## Context

The public site needs a launch-critical booking workflow. The repository already contains a `Booking` model, `bookings` table, request lifecycle statuses, preferred wall-clock date/time fields, service snapshots, and attribution fields. A second `BookingRequest` table would duplicate the existing domain.

## Decision

- Reuse `App\Models\Booking` and the existing `bookings` table.
- Treat every public submission as a booking request, not an automatically confirmed appointment.
- Use canonical public routes:
  - VI: `GET/POST /dat-lich`
  - EN: `GET/POST /en/booking`
- Do not create `/vi/dat-lich`.
- Public customers cannot control `status`, `admin_note`, lifecycle timestamps, `locale`, `reference`, or attribution/system fields.
- Initial public status is always `NEW`.
- Because the existing schema requires `bookings.service_id`, public service selection is required.
- Selected services must be publicly eligible in the exact route locale.
- Public submissions use CSRF and the native `booking-submissions` rate limiter.
- Successful submissions use POST/Redirect/GET and do not expose a public booking ID.
- No public booking read endpoint is exposed.
- Filament is the staff processing surface.
- Notifications, payments, and realtime availability are deferred.

## Consequences

The MVP is usable for launch without pretending to provide operational availability. Staff can process requests in Filament, while public customers receive accurate confirmation-pending messaging.
