# ADR-020: Public Design System, Global Blade Layout, and Component Primitives

**Status:** ACCEPTED  
**Context:**  
Phase 8 establishes the public frontend architecture for Việt Hàn Âu Hàn Spa. We evaluated:
1. Adopting an external SPA framework (Vue, React, Inertia, Next.js).
2. Using third-party UI component libraries or CSS frameworks.
3. Hardcoding individual public pages without a shared Blade shell.
4. Implementing a native Blade SSR architecture with Tailwind CSS 4 design tokens, Alpine.js for lightweight interactions, and focused Blade UI primitives.

**Decision:**  
Adopt **Blade SSR with Centralized Tailwind CSS 4 Tokens, Alpine.js Interactive Navigation, and Reusable Public Primitives**:
- Blade SSR remains the single public rendering architecture.
- Centralized color tokens in `@theme` enforce the brand palette (`#5B1121` burgundy, `#C5A880` gold, warm ivory canvas).
- Shared public layout (`layouts/public.blade.php`) manages the document shell, accessibility landmarks, and single `#main-content`.
- Navigation is strictly bilingual via localization files; language switching respects Phase 2 canonical mapping (`/` <-> `/en`).
- Mobile navigation is implemented strictly via Alpine.js with accessible ARIA attributes.
- Global Site Settings consumption is public-only through `SiteSettings::getPublic(...)` with zero direct database queries in Blade.
- No public content routes are registered prematurely.

**Consequences:**  
- **Positives:**
  - High performance, minimal client-side JavaScript, optimal SEO foundation.
  - Full compatibility with shared-hosting baseline.
  - Clean separation of concerns between layout shell, UI primitives, and future domain content.
- **Negatives:**
  - Future complex multi-step forms (e.g. Booking in Phase 11) must use Alpine.js or Livewire components.
