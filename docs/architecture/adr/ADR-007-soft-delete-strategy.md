# ADR-007: Selective Soft Delete Strategy for Leads Only

**Status:** ACCEPTED  
**Context:**  
Applying `SoftDeletes` universally causes slug collisions on content entities (a soft-deleted service blocks creating a new service with the same slug) and complicates foreign key delete behaviors. Furthermore, a clear distinction must exist between accidental-deletion recovery and legal data retention.

**Decision:**  
Apply `SoftDeletes` (`deleted_at`) **exclusively** to customer lead tables (`bookings`, `training_inquiries`) as an accidental-deletion recovery mechanism for staff. Content and taxonomy entities (`services`, `posts`, `categories`, `pages`, `media`) will NOT use soft deletes, but will instead use status flags (`status = 'ARCHIVED'`).

**Consequences:**  
- **Positives:**
  - Protects customer booking requests and student inquiries from accidental deletion by staff.
  - Eliminates slug collision edge cases on content tables.
  - Clean cascade deletion of translations when a content record is permanently deleted.
- **Clarifications:**
  - SoftDeletes are an accidental-deletion recovery mechanism, NOT a legal/business retention policy.
  - PII retention duration is `TO BE DEFINED BY BUSINESS`. No default retention period (indefinite, 12-month, 24-month) is assumed, and no automatic purge or anonymization schedule is implemented in MVP.
  - Deleting a service or post in Admin permanently deletes the record (mitigated by requiring explicit confirmation and using `ARCHIVED` status).
