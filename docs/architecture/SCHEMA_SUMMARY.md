# SCHEMA SUMMARY & TABLE INVENTORY

**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Phase:** Phase 1A — Domain Model & Database Architecture Design (Closure Patch)  
**Status:** ACCEPTED (Architect-Approved)  
**Total Business Tables:** **23 tables** (excluding Laravel 13 framework baseline tables `users`, `cache`, `jobs`, `migrations`)  

---

## 1. Table Inventory

| # | Table Name | Purpose / Bounded Context | Translated? | Soft Delete? | Primary Relationships | Expected Scale |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | `service_categories` | Service category taxonomy | YES (via #2) | NO | 1:M with `services` | 5–15 rows |
| 2 | `service_category_translations` | Localized category name, slug, SEO | - | NO | M:1 with `service_categories` | 10–30 rows |
| 3 | `services` | Core wellness service entity | YES (via #4) | NO | M:1 `service_categories`, 1:M `service_prices` | 20–100 rows |
| 4 | `service_translations` | Localized name, slug, content, JSON repeaters | - | NO | M:1 with `services` | 40–200 rows |
| 5 | `service_prices` | Duration & pricing tiers in VND | YES (via #6) | NO | M:1 with `services` | 50–300 rows |
| 6 | `service_price_translations` | Optional localized package tier labels | - | NO | M:1 with `service_prices` | 50–300 rows |
| 7 | `service_media` | Explicit gallery pivot for services | NO | NO | M:M `services` <-> `media` | 50–500 rows |
| 8 | `training_courses` | Core beauty academy course entity | YES (via #9) | NO | 1:M `training_course_media` | 5–20 rows |
| 9 | `training_course_translations` | Localized syllabus, duration, benefits, SEO | - | NO | M:1 with `training_courses` | 10–40 rows |
| 10 | `training_course_media` | Explicit gallery pivot for training courses | NO | NO | M:M `training_courses` <-> `media` | 20–200 rows |
| 11 | `training_inquiries` | Prospective student lead captures & attribution | NO (locale col) | **YES** | M:1 with `training_courses` | 1,000–50,000 rows |
| 12 | `post_categories` | Blog category taxonomy | YES (via #13) | NO | 1:M with `posts` | 5–15 rows |
| 13 | `post_category_translations` | Localized blog category name, slug, SEO | - | NO | M:1 with `post_categories` | 10–30 rows |
| 14 | `posts` | Core blog article entity & schedule | YES (via #15) | NO | M:1 `post_categories`, M:1 `users` | 50–1,000 rows |
| 15 | `post_translations` | Localized article title, slug, HTML content | - | NO | M:1 with `posts` | 100–2,000 rows |
| 16 | `post_media` | Explicit gallery pivot for blog articles | NO | NO | M:M `posts` <-> `media` | 50–500 rows |
| 17 | `pages` | Static singleton page registry (`home`, `about`, `contact`) | YES (via #18) | NO | 1:M with `page_translations` | 5–10 rows |
| 18 | `page_translations` | Localized page titles, slugs, content, SEO | - | NO | M:1 with `pages` | 10–20 rows |
| 19 | `page_media` | Explicit gallery pivot for static pages | NO | NO | M:M `pages` <-> `media` | 10–50 rows |
| 20 | `media` | Physical asset registry on disk | YES (via #21) | NO | M:1 with `users` | 100–5,000 rows |
| 21 | `media_translations` | Localized image alt text & captions | - | NO | M:1 with `media` | 200–10,000 rows |
| 22 | `bookings` | Customer spa appointment requests & attribution | NO (locale col) | **YES** | M:1 `services`, M:1 `service_prices` | 2,000–100,000 rows |
| 23 | `site_settings` | Key-value system & tracking configuration | Inline | NO | Standalone configuration registry | 30–100 rows |

---

## 2. Open Decisions Requiring Owner Review

All core technical architecture decisions have been resolved and accepted. The sole remaining business decision is:

### Decision 1: Customer Lead & PII Data Retention Duration
* **Status:** **`TO BE DEFINED BY BUSINESS`**
* **Governance Standard:**
  - SoftDeletes are an accidental-deletion recovery mechanism for leads; they are not a legal/business retention policy.
  - The technical architecture assumes no default retention period (no assumed indefinite, 12-month, or 24-month duration).
  - No automatic PII purge or anonymization schedule is implemented in MVP until a formal business/legal data retention policy is approved by the business owner.
* **Owner Action Needed:** When ready, the business owner will define the formal data retention policy and any legal purge/anonymization requirements for subsequent operational phases.
