# Phase 10A Invariant Map

Date: 2026-08-30
HEAD at implementation start: `b749ce0dbc8aecd11c9c05d2f6cb58a0a6df34af`

## Routing

- `GET /dich-vu` exists as `vi.services.index`: MACHINE VERIFIED by PHPUnit and route list.
- `GET /dich-vu/{slug}` exists as `vi.services.show`: MACHINE VERIFIED by PHPUnit and route list.
- `GET /en/services` exists as `en.services.index`: MACHINE VERIFIED by PHPUnit and route list.
- `GET /en/services/{slug}` exists as `en.services.show`: MACHINE VERIFIED by PHPUnit and route list.
- `/vi` remains a 301 redirect to `/`: MACHINE VERIFIED by PHPUnit.
- `/vi/dich-vu` is not canonical and returns 404: MACHINE VERIFIED by PHPUnit.
- No Training, Blog, About, Contact, Booking, Tracking, or SEO public routes were introduced by Phase 10A: SOURCE REVIEW and route-list review.

## Exact Locale

- Public Services listing requires exact requested-locale `service_translations.locale`: MACHINE VERIFIED by PHPUnit.
- Public Services detail lookup requires exact requested-locale `service_translations.locale + slug`: MACHINE VERIFIED by PHPUnit.
- Vietnamese Service content does not fallback under `/en`: MACHINE VERIFIED by PHPUnit.

## Locale-Specific Slugs

- VI slug resolves only under `/dich-vu/{slug}`: MACHINE VERIFIED by PHPUnit.
- EN slug resolves only under `/en/services/{slug}`: MACHINE VERIFIED by PHPUnit.
- Cross-locale slug lookup returns 404: MACHINE VERIFIED by PHPUnit.

## Publication

- `PUBLISHED` Services appear publicly: MACHINE VERIFIED by PHPUnit.
- `DRAFT` Services are excluded from listing and detail: MACHINE VERIFIED by PHPUnit.
- `ARCHIVED` Services are excluded from listing and detail: MACHINE VERIFIED by PHPUnit.

## Empty State

- `/dich-vu` returns 200 with no business records: MACHINE VERIFIED by PHPUnit.
- `/en/services` returns 200 with no business records: MACHINE VERIFIED by PHPUnit.
- Empty state does not fabricate Service records: MACHINE VERIFIED by PHPUnit.

## Media

- Existing physical Service hero media resolves to a public URL: MACHINE VERIFIED by PHPUnit.
- Missing physical Service media does not crash listing/detail pages: MACHINE VERIFIED by PHPUnit.
- Media alt text uses exact-locale `media_translations.alt_text`: MACHINE VERIFIED by PHPUnit.
- Wrong-locale media alt text does not leak: MACHINE VERIFIED by PHPUnit.

## Prices

- Active persisted integer VND price rows display publicly: MACHINE VERIFIED by PHPUnit.
- Optional missing price labels do not break rendering: MACHINE VERIFIED by PHPUnit.
- Wrong-locale price labels do not leak: MACHINE VERIFIED by PHPUnit.

## Category

- Category label displays only when exact requested-locale translation exists: MACHINE VERIFIED by PHPUnit.
- Missing category translation does not hide an otherwise valid Service: MACHINE VERIFIED by PHPUnit.

## Language Switching

- Services listing language switch targets the opposite locale listing route: SOURCE REVIEW.
- Bilingual Service detail language switch targets exact translated detail slug: MACHINE VERIFIED by PHPUnit.
- Missing target translation falls back to target locale Services index: SOURCE REVIEW.

## Header and CTA

- Homepage VI Services nav target is `/dich-vu`: MACHINE VERIFIED by PHPUnit.
- Homepage EN Services nav target is `/en/services`: MACHINE VERIFIED by PHPUnit.
- Header visual geometry CSS was not changed in Phase 10A: SOURCE REVIEW.
- Services inner pages use the existing sticky-style header state from initial render: MACHINE VERIFIED by PHPUnit and SOURCE REVIEW.
- Floating CTA and inner detail CTA target the existing Homepage `#contact-preview` fragment: SOURCE REVIEW.
- No Booking backend or `/dat-lich` route was introduced: SOURCE REVIEW and route-list review.

## Blade Query Isolation

- `resources/views/public/services/*.blade.php` contains no prohibited DB/Eloquent query patterns: MACHINE VERIFIED by `blade-db-query-review.txt`.

## Human Review Boundary

- Services listing desktop: HUMAN VISUALLY VERIFIED.
- Services listing mobile: HUMAN VISUALLY VERIFIED.
- Service detail desktop: HUMAN VISUALLY VERIFIED.
- Service detail mobile: HUMAN VISUALLY VERIFIED.
- Detail locale switching: HUMAN FUNCTIONALLY VERIFIED.
- Cross-locale slug 404 behavior: HUMAN FUNCTIONALLY VERIFIED.
- Browser screenshots and responsive QA were not automated in Phase 10A per instruction.
