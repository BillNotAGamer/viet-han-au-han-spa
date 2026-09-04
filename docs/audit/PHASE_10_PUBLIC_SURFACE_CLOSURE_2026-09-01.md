# Phase 10 Public Surface Closure

## Status

- Phase 10A Services: CLOSED
- Phase 10B Training: CLOSED
- Phase 10C Blog: CLOSED
- Phase 10D About/Contact: CLOSED
- Phase 10E Integration: CLOSED

PHASE 10 PUBLIC SURFACE: CLOSED

## Commit References

- Phase 10D implementation: `dfb94d596574ac03f9dd56e3fdc0ad2b6213466c`
- Phase 10D closure: `2df4441dc01a5dba889cc16473296ab64ab176df`
- Phase 10E implementation: `053b85f71847c6a97b16da88f643b3b898feed72`

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
