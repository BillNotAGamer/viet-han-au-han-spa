# SITE SETTINGS CMS & RUNTIME CONFIGURATION ARCHITECTURE

**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Phase:** Phase 7C — Site Settings CMS & Backend CMS Integration Closure  
**Status:** ACCEPTED (Architect-Approved)  
**Navigation Group:** `Hệ thống`  
**Navigation Label:** `Cài đặt` (Icon: `heroicon-o-cog-6-tooth`, sort order: 1)  

---

## 1. Scope & Architectural Purpose

`site_settings` provides a typed, cache-backed operational configuration store for non-secret global website metadata, including:
* Contact details (phone, hotline, email, primary address).
* Social channel links (Facebook, Zalo, YouTube).
* Public analytics/tag container IDs (GTM container ID, GA4 measurement ID, Meta Pixel ID).
* Global business operating hours.

**Non-Goals & Strict Boundaries:**
* **Never for Secrets:** API keys, database credentials, SMTP passwords, Meta CAPI tokens, private signing keys belong exclusively in `.env` and secure server environment variables.
* **Never for Editorial Content:** Page body text, service descriptions, blogs, and translations belong in dedicated translatable domain tables.
* **Never for Translations:** Site Settings are global, un-localized key-value pairs.

---

## 2. Key Grammar & Immutability

* **Grammar:** Lowercase alphanumeric with dots, hyphens, or underscores (`/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/`).
  - Examples: `contact.phone`, `social.facebook_url`, `tracking.gtm_container_id`.
  - Normalization trims and converts to lowercase before saving.
* **Immutability Invariant:**
  - **Create:** Editable and mandatory.
  - **Edit:** Read-only/disabled in the UI. Server-side `SiteSettingWriter::update` strictly preserves the original key, preventing accidental decoupling from application code.

---

## 3. Supported Types & Deterministic Serialization

Backing values are strictly restricted to `SiteSettingType` backed enum:
* `string`: Stored as plain string (e.g. `'0901234567'`).
* `text`: Stored as plain multi-line string.
* `boolean`: Stored canonically as `'1'` or `'0'`.
* `json`: Validated JSON string; invalid JSON is rejected with `InvalidArgumentException`.

---

## 4. Secret-Bearing Key Guard

`SiteSettingWriter` enforces a server-side deny-list preventing storage of secret-bearing keys. Keys containing any of the following substrings are rejected with clear administrative errors:
`password`, `passwd`, `secret`, `private_key`, `client_secret`, `access_token`, `app_key`, `smtp_password`, `database_password`, `db_password`.

Public tracking identifiers (e.g. `tracking.gtm_container_id`, `tracking.ga4_measurement_id`, `tracking.meta_pixel_id`) are not secrets and are allowed.

---

## 5. Cache Architecture & Shared-Hosting Portability

* Handled through `App\Services\Settings\SiteSettings` using standard Laravel `Cache` facades.
* **Cache Namespace:** `site_settings:<key>` and `site_settings:public_all`.
* **Shared-Hosting Compatible:** Does NOT require Redis, Memcached, persistent background daemons, or supervisor workers. Functions seamlessly with `database`, `file`, or `array` cache drivers.
* **Cache Invalidation:** Mutations (`create`, `update`, `delete`) via `SiteSettingWriter` automatically flush the specific key and the public map after successful DB transaction.

---

## 6. Public vs. Private Setting Boundaries

* Default visibility: `is_public = false`.
* `SiteSettings::getPublic(key)` and `SiteSettings::publicSettings()` return only settings where `is_public === true`.
* Internal server code may access private operational settings via `SiteSettings::get(key)`.
* Zero public HTTP endpoints (`/api/settings`, `/settings.json`) exist in Phase 7. Settings are consumed strictly server-side by Blade/controllers.

---

## 7. Administrative Policies & Deletion

* `SiteSettingPolicy` restricts all access to `$user->is_admin === true`.
* Hard deletion is supported with confirmation on individual records.
* Bulk deletion is strictly disabled to prevent accidental operational outages.
