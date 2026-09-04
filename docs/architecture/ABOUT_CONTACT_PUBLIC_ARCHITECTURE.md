# About And Contact Public Architecture

Phase 10D exposes the existing Pages CMS and public Site Settings as server-rendered About and Contact pages.

## Routes

- Vietnamese About: `GET /gioi-thieu`, route name `vi.about`
- English About: `GET /en/about`, route name `en.about`
- Vietnamese Contact: `GET /lien-he`, route name `vi.contact`
- English Contact: `GET /en/contact`, route name `en.contact`

Vietnamese routes remain prefixless. `/vi/gioi-thieu` and `/vi/lien-he` are not registered. No dynamic public Page slug route is added.

## Request Flow

Public static pages follow the locked public-site flow:

`Route -> App\Http\Controllers\Public\StaticPageController -> App\Services\PublicSite\StaticPageContent -> Eloquent/domain -> Blade`

`StaticPageController` resolves the current locale, selects the fixed Page key, delegates page composition, registers localized switch URLs, and returns Blade views.

## Fixed Page Keys

About and Contact are resolved by stable machine keys:

- About uses `pages.key = about`
- Contact uses `pages.key = contact`

`page_translations.slug` is nullable editorial data and does not define public Phase 10D routing.

## Page Eligibility

Public static Page visibility requires:

- the required fixed `pages.key`
- `pages.status = PUBLISHED`
- an exact `page_translations.locale` row for the requested locale

Pages have no `published_at` scheduling column, so Phase 10D does not invent publication scheduling semantics. Missing Pages, `DRAFT`, `ARCHIVED`, and missing requested-locale translations return public `404`.

## Exact Locale

About and Contact never fall back from Vietnamese to English or from English to Vietnamese. English public routes must not render Vietnamese Page content.

## Page Media

Page media uses the existing `page_media` pivot ordered by `sort_order ASC`. `StaticPageContent` resolves media through `PublicMediaResolver` before rendering. Missing physical files are omitted safely. Media alt and captions use exact requested-locale `media_translations`; missing exact-locale alt renders `alt=""`.

## Rich Content Safety

`page_translations.content` is authored through Filament RichEditor and stored as rich text. Phase 10D does not add a sanitizer package or raw HTML rendering. Public static pages use the accepted launch-safe fallback: script-like blocks are removed, tags are stripped, whitespace is normalized, and Blade renders escaped plain-text paragraphs.

## Contact Settings

Contact operational details are prepared through an explicit allow-list using `SiteSettings::getPublic(...)`:

- `contact.phone`
- `contact.email`
- `contact.address`
- `business.hours`
- `social.facebook_url`
- `social.zalo_url`
- `social.youtube_url`

The Contact page never dumps all public settings. Private settings and unrelated public settings are not prepared for rendering.

## Safe Contact Links

Prepared contact links are validated before they become active links:

- email uses `mailto:` only when the value validates as an email address
- phone uses `tel:` only from normalized phone characters matching a conservative numeric pattern
- external/social links require `http` or `https`

Unsafe schemes such as `javascript:`, `data:`, and `file:` are omitted.

## Language Switching

About uses fixed route-pair switching:

- `/gioi-thieu` -> `/en/about` when the English About translation exists
- `/en/about` -> `/gioi-thieu` when the Vietnamese About translation exists

Contact uses fixed route-pair switching:

- `/lien-he` -> `/en/contact` when the English Contact translation exists
- `/en/contact` -> `/lien-he` when the Vietnamese Contact translation exists

If the target translation is missing, the language switcher falls back to the target-locale Homepage.

## Header And Footer

The shared header keeps the Phase 9.5 visual geometry. Phase 10D changes only About and Contact navigation targets:

- VI About -> `/gioi-thieu`
- EN About -> `/en/about`
- VI Contact -> `/lien-he`
- EN Contact -> `/en/contact`

Footer quick navigation points to the same canonical routes. No visual redesign is included.

## Blade Boundary

`resources/views/public/about.blade.php` and `resources/views/public/contact.blade.php` receive prepared arrays from `StaticPageContent`. They do not execute direct database or Eloquent lookups.

## Phase 10E Boundary

Booking, contact submission, tracking, technical SEO, dynamic public Page routes, and any mutation endpoint remain outside Phase 10D.
