# ADR-012: Translation Resolution and Non-Masquerading Fallback Policy

**Status:** ACCEPTED  
**Context:**  
When serving bilingual content from database translation tables, a requested translation in a secondary language (`en`) may not yet exist for a newly published Vietnamese article or service. We evaluated:
1. Implicit automatic fallback: Silently render Vietnamese text under English URLs (`/en/services/...`).
2. Strict exact translation for public routes; explicit optional fallback for internal application contexts.

**Decision:**  
Enforce **Strict exact translation for public routes** and require explicit method calls (`translationOrFallback`) for non-route application contexts:
- `$model->translationFor('en')` returns `null` if no English record exists.
- Public `/en/*` content detail routes must return 404 if no exact English translation exists (never serve Vietnamese content under an English canonical URL).
- Non-route contexts (e.g. admin fallbacks, internal exports) can explicitly call `$model->translationOrFallback('en')`.

**Consequences:**  
- **Positives:**
  - Prevents SEO duplicate content penalties and search engine confusion.
  - Guarantees authentic English user experience on all English canonical URLs.
  - Clear, predictable Eloquent API.
- **Negatives:**
  - Content editors must publish English translations before English detail pages become live.
