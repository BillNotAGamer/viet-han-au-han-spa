# Training Public Architecture

**Phase:** 10B - Training public listing and detail pages
**Status:** Accepted for implementation review
**Date:** 2026-08-30

## Routes

Vietnamese remains the default canonical locale without a prefix:

- `GET /dao-tao` -> `vi.training.index`
- `GET /dao-tao/{slug}` -> `vi.training.show`

English remains under the `/en` prefix:

- `GET /en/training` -> `en.training.index`
- `GET /en/training/{slug}` -> `en.training.show`

There is no `/vi/dao-tao` route tree. The existing `/vi` redirect to `/` remains the only Vietnamese-prefix compatibility route.

## Controller

`App\Http\Controllers\Public\TrainingController` is a thin HTTP coordinator. It reads the current locale from middleware, registers localized switch URLs, delegates all content preparation to `App\Services\PublicSite\TrainingContent`, and returns Blade views.

## Public Read Service

`App\Services\PublicSite\TrainingContent` owns public TrainingCourse composition:

- Listing query with pagination.
- Detail lookup through `training_course_translations.locale + slug`.
- Exact-locale presentation arrays for Blade.
- Publication and schedule filtering.
- Tuition, media, gallery, structured-content, FAQ, and localized detail URL preparation.

Blade receives prepared arrays and paginators only; database access does not occur in the public Training views.

## Exact Locale

Public Training pages never call translation fallback helpers. A TrainingCourse must have a `training_course_translations` row in the requested locale to appear on listing pages or resolve on detail pages.

Examples:

- `PUBLISHED` TrainingCourse with VI translation only appears at `/dao-tao`.
- The same record does not appear at `/en/training`.
- `/en/training/{vi-slug}` returns 404.

## Localized Slug

Training detail slugs are locale-specific database content. The VI slug is resolved only by `vi.training.show`; the EN slug is resolved only by `en.training.show`. Cross-locale slug fallback and silent redirects are prohibited.

## Publication Eligibility

Public eligibility uses the existing TrainingCourse lifecycle semantics:

- `training_courses.status = PUBLISHED`
- `training_courses.published_at IS NOT NULL`
- `training_courses.published_at <= now()`
- exact requested-locale translation exists

Future scheduled courses are not public until their `published_at` timestamp arrives. `published_at` is a UTC lifecycle timestamp and is not displayed as a course start date, enrollment date, or class schedule.

## Listing Pagination

The listing page uses Laravel pagination at 12 courses per page. Ordering is deterministic: `sort_order ASC, id ASC`. Pagination is query-based and not performed after loading all courses.

## Tuition

`training_courses.tuition_fee` is persisted as nullable unsigned integer VND. When present, it is displayed as a numeric VND amount. `0` is displayed as `0 ₫`; the public layer does not reinterpret it as free, contact-required, scholarship, discount, deposit, or installment semantics.

## Media and Gallery

Training hero media uses `training_courses.hero_media_id`. Gallery media uses the ordered `training_course_media` pivot. Media is resolved in `TrainingContent` through `PublicMediaResolver`, which checks the configured storage disk and returns `null` for missing physical files. Missing media never crashes public pages.

Media alt text uses exact-locale `media_translations.alt_text`. If absent, presentation uses `alt=""`.

## Structured Content

Training detail may render only existing translation fields: safe plain-text content, `duration_display`, `schedule_display`, `target_audience`, `curriculum_modules`, `benefits`, and `faqs`. Rich HTML content is stripped to plain text before Blade renders it. No instructor, class schedule, certificate, employment guarantee, or location data is invented.

## Empty States

`/dao-tao` and `/en/training` return 200 when no eligible records exist. Empty copy is localized and does not fabricate courses or expose diagnostics.

## Language Switching

Listing pages switch between `/dao-tao` and `/en/training`. Detail pages switch to the exact translated TrainingCourse slug when the target translation exists. If the target translation is missing, the switch target falls back to the target locale Training index.

## Inquiry CTA Boundary

Phase 10B does not expose a public TrainingInquiry submission endpoint. Training CTAs point to the existing Homepage contact fragment: `/#contact-preview` for VI and `/en#contact-preview` for EN.

## Inner-Page Header

Training pages use the approved Phase 9.5 sticky-style header state from initial render through the shared layout's `header-mode="solid"` prop. Header geometry and visual classes are not changed.

## Phase 10C+ Boundary

This architecture does not introduce Blog, About, Contact, Booking, Tracking, structured SEO, sitemap, OpenGraph, or public TrainingInquiry POST routes.
