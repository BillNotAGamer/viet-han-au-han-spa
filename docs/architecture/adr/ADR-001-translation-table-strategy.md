# ADR-001: Native Eloquent Translation Tables Strategy

**Status:** ACCEPTED  
**Context:**  
The website requires strict bilingual support (Vietnamese as default `vi`, English as secondary `en`), with unique localized URL slugs (`/dich-vu/tri-lieu-co-vai-gay` vs `/en/services/neck-shoulder-therapy`), localized meta tags, and structured editorial content. We evaluated three architectural approaches:
1. Native Eloquent Translation Tables (`services` + `service_translations`, `service_prices` + `service_price_translations`).
2. JSON column translations (`name: {"vi": "...", "en": "..."}`).
3. Third-party packages (`spatie/laravel-translatable` or `astrotomic/laravel-translatable`).

**Decision:**  
Adopt **Native Eloquent Translation Tables** for all translatable entities (`service_categories`, `services`, `service_prices`, `training_courses`, `post_categories`, `posts`, `pages`, `media`).

**Consequences:**  
- **Positives:**
  - Database-level `UNIQUE (locale, slug)` enforcement.
  - High-performance indexed slug lookups in MySQL, MariaDB, and SQLite without JSON index overhead.
  - Zero third-party package dependencies (strictly adheres to package minimization).
  - Clean Eloquent relationships (`$service->translations`, `$service->translate('vi')`).
- **Negatives:**
  - Requires 8 dedicated translation tables.
