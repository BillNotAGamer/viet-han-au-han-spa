# DATABASE SCHEMA SPECIFICATION

**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Phase:** Phase 1A — Domain Model & Database Architecture Design (Closure Patch)  
**Status:** ACCEPTED (Architect-Approved)  
**Target Engines:** SQLite 3 (Local Development) / MySQL 8.0+ & MariaDB 10.4+ (Production)  

---

## Table of Contents

1. [Users Baseline](#1-users-baseline)
2. [Service Domain (7 Tables)](#2-service-domain)
3. [Training Domain (4 Tables)](#3-training-domain)
4. [Blog Domain (5 Tables)](#4-blog-domain)
5. [Page Domain (3 Tables)](#5-page-domain)
6. [Media Domain (2 Tables)](#6-media-domain)
7. [Lead Domain (1 Table)](#7-lead-domain)
8. [System Domain (1 Table)](#8-system-domain)

---

## 1. Users Baseline

### `users` (Laravel Baseline)
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | User ID |
| `name` | `string(255)` | NO | - | - | Staff / Admin full name |
| `email` | `string(255)` | NO | - | `UNIQUE` | Login email address |
| `email_verified_at` | `timestamp` | YES | `NULL` | - | Verification timestamp |
| `password` | `string(255)` | NO | - | - | Hashed password |
| `remember_token` | `string(100)` | YES | `NULL` | - | Remember me token |
| `created_at` | `timestamp` | YES | `NULL` | - | Creation timestamp |
| `updated_at` | `timestamp` | YES | `NULL` | - | Update timestamp |

---

## 2. Service Domain

### `service_categories`
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Category ID |
| `status` | `string(32)` | NO | `'PUBLISHED'` | `INDEX` | Content status (`DRAFT`, `PUBLISHED`, `ARCHIVED`) |
| `sort_order` | `unsignedInteger` | NO | `0` | `INDEX` | Display sort order |
| `created_at` | `timestamp` | YES | `NULL` | - | Creation timestamp |
| `updated_at` | `timestamp` | YES | `NULL` | - | Update timestamp |

### `service_category_translations`
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Translation ID |
| `service_category_id` | `foreignId` | NO | - | `FK -> service_categories.id ON DELETE CASCADE` | Parent category reference |
| `locale` | `string(12)` | NO | - | `INDEX` | Locale code (`vi`, `en`) |
| `name` | `string(255)` | NO | - | - | Category display name |
| `slug` | `string(255)` | NO | - | `INDEX` | URL slug |
| `description` | `text` | YES | `NULL` | - | Category summary |
| `seo_title` | `string(255)` | YES | `NULL` | - | Meta title |
| `seo_description` | `string(500)` | YES | `NULL` | - | Meta description |
| `created_at` | `timestamp` | YES | `NULL` | - | Creation timestamp |
| `updated_at` | `timestamp` | YES | `NULL` | - | Update timestamp |
| **Unique Keys** | - | - | - | `UNIQUE(service_category_id, locale)`<br>`UNIQUE(locale, slug)` | Compound integrity keys |

### `services`
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Service ID |
| `service_category_id` | `foreignId` | NO | - | `FK -> service_categories.id ON DELETE RESTRICT` | Primary category (cannot delete category with services) |
| `hero_media_id` | `foreignId` | YES | `NULL` | `FK -> media.id ON DELETE RESTRICT` | Cover image (cannot delete media if used as hero) |
| `status` | `string(32)` | NO | `'DRAFT'` | `INDEX` | Status (`DRAFT`, `PUBLISHED`, `ARCHIVED`) |
| `is_featured` | `boolean` | NO | `false` | `INDEX` | Featured on homepage flag |
| `sort_order` | `unsignedInteger` | NO | `0` | `INDEX` | Sorting index |
| `created_at` | `timestamp` | YES | `NULL` | - | Creation timestamp |
| `updated_at` | `timestamp` | YES | `NULL` | - | Update timestamp |

### `service_translations`
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Translation ID |
| `service_id` | `foreignId` | NO | - | `FK -> services.id ON DELETE CASCADE` | Parent service reference |
| `locale` | `string(12)` | NO | - | `INDEX` | Locale code (`vi`, `en`) |
| `name` | `string(255)` | NO | - | - | Localized service name |
| `slug` | `string(255)` | NO | - | `INDEX` | Localized URL slug |
| `excerpt` | `text` | YES | `NULL` | - | Short summary for cards |
| `content` | `longText` | YES | `NULL` | - | Detailed description (HTML) |
| `benefits` | `json` | YES | `NULL` | - | Structured benefit items `[{"title":"","desc":""}]` |
| `process_steps` | `json` | YES | `NULL` | - | Procedure steps `[{"step":1,"title":"","desc":""}]` |
| `faqs` | `json` | YES | `NULL` | - | FAQ items `[{"q":"","a":""}]` |
| `seo_title` | `string(255)` | YES | `NULL` | - | Meta title |
| `seo_description` | `string(500)` | YES | `NULL` | - | Meta description |
| `created_at` | `timestamp` | YES | `NULL` | - | Creation timestamp |
| `updated_at` | `timestamp` | YES | `NULL` | - | Update timestamp |
| **Unique Keys** | - | - | - | `UNIQUE(service_id, locale)`<br>`UNIQUE(locale, slug)` | Compound integrity keys |

### `service_prices`
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Price Tier ID |
| `service_id` | `foreignId` | NO | - | `FK -> services.id ON DELETE CASCADE` | Parent service reference |
| `duration_minutes` | `unsignedInteger` | NO | `60` | `INDEX` | Duration in minutes (e.g. 60, 90, 120) |
| `price_amount` | `unsignedBigInteger` | NO | - | `INDEX` | Price in integer VND (e.g. 390000) |
| `sort_order` | `unsignedInteger` | NO | `0` | - | Display order within service |
| `is_active` | `boolean` | NO | `true` | `INDEX` | Active availability toggle |
| `created_at` | `timestamp` | YES | `NULL` | - | Creation timestamp |
| `updated_at` | `timestamp` | YES | `NULL` | - | Update timestamp |

### `service_price_translations`
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Translation ID |
| `service_price_id` | `foreignId` | NO | - | `FK -> service_prices.id ON DELETE CASCADE` | Parent price reference |
| `locale` | `string(12)` | NO | - | `INDEX` | Locale code (`vi`, `en`) |
| `label` | `string(150)` | NO | - | - | Optional localized tier title (e.g. "Gói Chuyên Sâu") |
| `created_at` | `timestamp` | YES | `NULL` | - | Creation timestamp |
| `updated_at` | `timestamp` | YES | `NULL` | - | Update timestamp |
| **Unique Keys** | - | - | - | `UNIQUE(service_price_id, locale)` | Single translation per locale |

### `service_media` (Gallery Pivot)
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Pivot ID |
| `service_id` | `foreignId` | NO | - | `FK -> services.id ON DELETE CASCADE` | Service reference |
| `media_id` | `foreignId` | NO | - | `FK -> media.id ON DELETE RESTRICT` | Media reference (cannot delete media while attached) |
| `sort_order` | `unsignedInteger` | NO | `0` | - | Gallery sort order |
| `created_at` | `timestamp` | YES | `NULL` | - | Attachment timestamp |
| **Unique Keys** | - | - | - | `UNIQUE(service_id, media_id)` | Unique attachment constraint |

---

## 3. Training Domain

### `training_courses`
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Course ID |
| `hero_media_id` | `foreignId` | YES | `NULL` | `FK -> media.id ON DELETE RESTRICT` | Cover image (cannot delete media if used as hero) |
| `tuition_fee` | `unsignedBigInteger` | YES | `NULL` | `INDEX` | Tuition in integer VND |
| `status` | `string(32)` | NO | `'DRAFT'` | `INDEX` | Status (`DRAFT`, `PUBLISHED`, `ARCHIVED`) |
| `is_featured` | `boolean` | NO | `false` | `INDEX` | Featured on academy highlight |
| `sort_order` | `unsignedInteger` | NO | `0` | `INDEX` | Sorting order |
| `published_at` | `timestamp` | YES | `NULL` | `INDEX` | Publication datetime |
| `created_at` | `timestamp` | YES | `NULL` | - | Creation timestamp |
| `updated_at` | `timestamp` | YES | `NULL` | - | Update timestamp |

### `training_course_translations`
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Translation ID |
| `training_course_id` | `foreignId` | NO | - | `FK -> training_courses.id ON DELETE CASCADE` | Parent course reference |
| `locale` | `string(12)` | NO | - | `INDEX` | Locale code (`vi`, `en`) |
| `title` | `string(255)` | NO | - | - | Course title |
| `slug` | `string(255)` | NO | - | `INDEX` | Localized URL slug |
| `excerpt` | `text` | YES | `NULL` | - | Short summary |
| `content` | `longText` | YES | `NULL` | - | Full course description (HTML) |
| `duration_display` | `string(100)` | YES | `NULL` | - | Localized duration (e.g. "4 tuần (80 giờ)") |
| `schedule_display` | `string(100)` | YES | `NULL` | - | Localized schedule (e.g. "T2 - T6 / Ca Sáng & Chiều") |
| `target_audience` | `text` | YES | `NULL` | - | Target student profile |
| `curriculum_modules` | `json` | YES | `NULL` | - | Array of syllabus modules `[{"module":1,"title":"","hours":20}]` |
| `benefits` | `json` | YES | `NULL` | - | Array of course benefits / certification perks |
| `faqs` | `json` | YES | `NULL` | - | FAQ array `[{"q":"","a":""}]` |
| `seo_title` | `string(255)` | YES | `NULL` | - | Meta title |
| `seo_description` | `string(500)` | YES | `NULL` | - | Meta description |
| `created_at` | `timestamp` | YES | `NULL` | - | Creation timestamp |
| `updated_at` | `timestamp` | YES | `NULL` | - | Update timestamp |
| **Unique Keys** | - | - | - | `UNIQUE(training_course_id, locale)`<br>`UNIQUE(locale, slug)` | Compound integrity keys |

### `training_course_media` (Gallery Pivot)
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Pivot ID |
| `training_course_id` | `foreignId` | NO | - | `FK -> training_courses.id ON DELETE CASCADE` | Course reference |
| `media_id` | `foreignId` | NO | - | `FK -> media.id ON DELETE RESTRICT` | Media reference (cannot delete media while attached) |
| `sort_order` | `unsignedInteger` | NO | `0` | - | Gallery sort order |
| `created_at` | `timestamp` | YES | `NULL` | - | Attachment timestamp |
| **Unique Keys** | - | - | - | `UNIQUE(training_course_id, media_id)` | Unique attachment constraint |

### `training_inquiries`
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Inquiry ID |
| `reference` | `string(32)` | NO | - | `UNIQUE` | Human-readable reference code (`TRN-20260825-XYZ789`) |
| `training_course_id` | `foreignId` | YES | `NULL` | `FK -> training_courses.id ON DELETE RESTRICT` | Target course (cannot delete course while inquiries exist; use ARCHIVED) |
| `customer_name` | `string(150)` | NO | - | - | Student / customer name |
| `phone` | `string(30)` | NO | - | - | Raw phone number as entered |
| `phone_normalized` | `string(30)` | NO | - | `INDEX` | Canonical E.164 phone (`+84901234567`) |
| `email` | `string(255)` | YES | `NULL` | - | Contact email |
| `message` | `text` | YES | `NULL` | - | Student inquiry message |
| `status` | `string(32)` | NO | `'NEW'` | `INDEX` | Lifecycle status (`NEW`, `CONTACTED`, `ENROLLED`, `CLOSED`) |
| `locale` | `string(12)` | NO | `'vi'` | `INDEX` | Submission locale |
| `admin_note` | `text` | YES | `NULL` | - | Internal counselor notes |
| `contacted_at` | `timestamp` | YES | `NULL` | - | First consultation timestamp (UTC) |
| `enrolled_at` | `timestamp` | YES | `NULL` | - | Enrollment confirmation timestamp (UTC) |
| `closed_at` | `timestamp` | YES | `NULL` | - | Closed / lost timestamp (UTC) |
| `utm_source` | `string(100)` | YES | `NULL` | `INDEX` | Attribution source (`facebook`, `google`, `zalo`) |
| `utm_medium` | `string(100)` | YES | `NULL` | - | Attribution medium (`cpc`, `organic`, `referral`) |
| `utm_campaign` | `string(150)` | YES | `NULL` | `INDEX` | Attribution campaign |
| `utm_content` | `string(150)` | YES | `NULL` | - | Attribution ad content |
| `utm_term` | `string(150)` | YES | `NULL` | - | Attribution keyword term |
| `gclid` | `string(150)` | YES | `NULL` | - | Google Click ID |
| `gbraid` | `string(100)` | YES | `NULL` | - | Google iOS Click ID |
| `wbraid` | `string(100)` | YES | `NULL` | - | Google Web Click ID |
| `fbclid` | `string(150)` | YES | `NULL` | - | Facebook Click ID |
| `fbp` | `string(100)` | YES | `NULL` | - | Facebook Browser ID |
| `fbc` | `string(100)` | YES | `NULL` | - | Facebook Click ID cookie |
| `landing_page` | `string(500)` | YES | `NULL` | - | Initial arrival URL |
| `referrer` | `string(500)` | YES | `NULL` | - | HTTP Referrer header |
| `created_at` | `timestamp` | YES | `NULL` | `INDEX` | Submission timestamp (UTC) |
| `updated_at` | `timestamp` | YES | `NULL` | - | Update timestamp (UTC) |
| `deleted_at` | `timestamp` | YES | `NULL` | `INDEX` | Soft delete timestamp (for accidental-deletion recovery) |

---

## 4. Blog Domain

### `post_categories`
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Category ID |
| `status` | `string(32)` | NO | `'PUBLISHED'` | `INDEX` | Status (`DRAFT`, `PUBLISHED`, `ARCHIVED`) |
| `sort_order` | `unsignedInteger` | NO | `0` | `INDEX` | Sorting order |
| `created_at` | `timestamp` | YES | `NULL` | - | Creation timestamp |
| `updated_at` | `timestamp` | YES | `NULL` | - | Update timestamp |

### `post_category_translations`
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Translation ID |
| `post_category_id` | `foreignId` | NO | - | `FK -> post_categories.id ON DELETE CASCADE` | Parent category reference |
| `locale` | `string(12)` | NO | - | `INDEX` | Locale code (`vi`, `en`) |
| `name` | `string(255)` | NO | - | - | Category display name |
| `slug` | `string(255)` | NO | - | `INDEX` | Category URL slug |
| `description` | `text` | YES | `NULL` | - | Category summary |
| `seo_title` | `string(255)` | YES | `NULL` | - | Meta title |
| `seo_description` | `string(500)` | YES | `NULL` | - | Meta description |
| `created_at` | `timestamp` | YES | `NULL` | - | Creation timestamp |
| `updated_at` | `timestamp` | YES | `NULL` | - | Update timestamp |
| **Unique Keys** | - | - | - | `UNIQUE(post_category_id, locale)`<br>`UNIQUE(locale, slug)` | Compound integrity keys |

### `posts`
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Post ID |
| `post_category_id` | `foreignId` | NO | - | `FK -> post_categories.id ON DELETE RESTRICT` | Primary category (cannot delete category with posts) |
| `author_id` | `foreignId` | YES | `NULL` | `FK -> users.id ON DELETE SET NULL` | Author user reference |
| `hero_media_id` | `foreignId` | YES | `NULL` | `FK -> media.id ON DELETE RESTRICT` | Cover image (cannot delete media while used as hero) |
| `status` | `string(32)` | NO | `'DRAFT'` | `INDEX` | Status (`DRAFT`, `PUBLISHED`, `ARCHIVED`) |
| `is_featured` | `boolean` | NO | `false` | `INDEX` | Featured article flag |
| `published_at` | `timestamp` | YES | `NULL` | `INDEX` | Publication datetime (UTC) |
| `created_at` | `timestamp` | YES | `NULL` | - | Creation timestamp |
| `updated_at` | `timestamp` | YES | `NULL` | - | Update timestamp |

### `post_translations`
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Translation ID |
| `post_id` | `foreignId` | NO | - | `FK -> posts.id ON DELETE CASCADE` | Parent post reference |
| `locale` | `string(12)` | NO | - | `INDEX` | Locale code (`vi`, `en`) |
| `title` | `string(255)` | NO | - | - | Article title |
| `slug` | `string(255)` | NO | - | `INDEX` | Article URL slug |
| `excerpt` | `text` | YES | `NULL` | - | Summary excerpt for cards |
| `content` | `longText` | YES | `NULL` | - | Article body content (HTML) |
| `seo_title` | `string(255)` | YES | `NULL` | - | Meta title |
| `seo_description` | `string(500)` | YES | `NULL` | - | Meta description |
| `created_at` | `timestamp` | YES | `NULL` | - | Creation timestamp |
| `updated_at` | `timestamp` | YES | `NULL` | - | Update timestamp |
| **Unique Keys** | - | - | - | `UNIQUE(post_id, locale)`<br>`UNIQUE(locale, slug)` | Compound integrity keys |

### `post_media` (Gallery & Editorial Attachments Pivot)
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Pivot ID |
| `post_id` | `foreignId` | NO | - | `FK -> posts.id ON DELETE CASCADE` | Post reference |
| `media_id` | `foreignId` | NO | - | `FK -> media.id ON DELETE RESTRICT` | Media reference (cannot delete media while attached) |
| `sort_order` | `unsignedInteger` | NO | `0` | - | Gallery sort order |
| `created_at` | `timestamp` | YES | `NULL` | - | Attachment timestamp |
| **Unique Keys** | - | - | - | `UNIQUE(post_id, media_id)` | Unique attachment constraint |

---

## 5. Page Domain

### `pages`
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Page ID |
| `key` | `string(64)` | NO | - | `UNIQUE` | Stable internal key (`home`, `about`, `contact`, optional future: `terms`, `privacy`) |
| `status` | `string(32)` | NO | `'PUBLISHED'` | `INDEX` | Page status |
| `created_at` | `timestamp` | YES | `NULL` | - | Creation timestamp |
| `updated_at` | `timestamp` | YES | `NULL` | - | Update timestamp |

### `page_translations`
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Translation ID |
| `page_id` | `foreignId` | NO | - | `FK -> pages.id ON DELETE CASCADE` | Parent page reference |
| `locale` | `string(12)` | NO | - | `INDEX` | Locale code (`vi`, `en`) |
| `title` | `string(255)` | NO | - | - | Localized page title |
| `slug` | `string(255)` | YES | `NULL` | `INDEX` | URL slug (NULL for homepage `/`) |
| `content` | `longText` | YES | `NULL` | - | Page body / rich description (HTML) |
| `seo_title` | `string(255)` | YES | `NULL` | - | Meta title |
| `seo_description` | `string(500)` | YES | `NULL` | - | Meta description |
| `created_at` | `timestamp` | YES | `NULL` | - | Creation timestamp |
| `updated_at` | `timestamp` | YES | `NULL` | - | Update timestamp |
| **Unique Keys** | - | - | - | `UNIQUE(page_id, locale)`<br>`UNIQUE(locale, slug)` | Global unique slug constraint (multiple NULLs allowed) |

### `page_media` (Gallery & Banner Attachments Pivot)
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Pivot ID |
| `page_id` | `foreignId` | NO | - | `FK -> pages.id ON DELETE CASCADE` | Page reference |
| `media_id` | `foreignId` | NO | - | `FK -> media.id ON DELETE RESTRICT` | Media reference (cannot delete media while attached) |
| `sort_order` | `unsignedInteger` | NO | `0` | - | Gallery sort order |
| `created_at` | `timestamp` | YES | `NULL` | - | Attachment timestamp |
| **Unique Keys** | - | - | - | `UNIQUE(page_id, media_id)` | Unique attachment constraint |

---

## 6. Media Domain

### `media`
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Media ID |
| `disk` | `string(32)` | NO | `'public'` | - | Storage disk (`public`, `s3`) |
| `path` | `string(500)` | NO | - | `INDEX` | File storage path on disk |
| `file_name` | `string(255)` | NO | - | - | Sanitized stored filename |
| `mime_type` | `string(100)` | NO | - | `INDEX` | MIME type (e.g. `image/webp`) |
| `extension` | `string(16)` | NO | - | - | File extension (`webp`, `png`, `jpg`) |
| `size_bytes` | `unsignedBigInteger` | NO | - | - | File size in bytes |
| `width` | `unsignedInteger` | YES | `NULL` | - | Image width in pixels |
| `height` | `unsignedInteger` | YES | `NULL` | - | Image height in pixels |
| `uploaded_by` | `foreignId` | YES | `NULL` | `FK -> users.id ON DELETE SET NULL` | Uploading staff/admin user |
| `created_at` | `timestamp` | YES | `NULL` | `INDEX` | Upload timestamp |
| `updated_at` | `timestamp` | YES | `NULL` | - | Update timestamp |

### `media_translations`
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Translation ID |
| `media_id` | `foreignId` | NO | - | `FK -> media.id ON DELETE CASCADE` | Parent media reference |
| `locale` | `string(12)` | NO | - | `INDEX` | Locale code (`vi`, `en`) |
| `alt_text` | `string(255)` | YES | `NULL` | - | Localized image alt text for SEO & Accessibility |
| `caption` | `string(500)` | YES | `NULL` | - | Localized media caption |
| `created_at` | `timestamp` | YES | `NULL` | - | Creation timestamp |
| `updated_at` | `timestamp` | YES | `NULL` | - | Update timestamp |
| **Unique Keys** | - | - | - | `UNIQUE(media_id, locale)` | Single translation per locale |

---

## 7. Lead Domain

### `bookings`
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Internal ID |
| `reference` | `string(32)` | NO | - | `UNIQUE` | Public reference code (`BK-20260825-ABC123`) |
| `service_id` | `foreignId` | NO | - | `FK -> services.id ON DELETE RESTRICT` | Selected service (mandatory in booking request; cannot delete service) |
| `service_price_id` | `foreignId` | YES | `NULL` | `FK -> service_prices.id ON DELETE RESTRICT` | Selected pricing tier (cannot delete tier while referenced) |
| `service_name_snapshot` | `string(255)` | YES | `NULL` | - | **Historical snapshot:** Service name displayed at submission time |
| `service_price_label_snapshot` | `string(150)` | YES | `NULL` | - | **Historical snapshot:** Pricing label displayed at submission time |
| `duration_minutes_snapshot` | `unsignedInteger` | YES | `NULL` | - | **Historical snapshot:** Duration in minutes at submission time |
| `price_amount_snapshot` | `unsignedBigInteger` | YES | `NULL` | - | **Historical snapshot:** Exact VND price displayed at submission time |
| `customer_name` | `string(150)` | NO | - | - | Customer full name |
| `phone` | `string(30)` | NO | - | - | Customer raw phone number |
| `phone_normalized` | `string(30)` | NO | - | `INDEX` | Canonical E.164 phone (`+84901234567`) |
| `email` | `string(255)` | YES | `NULL` | - | Customer email |
| `preferred_date` | `date` | NO | - | `INDEX` | Requested appointment date (Vietnam local date) |
| `preferred_time` | `time` | NO | - | - | Requested appointment time (Vietnam wall-clock time) |
| `guest_count` | `unsignedInteger` | NO | `1` | - | Number of guests |
| `customer_note` | `text` | YES | `NULL` | - | Customer special requests / health notes |
| `admin_note` | `text` | YES | `NULL` | - | Staff / internal operational notes |
| `status` | `string(32)` | NO | `'NEW'` | `INDEX` | Lifecycle status (`NEW`, `CONTACTED`, `CONFIRMED`, `COMPLETED`, `CANCELLED`, `NO_SHOW`) |
| `locale` | `string(12)` | NO | `'vi'` | `INDEX` | Submission locale |
| `contacted_at` | `timestamp` | YES | `NULL` | - | First phone/Zalo contact timestamp (UTC) |
| `confirmed_at` | `timestamp` | YES | `NULL` | - | Staff confirmation timestamp (UTC) |
| `completed_at` | `timestamp` | YES | `NULL` | - | Treatment completion timestamp (UTC) |
| `cancelled_at` | `timestamp` | YES | `NULL` | - | Cancellation timestamp (UTC) |
| `utm_source` | `string(100)` | YES | `NULL` | `INDEX` | Marketing attribution source (`facebook`, `google`, `zalo`) |
| `utm_medium` | `string(100)` | YES | `NULL` | - | Marketing medium (`cpc`, `organic`, `referral`) |
| `utm_campaign` | `string(150)` | YES | `NULL` | `INDEX` | Marketing campaign name |
| `utm_content` | `string(150)` | YES | `NULL` | - | Marketing ad creative / content identifier |
| `utm_term` | `string(150)` | YES | `NULL` | - | Search keyword term |
| `gclid` | `string(150)` | YES | `NULL` | - | Google Click ID |
| `gbraid` | `string(100)` | YES | `NULL` | - | Google iOS Click ID |
| `wbraid` | `string(100)` | YES | `NULL` | - | Google Web Click ID |
| `fbclid` | `string(150)` | YES | `NULL` | - | Facebook Click ID |
| `fbp` | `string(100)` | YES | `NULL` | - | Facebook Browser ID cookie |
| `fbc` | `string(100)` | YES | `NULL` | - | Facebook Click ID cookie |
| `landing_page` | `string(500)` | YES | `NULL` | - | Arrival page URL |
| `referrer` | `string(500)` | YES | `NULL` | - | Inbound HTTP Referrer |
| `created_at` | `timestamp` | YES | `NULL` | `INDEX` | Request submission timestamp (UTC) |
| `updated_at` | `timestamp` | YES | `NULL` | - | Record update timestamp (UTC) |
| `deleted_at` | `timestamp` | YES | `NULL` | `INDEX` | Soft delete timestamp (for accidental-deletion recovery) |

---

## 8. System Domain

### `site_settings`
| Column | Type | Nullable | Default | Constraints / Index | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` | NO | AUTO | `PRIMARY KEY` | Setting ID |
| `key` | `string(100)` | NO | - | `UNIQUE` | Configuration key (e.g. `hotline_primary`, `zalo_url`, `gtm_container_id`) |
| `value` | `longText` | YES | `NULL` | - | Stored configuration value |
| `type` | `string(32)` | NO | `'string'` | - | Cast type (`string`, `text`, `boolean`, `json`) |
| `group` | `string(32)` | NO | `'general'` | `INDEX` | Functional group (`general`, `contact`, `social`, `tracking`) |
| `is_public` | `boolean` | NO | `false` | `INDEX` | **Secure by default:** Public frontend accessibility flag |
| `created_at` | `timestamp` | YES | `NULL` | - | Creation timestamp |
| `updated_at` | `timestamp` | YES | `NULL` | - | Update timestamp |
