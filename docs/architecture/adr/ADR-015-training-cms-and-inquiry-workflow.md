# ADR-015: Training CMS and Training Inquiry Admin Workflow

**Status:** ACCEPTED  
**Context:**  
Phase 5 requires managing bilingual training courses (with curriculum modules, benefits, FAQs, tuition, and gallery media) and customer training inquiries (leads). Training inquiries contain PII, attribution data, and critical status timestamps. We evaluated:
1. Treating training inquiries as standard CRUD articles with generic edit forms and free-form status selects.
2. Embedding multi-table course persistence and inquiry state transitions directly within Filament page callbacks.
3. Establishing dedicated domain writers and workflow services with atomic transactions, lead immutability, and timestamp invariants.

**Decision:**  
Adopt **Dedicated Domain Writers, Lead Immutability, and Controlled Workflow Services**:
- `App\Services\Training\TrainingCourseWriter` handles transactional multi-table persistence, stable translation ID preservation, UTC `published_at` lifecycle, integer VND tuition storage, and conservative hard deletion (`status = DRAFT` AND `inquiries_count = 0`).
- `App\Services\Training\TrainingInquiryWorkflow` manages lead lifecycle state transitions (`NEW` -> `CONTACTED` -> `ENROLLED` / `CLOSED`) with atomic timestamp recording (`contacted_at`, `enrolled_at`, `closed_at`).
- Training inquiry submission fields and attribution parameters are strictly read-only; admin edits are restricted to internal notes and modal workflow actions.
- Soft delete is restricted to recovery; permanent force delete is prohibited.
- Phase 5 media behavior remains association-only.

**Consequences:**  
- **Positives:**
  - Guaranteed database atomicity and zero partial/orphan rows.
  - Total protection of historical lead submissions and attribution audit trails against accidental tampering.
  - Complete testability via PHPUnit feature tests without UI automation.
  - Zero database schema modifications required (business table count remains 23).
- **Negatives:**
  - Minor customer typos in phone/email cannot be edited in Phase 5 without a future dedicated lead correction workflow.
