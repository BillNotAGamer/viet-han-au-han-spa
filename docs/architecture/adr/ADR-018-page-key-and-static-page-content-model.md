# ADR-018: Page Key Semantics, Nullable Slug Architecture, and Static Page Content Model

**Status:** ACCEPTED  
**Context:**  
Phase 7B requires implementing editorial management for static website pages (e.g. Home, About Us, Contact Us) with bilingual content, SEO metadata, and media associations. We evaluated:
1. Translating page keys per locale (e.g. `key_vi`, `key_en`).
2. Reusing Blog/Service automatic slug generation from page titles.
3. Building a full arbitrary JSON block/component page builder.
4. Adopting an immutable machine identifier (`key`), nullable localized slugs, relational translations with stable IDs, and explicit `page_media` associations.

**Decision:**  
Adopt **Immutable Page Keys, Nullable Localized Slugs, and Dedicated Transactional PageWriter**:
- `pages.key` is a unique, untranslated machine identifier, validated as lowercase alphanumeric and immutable across normal edit workflows.
- `page_translations.slug` is nullable by design: blank slugs persist as `NULL` and are NOT auto-generated from titles, supporting homepage and static page semantics.
- Non-null slugs enforce compound locale uniqueness (`UNIQUE(locale, slug)`).
- Multi-table writes execute inside `DB::transaction(...)` via `App\Services\Pages\PageWriter`.
- Translations update in-place, preserving `PageTranslation.id`.
- Conservative hard delete: only `DRAFT` pages may be deleted.
- Media management uses existing `page_media` associations with order persistence.

**Consequences:**  
- **Positives:**
  - Stable template and route resolution decoupled from editorial title changes.
  - Zero database schema migrations required (table count remains 23).
  - Clean support for root/home page without artificial slug workarounds.
  - Zero orphan database records on transaction failures.
- **Negatives:**
  - Requires explicit layout/block rendering logic in later frontend phases (Phase 8–10).
