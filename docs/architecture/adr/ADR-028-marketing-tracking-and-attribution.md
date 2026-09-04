# ADR-028: Marketing Tracking and Attribution

**Status:** ACCEPTED

## Context

Viet Han Au Han Spa requires comprehensive marketing tracking (Google Tag Manager, Google Analytics 4, and Meta Pixel) alongside robust server-side attribution for booking requests. The tracking implementation must avoid duplicate event emission, prevent Personally Identifiable Information (PII) leakage, eliminate schema changes, and strictly isolate administrative operations from public marketing tags.

## Decision

- **Zero Schema Changes:** Leverage existing columns in the `bookings` schema (`utm_source`, `utm_medium`, `utm_campaign`, `utm_content`, `utm_term`, `gclid`, `gbraid`, `wbraid`, `fbclid`, `fbp`, `fbc`, `landing_page`, `referrer`) with zero migrations (`Phase 12 migrations = 0`).
- **Dynamic Site Settings Governance:** Gate all tag injection behind `tracking.enabled` (default `false`) with settings:
  - `tracking.gtm_container_id`
  - `tracking.ga4_measurement_id`
  - `tracking.meta_pixel_id`
  - `tracking.enabled`
- **GTM Priority Mode:** When `tracking.gtm_container_id` is set and valid, GTM script and noscript are rendered exclusively; direct GA4 and direct Meta Pixel tags are suppressed to prevent duplicate event capture.
- **Direct Delivery Mode:** Direct GA4 and/or Meta Pixel tags are injected if and only if GTM is absent/invalid.
- **Strict Identifier Validation:** Reject non-conformant IDs via regex:
  - GTM: `/^GTM-[A-Z0-9]+$/i`
  - GA4: `/^G-[A-Z0-9]+$/i`
  - Meta Pixel: `/^[0-9]{5,25}$/`
- **Zero PII in Tracking:** Client-side tracking payloads emit only non-PII metadata (`locale`, `service_id`). Customer names, phones, emails, and notes are strictly excluded from `dataLayer`, `gtag`, and `fbq`.
- **PRG-Only Conversion Dispatch:** Conversion events (`booking_request_submitted`, `generate_lead`, `Lead`) fire strictly once upon Post-Redirect-Get session flash (`booking_conversion`). They never fire on validation failure or page refresh.
- **Session Attribution Lifecycle:**
  - Initial touch: `landing_page` and `referrer` captured once per session, never overwritten.
  - Latest touch: `utm_*` parameters and click IDs (`gclid`, `gbraid`, `wbraid`, `fbclid`) update per campaign visit.
  - Meta cookies: `_fbp` -> `fbp`, `_fbc` -> `fbc` mapped automatically.
  - Truncation & Sanitization: All values are sanitized of control characters and bounded by database column lengths.
- **Admin Isolation:** All tracking components and attribution middleware are excluded from `/admin` and `/livewire` routes.
- **Meta CAPI Deferred:** Meta Conversions API (CAPI) is explicitly deferred past Phase 12.

## Consequences

Marketing tracking and booking attribution are fully functional, resilient, and configurable directly via Site Settings without requiring code deploys or risking PII leakage or duplicate conversion reporting.
