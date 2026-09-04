# Phase 9.5 Invariant Map

## Human Visual QA

- Desktop Hero / overlay header: HUMAN VISUALLY VERIFIED
- Desktop sticky white header: HUMAN VISUALLY VERIFIED

## Header And Hero Invariants

- Overlay state exists: VERIFIED FROM SOURCE (`public-header--overlay`)
- Sticky white state exists: VERIFIED FROM SOURCE (`public-header--sticky`)
- Sticky threshold: VERIFIED FROM SOURCE (`scrollY > 96`)
- True-centered logo: VERIFIED FROM SOURCE (`grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr)`)
- Desktop nav split left/right: VERIFIED FROM SOURCE (`public-header__nav--left`, `public-header__nav--right`)
- VI/EN retained: VERIFIED FROM SOURCE (`public-language-switcher`)
- Mobile hamburger retained: VERIFIED FROM SOURCE (`public-header__menu-button`, `aria-controls="public-mobile-menu"`)
- Floating CTA target exists: VERIFIED FROM SOURCE (`href="#contact-preview"`)
- Contact target exists exactly once: VERIFIED FROM SOURCE (`id="contact-preview"`)
- Hero uses local Vite asset: VERIFIED FROM SOURCE (`resources/images/homepage/viet-han-banner-hero.png`)
- No Phase 10 route: VERIFIED FROM `route-list.txt`
- No Booking backend: VERIFIED FROM SOURCE AND ROUTE BOUNDARY; floating CTA uses `#contact-preview`

## Geometry

- Overlay header height: 96px
- Sticky header height: 88px
- Logo geometry: 72x72 overlay, 66x66 sticky
- Navigation typography: 15px, weight 500
- Navigation gaps: 34px, 40px at 1440px+
- Hero viewport: 100vh / 100svh
- Hero H1: 46px desktop, 48px at 1440px+
- Hero content placement: lower-left content block, max width 600px
