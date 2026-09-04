# ADR-022: Services Public Routing and Locale Policy

**Status:** ACCEPTED
**Date:** 2026-08-29

## Context

Phase 10A introduces public Services listing and detail pages on top of the existing Services CMS. The project already requires prefixless Vietnamese canonical URLs, `/en` English URLs, exact public translations, and no fallback rendering of Vietnamese content under English URLs.

## Decision

Adopt canonical Services routes:

- Vietnamese listing: `/dich-vu`
- Vietnamese detail: `/dich-vu/{slug}`
- English listing: `/en/services`
- English detail: `/en/services/{slug}`

Service detail slugs are locale-specific. Detail lookup is performed through `service_translations.locale + slug`, and the parent `Service` must be `PUBLISHED`.

There is no cross-locale slug fallback. A Vietnamese slug under `/en/services/{slug}` returns 404, and an English slug under `/dich-vu/{slug}` returns 404.

Unpublished Services are not public. `DRAFT` and `ARCHIVED` records are excluded from listings and return 404 for detail requests.

Public Services listing and detail pages require exact requested-locale translations. Public Services Blade templates receive prepared data and perform no database or Eloquent queries.

## Consequences

Editors can publish Vietnamese Services before English translations exist without leaking Vietnamese content into English canonical URLs. English detail pages become live only after exact English translations and slugs are present.

Language switching on detail pages must use the target Service translation slug when available, otherwise it falls back to the target locale Services index.

The public Services read layer remains separate from Filament writer services and does not mutate domain data during rendering.
