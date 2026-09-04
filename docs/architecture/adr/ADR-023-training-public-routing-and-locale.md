# ADR-023: Training Public Routing and Locale Policy

**Status:** ACCEPTED
**Date:** 2026-08-30

## Context

Phase 10B introduces public TrainingCourse listing and detail pages using the existing Phase 5 Training CMS schema. The public site already uses canonical Vietnamese prefixless routes, English `/en` routes, and exact-locale public content resolution.

## Decision

Vietnamese Training canonical URLs are:

- `/dao-tao`
- `/dao-tao/{slug}`

English Training canonical URLs are:

- `/en/training`
- `/en/training/{slug}`

Training detail slugs are locale-specific. Public lookup uses the requested locale and requested slug from `training_course_translations`; there is no cross-locale slug fallback.

Public Training pages require exact requested-locale translations. Vietnamese Training content must not render under English URLs.

Public eligibility uses the actual TrainingCourse lifecycle policy: `status = PUBLISHED`, non-null `published_at`, `published_at <= now()`, and exact requested-locale translation. Unpublished, archived, future-scheduled, nonexistent, and missing-locale detail requests return 404.

Public Training Blade templates perform no direct database or Eloquent queries.

Public Training inquiry submission remains out of scope for Phase 10B. No public POST TrainingInquiry routes are introduced.

## Consequences

Language switching on Training detail pages is entity-based: when the target translation exists, the target URL uses that translation's slug. When it does not exist, the switch target falls back to the localized Training index.

`published_at` remains publication lifecycle metadata and is not displayed as a class date or enrollment date.
