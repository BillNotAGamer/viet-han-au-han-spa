# Phase 10E Invariant Map

| Invariant | Evidence | Status |
| --- | --- | --- |
| VI primary nav is prefixless | Header/footer route helpers | PASS |
| EN primary nav uses `/en` | Header/footer route helpers | PASS |
| No primary nav placeholders | Header route matrix test | PASS |
| Services chevron removed | Header source and test | PASS |
| No fake dropdown semantics | No `aria-haspopup` in header | PASS |
| Header active state present | `aria-current="page"` on active section | PASS |
| Home switch `/` <-> `/en` | Locale switch matrix test | PASS |
| Services switch `/dich-vu` <-> `/en/services` | Locale switch matrix test | PASS |
| Training switch `/dao-tao` <-> `/en/training` | Locale switch matrix test | PASS |
| Blog switch `/blog` <-> `/en/blog` | Locale switch matrix test | PASS |
| About switch `/gioi-thieu` <-> `/en/about` | Locale switch matrix test | PASS |
| Contact switch `/lien-he` <-> `/en/contact` | Locale switch matrix test | PASS |
| Entity detail switches use translated slugs | Phase 10A/10B/10C tests remain in full suite | PASS |
| Static Page switches use fixed pairs | Phase 10D tests remain in full suite | PASS |
| Header geometry documented | Header final metrics evidence | PASS |
| Mobile header preserved | Source review | PASS |
| Homepage overlay/sticky source preserved | Header mode and CSS source review | PASS |
| Inner solid header source preserved | `<x-layouts.public header-mode="solid">` views | PASS |
| Active public layout identified | `<x-layouts.public>` usage | PASS |
| Duplicate layout left deferred | Architecture document | PASS |
| Booking route not added | Route/test review | PASS |
| Public mutation route not added | Route/test review | PASS |
| No migrations | Git diff and migration review | PASS |
| No packages | Composer/npm lock files unchanged | PASS |
| Shared public Blade no DB queries | Blade DB-query review | PASS |
| No Phase 11 work | Route boundary and diff scope | PASS |
