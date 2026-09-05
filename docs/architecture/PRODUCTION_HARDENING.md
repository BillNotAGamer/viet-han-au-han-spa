# Production Hardening & Security Architecture

**Phase:** Phase 14
**Status:** Closed
**Production Domain:** `https://viethanauhanspa.com`

---

## 1. Trusted Proxy & Client IP Policy

- **Current Production Topology:** Production DNS (`viethanauhanspa.com`) resolves directly to LiteSpeed Web Server on Vietnix (`103.200.23.68`).
- **Configuration-Driven Trust:** In `bootstrap/app.php`, Laravel's `trustProxies` is dynamically configured using the `TRUSTED_PROXIES` environment variable.
  - When `TRUSTED_PROXIES` is empty/null (default), zero proxies are trusted (`at: []`). Direct connections resolve client IP from `REMOTE_ADDR` and strictly ignore untrusted `X-Forwarded-For` headers, preventing client IP spoofing against Booking rate limits.
  - When deployed behind a verified reverse proxy (e.g. Cloudflare or load balancer), the proxy's IP addresses or CIDR blocks are supplied via `TRUSTED_PROXIES` (e.g. in production `.env`), allowing Laravel to safely resolve the visitor's real IP from `X-Forwarded-For`.
- **Headers Trusted:** Standard forwarding headers (`X-Forwarded-For`, `X-Forwarded-Host`, `X-Forwarded-Port`, `X-Forwarded-Proto`, `X-Forwarded-AWS-ELB`).

---

## 2. Application-Owned Security Headers

Implemented via `App\Http\Middleware\SecurityHeaders` registered in the global HTTP middleware stack:

- **`X-Content-Type-Options: nosniff`**: Prevents MIME-type confusion attacks across all public and admin responses.
- **`X-Frame-Options: SAMEORIGIN`**: Protects against clickjacking while maintaining full compatibility with internal administrative components.
- **`Referrer-Policy: strict-origin-when-cross-origin`**: Balances privacy by withholding detailed URL paths on cross-origin requests while preserving campaign attribution on legitimate referrals.
- **`Permissions-Policy: camera=(), microphone=(), geolocation=()`**: Restricts unneeded device capabilities.
- **`Strict-Transport-Security: max-age=31536000`**: Conditionally enforced only when requests are secure (`$request->isSecure()`) and in production environment (`app()->isProduction()`). Local development and tests over HTTP remain unaffected.

---

## 3. Content Security Policy (CSP) â€” Deferred

- **Status:** DEFERRED.
- **Rationale:** Strict enforcing CSP requires nonces/hashes for Alpine.js dynamic expressions, Vite inline modules, Filament administrative panel assets, and marketing tags (GTM, GA4, Meta Pixel). Broad or insecure CSP (e.g., allowing `'unsafe-inline' *`) provides no meaningful protection and risks breaking production functionality. Dedicated CSP architecture will be addressed in a future milestone.

---

## 4. Session & Cookie Policy

- **Session Driver:** Database sessions (`sessions` table) with JSON serialization.
- **Cookie Security:**
  - `HttpOnly`: Strictly `true` (`config('session.http_only')`), preventing JavaScript access to session identifiers.
  - `SameSite`: Set to `lax` (`config('session.same_site')`), defending against cross-site request forgery.
  - `Secure`: Production cookies use `secure = true` over HTTPS (`SESSION_SECURE_COOKIE=true`).
- **CSRF Protection:** Laravel's `PreventRequestForgery` middleware guards all public mutation endpoints (e.g. Booking submissions) and Filament administrative actions.

---

## 5. Administrative & Access Control Boundary

- **Panel Route Prefix:** Administrative panel mounted strictly at `/admin`.
- **Panel Access Gate:** Governed by `FilamentUser::canAccessPanel()` which checks `users.is_admin === true`.
- **Mass Assignment Protection:** `users.is_admin` is guarded against mass assignment (`$fillable` only permits `name`, `email`, `password`).
- **Registration Endpoint:** Public registration is disabled; `/admin/register` returns 404.
- **Login Throttling:** Filament's authentication layer natively limits login attempts to 5 attempts per minute using `Timebox` to mitigate brute-force and timing attacks.
- **Authorization Policies:** Resources (e.g., Bookings, Media, Services, Posts, Training) are guarded by explicit Laravel policies restricting mutations to authenticated administrators.

---

## 6. Upload & Media Boundary

- **Upload Isolation:** File uploads are restricted exclusively to `MediaResource` via `MediaUploadService`.
- **MIME & Extension Whitelist:** Only raster images (`image/jpeg`, `image/png`, `image/webp` with `.jpg`, `.jpeg`, `.png`, `.webp`) are accepted.
- **Deep Byte Inspection:** Uploads are validated server-side using `finfo` and `getimagesize()`. SVGs, executable code, HTML, and script-bearing XML are rejected unconditionally.
- **Storage Safety:** Files are stored using random ULIDs (`media/YYYY/MM/<ulid>.<ext>`) on the configured public disk (`storage/app/public`), never accepting client-supplied paths.
- **Referential Integrity:** Media files referenced by services, training, blog posts, or pages are protected against deletion until all references are removed.

---

## 7. Public Filesystem Boundary & Secret Protection

- **Web Document Root:** Only `public/` is exposed to the web server. Application source, `.env`, SQLite databases, backups, and logs reside outside the document root.
- **Secret Tracking:** Zero secrets, `.env` files, or private keys are tracked in version control.
- **Static Secret Scan:** Clean scan confirmed across all tracked repository files.

---

## 8. Dependency Security Audit

- **Composer Dependencies:** `composer audit` reports 0 security vulnerability advisories.
- **NPM Dependencies:** `npm audit` reports 0 vulnerabilities.

---

## 9. Production Operational Requirements

| Variable | Required Value | Notes |
| :--- | :--- | :--- |
| `APP_ENV` | `production` | Ensures error stack traces and debug tools are disabled. |
| `APP_DEBUG` | `false` | Shields SQL queries, credentials, and internal paths from public error responses. |
| `APP_URL` | `https://viethanauhanspa.com` | Canonical production base URL. |
| `SESSION_SECURE_COOKIE` | `true` | Restricts session cookies to HTTPS connections. |
| `TRUSTED_PROXIES` | Proxy IPs/CIDRs (or blank) | Set only if an upstream reverse proxy (e.g. Cloudflare) terminates TLS before Vietnix. |
