# DATABASE INDEX STRATEGY

**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Phase:** Phase 1A — Domain Model & Database Architecture Design (Closure Patch)  
**Status:** ACCEPTED (Architect-Approved)  

---

## 1. Indexing Principles

1. **Targeted Query Optimization:** Every index directly serves a concrete, high-frequency query pattern in the public UI, API, or Filament 5 admin panel.
2. **Compound Index Ordering:** Compound indexes order columns from highest equality selectivity to range/sorting filters (`[equality_col_1, equality_col_2, range_col]`).
3. **No Redundant Single Indexes:** If a column is the leading column of a compound index, a standalone index on that column is omitted.
4. **Write Efficiency on Shared Hosting:** Avoid excessive index creation to prevent write amplification during booking submissions and bulk content updates.

---

## 2. Table-by-Table Index Inventory (23 Tables)

### 2.1 Content & Translation Tables

#### `service_category_translations`
* `UNIQUE (service_category_id, locale)`: Enforces 1 translation per locale for a category.
* `UNIQUE (locale, slug)`: Primary routing lookup `WHERE locale = ? AND slug = ?`.

#### `services`
* `INDEX (status, is_featured, sort_order)`: Powers homepage featured treatments query (`WHERE status = 'PUBLISHED' AND is_featured = 1 ORDER BY sort_order ASC`).
* `INDEX (service_category_id, status, sort_order)`: Powers category service catalog page (`WHERE service_category_id = ? AND status = 'PUBLISHED' ORDER BY sort_order ASC`).

#### `service_translations`
* `UNIQUE (service_id, locale)`: Enforces 1 translation per locale for a service.
* `UNIQUE (locale, slug)`: Primary public route lookup `WHERE locale = ? AND slug = ?`.

#### `service_prices`
* `INDEX (service_id, is_active, sort_order)`: Powers treatment price card dropdown and booking selection (`WHERE service_id = ? AND is_active = 1 ORDER BY sort_order ASC`).

#### `service_price_translations`
* `UNIQUE (service_price_id, locale)`: Enforces 1 label translation per locale.

#### `training_courses`
* `INDEX (status, is_featured, sort_order)`: Powers homepage academy preview list.
* `INDEX (status, published_at)`: Powers course catalog listing.

#### `training_course_translations`
* `UNIQUE (training_course_id, locale)`: Enforces 1 translation per locale.
* `UNIQUE (locale, slug)`: Primary public course route lookup `WHERE locale = ? AND slug = ?`.

#### `post_category_translations`
* `UNIQUE (post_category_id, locale)`: Enforces 1 translation per locale.
* `UNIQUE (locale, slug)`: Category archive page routing.

#### `posts`
* `INDEX (status, published_at)`: Powers public blog listing with scheduled post filtering (`WHERE status = 'PUBLISHED' AND published_at <= NOW() ORDER BY published_at DESC`).
* `INDEX (post_category_id, status, published_at)`: Powers category-filtered blog feed.
* `INDEX (is_featured, status, published_at)`: Powers blog hero featured banner.

#### `post_translations`
* `UNIQUE (post_id, locale)`: Enforces 1 translation per locale.
* `UNIQUE (locale, slug)`: Primary blog article route lookup `WHERE locale = ? AND slug = ?`.

#### `pages`
* `UNIQUE (key)`: Guarantees stable singleton pages (`home`, `about`, `contact`, plus optional future keys).

#### `page_translations`
* `UNIQUE (page_id, locale)`: Enforces 1 translation per locale for a page.
* `UNIQUE (locale, slug)`: Enforces global unique page slugs per locale (allows multiple `NULL` slugs for homepage `/`).

---

### 2.2 Media & Attachment Tables

#### `media`
* `INDEX (mime_type)`: Powers media library filter by asset type in Filament.
* `INDEX (created_at)`: Powers media library chronologically sorted view.

#### `media_translations`
* `UNIQUE (media_id, locale)`: Enforces 1 metadata record per locale.

#### `service_media`
* `UNIQUE (service_id, media_id)`: Prevents duplicate image attachments.
* `INDEX (service_id, sort_order)`: Powers fast ordered gallery hydration.

#### `training_course_media`
* `UNIQUE (training_course_id, media_id)`: Prevents duplicate image attachments.
* `INDEX (training_course_id, sort_order)`: Powers fast ordered gallery hydration.

#### `post_media`
* `UNIQUE (post_id, media_id)`: Prevents duplicate image attachments.
* `INDEX (post_id, sort_order)`: Powers fast ordered gallery hydration.

#### `page_media`
* `UNIQUE (page_id, media_id)`: Prevents duplicate image attachments.
* `INDEX (page_id, sort_order)`: Powers fast ordered gallery hydration.

---

### 2.3 Lead Generation & Attribution Tables

#### `bookings`
* `UNIQUE (reference)`: Public alphanumeric lookup code (`BK-20260825-ABC123`).
* `INDEX (status, preferred_date)`: Powers Filament daily operational schedule and status filtering (`WHERE status = 'CONFIRMED' AND preferred_date = ?`).
* `INDEX (phone_normalized)`: Powers instant customer lookup, repeat customer identification, and deduplication.
* `INDEX (service_id, status)`: Powers service popularity and lead volume reporting.
* `INDEX (created_at)`: Powers date-range exports, dashboard analytics, and CRM synchronization.
* `INDEX (deleted_at)`: Powers soft-deleted record filtering (`whereNull('deleted_at')`).
* `INDEX (utm_source, utm_campaign, created_at)`: Powers ad attribution ROI reporting in Admin.

#### `training_inquiries`
* `UNIQUE (reference)`: Public lookup code (`TRN-20260825-XYZ789`).
* `INDEX (status, created_at)`: Powers counselor pipeline queue (`WHERE status = 'NEW' ORDER BY created_at DESC`).
* `INDEX (phone_normalized)`: Powers student lookup and duplicate inquiry merging.
* `INDEX (training_course_id, status)`: Powers course enrollment rate analytics.
* `INDEX (deleted_at)`: Powers soft-deleted record filtering.
* `INDEX (utm_source, utm_campaign, created_at)`: Powers course ad spend attribution reports.

#### `site_settings`
* `UNIQUE (key)`: Fast setting fetch by key.
* `INDEX (group, is_public)`: Powers batch loading of public frontend settings (`WHERE is_public = 1`) and grouped admin panels.

---

## 3. Indexes Intentionally Omitted & Justification

| Candidate Index | Reason for Omission |
| :--- | :--- |
| `bookings.email` | Low query frequency; primary customer identity in Vietnam is phone/Zalo. |
| `bookings.customer_name` | Handled via full-text/LIKE search in Filament; `phone_normalized` is the primary lookup key. |
| `service_translations.name` | Localized slugs (`slug`) are the indexed routing keys; names are loaded with the entity. |
| `media.file_name` | Media is addressed by relational ID or path; filenames are not queried in high-throughput paths. |
| Individual `utm_medium`, `utm_content`, `utm_term`, `gclid`, `fbclid` indexes | Low query selectivity; compound index on `(utm_source, utm_campaign, created_at)` satisfies all attribution ROI reporting without write overhead. |
