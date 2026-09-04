# ADR-025: About And Contact Static Page Routing

## Status

ACCEPTED

## Context

Phase 10D exposes About and Contact pages from the existing Pages CMS. The Page model uses immutable machine keys and nullable localized slugs. The localization policy requires Vietnamese canonical URLs without a `/vi` prefix and exact requested-locale content under English routes.

## Decision

- About uses fixed Page key `about`.
- Contact uses fixed Page key `contact`.
- Vietnamese About is `/gioi-thieu`.
- English About is `/en/about`.
- Vietnamese Contact is `/lien-he`.
- English Contact is `/en/contact`.
- `PageTranslation.slug` does not define canonical About or Contact public routes.
- Public static Page rendering requires `pages.status = PUBLISHED` and an exact requested-locale translation.
- Cross-locale content fallback is prohibited.
- Contact settings use an explicit public-safe allow-list through `SiteSettings::getPublic(...)`.
- Unsafe URL schemes are not rendered as active contact links.
- Phase 10D Contact is GET-only and adds no submission endpoint.
- Public static Page Blade templates perform no direct database or Eloquent queries.

## Consequences

About and Contact can launch on stable canonical URLs while retaining the CMS translation model and public safety boundaries. Dynamic Page routing, contact submissions, Booking, and technical SEO remain deferred.
