# DATABASE PORTABILITY MATRIX

**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Phase:** Phase 1A — Domain Model & Database Architecture Design (Closure Patch)  
**Status:** ACCEPTED (Architect-Approved)  
**Engines:** SQLite 3 (Local Development) vs MySQL 8.0+ & MariaDB 10.4+ (Production)  

---

## 1. Portability Objective & Architectural Scope

The database schema is designed to be **application-level portable across SQLite 3, MySQL 8.x, and supported MariaDB versions**.

While underlying storage engines possess physical differences (e.g. JSON binary formatting vs TEXT, integer storage, and collation semantics), Laravel 13 and Eloquent abstract these differences into consistent application-level behavior.

---

## 2. Feature-by-Feature Portability Comparison

| Schema Feature | SQLite 3 Behavior | MySQL 8.x / MariaDB 10.x Behavior | Architectural Rule & Safeguard |
| :--- | :--- | :--- | :--- |
| **Primary Keys** | `INTEGER PRIMARY KEY AUTOINCREMENT` | `BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY` | Use standard Laravel `$table->id()`. |
| **Foreign Keys** | Supported when enabled (`PRAGMA foreign_keys = ON`) | Enforced by InnoDB engine with transactional integrity | Use `$table->foreignId()->constrained()`. Laravel test runner enables SQLite foreign keys by default. |
| **Booleans** | Stored as `INTEGER` (`0` or `1`) | Stored as `TINYINT(1)` | Use `$table->boolean()`. Eloquent handles automatic casting to PHP boolean. |
| **JSON Columns** | Stored as `TEXT` with JSON syntax validation | Native `JSON` binary format (MySQL 8) / `LONGTEXT` alias (MariaDB) | Use `$table->json()`. JSON fields are strictly used for structured editorial repeaters (benefits, FAQs), never for relational joins. |
| **Timestamps** | Stored as `TEXT` (ISO-8601 string) | Stored as `DATETIME` or `TIMESTAMP` | Use standard `$table->timestamps()` and `$table->timestamp()`. Always persist in UTC. |
| **Time Columns** | Stored as `TEXT` (`HH:MM:SS`) | Stored as native `TIME` type | Use `$table->time('preferred_time')`. Represents local Vietnam business wall-clock time. |
| **String Lengths (`VARCHAR`)** | Dynamic text length | Enforces `VARCHAR(n)` size limit | Always specify explicit lengths (e.g. `string('phone', 30)`, `string('slug', 255)`). Slugs use standard `VARCHAR(255)` for modern MySQL 8.x InnoDB engines. The project does not promise compatibility with obsolete InnoDB configurations limited to legacy 767-byte index keys. |
| **Enum Types** | Not natively supported (treated as `TEXT` with check) | `ENUM('a','b')` requires ALTER TABLE to modify | **STRICT RULE:** Use `VARCHAR(32)` string columns with PHP 8.4 Backed Enums. Zero database-level ENUM types. |
| **Nullable Unique Columns** | Multiple `NULL` values are permitted in unique indexes | Multiple `NULL` values permitted (standard SQL) | `page_translations.slug` is `NULL` for the homepage record without unique collision. |
| **Cascade Behavior** | Cascades deletes properly when FKs are enabled | Cascades deletes via InnoDB engine | Explicit foreign key rules (`CASCADE`, `RESTRICT`, `SET NULL`) defined on every relationship. |

---

## 3. Storage Parity & Potential Edge Cases

1. **Unsigned Integer Semantics:**
   * *Engine Difference:* MySQL strictly enforces unsigned integer bounds on insert, whereas SQLite allows negative numbers unless checked.
   * *Mitigation:* FormRequests and Eloquent attribute casting enforce positive integer validation at the application layer.
2. **Case Sensitivity in Collation:**
   * *Engine Difference:* SQLite is case-sensitive by default for `LIKE` and text comparisons, while MySQL default `utf8mb4_unicode_ci` is case-insensitive.
   * *Mitigation:* Slugs and `phone_normalized` are strictly lowercased and formatted before persistence (`Str::lower($slug)`).
3. **JSON Search & Path Functions:**
   * *Engine Difference:* JSON query operators vary across database dialects.
   * *Mitigation:* Structured JSON columns are purely editorial display payloads and are never queried using SQL JSON functions in runtime query paths.
