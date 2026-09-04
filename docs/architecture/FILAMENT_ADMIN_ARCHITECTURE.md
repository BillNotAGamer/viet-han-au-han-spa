# FILAMENT ADMIN ARCHITECTURE & ACCESS CONTROL

**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Phase:** Phase 3 — Filament Admin Foundation & Access Control  
**Status:** ACCEPTED (Architect-Approved)  
**Mount Path:** `/admin`  

---

## 1. Authentication & Access Control Model

* **Authentication Primitive:** Native Filament 5 authentication built on Laravel's standard session and authentication guards.
* **Panel Access Gate:** Enforced server-side via `Filament\Models\Contracts\FilamentUser` implemented on `App\Models\User`:
  ```php
  public function canAccessPanel(Panel $panel): bool
  {
      return (bool) $this->is_admin;
  }
  ```
* **Separation of Authentication vs Authorization:** A valid registered Laravel user account does NOT imply access to the administrative panel. Only users with `is_admin = true` are granted panel access. Non-admin authenticated users receive an immediate `403 Forbidden` response.
* **Database & Factory Defaults:** `is_admin` defaults to `false` at the database schema level (`$table->boolean('is_admin')->default(false)`) and factory level.
* **Mass-Assignment Protection:** `is_admin` is intentionally omitted from `$fillable` on `User.php` to prevent privilege escalation via mass assignment.

---

## 2. Admin Security & Hardening Boundaries

1. **No Public Registration:** Customer accounts and public registration routes are disabled (`/admin/register` -> 404, `/register` -> 404).
2. **Zero Hardcoded Credentials & Safe Admin Provisioning:**
   - No administrator credentials, default accounts, or production passwords exist in code, seeders, or documentation.
   - **Operational Rule:** Never place administrator passwords in shell command arguments, committed scripts, documentation examples, or `.env.example`.
   - **Supported Provisioning Workflow:**
     1. Create user account interactively with masked password prompt (never logged in shell history):
        ```powershell
        php artisan make:filament-user
        ```
     2. Explicitly promote the account to administrator:
        ```powershell
        php artisan tinker --execute="App\Models\User::where('email', 'admin@viethanauhanspa.com')->update(['is_admin' => true]);"
        ```
        or interactively inside Tinker:
        ```powershell
        php artisan tinker
        $user = App\Models\User::where('email', 'admin@viethanauhanspa.com')->first();
        $user->is_admin = true;
        $user->save();
        ```
3. **No Third-Party RBAC Overhead:** Complex third-party permission packages (e.g. `spatie/laravel-permission`, `Shield`) are prohibited in MVP. Coarse panel access is governed by `is_admin`; fine-grained resource permissions in later CMS phases will utilize native Laravel Policies.
4. **CSRF Protection:**
   - **Status:** **ACTIVE via native Laravel/Livewire/Filament stack**.
   - `Illuminate\Foundation\Http\Middleware\PreventRequestForgery` is active in `AdminPanelProvider`. All Livewire component mutations and login submissions enforce valid CSRF session tokens.
5. **Login Brute-Force & Rate Limiting:**
   - **Status:** **ACTIVE via native Filament 5 authentication stack**.
   - Filament's native `Filament\Auth\Pages\Login` component implements `WithRateLimiting` (throttles to 5 attempts before raising `TooManyRequestsException`) and wraps credential verification in `Illuminate\Support\Timebox` to mitigate timing attacks.
   - Infrastructure-level hardening (IP blocking, WAF, Fail2ban, Turnstile) is tracked for **Phase 14 — Performance / Accessibility / Security**.
6. **Localization Isolation:** The administrative interface is mounted strictly at `/admin`. It is completely isolated from public locale prefixes (`/en/admin` -> 404).

---

## 3. Brand Identity & Panel Shell

* **Brand Name:** `Việt Hàn Âu Hàn Spa`
* **Primary Color:** `#5B1121` (matching the official burgundy brand identity)
* **Shell Widgets:** Clean, operational dashboard foundation retaining `AccountWidget` and removing unnecessary framework info widgets.

---

## 4. Logical Navigation Organization for Future CMS Phases

When Resources are introduced in Phases 4–7, they will be organized into the following logical navigation groups:

```text
Tổng quan
    Dashboard

Dịch vụ
    Dịch vụ (Services)
    Danh mục dịch vụ (Service Categories)

Đào tạo
    Khóa học (Training Courses)
    Yêu cầu tư vấn (Training Inquiries)

Nội dung
    Blog (Articles)
    Danh mục Blog (Post Categories)
    Trang (Pages)

Khách hàng / Leads
    Đặt lịch (Bookings)

Media
    Thư viện Media (Media Library)

Hệ thống
    Cài đặt (Site Settings)
```

*Note: No placeholder or empty Resource classes are created in Phase 3. Resources are created only in their respective roadmap phases.*

---

## 5. Bilingual Content Editing Conventions (Phases 4–7)

For translatable resources, the administrative forms must adhere to the following conventions:
1. **Explicit Language Tabs/Sections:** Separate form tabs for `Tiếng Việt` and `English`.
2. **Entity vs Translation Separation:** Core fields (`status`, `sort_order`, `category_id`, `hero_media_id`) are presented separately from localized fields (`name`, `slug`, `content`, `SEO metadata`).
3. **No Hidden Locale Scope:** The admin panel never applies a global locale filter; administrators must always have access to view and edit all language translations.
4. **Thin Resource Classes:** Resource classes delegate non-trivial operations to domain services in `App\Services`.
