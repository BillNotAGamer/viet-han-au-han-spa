# Phase 9.5 Homepage Visual Fidelity Audit

## A. Objective

Phase 9.5 rebuilt and closed the public homepage first-viewport visual fidelity work for the Việt Hàn Âu Hàn Spa public site. The scope was limited to the desktop overlay header, sticky white header state, mobile header compatibility, hero/header integration, hero top-viewport geometry, and hero typography/spacing required for fidelity.

## B. Starting Git State

Starting HEAD was verified as `8ed77a8d9bc3668b2505a377fe3bf89572c6f279`.

The starting working tree contained accumulated Phase 9.5 modifications in public CSS, public JavaScript, public layouts, public header, language switcher, homepage view, and one public layout test. It also contained untracked local project image assets and third-party visual reference screenshots.

## C. Agents Involved

- Gemini initial visual iterations
- Codex precision header/sticky closure

## D. Hero Photography Asset

- Path: `resources/images/homepage/viet-han-banner-hero.png`
- Dimensions: 1672x941
- Size: 2,204,075 bytes
- SHA-256: `d8271ea620801cb7b5fee38e717f369d726e6a2e24fb0759514d6e149884957d`

## E. Việt Hàn Logo Asset

- Path: `resources/images/general/viet-han-logo.png`
- Dimensions: 480x480
- Size: 386,779 bytes
- SHA-256: `75c1e428d93d3282500059cc450ce2d3540f5f137432aa6aa569db32ce80a649`

## F. Overlay Header Architecture

The top-of-page header is fixed over the hero and starts in `public-header--overlay`. The visual state is transparent over the hero image with a restrained top gradient and no opaque navigation band.

## G. Sticky White Header Architecture

The sticky state is driven by Alpine scroll state. Once the threshold is crossed, the header applies `public-header--sticky`, using a warm white background, dark navigation text, subtle border, and light shadow.

## H. Header Geometry

- Overlay height: 96px
- Sticky height: 88px

## I. Logo Geometry

- Overlay logo: 72x72
- Sticky logo: 66x66

## J. Navigation Typography

- Desktop nav font size: 15px
- Desktop nav weight: 500

## K. Navigation Gaps

- Default desktop gap: 34px
- 1440px+ desktop gap: 40px

## L. Sticky Threshold

Sticky state is activated at `scrollY > 96`.

## M. Hero Geometry

The hero uses a full first-viewport treatment with `100vh / 100svh`.

## N. Hero H1

- Desktop H1: 46px
- 1440px+ H1: 48px

## O. Hero Lower-Left Placement

Hero copy is placed in a lower-left content block with a 600px content width and constrained 1420px outer width.

## P. Floating Booking/Inquiry Tab

The floating tab points only to `#contact-preview`. No Booking backend or booking route was introduced.

## Q. Mobile Header Preservation

The mobile header retains logo, VI/EN language switcher, hamburger trigger, accessible drawer controls, and a dark mobile drawer.

## R. Localization

Navigation labels continue to use `lang/{locale}/navigation.php`. VI/EN switching remains source-driven through the existing localization support and no `/vi` canonical route tree was introduced.

## S. Route Boundary

Route evidence confirms public content routes remain limited to `/`, `/en`, and `/vi` redirect behavior. Phase 10 public detail routes were not introduced.

## T. Database/Schema Changes

None.

## U. PHPUnit Result

Fresh JUnit evidence reports 235 tests, 881 assertions, 0 failures, 0 errors, 0 skipped, duration 7.681810s.

## V. Pint Result

Pint passed with exit code 0.

## W. Vite Result

`cmd /c npm run build` passed with exit code 0.

## X. Composer Result

Composer validate passed with exit code 0. Composer runtime was `Composer 2.10.2` via Herd PHP `8.4.24`.

## Y. Human Browser QA

Desktop Hero / overlay header:
HUMAN VISUALLY VERIFIED

Desktop sticky white header:
HUMAN VISUALLY VERIFIED

No automated browser verification is claimed for this audit.

## Z. Production DB Status

No production database operations were performed. No migrations were added or run as part of this closure.

## AA. Known Technical Debt

The canonical public layout currently used by `<x-layouts.public>` is `resources/views/components/layouts/public.blade.php`. A legacy/apparently unused duplicate layout remains at `resources/views/layouts/public.blade.php` through `App\View\Components\PublicLayout`. This duplicate-layout debt is recorded for later cleanup and was not refactored in Phase 9.5.
