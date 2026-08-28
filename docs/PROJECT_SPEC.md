# PROJECT SPECIFICATION

**Project Name:** Việt Hàn Âu Hàn Spa  
**Production Domain:** `viethanauhanspa.com`  
**Document Version:** 1.0.0 (Phase 0 Baseline)  

---

## 1. Brand Overview

* **Brand Name:** Việt Hàn Âu Hàn Spa (Việt Hàn Spa)
* **Industry:** Luxury Asian Wellness, Therapeutic Massage, Aesthetic Spa & Beauty Training Academy
* **Core Market:** High-end wellness clients, tourists, and students seeking professional spa/massage certification in Vietnam.

---

## 2. Navigation & Site Structure

### 2.1 Navigation Bar (Bilingual)
| Item Index | Vietnamese (Default) | English (Secondary) | Target Route / Section |
| :--- | :--- | :--- | :--- |
| 1 | Trang chủ | Home | `/` (or `/{locale}`) |
| 2 | Dịch vụ | Services | `/dich-vu` / `/en/services` |
| 3 | Đào tạo học viên | Training | `/dao-tao` / `/en/training` |
| 4 | Blog | Blog | `/blog` / `/en/blog` |
| 5 | Giới thiệu | About | `/gioi-thieu` / `/en/about` |
| 6 | Liên hệ | Contact | `/lien-he` / `/en/contact` |

**Header Elements:**
* Brand Logo with responsive sizing.
* Main navigation links with active state indicator.
* Language Switcher (`VI` / `EN`).
* Prominent CTA Button: **"Đặt Lịch Ngay" / "Book Now"**.

---

## 3. Public Modules (Scope)

1. **Homepage:**
   * Hero section with high-impact spa imagery, tagline, and instant booking CTA.
   * Featured Spa Services & Signature Treatments.
   * Training Academy Highlights & Certification benefits.
   * Brand Story / Why Choose Việt Hàn.
   * Customer Testimonials & Social Proof.
   * Latest Blog Articles / Wellness Tips.
   * Location Map, Contact Info, and Booking Banner.
2. **Services Module:**
   * Service categories (e.g., Body Therapy, Facial Care, Herbal Foot Bath, Combo Packages).
   * Detailed service pages with duration, transparent pricing, step-by-step procedure, benefits, and direct booking trigger.
3. **Training Module:**
   * Vocational courses for aspiring spa therapists and technicians.
   * Course curriculum, duration, tuition, certification details, and registration form.
4. **Blog / Wellness Insights:**
   * Categories: Health tips, skincare guides, massage benefits, academy news.
   * SEO-optimized articles with reading time and social share buttons.
5. **About Us:**
   * Brand philosophy, founder story, facility showcase, quality commitments.
6. **Contact Us:**
   * Contact details, interactive Google Map embed, direct hotline, Zalo integration, and inquiry form.
7. **Booking Module (MVP):**
   * Lead capture and booking request form.
   * Selection of service, preferred branch, date, time slot, guest count, and customer contact info (Name, Phone, Email, Notes).
   * Full marketing attribution capture (UTMs, Click IDs).
   * Server-side validation and automated confirmation email / notification dispatch.

---

## 4. Admin Management Modules (Planned for Filament 5)

1. **Bookings Management:** Review booking requests, filter by date/status (Pending, Confirmed, Completed, Cancelled), export to Excel/CSV.
2. **Services CMS:** Manage service categories, services, pricing, durations, image galleries, and bilingual content.
3. **Training CMS:** Manage training courses, syllabus, tuition fees, schedules, and trainee inquiries.
4. **Blog CMS:** Rich text editor, category taxonomy, featured images, author attribution, and publication scheduling.
5. **Pages CMS:** Manage static page content (About, Contact, Terms, Privacy Policy).
6. **Media Manager:** Organize uploaded imagery and assets.
7. **SEO Management:** Meta titles, descriptions, OpenGraph tags, canonical URLs, and structured data (Schema.org) configuration.
8. **System Settings:** Business contact information, operating hours, social media links, notification email recipients, and tracking script injection settings.

---

## 5. Localization Strategy

* **Default Locale:** Vietnamese (`vi`)
* **Secondary Locale:** English (`en`)
* URL localization prefixing with clean fallback:
  * Vietnamese: `/dich-vu`
  * English: `/en/services`
* Translatable database content handled via standard Eloquent JSON translations or dedicated translation tables.

---

## 6. MVP Scope Boundaries

### In Scope (MVP):
* Booking request system (asynchronous staff confirmation via phone/Zalo).
* Responsive, mobile-first luxury public website (Blade + Tailwind CSS 4 + Alpine.js).
* Filament 5 back-office CMS.
* Centralized marketing attribution tracking (UTMs, GCLID, FBCLID).
* Full technical SEO and Core Web Vitals optimization.

### Explicitly Out of Scope for MVP:
* Customer login / registration accounts.
* Online payment gateway integration (VNPAY, MoMo, Stripe, etc.).
* Membership cards, loyalty points, or gift voucher engine.
* Real-time therapist calendar assignment or room management.
* Learning Management System (LMS) or online exam portal.
* Native mobile applications (iOS/Android).
* Full CRM or POS billing system.
