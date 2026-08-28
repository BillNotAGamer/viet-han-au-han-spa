# ADR-008: Grouped Key-Value Site Settings Table

**Status:** ACCEPTED  
**Context:**  
The application requires global runtime configuration: primary hotline, Zalo phone, Messenger URL, operating hours, Google Maps embed code, and tracking container IDs (GTM, GA4, Meta Pixel).

**Decision:**  
Implement a single `site_settings` table with `key`, `value`, `type`, `group`, and `is_public` (default `false`). Settings are cached through Laravel Cache using the configured cache store.

**Consequences:**  
- **Positives:**
  - Secure by default (`is_public = false`).
  - No secret tokens or passwords allowed in `site_settings` (secrets live in `.env`).
  - Allows grouping settings cleanly in Filament tabs (`General`, `Contact`, `Social`, `Tracking`).
  - Compatible with any standard Laravel cache driver (`file`, `database`).
- **Negatives:**
  - Requires simple type-casting logic in a lightweight `Setting` repository/service.
