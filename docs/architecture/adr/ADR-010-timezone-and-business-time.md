# ADR-010: Timezone and Business Wall-Clock Architecture

**Status:** ACCEPTED  
**Context:**  
The application handles two distinct temporal concepts:
1. System instant timestamps (`created_at`, `updated_at`, `published_at`, `contacted_at`, `confirmed_at`, `completed_at`, `cancelled_at`, `deleted_at`).
2. Appointment booking requests (`preferred_date`, `preferred_time`).

**Decision:**  
- Persist all system instant timestamps in **UTC** in the database.
- Convert timestamps to **Asia/Ho_Chi_Minh** (`UTC+7`) when rendering in views and the Filament admin panel.
- Store `preferred_date` as native `DATE` and `preferred_time` as native `TIME` representing the customer's intended **Vietnam business wall-clock time**. These fields are NEVER timezone-shifted as instant timestamps.

**Consequences:**  
- **Positives:**
  - Eliminates daylight savings and timezone conversion bugs on booking appointment schedules.
  - Clean date/time filtering and range queries in SQL.
  - Consistent UTC baseline across server environments.
- **Negatives:**
  - Developers must understand the explicit distinction between instant timestamps (UTC) and local wall-clock fields.
