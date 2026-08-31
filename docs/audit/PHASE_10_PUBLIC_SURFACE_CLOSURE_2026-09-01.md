# Phase 10 Public Surface Closure

## Status

- Phase 10A Services: CLOSED
- Phase 10B Training: CLOSED
- Phase 10C Blog: CLOSED
- Phase 10D About/Contact: CLOSED
- Phase 10E Integration: ready for final closure

## Route Matrix

Vietnamese canonical public routes:

- `/`
- `/dich-vu`
- `/dich-vu/{slug}`
- `/dao-tao`
- `/dao-tao/{slug}`
- `/blog`
- `/blog/{slug}`
- `/gioi-thieu`
- `/lien-he`

English canonical public routes:

- `/en`
- `/en/services`
- `/en/services/{slug}`
- `/en/training`
- `/en/training/{slug}`
- `/en/blog`
- `/en/blog/{slug}`
- `/en/about`
- `/en/contact`

`/vi` remains the canonical redirect to `/`. No `/vi/...` public route tree is part of Phase 10.

## Core Invariants

- Public localized content requires exact requested-locale translations.
- Vietnamese content does not fall back under English public URLs.
- Services, Training, and Blog detail routes use locale-specific translated entity slugs.
- About and Contact use fixed Page keys and fixed canonical route pairs.
- Public listings enforce each domain's publication policy.
- Shared public Blade templates do not execute direct database queries.
- Header and footer primary navigation use canonical route helpers.
- Booking, public contact submission, tracking, and technical SEO remain deferred.

## Remaining Known Debt

- Legacy duplicate public layout.
- Full RichEditor public HTML renderer/sanitizer.
- Production content/media finalization.
- Booking not yet implemented.
- Technical SEO not yet implemented.
