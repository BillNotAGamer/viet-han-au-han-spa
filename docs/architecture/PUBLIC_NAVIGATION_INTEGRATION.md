# Public Navigation Integration

Phase 10E finalizes the shared public navigation surface after Services, Training, Blog, About, and Contact routes exist.

## Canonical Route Matrix

Vietnamese public navigation is prefixless:

- Home -> `/`
- About -> `/gioi-thieu`
- Services -> `/dich-vu`
- Training -> `/dao-tao`
- Blog -> `/blog`
- Contact -> `/lien-he`

English public navigation uses `/en`:

- Home -> `/en`
- About -> `/en/about`
- Services -> `/en/services`
- Training -> `/en/training`
- Blog -> `/en/blog`
- Contact -> `/en/contact`

`/vi` remains the canonical redirect to `/`. No `/vi/...` public route tree is introduced.

## Header Architecture

The shared public header keeps the centered-logo architecture:

- desktop left nav group
- true centered logo
- desktop right nav group plus locale switch
- mobile logo, locale switch, and hamburger drawer
- Homepage overlay mode with sticky transition
- solid/sticky-style mode for inner pages

Phase 10E removes the Services chevron because there is no real submenu. Services remains a normal navigation link.

## Header Metrics

Final CSS metrics:

- Desktop breakpoint: `1180px`
- Overlay desktop header height: `100px`
- Sticky/solid desktop header height: `90px`
- Overlay desktop logo: `80px x 80px`
- Sticky/solid desktop logo: `72px x 72px`
- Desktop nav font size: `15px`
- Desktop nav weight: `500`
- Desktop nav gap: `32px`
- Desktop nav gap at `1440px+`: `38px`
- Mobile header height: `76px`, sticky `70px`
- Mobile logo: `48px x 48px`, sticky `44px x 44px`

Before Phase 10E, the desktop header used `96px` overlay height, `88px` sticky height, `72px` overlay logo, `66px` sticky logo, `34px` nav gap, and `40px` nav gap at `1440px+`.

## Active State

The header applies `aria-current="page"` and a subtle gold active class to the current top-level section. No animated indicator is introduced.

## Footer Navigation

Footer quick navigation uses the same canonical route matrix as the header. Placeholder `#`, future Booking URLs, and wrong-locale routes are not used for primary navigation.

Footer contact data remains limited to the Phase 10D public-safe SiteSettings behavior. No new setting keys are introduced in Phase 10E.

## Language Switching

Global switch behavior:

- Home: `/` <-> `/en`
- Services index: `/dich-vu` <-> `/en/services`
- Service detail: exact translated Service slug; missing target translation -> target Services index
- Training index: `/dao-tao` <-> `/en/training`
- Training detail: exact translated TrainingCourse slug; missing target translation -> target Training index
- Blog index: `/blog` <-> `/en/blog`
- Blog detail: exact translated Post slug; missing target translation -> target Blog index
- About: `/gioi-thieu` <-> `/en/about`
- Contact: `/lien-he` <-> `/en/contact`

Entity detail switching is entity-based and never string-translates slugs. Static Page switching uses fixed route pairs.

## Booking Boundary

Phase 10E adds no Booking route, mutation route, contact submission route, newsletter route, comments, search, tracking, technical SEO, sitemap, OpenGraph, or JSON-LD.

The floating CTA continues to use existing safe contact destinations and does not point to a nonexistent Booking route.

## Active Public Layout

The active public layout is `resources/views/components/layouts/public.blade.php`, used by `<x-layouts.public>`. The legacy `resources/views/layouts/public.blade.php` remains deferred technical debt through `App\View\Components\PublicLayout` and is not deleted in Phase 10E.

## Known Deferred Debt

- Legacy duplicate public layout cleanup
- Future broader header polish only if explicitly approved
- Full RichEditor HTML renderer/sanitizer
- Real production content/media polish
