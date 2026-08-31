# Phase 10C Invariant Map

| Invariant | Evidence | Result |
| --- | --- | --- |
| VI Blog index canonical | `GET /blog`, `vi.blog.index` | PASS |
| EN Blog index canonical | `GET /en/blog`, `en.blog.index` | PASS |
| VI Blog detail route | `GET /blog/{slug}`, exact VI slug | PASS |
| EN Blog detail route | `GET /en/blog/{slug}`, exact EN slug | PASS |
| No `/vi/blog` canonical route | Route test and route list | PASS |
| Public publication rule | `status = PUBLISHED`, `published_at IS NOT NULL`, `published_at <= now()` | PASS |
| Future posts excluded | Listing hidden and direct detail 404 | PASS |
| Exact locale required | `post_translations.locale = requested locale` | PASS |
| No VI fallback under `/en` | EN listing/detail require EN translation | PASS |
| Locale-specific slug | Cross-locale slug requests return 404 | PASS |
| Empty listing | `/blog` and `/en/blog` return 200 with localized empty state | PASS |
| Category label exact locale | Missing requested-locale category label is omitted; wrong locale not leaked | PASS |
| No category archive route | `/blog/category/{slug}` and `/en/blog/category/{slug}` not registered | PASS |
| Author privacy | Only safe author name is rendered; email/auth fields are not exposed | PASS |
| Rich content safety | RichEditor content converted to escaped plain text; no raw CMS HTML emitted | PASS |
| SEO fields deferred | `seo_title` and `seo_description` not rendered visibly | PASS |
| Missing media safe | Missing physical media returns no image and page remains 200 | PASS |
| Exact-locale media alt | Missing exact-locale alt renders empty alt; wrong-locale alt not leaked | PASS |
| Gallery ordering | `post_media.sort_order ASC` | PASS |
| Detail language pairing | Entity translations generate exact other-locale detail URLs | PASS |
| Missing target translation switch fallback | Falls back to target Blog index | PASS |
| Header Blog route-aware | VI `/blog`, EN `/en/blog`; Services and Training links preserved | PASS |
| Header geometry unchanged | Only header URL variable/link target changed | PASS |
| No public Blog mutation routes | No public POST Blog routes registered | PASS |
| No DB query in Blade | Review of public Blog Blade templates | PASS |
| No migrations | No database migration files changed or added | PASS |
| No Phase 10D routes | About, Contact, Booking, comments, search, newsletter, SEO routes not added | PASS |
