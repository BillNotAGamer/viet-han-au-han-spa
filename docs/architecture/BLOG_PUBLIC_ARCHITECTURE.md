# Blog Public Architecture

Phase 10C adds server-rendered public Blog listing and article detail pages on top of the Phase 6 Blog CMS schema.

## Routes

- Vietnamese listing: `GET /blog`, route name `vi.blog.index`
- Vietnamese detail: `GET /blog/{slug}`, route name `vi.blog.show`
- English listing: `GET /en/blog`, route name `en.blog.index`
- English detail: `GET /en/blog/{slug}`, route name `en.blog.show`
- `/vi/blog` is not canonical and is not registered.
- Category archives, search, comments, newsletter, and mutation routes are deferred.

## Request Flow

Public Blog pages follow the locked public-site flow:

`Route -> App\Http\Controllers\Public\BlogController -> App\Services\PublicSite\BlogContent -> Eloquent/domain -> Blade`

`BlogController` resolves the current locale from middleware, registers localized switch URLs on the request, delegates all content composition to `BlogContent`, and returns Blade views.

## Publication Rule

Public Blog visibility requires all of the following:

- `posts.status = PUBLISHED`
- `posts.published_at IS NOT NULL`
- `posts.published_at <= now()`
- an exact `post_translations.locale` row for the requested locale

`DRAFT`, `ARCHIVED`, future-scheduled `PUBLISHED`, and missing-locale records are hidden from listing and return `404` on detail routes.

## Published At

`published_at` is the UTC publication lifecycle timestamp established by Phase 6. It is used for scheduling and public ordering. It is not a service date, class date, or content creation date. Public presentation displays it as a date in `Asia/Ho_Chi_Minh`.

## Exact Locale And Slugs

Post detail lookup resolves by exact requested locale plus exact requested slug through `post_translations`.

- Vietnamese slug resolves only under `/blog/{slug}`.
- English slug resolves only under `/en/blog/{slug}`.
- Cross-locale slug fallback is prohibited.
- Vietnamese content must never render under `/en/blog`.

## Listing

The listing uses bounded database pagination at 12 posts per page, ordered by `published_at DESC, id DESC`. Each item receives prepared data:

- exact-locale title
- exact-locale excerpt, or a plain-text content excerpt
- exact-locale category label when available and category is published
- safe author display name when present
- publication date
- resolved hero media when the physical file exists
- canonical detail URL for the current locale

The empty state returns HTTP 200 with localized neutral copy and no fabricated articles.

## Category Behavior

Post category is metadata only in Phase 10C. Category archive/filter routes are not registered. Category labels render only when the category is `PUBLISHED` and has an exact requested-locale translation. Missing or wrong-locale category translations are omitted without hiding an otherwise public post.

## Author Privacy

When a post author exists, public pages may render only `users.name`. Email, account ID, password hash, admin flags, remember tokens, and authentication metadata are never prepared for public presentation.

## Rich Content Rendering

`post_translations.content` is authored through Filament RichEditor and stored as rich text in a `longText` column. Phase 10C does not introduce an HTML sanitizer package and does not implement full rich-content rendering. Blog public pages convert article content to escaped plain-text paragraphs after stripping tags and script-like blocks. SEO fields are not rendered as visible content.

## Media And Gallery

Hero media uses `posts.hero_media_id`. Gallery media uses the `post_media` pivot ordered by `sort_order ASC`. All media is resolved in `BlogContent` through `PublicMediaResolver`, not in Blade. Missing physical files return no media and do not crash public pages. Media alt and captions use exact requested-locale `media_translations` only; missing exact-locale alt renders `alt=""`.

## Language Switching

Listing switches between `/blog` and `/en/blog`. Detail pages switch by entity translation:

- VI detail with EN translation -> `/en/blog/{exact-en-slug}`
- EN detail with VI translation -> `/blog/{exact-vi-slug}`

If the target locale translation is missing, the switch target falls back to the target-locale Blog index.

## Header Behavior

The shared public header keeps the Phase 9.5 visual geometry. Phase 10C changes only the Blog navigation target:

- VI Blog -> `/blog`
- EN Blog -> `/en/blog`

Services and Training links remain routed to their Phase 10A and 10B public pages.

## Blade Boundary

`resources/views/public/blog/*.blade.php` receives prepared arrays and paginator data. Blog Blade files do not execute direct database or Eloquent lookups.

## Phase 10D Boundary

About, Contact, Booking, tracking, comments, search, category archive pages, newsletter submission, and technical SEO are deferred outside Phase 10C.
