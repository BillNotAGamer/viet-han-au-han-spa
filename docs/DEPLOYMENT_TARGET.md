# PRODUCTION DEPLOYMENT TARGET SPECIFICATION

**Target Production Domain:** `viethanauhanspa.com`  

---

## 1. Hosting Environment Specifications

* **Hosting Type:** Linux Shared Web Hosting (cPanel / DirectAdmin / CloudLinux / LiteSpeed Web Server).
* **PHP Runtime:** PHP 8.3 or PHP 8.4 with OPcache enabled.
* **Database Server:** MySQL 8.0+ or MariaDB 10.6+ (Managed by hosting panel).
* **SSL / TLS:** Free automated Let's Encrypt SSL certificate + Cloudflare Universal SSL.
* **Edge Proxy:** Cloudflare Free Plan (DNS management, HTTPS enforcement, Brotli compression, DDoS protection).

---

## 2. Infrastructure Constraints & Rules

1. **No Continuous Node.js Daemon:** The shared hosting environment does not run a Node server process. All CSS/JS assets are pre-compiled via `npm run build` locally or during CI/CD into `public/build`.
2. **No Redis Dependency:** Laravel default file cache or database cache is used for MVP caching and session storage to avoid requiring dedicated in-memory Redis instances on shared hosting.
3. **No VPS Requirement:** The architecture is intentionally lean and optimized to run reliably and fast on standard shared cPanel hosting.
4. **Symlink / Storage:** `php artisan storage:link` creates the link from `storage/app/public` to `public/storage`.
5. **Cron Execution:** A single crontab entry runs Laravel's task scheduler every minute:
   ```cron
   * * * * * cd /home/username/viethanauhanspa.com && php artisan schedule:run >> /dev/null 2>&1
   ```
