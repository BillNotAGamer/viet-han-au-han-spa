# Marketing Tracking & Attribution Architecture (Phase 12)

**Status:** COMPLETE  
**Applies to:** Public Frontend, Booking Request Pipeline, Site Settings  
**Domain Service:** `App\Services\Tracking\MarketingAttribution`, `App\Services\Tracking\TrackingConfiguration`  
**Middleware:** `App\Http\Middleware\CaptureMarketingAttribution`  
**Blade Components:** `<x-tracking.head />`, `<x-tracking.body />`

---

## 1. Executive Summary

Phase 12 delivers production-ready marketing tracking and server-side lead attribution for Việt Hàn Âu Hàn Spa. The architecture supports:
1. **Multi-Provider Client-Side Tracking:** Google Tag Manager (GTM), Google Analytics 4 (GA4), and Meta Pixel with priority-mode switching.
2. **Server-Side Booking Attribution:** Full capture of initial-touch landing page/referrer, latest-touch campaign parameters (`utm_*`, `gclid`, `fbclid`, `gbraid`, `wbraid`), and Meta browser cookies (`_fbp`, `_fbc`).
3. **Guaranteed Zero-PII Policy:** Customer names, phone numbers, emails, and notes are strictly excluded from client-side tracking payloads.
4. **Post-Redirect-Get (PRG) Conversion Guarantee:** Conversion events fire strictly once upon confirmed booking persistence via session flash, preventing duplicate event delivery on reload or validation failure.
5. **Zero Migrations:** Full utilization of pre-existing attribution columns in the `bookings` table (`Phase 12 migrations = 0`).

---

## 2. Dynamic Tag Delivery Engine

Client-side tracking is controlled dynamically through the typed `TrackingConfiguration` service, reading from cached public Site Settings:

| Site Setting Key | Type | Description | Strict Regex Validation |
| :--- | :--- | :--- | :--- |
| `tracking.enabled` | `BOOLEAN` | Master switch for public tracking (default `false`) | `in:0,1,true,false` |
| `tracking.gtm_container_id` | `STRING` | GTM Container ID | `/^GTM-[A-Z0-9]+$/i` |
| `tracking.ga4_measurement_id` | `STRING` | GA4 Measurement ID | `/^G-[A-Z0-9]+$/i` |
| `tracking.meta_pixel_id` | `STRING` | Meta Pixel ID | `/^[0-9]{5,25}$/` |

### Delivery Mode Resolution Priority
1. **Disabled Mode (`none`):** When `tracking.enabled` is false, no tracking scripts, noscript iframes, or conversion events are rendered.
2. **GTM Priority Mode (`gtm`):** When `tracking.gtm_container_id` is present and valid, GTM head and body tags are injected exclusively. Direct GA4 and direct Meta Pixel tags are suppressed to prevent duplicate event collection.
3. **Direct Delivery Mode (`direct`):** If GTM container ID is absent or invalid, direct GA4 and/or Meta Pixel tags are rendered according to the availability of valid IDs.

---

## 3. Client-Side Conversion Tracking & Zero-PII Policy

Conversion events are dispatched only after successful booking persistence in `BookingController::store` and redirected via PRG to the localized booking page with a flashed `booking_conversion` session payload.

### Conversion Event Matrix

| Provider | Mode | Event Name | Payload Attributes (Non-PII Only) |
| :--- | :--- | :--- | :--- |
| **GTM** | GTM Mode | `booking_request_submitted` | `locale` (string), `service_id` (integer) |
| **GA4** | Direct Mode | `generate_lead` | `locale` (string), `service_id` (integer) |
| **Meta Pixel** | Direct Mode | `Lead` | `locale` (string), `service_id` (integer) |

### PII Safety Mandate
- Customer name (`customer_name`) is **never** emitted.
- Customer phone (`phone`, `phone_normalized`) is **never** emitted.
- Customer email (`email`) is **never** emitted.
- Booking notes (`customer_note`) are **never** emitted.
- Client-side dataLayer and script payloads contain only public entity IDs and locale context.

---

## 4. Server-Side Attribution Capture Pipeline

### Flow Overview
```
Incoming Request -> CaptureMarketingAttribution Middleware
                 -> MarketingAttribution Domain Service
                 -> Session Storage (attribution keys)
                 -> Booking Request Submission
                 -> BookingRequestCreator
                 -> Booking Eloquent Persistence
```

### Session Attribution Lifecycle
- **Initial Touch (First Touch):**
  - `landing_page`: Full URL of the initial request (truncated to 500 chars). Captured once, never overwritten during subsequent internal navigation.
  - `referrer`: HTTP referrer of the initial external visit (truncated to 500 chars). Captured once, never overwritten during internal navigation.
- **Latest Touch (Last Touch):**
  - `utm_source` (max 100 chars)
  - `utm_medium` (max 50 chars)
  - `utm_campaign` (max 150 chars)
  - `utm_content` (max 100 chars)
  - `utm_term` (max 100 chars)
  - `gclid` (max 150 chars)
  - `gbraid` (max 100 chars)
  - `wbraid` (max 100 chars)
  - `fbclid` (max 150 chars)
  - Updated whenever query parameters are present on an incoming request.
- **Meta Browser Cookies:**
  - `_fbp` -> mapped to `fbp` (max 255 chars)
  - `_fbc` -> mapped to `fbc` (max 255 chars)
  - Read from incoming request cookies and persisted with the booking.

### Data Sanitization
All attribution input strings are:
1. Stripped of ASCII and UTF-8 control characters (`[\x00-\x1F\x7F]`).
2. Trimmed of leading and trailing whitespace.
3. Truncated strictly to their respective database column length limits before writing to session or database.
4. Non-scalar or array query values are safely discarded.

---

## 5. Administrative & Livewire Isolation

All tracking elements are strictly excluded from administrative operations:
- `CaptureMarketingAttribution` skips execution on `/admin`, `/admin/*`, and `/livewire/*` paths.
- `<x-tracking.head />` and `<x-tracking.body />` are included only in the public site layout (`components/layouts/public.blade.php`), ensuring the Filament admin panel remains completely free of third-party tracking scripts.
- Admin staff can view all 13 attribution fields as read-only metadata in the Booking Infolist schema (`BookingInfolist.php`).
