# IMPLEMENTATION ROADMAP

**Project:** Việt Hàn Âu Hàn Spa  
**Target Domain:** `viethanauhanspa.com`  

The project follows a structured 18-phase implementation roadmap. Each phase must be completed, tested, and audited independently with a dedicated report under `docs/audit/`.

---

## Roadmap Phases

| Phase | Phase Name | Scope & Core Deliverables | Status |
| :--- | :--- | :--- | :--- |
| **Phase 0** | **Project Bootstrap & Governance** | Environment audit, governance rules (`AGENTS.md`), documentation suite, Laravel 13 + Filament 5 baseline, temporary placeholder. | 🔄 Current |
| **Phase 1** | **Domain Model & Database** | Database migrations, Eloquent models, relationships, factory seeders for services, courses, posts, bookings, and settings. | ⏳ Pending |
| **Phase 2** | **Localization System** | Bilingual routing (`vi`/`en`), locale middleware, language switcher, translatable model traits, translation files. | ⏳ Pending |
| **Phase 3** | **Filament Admin Foundation** | Admin panel configuration, branding, navigation groups, user management, and security policies. | ⏳ Pending |
| **Phase 4** | **Services CMS** | Filament resource for Service Categories and Services, duration, pricing, procedure steps, and media uploads. | ⏳ Pending |
| **Phase 5** | **Training CMS** | Filament resource for Training Courses, syllabus modules, tuition rates, and student inquiries. | ⏳ Pending |
| **Phase 6** | **Blog CMS** | Filament resource for Blog Categories, Articles, Rich Text editor, tags, authors, and publishing workflow. | ⏳ Pending |
| **Phase 7** | **Pages, Settings & Media** | Static pages CMS, global business settings (hotlines, hours, map), social links, and media management. | ⏳ Pending |
| **Phase 8** | **Public Design System** | Tailwind CSS 4 luxury wellness design system, color tokens, typography, reusable Blade UI components. | ⏳ Pending |
| **Phase 9** | **Homepage Implementation** | Luxury hero section, featured treatments, academy preview, brand story, testimonials, and booking banner. | ⏳ Pending |
| **Phase 10** | **Public Content Pages** | Service catalog & detail pages, course catalog & detail pages, blog listing & article view, about, and contact pages. | ⏳ Pending |
| **Phase 11** | **Booking Engine (MVP)** | Multi-step interactive booking form (Alpine.js + Blade), server-side validation, rate limiting, and email alerts. | ⏳ Pending |
| **Phase 12** | **Marketing Tracking & Attribution** | Centralized attribution capture (UTM parameters, GCLID, FBCLID), GTM container script injection, and conversion dispatch. | ⏳ Pending |
| **Phase 13** | **Technical SEO & Structured Data** | Dynamic XML sitemaps, OpenGraph tags, canonical tags, JSON-LD Schema (LocalBusiness, Spa, Course, Article). | ⏳ Pending |
| **Phase 14** | **Performance & Security Hardening** | Asset minification, responsive WebP image pipelines, Core Web Vitals tuning, security headers, CSP, and rate limiting. | ⏳ Pending |
| **Phase 15** | **Full End-to-End QA** | Automated test suite execution, cross-device responsiveness verification, booking flow testing, admin CRUD validation. | ⏳ Pending |
| **Phase 16** | **Production Deployment** | Shared hosting deployment scripts, production database migration, symlink setup, environment config, and SSL validation. | ⏳ Pending |
| **Phase 17** | **Launch Verification** | Post-launch smoke tests, live tracking verification, DNS propagation check, and operational handover. | ⏳ Pending |
