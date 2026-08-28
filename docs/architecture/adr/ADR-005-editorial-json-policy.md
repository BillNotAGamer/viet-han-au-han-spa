# ADR-005: Editorial Structured Content in Translation JSON Columns

**Status:** ACCEPTED  
**Context:**  
Services and Training Courses require structured editorial blocks:
- Treatment Benefits (icon, title, description).
- Treatment Procedure Steps (step number, title, description).
- Course Syllabus Modules (module number, title, hours, topics).
- Frequently Asked Questions (question, answer).

Normalizing these into micro-tables (`service_steps`, `service_benefits`, `course_modules`, `faqs`) would add 8+ extra tables with complex bilingual joins.

**Decision:**  
Store structured editorial content as `JSON` columns directly on translation tables (`service_translations.benefits`, `service_translations.process_steps`, `training_course_translations.curriculum_modules`, `service_translations.faqs`).

**Consequences:**  
- **Positives:**
  - Directly maps to Filament 5 `Repeater` and `KeyValue` schema components.
  - Automatically localized within each language translation record.
  - Minimizes database join overhead during page rendering.
- **Negatives:**
  - Structured items cannot be individually joined via relational SQL (acceptable since they are strictly display-only payloads).
