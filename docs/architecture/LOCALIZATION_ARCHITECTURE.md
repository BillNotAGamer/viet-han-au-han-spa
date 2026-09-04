# LOCALIZATION & BILINGUAL ROUTING ARCHITECTURE

**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Phase:** Phase 2 — Bilingual Localization Foundation  
**Status:** ACCEPTED (Architect-Approved)  

---

## 1. Locale Baseline & Policy

The website strictly supports two languages:
* **Default / Primary Locale:** `vi` (Vietnamese)
* **Secondary Locale:** `en` (English)
* **Fallback Locale:** `vi` (Vietnamese)

Supported locales are configuration-driven via `config/localization.php`. Future locale additions (e.g. `ko`, `zh`) will only require updating this configuration file without modifying database schema or introducing rigid PHP enum types.

---

## 2. Canonical URL Architecture

Canonical URLs are deterministic and follow strict prefix rules:
* **Vietnamese (Default):** **NO URL prefix** (e.g. `/`, `/dich-vu`, `/dao-tao-hoc-vien`, `/blog`, `/gioi-thieu`, `/lien-he`).
* **English (Secondary):** **`/en` URL prefix** (e.g. `/en`, `/en/services`, `/en/training`, `/en/blog`, `/en/about`, `/en/contact`).

### Anti-Duplication Rule
There is **NO canonical `/vi` route tree**. Any request to `/vi` is issued an HTTP 301 Permanent Redirect to `/`.

### URL-as-Truth Resolution Rule
The **URL is the sole source of truth** for public content locale resolution. The application does NOT perform automatic redirects based on `Accept-Language`, browser locale, IP geolocation, or cookies. This guarantees deterministic crawling for search engines, paid ads (Google Ads, Meta Ads), and social link previews.

---

## 3. Route Naming & Segment Mapping

Public routes follow a structured naming convention: `{locale}.{domain}.{action}` (e.g. `vi.home`, `en.home`, `vi.services.index`, `en.services.index`).

### Reserved Future Route Segments
| Logical Route | Vietnamese URL (`vi`) | English URL (`en`) | Route Name |
| :--- | :--- | :--- | :--- |
| **Home** | `/` | `/en` | `vi.home` / `en.home` |
| **Services Index** | `/dich-vu` | `/en/services` | `vi.services.index` / `en.services.index` |
| **Training Index** | `/dao-tao-hoc-vien` | `/en/training` | `vi.training.index` / `en.training.index` |
| **Blog Index** | `/blog` | `/en/blog` | `vi.blog.index` / `en.blog.index` |
| **About Page** | `/gioi-thieu` | `/en/about` | `vi.about` / `en.about` |
| **Contact Page** | `/lien-he` | `/en/contact` | `vi.contact` / `en.contact` |

---

## 4. UI Strings vs Database Content Separation

The application enforces a strict architectural boundary between static interface strings and dynamic business content:

| Content Category | Storage Mechanism | Examples |
| :--- | :--- | :--- |
| **Static UI Strings** | Laravel Language Files (`lang/{locale}/`) | Navigation labels (`lang/vi/navigation.php`), CTA buttons ("Đặt lịch"), form labels, generic error/success messages. |
| **Dynamic Editorial Content** | Database Translation Tables | Service names, rich descriptions, translated slugs, syllabus modules, benefits, FAQs, page copy, media alt texts, SEO meta titles/descriptions. |

Editorial business content must **never** be duplicated into PHP language files.

---

## 5. Model Translation & Fallback Policy

Translatable Eloquent models utilize the `App\Models\Concerns\HasTranslations` trait:
* **Exact Translation (`$model->translationFor(string $locale)`):**
  - Returns the translation record matching the requested locale.
  - Reuses the eager-loaded `translations` collection when loaded to prevent N+1 query overhead.
  - Returns `null` if the exact translation does not exist.
* **Explicit Fallback (`$model->translationOrFallback(string $locale, ?string $fallback = null)`):**
  - Used strictly in non-routing application contexts (e.g., internal notifications, administrative summaries).
  - Explicitly falls back to `vi` if the requested locale is missing.
* **Public Route Non-Masquerade Rule:**
  - Public localized detail routes (e.g. `/en/services/{slug}`) **MUST require an exact translation** in the requested locale.
  - Missing English translations must **never** silently render Vietnamese content under an English canonical URL. If a translation does not exist, the route returns 404.

---

## 6. Localized Slug Resolution

Translated entities are queried using compound indexed lookups:
```php
Service::whereSlug('vi', 'massage-co-vai-gay')->first();
Service::whereSlug('en', 'neck-shoulder-massage')->first();
```
Because the database enforces `UNIQUE (locale, slug)`, slug resolution is deterministic and fast across SQLite and MySQL/MariaDB.

---

## 7. Language Switch Foundation

Static routes map between locales using `App\Support\Localization::switchLocaleUrl($targetLocale)`.

For dynamic content detail pages (implemented in Phase 10):
* If the target translation exists: navigates to the translated entity URL.
* If the target translation does not exist: navigates to the localized domain index (e.g. `/dich-vu` -> missing EN -> `/en/services`), avoiding broken 404 links or invalid slug fabrication.

---

## 8. HTML `lang` Attribute & Admin Panel Isolation

* Public Blade views dynamically output `<html lang="{{ app()->getLocale() }}">`.
* Filament Admin Panel (`/admin`) is completely isolated from public locale middleware and preserves standard administrative routing and authentication.
