# ADR-009: Booking Historical Commercial Snapshot

**Status:** ACCEPTED  
**Context:**  
Service names, pricing tiers, durations, and VND prices can change over time. When a customer submits a booking request, the commercial terms they agreed to must be preserved permanently on the `bookings` record, even if the referenced `Service` or `ServicePrice` is updated or archived later.

**Decision:**  
Add dedicated historical snapshot columns directly on the `bookings` table:
- `service_name_snapshot` (string 255 nullable)
- `service_price_label_snapshot` (string 150 nullable)
- `duration_minutes_snapshot` (unsignedInteger nullable)
- `price_amount_snapshot` (unsignedBigInteger nullable)

**Consequences:**  
- **Positives:**
  - Guarantees historical commercial integrity for financial audits and customer service dispute resolution.
  - Decouples past booking history from future price changes.
  - Zero extra relational join overhead.
- **Negatives:**
  - Adds 4 nullable snapshot columns to `bookings`.
