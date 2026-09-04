# DATA RETENTION & DELETE POLICY

**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Phase:** Phase 1A — Domain Model & Database Architecture Design (Closure Patch)  
**Status:** ACCEPTED (Architect-Approved)  

---

## 1. Soft Delete Strategy Matrix

Soft deletes (`SoftDeletes`) are applied selectively to lead generation entities only. Content entities use explicit lifecycle statuses (`status = 'ARCHIVED'`).

> **Critical Clarification:** Soft deletion is an accidental-deletion recovery mechanism for staff. It is NOT the business/legal data-retention policy.

| Entity | Soft Delete? | Rationale & Tradeoff Analysis |
| :--- | :--- | :--- |
| `service_categories` | **NO** | Foundational taxonomy. Deletion prevented by `ON DELETE RESTRICT` on `services.service_category_id`. |
| `services` | **NO** | Bound to `(locale, slug)` unique constraints. Soft-deleting would block reusing its slug. Inactive services use `status = 'ARCHIVED'`. |
| `service_prices` | **NO** | Inactive pricing tiers use `is_active = false`. Historical bookings maintain historical integrity via `ON DELETE RESTRICT`. |
| `training_courses` | **NO** | Inactive courses use `status = 'ARCHIVED'` to preserve slug reservations while removing them from the public UI. |
| `post_categories` | **NO** | Taxonomies protected by `ON DELETE RESTRICT` on `posts.post_category_id`. |
| `posts` | **NO** | Draft/Archived states (`status = 'ARCHIVED'`) handle content removal cleanly without slug collisions. |
| `pages` | **NO** | Static pages are fixed singletons (`home`, `about`, `contact`). Deleting them is disabled in Filament. |
| `media` | **NO** | Media files on disk must not be orphaned. Filament Media Manager handles explicit deletion with reference check. |
| `bookings` | **YES** | **Soft delete approved for lead recovery.** Protects customer booking requests from accidental deletion by staff. |
| `training_inquiries` | **YES** | **Soft delete approved for lead recovery.** Protects high-value prospective student inquiries from accidental removal. |
| `site_settings` | **NO** | System configuration keys are fixed runtime constants. |

---

## 2. Comprehensive Foreign Key Delete Matrix

| Parent Table | Child / Dependent Table | FK Column | FK Delete Rule | Business Justification |
| :--- | :--- | :--- | :--- | :--- |
| `service_categories` | `service_category_translations` | `service_category_id` | `CASCADE` | Deleting a category deletes its language translation records. |
| `service_categories` | `services` | `service_category_id` | `RESTRICT` | Cannot delete a category that still contains active or archived services. |
| `services` | `service_translations` | `service_id` | `CASCADE` | Deleting a service deletes its translation records. |
| `services` | `service_prices` | `service_id` | `CASCADE` | Deleting a service deletes its pricing tiers. |
| `services` | `service_media` | `service_id` | `CASCADE` | Deleting a service removes its gallery pivot attachments. |
| `services` | `bookings` | `service_id` | **`RESTRICT`** | **Cannot delete a service referenced by historical bookings.** Retired services must use `status = 'ARCHIVED'`. |
| `service_prices` | `service_price_translations` | `service_price_id` | `CASCADE` | Deleting a price tier deletes its translated labels. |
| `service_prices` | `bookings` | `service_price_id` | **`RESTRICT`** | **Cannot delete a pricing tier referenced by historical bookings.** Discontinued tiers use `is_active = false`. |
| `training_courses` | `training_course_translations` | `training_course_id` | `CASCADE` | Deleting a course deletes its translations. |
| `training_courses` | `training_course_media` | `training_course_id` | `CASCADE` | Deleting a course deletes its gallery pivot attachments. |
| `training_courses` | `training_inquiries` | `training_course_id` | **`RESTRICT`** | **Cannot delete a training course referenced by inquiries.** Retired courses use `status = 'ARCHIVED'`. |
| `post_categories` | `post_category_translations` | `post_category_id` | `CASCADE` | Deleting a category deletes its translations. |
| `post_categories` | `posts` | `post_category_id` | `RESTRICT` | Cannot delete a blog category that still contains articles. |
| `posts` | `post_translations` | `post_id` | `CASCADE` | Deleting a post deletes its translations. |
| `posts` | `post_media` | `post_id` | `CASCADE` | Deleting a post removes its gallery pivot attachments. |
| `pages` | `page_translations` | `page_id` | `CASCADE` | Deleting a page deletes its translations. |
| `pages` | `page_media` | `page_id` | `CASCADE` | Deleting a page removes its gallery pivot attachments. |
| `media` | `media_translations` | `media_id` | `CASCADE` | Deleting a media record deletes its localized metadata. |
| `media` | `services` | `hero_media_id` | **`RESTRICT`** | **Cannot delete a media file currently assigned as a service hero image.** |
| `media` | `training_courses` | `hero_media_id` | **`RESTRICT`** | **Cannot delete a media file currently assigned as a course hero image.** |
| `media` | `posts` | `hero_media_id` | **`RESTRICT`** | **Cannot delete a media file currently assigned as a post hero image.** |
| `media` | `service_media` | `media_id` | **`RESTRICT`** | **Cannot delete a media file attached in a service gallery.** |
| `media` | `training_course_media` | `media_id` | **`RESTRICT`** | **Cannot delete a media file attached in a training course gallery.** |
| `media` | `post_media` | `media_id` | **`RESTRICT`** | **Cannot delete a media file attached in a blog post gallery.** |
| `media` | `page_media` | `media_id` | **`RESTRICT`** | **Cannot delete a media file attached in a page gallery.** |
| `users` | `posts` | `author_id` | `SET NULL` | Deleting a staff user unlinks authorship without deleting published articles. |
| `users` | `media` | `uploaded_by` | `SET NULL` | Deleting a staff user preserves uploaded media assets. |

---

## 3. Complete Media Reference Check & Deletion Safety

A media file must NOT be physically deleted from disk if it is referenced anywhere in the application.

In Phase 7 (Media Management), the `MediaDeletionService` will verify that all 7 references are zero before removing the database row and deleting the file from storage:

```text
Admin requests media deletion
             ↓
Check references across:
  1. services.hero_media_id
  2. training_courses.hero_media_id
  3. posts.hero_media_id
  4. service_media.media_id
  5. training_course_media.media_id
  6. post_media.media_id
  7. page_media.media_id
             ↓
Referenced? ─── YES ───► REJECT DELETION (Display error: Asset is in use)
      │
     NO
      ↓
Delete database record + Delete file from disk
```

---

## 4. Lead & PII Data Retention Policy

* **Customer PII Fields:** `customer_name`, `phone`, `phone_normalized`, `email`, `customer_note`.
* **Legal / Operational Retention Duration:** **`TO BE DEFINED BY BUSINESS`**
* **Accidental-Deletion Recovery vs Retention Policy:**
  - SoftDeletes (`deleted_at`) on `bookings` and `training_inquiries` serve solely as an accidental-deletion recovery mechanism for staff.
  - SoftDeletes do NOT constitute a legal or business data-retention policy.
  - No default retention duration (e.g. 12 months, 24 months, or indefinite) is mandated by the technical architecture.
  - No automatic PII purge or anonymization schedule is implemented in MVP until a formal business/legal data retention policy is approved by the project owner.
