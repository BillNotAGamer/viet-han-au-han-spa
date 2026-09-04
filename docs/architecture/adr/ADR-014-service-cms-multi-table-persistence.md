# ADR-014: Service CMS Multi-Table Persistence and Price ID Synchronization

**Status:** ACCEPTED  
**Context:**  
Managing spa services requires updating multiple relational tables atomically: `services`, `service_translations`, `service_prices`, `service_price_translations`, and `service_media`. Additionally, historical bookings hold foreign keys to `service_prices.id` with `ON DELETE RESTRICT`. We evaluated:
1. Blindly deleting and re-inserting all price tiers and translations on every form submission.
2. Embedding all multi-table save algorithms directly inside Filament Resource Page classes.
3. Decoupling multi-table mutations into dedicated domain writer services with transactional, ID-aware synchronization.

**Decision:**  
Adopt **Dedicated Domain Writers with Transactional ID-Aware Synchronization**:
- Implement `App\Services\ServiceCatalog\ServiceCategoryWriter` and `ServiceWriter`.
- Keep Filament Resource classes thin by delegating `handleRecordCreation` and `handleRecordUpdate`.
- Perform all mutations inside `DB::transaction`.
- Implement ID-aware price synchronization: existing price tiers retain their primary key `id`; omitted tiers referenced by historical bookings are deactivated (`is_active = false`) rather than hard-deleted.
- Preserve translation primary key IDs in-place during updates.
- Lock structured JSON repeaters (`benefits`, `process_steps`, `faqs`) to a consistent `NULL` empty-state policy.
- Enforce strict Service hard-deletion invariant: only `DRAFT` records with 0 bookings can be hard-deleted.
- Restrict Phase 4 media operations strictly to relationship associations and sort_order persistence.

**Consequences:**  
- **Positives:**
  - Guaranteed database atomicity and zero partial/orphan rows.
  - Complete protection of historical booking integrity and stable price references.
  - Clean separation of UI schema definitions from persistence logic.
  - 100% testable via standard PHPUnit/Pest feature tests without browser automation.
- **Negatives:**
  - Requires explicit form data mapping (`mutateFormDataBeforeFill`) in EditRecord pages.
