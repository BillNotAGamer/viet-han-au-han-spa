# Public Design System V2

**Direction:** Việt Hàn — Korean Quiet Luxury
**Status:** Phase 1 foundation; public-page migration is deferred to later phases.
**Source:** `resources/css/app.css` and `resources/views/components/public/v2/`

## Direction

V2 combines warm editorial hospitality, authentic spa photography, restrained Korean minimalism, slow cinematic pacing, and a strong burgundy identity. It is not generic beige minimalism, a card-heavy SaaS interface, metallic-gold luxury styling, or a template clone.

The intended page rhythm is approximately 65–70% ivory/paper, 20–25% espresso/deep-wine cinematic sections, and 8–12% burgundy/champagne accents. Champagne is an accent, not a default text color or decorative gradient.

## Semantic color system

| Token | Value | Role |
| --- | --- | --- |
| `--color-v2-brand` | `#5B1121` | Brand wine and primary action |
| `--color-v2-brand-strong` | `#310811` | Deep wine and primary hover |
| `--color-v2-surface` | `#FCF9F4` | Paper |
| `--color-v2-surface-soft` | `#F7F2EA` | Warm ivory |
| `--color-v2-surface-muted` | `#EEE7DF` | Restrained neutral section |
| `--color-v2-surface-dark` | `#171210` | Espresso |
| `--color-v2-text` | `#171210` | Primary text |
| `--color-v2-text-muted` | `#665C56` | Secondary text |
| `--color-v2-text-inverse` | `#FCF9F4` | Text on dark surfaces |
| `--color-v2-accent` | `#B99B6B` | Champagne accent |
| `--color-v2-border` | `#D8CCC0` | Fine warm-stone border |
| `--color-v2-border-strong` | `#B9A99C` | Strong border |
| `--color-v2-focus` | `#5B1121` | Focus indicator |

V2 components consume these tokens. Legacy hardcoded colors remain until their owning pages are migrated.

## Typography

- Cormorant Garamond owns hero H1, major editorial H2, display statements, pull quotes, and selected editorial numbers.
- Montserrat owns body copy, navigation, buttons, forms, labels, metadata, descriptions, blog body, footer, and utility UI.
- Local Vite font delivery includes Latin and Vietnamese subsets with `font-display: swap`.
- V2 uses Cormorant weights 400/500/600 and Montserrat 400/500/600. Cormorant 700 remains loaded only for legacy compatibility until migration proves it removable.
- Semantic roles are `display-xl`, `display-lg`, `heading-lg`, `heading-md`, `body-lg`, `body`, `body-sm`, `eyebrow`, `caption`, button, and text link.
- Headings balance and wrap at word boundaries. Long copy never uses `break-all`.

## Spacing and sections

The spacing scale is 4, 8, 12, 16, 24, 32, 48, 64, 80, 96, 120, and 144px. Sections expose `compact`, `default`, and `spacious` rhythms using responsive clamps. Whitespace expresses hierarchy; it must not become an unstructured empty region.

## Containers and responsive composition

| Container | Maximum | Use |
| --- | ---: | --- |
| `reading` | 720px | Long-form prose |
| `standard` | 1240px | Default composition |
| `wide` | 1440px | Editorial media compositions |
| `full` | none | Intentional full-bleed regions |

Horizontal gutters use `clamp(20px, 4vw, 48px)`. Use existing Tailwind breakpoints: mobile below `md` (768px), tablet/narrow desktop from `md` through the space below 1200px, and wide desktop from approximately 1200px. Existing header behavior at 1180px remains legacy-compatible. New page-specific breakpoints require a documented composition need.

## Surfaces, borders, radius, and shadow

Surfaces are `paper`, `ivory`, `muted`, `brand`, `deep`, and `espresso`. Image overlays use espresso at controlled transparency. Dark surfaces always use inverse text and champagne only as an accent.

Borders are fine 1px warm stone. Radius is intentionally limited to 4px small controls, 8px restrained surfaces, and 12px media. `shadow-soft` is the only foundation shadow and should be exceptional. Avoid large SaaS cards, heavy shadows, pervasive glass effects, and giant rounded rectangles.

## Interactions

Buttons use Montserrat and a 48px default target; icon utility controls use a 44px square target. Variants are `primary`, `secondary`, `inverse`, and `icon`. The `text` variant is the canonical text link. Every variant provides keyboard focus, hover, and disabled behavior with correct anchor/button semantics. Uppercase is reserved for concise eyebrow metadata, not general actions.

Cards are an exception, not a layout default. When domain content genuinely requires a card, use a fine border, restrained radius, almost no shadow, strong typography, and deliberate internal spacing. V2 intentionally provides no generic mega-card primitive.

## Media roles

| Role | Ratio | Typical use |
| --- | --- | --- |
| `cinematic` | 21:9 | Wide atmospheric storytelling |
| `landscape` | 3:2 | Editorial photography |
| `portrait` | 4:5 | People and vertical treatment scenes |
| `atmosphere` | 16:10 | Interior/detail context |
| `collection` | 1:1 | Controlled collections |
| `gallery` | 5:4 | Narrative gallery frames |

All roles use `object-fit: cover`; object position is explicit when center crop is unsuitable. Below-the-fold images default to lazy loading and async decode. True hero images use eager loading and high fetch priority. No carousel dependency is allowed.

Raw photographs under `resources/images/images resource/` are a source library, remain ignored, and must not be shipped wholesale. Later phases curate only production-ready derivatives.

## Logo matrix

| Context | Variant | Asset |
| --- | --- | --- |
| Dark cinematic hero | `white` | `viet-han-spa-white-logo.png` |
| Light/ivory surface or brand story | `colored` | `viet-han-spa-no-bg-logo.png` |
| Sticky compact header | `compact` | `viet-han-logo.png` |
| Dark footer | `white` | `viet-han-spa-white-logo.png` |

`<x-public.v2.logo>` supplies intrinsic dimensions and a localized accessible name. Use `decorative` only when an adjacent visible brand name already provides the accessible identity.

## Components

The Phase 1 namespace is `resources/views/components/public/v2/`:

- `container`
- `section`
- `eyebrow`
- `display-heading`
- `editorial-copy`
- `button`
- `media-frame`
- `logo`

These are composition primitives, not page designs. Homepage heroes, service menus, training layouts, and blog grids belong to later migration phases.

## Motion and View Transitions

Motion tokens are `fast` (200ms), `base` (280ms), `slow` (900ms), and `cinematic` (1400ms), with standard and cinematic easing curves. Phase 1 does not attach new section or cinematic animation behavior to production pages. Reduced-motion users receive effectively instant V2 interaction transitions.

Cross-document View Transitions remain disabled. Phase 4 may progressively enhance same-origin public navigation after checking browser support, reduced-motion preference, persistent header/floating UI isolation, focus restoration, scroll behavior, and Alpine/Livewire lifecycle interaction. The fallback must remain normal document navigation.

## Accessibility and anti-patterns

- Preserve semantic heading order, button/link semantics, visible focus, minimum 44px interaction targets, meaningful image alternatives, and contrast-aware inverse surfaces.
- Do not claim WCAG certification from component rules alone.
- Do not use Cormorant for body/UI copy, fake metallic gradients, champagne as low-contrast small text, arbitrary one-off colors/sizes, excessive uppercase, universal cards, decorative motion without reduced-motion support, or duplicated accessible logo names.
- Public Blade remains query-free; V2 presentation must not alter routing, localization, SEO, tracking, Booking, or CMS contracts.
