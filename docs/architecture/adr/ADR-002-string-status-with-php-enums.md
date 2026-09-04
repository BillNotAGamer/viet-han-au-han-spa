# ADR-002: VARCHAR Status Columns with PHP 8.4 Backed Enums

**Status:** ACCEPTED  
**Context:**  
Entity publishing states (`DRAFT`, `PUBLISHED`, `ARCHIVED`) and lead lifecycle states (`NEW`, `CONTACTED`, `CONFIRMED`, `COMPLETED`, `CANCELLED`) need database persistence. Database-level `ENUM(...)` types present portability issues across SQLite and MySQL, and require destructive `ALTER TABLE` statements when adding new statuses.

**Decision:**  
Use `VARCHAR(32)` columns at the database level paired with PHP 8.4 string-backed enums (`App\Enums\ContentStatus`, `App\Enums\BookingStatus`, `App\Enums\InquiryStatus`).

**Consequences:**  
- **Positives:**
  - 100% application-level portable between SQLite (local) and MySQL/MariaDB (production).
  - New business statuses can be added in PHP code without database migrations.
  - Type-safe validation and casting in Laravel 13 and Filament 5.
- **Negatives:**
  - Database does not strictly prevent inserting unmapped strings if bypassed outside Eloquent (mitigated by FormRequests and Filament schema validation).
