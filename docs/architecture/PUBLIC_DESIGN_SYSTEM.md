# PUBLIC DESIGN SYSTEM & GLOBAL LAYOUT ARCHITECTURE

**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Phase:** Phase 8 — Public Design System & Global Layout  
**Status:** ACCEPTED (Architect-Approved)  
**Primary Brand Color:** `#5B1121` (Deep Burgundy / Wine Red)  
**Secondary Accent:** `#C5A880` (Champagne Gold)  
**Neutral Canvas:** `#FAF7F2` (Warm Ivory) & `#F4EFEA` (Warm Sand)  

---

## 1. Visual Direction & Brand Identity

Việt Hàn Âu Hàn Spa delivers high-end Korean aesthetic therapy, advanced rejuvenation, and professional beauty training.
* **Atmosphere:** Warm, serene, restorative, elegant Asian sanctuary.
* **Design Tenets:**
  - Restrained, generous whitespace with breathable margins.
  - High typographic hierarchy prioritizing comfort and readability.
  - Organic warm neutral backgrounds avoiding harsh clinical whites or dark-mode SaaS styling.
  - High contrast for accessibility without aggressive neons or saturated gradients.
* **Reference Review Status:**
  - Reference site `https://zenmassagespa.vn/` was inspected live.
  - Extracted UX principles: grounded earthy warmth, clear category orientation, sticky header with persistent booking CTA, responsive mobile drawer.
  - Zero proprietary code, photographs, brand assets, or copyrighted copy were copied.

---

## 2. Centralized Design Tokens (Tailwind CSS 4)

Integrated via `@theme` in `resources/css/app.css`:

```css
@theme {
    --color-brand-primary: #5b1121;
    --color-brand-primary-hover: #4a0d1a;
    --color-brand-primary-light: #7a1c30;
    --color-brand-gold: #c5a880;
    --color-brand-gold-hover: #b3956b;
    --color-brand-gold-light: #e8dfc8;
    --color-brand-ivory: #faf7f2;
    --color-brand-warm: #f4efea;
    --color-brand-surface: #ffffff;
    --color-brand-text: #2c2420;
    --color-brand-text-secondary: #5a4d45;
    --color-brand-text-muted: #8c7a6b;
    --color-brand-border: #e8dfc8;
    --color-brand-border-subtle: #f0e8d9;

    --font-sans: 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
```

---

## 3. Typographic Hierarchy & Vietnamese Diacritics

* **Primary Sans Typeface:** `Instrument Sans`, sans-serif with native Vietnamese diacritic support.
* **Primary Serif / Display Typeface:** `'Times New Roman', 'Noto Serif', Georgia, serif` configured in `--font-serif` to guarantee full, native precomposed Vietnamese diacritic rendering without glyph fallback fragmentation.
* **Editorial Heading Wrapping Invariants:**
  - Editorial and display headings wrap naturally at whole-word boundaries (`overflow-wrap: break-word; word-break: normal; text-wrap: balance;`).
  - Arbitrary word-internal breaking (`break-all`, `word-break: break-all`) is strictly forbidden for human-language editorial copy.
  - Multi-syllable Vietnamese words (`Thống`, `Tuyến`, `Dưỡng`, `Liệu`, `Điều`, `Trị`) remain intact and never split internally merely to satisfy line widths.
* **Hierarchy:**
  - **Display Heading:** `font-serif text-3xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-brand-primary break-words`
  - **Section Heading:** `font-serif text-2xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-brand-primary break-words [text-wrap:balance]`
  - **Brand Lockup:** `font-serif text-lg sm:text-2xl font-bold tracking-tight text-brand-primary`
  - **Subheading:** `text-base sm:text-lg text-brand-text-secondary leading-relaxed break-words`
  - **Eyebrow:** `text-xs sm:text-sm font-semibold tracking-widest uppercase text-brand-gold-hover`
  - **Body Text:** `text-base sm:text-lg text-brand-text-secondary leading-relaxed`
  - **Meta / Small:** `text-xs sm:text-sm text-brand-text-muted`

---

## 4. Shared Public Layout (`resources/views/layouts/public.blade.php`)

* **Structure:**
  - Root `html` tag with dynamic `lang="{{ app()->getLocale() }}"`.
  - Accessible Skip Link: `<a href="#main-content">` targeting the single primary `<main id="main-content">`.
  - Reusable `<x-public.header />` with sticky positioning and backdrop blur.
  - Reusable `<x-public.footer />` with semantic landmarks and public Site Settings.
  - Vite asset inclusion for compiled CSS and Alpine.js.

---

## 5. Header & Navigation Architecture

* **Brand Region:** Textual lockup `Việt Hàn Âu Hàn Spa` linking to the localized home route (`/` for VI, `/en` for EN).
* **Desktop Navigation:** Semantic `<nav aria-label="{{ __('navigation.main_navigation') }}">` with 6 canonical items:
  1. Trang chủ / Home (`/` vs `/en`)
  2. Dịch vụ / Services (`/dich-vu` vs `/en/services`)
  3. Đào tạo học viên / Training (`/dao-tao-hoc-vien` vs `/en/training`)
  4. Blog / Blog (`/blog` vs `/en/blog`)
  5. Giới thiệu / About (`/gioi-thieu` vs `/en/about`)
  6. Liên hệ / Contact (`/lien-he` vs `/en/contact`)
* **Language Switcher:**
  - Deterministic switching via `Localization::switchLocaleUrl(...)`.
  - VI route targets `/en`.
  - EN route targets `/`.
  - Zero `/vi` URL generation.
* **Mobile Navigation (Alpine.js only):**
  - Accessible button with `aria-expanded` and `aria-label`.
  - Keyboard accessible: closes on `Escape`.
  - Toggles drawer containing all navigation links, language switcher, and mobile CTA.

---

## 6. Public UI Primitives (`resources/views/components/public/`)

1. `<x-public.container>`: Responsive container with `max-w-7xl` and gutter padding.
2. `<x-public.button>`: Flexible button / anchor supporting variants `primary`, `secondary`, `gold`, `outline-gold`, `text` across sizes `sm`, `md`, `lg`.
3. `<x-public.section>`: Vertical rhythm wrapper supporting background variants (`ivory`, `warm`, `white`) and spacing variants (`compact`, `default`, `spacious`).
4. `<x-public.section-heading>`: Structured eyebrow, title, and subtitle container.
5. `<x-public.card>`: Surface card with subtle border and elevation.
6. `<x-public.badge>`: Rounded status pill with muted accent background.

---

## 7. Site Settings Public Consumption

* Injected via `@inject('siteSettings', 'App\Services\Settings\SiteSettings')`.
* Uses `siteSettings->getPublic(...)` exclusively.
* Private operational settings (`is_public = false`) are never accessible.
* Graceful fallback values prevent broken rendering when database settings are empty.
* Zero direct Eloquent `SiteSetting::query()` calls in Blade.

---

## 8. Accessibility & Motion

* **Landmarks:** Distinct `<header>`, `<nav>`, single `<main id="main-content">`, `<footer>`.
* **Focus States:** High-contrast 2px outline in brand primary color with 2px offset on all interactive elements.
* **Motion:** Subtle transitions (150–200ms) adhering to `prefers-reduced-motion`. Zero auto-playing decorative animations.
