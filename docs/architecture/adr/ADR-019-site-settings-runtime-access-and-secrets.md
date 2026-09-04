# ADR-019: Site Settings Runtime Access, Cache Architecture, and Secret Safeguards

**Status:** ACCEPTED  
**Context:**  
The project requires a lightweight, manageable configuration store for global website settings (contact info, social links, public analytics IDs) while strictly preserving security boundaries, cache performance, and shared-hosting portability. We evaluated:
1. Hardcoding all configurations in Laravel `config/*.php` files (inconvenient for non-technical admins).
2. Storing secrets and tokens inside the database `site_settings` table (critical security risk).
3. Using third-party settings packages with Redis dependencies (incompatible with shared-hosting baseline).
4. Building an explicit `SiteSettings` service and `SiteSettingWriter` utilizing native Laravel Cache and strict secret deny-listing.

**Decision:**  
Adopt **Typed Native SiteSettings Service with Server-Side Secret Safeguards and Configured Laravel Cache**:
- Global configuration only; secrets belong in `.env` and environment variables.
- `SiteSettingWriter` enforces a secret keyword deny-list (`password`, `secret`, `token`, `key`, etc.).
- Machine keys are normalized lowercase alphanumeric (`contact.phone`) and immutable on edit.
- Storage format is strictly typed (`string`, `text`, `boolean`, `json`).
- `is_public` defaults to `false`; public access methods strictly filter `is_public = true`.
- Cache invalidation occurs automatically on all mutations via `site_settings:<key>` cache keys.
- Bulk deletion is disabled in the admin table.

**Consequences:**  
- **Positives:**
  - Zero third-party package overhead.
  - Full compatibility with shared-hosting setups (cPanel/direct file/database cache).
  - Clean separation between public tracking IDs and sensitive server secrets.
  - Zero query overhead on repeated setting lookups via caching.
- **Negatives:**
  - Complex nested multi-locale configurations must remain in relational tables.
