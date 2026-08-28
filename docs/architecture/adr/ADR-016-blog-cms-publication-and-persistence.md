# ADR-016: Blog CMS Publication and Persistence Architecture

**Status:** ACCEPTED  
**Context:**  
Phase 6 requires implementing editorial blog management: articles, categories, bilingual translations, author attribution, publication scheduling, and gallery media associations. We evaluated:
1. Storing localized titles and contents as JSON columns on `posts` and `post_categories`.
2. Embedding all persistence, author assignment, and publication logic directly inside Filament page lifecycle hooks.
3. Establishing dedicated transactional writers, relational translation tables with stable IDs, conservative hard-deletion guards, and default author assignment.

**Decision:**  
Adopt **Dedicated Domain Writers, Relational Translations with Stable IDs, and Strict Publication Invariants**:
- `App\Services\Blog\PostCategoryWriter` and `App\Services\Blog\PostWriter` manage multi-table operations inside `DB::transaction(...)`.
- Translations update in place via `updateOrCreate`, preserving primary key IDs.
- Vietnamese translation is mandatory; English is optional and not fabricated.
- Slugs are locale-unique (`UNIQUE(locale, slug)`) and auto-generate from titles if blank.
- Post author defaults to the authenticated administrator if omitted.
- `published_at` follows UTC publication lifecycle rules.
- Conservative hard delete: Posts can be hard-deleted only in `DRAFT` status; Categories cannot be deleted while containing posts.
- Media management is association-only.

**Consequences:**  
- **Positives:**
  - Complete database atomicity and zero partial/orphan rows.
  - Protection of published and archived editorial content against accidental deletion.
  - Safe decoupling of authors with `ON DELETE SET NULL`.
  - 100% testable without browser automation.
  - Zero database migrations needed (table count remains 23).
- **Negatives:**
  - Requires explicit form data pre-filling in Filament EditRecord pages.
