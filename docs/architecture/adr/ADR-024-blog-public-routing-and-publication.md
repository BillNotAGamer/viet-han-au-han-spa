# ADR-024: Blog Public Routing And Publication

## Status

ACCEPTED

## Context

Phase 10C exposes the Phase 6 Blog CMS on the public server-rendered Blade site. The project localization policy requires Vietnamese canonical URLs without a `/vi` prefix and exact-locale content under English routes. Blog posts also have lifecycle publication timestamps that support scheduled publishing.

## Decision

- Vietnamese Blog listing is `/blog`.
- English Blog listing is `/en/blog`.
- Blog detail slugs are locale-specific and are resolved only through exact `post_translations.locale + slug`.
- Cross-locale slug fallback is prohibited.
- Public Blog content requires `posts.status = PUBLISHED`, non-null `published_at`, `published_at <= now()`, and an exact requested-locale translation.
- Scheduled future posts are not public.
- `DRAFT` and `ARCHIVED` posts return public 404 on detail routes.
- Public Blog Blade templates perform no direct database or Eloquent queries.
- Phase 10C adds no public Blog mutation routes.
- RichEditor content must use a verified safe renderer or an escaped/plain-text fallback. Phase 10C uses escaped plain text because no project-approved sanitizer or RichEditor public renderer is established.

## Consequences

The public Blog can launch with correct locale, slug, and publication behavior without introducing new packages or schema changes. Rich formatting is intentionally limited until a verified rendering policy exists. Category archives, search, comments, newsletter, and technical SEO remain deferred.
