# Phase 10D Invariant Map

| Invariant | Evidence | Status |
| --- | --- | --- |
| VI About canonical route | `GET /gioi-thieu`, `vi.about` | PASS |
| EN About canonical route | `GET /en/about`, `en.about` | PASS |
| VI Contact canonical route | `GET /lien-he`, `vi.contact` | PASS |
| EN Contact canonical route | `GET /en/contact`, `en.contact` | PASS |
| No `/vi/...` static page routes | `/vi/gioi-thieu` and `/vi/lien-he` are not registered | PASS |
| Fixed Page keys | About resolves key `about`; Contact resolves key `contact` | PASS |
| Page slug irrelevant | `PageTranslation.slug` is not used for public static routing | PASS |
| PUBLISHED Page required | `StaticPageContent` filters `pages.status = PUBLISHED` | PASS |
| No Page scheduling invented | Pages have no `published_at`; eligibility uses status and exact locale only | PASS |
| Exact locale required | `whereHas('translations', locale = requested locale)` | PASS |
| No VI fallback under EN | Missing EN translation returns 404 | PASS |
| Contact public allow-list | Contact requests only explicit supported SiteSetting keys | PASS |
| Private settings not leaked | SiteSettings::getPublic(...) used; no publicSettings dump | PASS |
| Unsafe URL schemes omitted | email/phone/http URL validation in composition service | PASS |
| Missing Contact settings safe | Contact page renders localized empty state, no fabricated contact data | PASS |
| Rich content safe fallback | script-like blocks removed, tags stripped, Blade escaped | PASS |
| Page media safe | PublicMediaResolver resolves media outside Blade and omits missing files | PASS |
| Exact-locale media alt | PublicMediaResolver loads requested-locale MediaTranslation only | PASS |
| Language switch fixed pairs | About and Contact set localized_urls to fixed route pairs | PASS |
| Missing target translation fallback | language switch falls back to target home route | PASS |
| Header About/Contact route-aware | Header links point to `/gioi-thieu`, `/lien-he`, `/en/about`, `/en/contact` | PASS |
| Footer About/Contact route-aware | Footer quick links point to canonical static routes | PASS |
| Header geometry unchanged | Phase 10D changed route variables only, not header classes/CSS geometry | PASS |
| Contact mutation endpoint absent | Phase 10D adds GET routes only | PASS |
| Public static Page Blade no DB queries | Blade DB-query review | PASS |
| No migrations | No database migration files added | PASS |
| No packages | Composer and npm dependency files unchanged | PASS |
| No Booking | No Booking route or endpoint added | PASS |
| No Phase 10E routes | Route list adds only About/Contact public routes | PASS |
