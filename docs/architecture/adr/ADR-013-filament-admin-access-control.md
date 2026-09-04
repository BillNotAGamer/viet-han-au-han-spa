# ADR-013: Filament Admin Access Control and Privilege Architecture

**Status:** ACCEPTED  
**Context:**  
The application requires a secure, high-performance administrative panel for spa managers and content editors. We evaluated:
1. Installing third-party RBAC packages (`spatie/laravel-permission` or `filament-shield`).
2. Custom multi-guard authentication system (`admin` guard vs `web` guard).
3. Native Laravel User model with `is_admin` boolean flag and Filament 5 `FilamentUser` interface.

**Decision:**  
Adopt **Native User Model with `is_admin` boolean and `FilamentUser` interface**:
- Add `is_admin` (boolean, default false) to the existing `users` table.
- Implement `canAccessPanel(Panel $panel): bool` on `App\Models\User` returning `(bool) $this->is_admin`.
- Prohibit casual mass assignment of `is_admin`.
- Disable public user registration.
- Reserve granular domain-level permissions for native Laravel Policies in subsequent CMS phases.

**Consequences:**  
- **Positives:**
  - Zero third-party package dependencies (strictly adheres to package minimization).
  - Lightweight, instant database evaluation without permission matrix join overhead.
  - 100% portable across SQLite, MySQL 8, and MariaDB.
  - Compatible with standard Laravel testing and factory states (`User::factory()->admin()`).
- **Negatives:**
  - Does not support multi-role hierarchies in MVP (acceptable as the current business requirement is binary: staff admin vs non-admin).
