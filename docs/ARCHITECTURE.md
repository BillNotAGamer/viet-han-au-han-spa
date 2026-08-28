# TARGET SYSTEM ARCHITECTURE

**Project:** Việt Hàn Âu Hàn Spa  
**Production Domain:** `viethanauhanspa.com`  

---

## 1. System Topology & Request Flow

```text
[ Internet Client / Browser ]
           │
           ▼ (HTTPS / TLS 1.3, Edge Caching, DDoS Protection)
  [ Cloudflare CDN & DNS ]
           │
           ▼ (Reverse Proxy / Origin Pull)
[ Linux Shared Web Hosting (cPanel/DirectAdmin/CloudLinux) ]
  ├── Web Root: public/
  │    ├── index.php (Laravel Entrypoint)
  │    ├── build/ (Vite Compiled Static Assets: CSS, JS)
  │    └── storage -> ../storage/app/public
  │
  └── PHP 8.3+ Runtime (OPcache enabled)
       │
       ├── Public Layer: Blade Templates + Tailwind CSS 4 + Alpine.js
       │     └── Routes -> Controllers -> Domain Services -> Eloquent Models
       │
       ├── Admin Layer: Filament 5 Panel Builder (/admin)
       │     └── Filament Resources/Pages -> Domain Services -> Eloquent Models
       │
       └── Database: MySQL 8.x / MariaDB 10.x
             ├── Tables (Services, Courses, Posts, Bookings, Leads, Settings)
             └── Foreign Keys & Indexes
```

---

## 2. Layered Responsibilities

### 2.1 Public Frontend Layer
* **Rendering Engine:** Laravel Blade Templates (Server-Side Rendering for maximum SEO and fastest First Contentful Paint).
* **Styling:** Tailwind CSS 4 compiled via Vite.
* **Client-Side Interactivity:** Alpine.js for lightweight UI behaviors (mobile drawer menus, modals, dropdowns, booking step accordions, toast notifications).
* **Asset Bundler:** Vite.

### 2.2 Admin Back-Office Layer
* **Panel Builder:** Filament 5.x.
* **Authentication:** Guarded session authentication at `/admin`.
* **Authorization:** Role & Policy-based gates protecting resources.
* **Direct CRUD:** Models bound cleanly for basic edits, delegating complex actions to Domain Services.

### 2.3 Domain Services Layer (`app/Services`)
* Encapsulates all non-trivial business logic, including:
  * `BookingService`: Validates booking availability windows, creates booking records, captures tracking attribution DTOs, and triggers notification jobs.
  * `NotificationService`: Dispatches administrative emails or webhook notifications (e.g. Zalo OA/Telegram alerts).
  * `TrackingAttributionService`: Parses UTM parameters, Google Click IDs (`gclid`, `gbraid`, `wbraid`), Meta Click IDs (`fbclid`), and browser cookies (`_fbp`, `_fbc`).

### 2.4 Persistence Layer
* **Target Engine:** MySQL 8.x / MariaDB 10.x with InnoDB storage engine and `utf8mb4_unicode_ci` collation.
* **Local Development Strategy:**
  * SQLite is permitted during Phase 0 bootstrap and local testing when MySQL is unavailable.
  * Production parity tests must be performed prior to release.
* **Filesystem Storage:** Local disk storage symlinked via `php artisan storage:link` to `public/storage`.

---

## 3. Localization & Routing Strategy

* **URL Structure:**
  * Default Vietnamese: `/`, `/dich-vu`, `/dao-tao`, `/blog`, `/dat-lich`
  * English Secondary: `/en`, `/en/services`, `/en/training`, `/en/blog`, `/en/booking`
* **Route Organization:** Grouped with locale middleware that automatically sets `app()->setLocale($locale)`.

---

## 4. Deployment Constraints & Assumptions

* **Hosting Environment:** Optimized for standard Linux shared PHP hosting (PHP 8.3+, Apache/Nginx reverse proxy, MySQL).
* **Process Management:** No persistent Node.js daemon runs on the production server. Vite compiles static assets (`npm run build`) before deployment or during CI/CD.
* **Background Tasks:** Scheduled commands (e.g., sitemap regeneration, cleanup) run via standard Linux crontab:
  ```cron
  * * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
  ```
* **Caching:** Laravel's native file-based or database cache is utilized for MVP. No mandatory Redis dependency is required.
