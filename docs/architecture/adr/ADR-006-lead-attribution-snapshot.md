# ADR-006: Lead Attribution Snapshot Columns

**Status:** ACCEPTED  
**Context:**  
Paid advertising on Google and Meta is a core traffic driver. When a booking or inquiry is submitted, we must retain the complete marketing attribution context (UTMs, GCLID, FBCLID, referrer, landing page) for ROI tracking and Meta Conversions API (CAPI) dispatch.

**Decision:**  
Store marketing attribution fields directly as point-in-time snapshot columns on `bookings` and `training_inquiries`.

**Consequences:**  
- **Positives:**
  - Zero performance overhead for visitor session logging.
  - Attribution data is permanently bound to the lead record even if sessions expire or cookies are cleared.
  - Instant accessibility in Filament lead detail views and CSV exports.
- **Negatives:**
  - Adds ~12 nullable columns to `bookings` and `training_inquiries` tables.
