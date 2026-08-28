# PHASE 0 ENVIRONMENT AUDIT & BLOCKER REPORT

**Date:** 2026-08-19 / 2026-08-20  
**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Workspace Root:** `F:\Coding\Web development\Viet Han Spa`  
**Status:** **BLOCKED (Missing Essential Prerequisites: PHP 8.3+ & Composer)**

---

## 1. Executive Summary

During the Phase 0 preflight environment inspection, the workspace and system tools were audited. While Node.js, npm, Git, and SQLite CLI are installed and operational, **PHP** (minimum required: PHP 8.3+ for Laravel 13) and **Composer** are **NOT installed** or are not registered in the system/user PATH.

In strict compliance with **Safety Rules (Section 1 & Section 2)**:
- No global installers (`winget`, Chocolatey, system MSI/EXE installers) were executed.
- No system PATH or machine-wide environment variables were altered without explicit user authorization.
- No mock or fabricated Laravel scaffolding was generated.
- Execution was paused immediately at the environment gate to report the blocker and remediation steps.

---

## 2. Environment Audit Matrix

| Component | Status | Detected Version | Executable Path / Details | Requirement |
| :--- | :--- | :--- | :--- | :--- |
| **PHP** | ❌ **MISSING** | None (`php` command not found) | N/A | **PHP >= 8.3.0** (with extensions: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`, `intl`, `zip`, `pdo_sqlite` / `pdo_mysql`) |
| **Composer** | ❌ **MISSING** | None (`composer` command not found) | N/A | **Composer 2.x** |
| **Node.js** | ✅ **INSTALLED** | `v20.14.0` (LTS) | `C:\Program Files\nodejs\node.exe` | Node.js >= 18.x (Satisfied) |
| **npm** | ✅ **INSTALLED** | `10.8.1` | `C:\Program Files\nodejs\npm.cmd` | npm >= 9.x (Satisfied) |
| **Git** | ✅ **INSTALLED** | `2.46.1.windows.1` | `C:\Program Files\Git\cmd\git.exe` | Git >= 2.x (Satisfied) |
| **Database (Local)** | ⚠️ **PARTIAL** | SQLite CLI 3.x available | `C:\msys64\ucrt64\bin\sqlite3.exe` | SQLite 3 (Dev temporary) / MySQL 8.x / MariaDB 10.x |
| **Docker** | ℹ️ Detected | `29.0.1` (Daemon stopped) | `C:\Program Files\Docker\Docker\resources\bin\docker.exe` | Optional / Not required for shared hosting target |

---

## 3. Detailed Audit Findings

### 3.1 Initial Workspace State
- **Workspace Directory:** `F:\Coding\Web development\Viet Han Spa`
- **Initial File Count:** 0 files / clean directory.
- **Existing Git Repository:** No `.git` found initially.
- **Existing Package Files:** No `composer.json` or `package.json`.

### 3.2 Missing Components
1. **PHP 8.3+ Runtime**:
   - `php.exe` is not found in PATH or standard locations (`C:\php`, `C:\laragon`, `C:\xampp`, `C:\tools\php`, `C:\Users\Admin\.config\herd`).
   - Laravel 13 requires PHP 8.3 or PHP 8.4 with standard extensions enabled.
2. **Composer**:
   - `composer` CLI is not found on PATH.

---

## 4. Recommended Safe Installation Approaches

The user must install or expose PHP 8.3+ and Composer using one of the following methods before rerunning Phase 0:

### Option A: Laravel Herd for Windows (Recommended - Fastest & Easiest)
- Download and install **Laravel Herd** from [https://herd.laravel.com/windows](https://herd.laravel.com/windows).
- Herd bundles PHP 8.3/8.4, Composer, Node.js, and all required Laravel extensions automatically without conflicting with existing software.

### Option B: Laragon (Full Local LAMP/WAMP Stack)
- Download and install **Laragon** from [https://laragon.org/download/](https://laragon.org/download/).
- Add PHP 8.3+ binary to `C:\laragon\bin\php\php-8.3.x` and enable it via Laragon menu.
- Includes MySQL/MariaDB, Composer, and Redis out-of-the-box.

### Option C: Manual PHP 8.3+ & Composer Setup
1. Download PHP 8.3+ (VS16 x64 Thread Safe or Non-Thread Safe) from [https://windows.php.net/download/](https://windows.php.net/download/).
2. Extract to `C:\php`.
3. In `php.ini`, enable required extensions:
   ```ini
   extension_dir = "ext"
   extension=bcmath
   extension=curl
   extension=fileinfo
   extension=gd
   extension=intl
   extension=mbstring
   extension=openssl
   extension=pdo_mysql
   extension=pdo_sqlite
   extension=sqlite3
   extension=zip
   ```
4. Add `C:\php` to Windows User Environment Variable `Path`.
5. Install Composer using Windows Installer from [https://getcomposer.org/Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).

---

## 5. Required Actions Before Rerunning Phase 0

1. Complete one of the installation options above (e.g. Laravel Herd or Manual PHP 8.3 + Composer).
2. Open a new terminal / reload IDE to refresh environment variables.
3. Verify that the following commands run successfully:
   ```powershell
   php -v         # Must show PHP 8.3.x or 8.4.x
   composer -V    # Must show Composer 2.x
   ```
4. Rerun Phase 0 bootstrap.

---

## 6. Readiness Status

**STATUS:** `BLOCKED` (Awaiting PHP 8.3+ and Composer installation by user).
