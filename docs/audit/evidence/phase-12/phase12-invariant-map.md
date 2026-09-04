# Phase 12 Invariant Verification Map

**Project:** Việt Hàn Âu Hàn Spa  
**Phase:** 12 — Marketing Tracking & Attribution  
**Audit Date:** 2026-09-03  
**Implementation Agent:** ANTIGRAVITY / GEMINI 3.8 FLASH  
**Verification Base Commit:** `51097ca75a064b34b19d582356e0178c1376e417`

---

## Invariant Compliance Matrix

| # | Phase 12 Architectural Invariant | Enforcement Mechanism | Verification File / Evidence | Status |
| :- | :--- | :--- | :--- | :--- |
| 1 | **Zero Migrations** | Strict freeze on `database/migrations` | `git diff --stat 51097ca75a064b34b19d582356e0178c1376e417 database/migrations` (0 changes) | **PASS** |
| 2 | **Zero Package Additions** | No new composer / npm packages | `git diff 51097ca75a064b34b19d582356e0178c1376e417 composer.json package.json` (0 changes) | **PASS** |
| 3 | **Default Disabled Tracking** | `tracking.enabled` defaults to `false` in Site Settings | `TrackingConfiguration::isEnabled()` & `test_tracking_disabled_by_default_and_renders_no_tags` | **PASS** |
| 4 | **Exact Setting Keys** | `tracking.gtm_container_id`, `tracking.ga4_measurement_id`, `tracking.meta_pixel_id`, `tracking.enabled` | `TrackingConfiguration` constants & `SiteSettings` lookup | **PASS** |
| 5 | **GTM Priority Mode** | GTM renders exclusively when container ID valid; direct GA4 & Meta suppressed | `TrackingConfiguration::getDeliveryMode()` & `test_gtm_mode_exclusive_delivery_when_all_providers_configured` | **PASS** |
| 6 | **Direct Delivery Fallback** | GA4 and/or Meta render if and only if GTM absent | `test_direct_ga4_only_mode`, `test_direct_meta_only_mode`, `test_direct_mode_with_both_ga4_and_meta` | **PASS** |
| 7 | **Strict Identifier Regex** | GTM (`/^GTM-[A-Z0-9]+$/i`), GA4 (`/^G-[A-Z0-9]+$/i`), Meta (`/^[0-9]{5,25}$/`) | `test_gtm_container_id_validation_and_malicious_script_suppression`, etc. | **PASS** |
| 8 | **Zero PII Policy** | No customer name, phone, email, or notes in client-side scripts | `test_pii_leak_safety_in_tracking_output` & `pii-review.txt` | **PASS** |
| 9 | **PRG-Only Conversion Event** | Conversion events fire once upon session flash; never on validation error or refresh | `test_gtm_conversion_event_fired_after_prg_and_never_on_validation_failure_or_refresh` | **PASS** |
| 10 | **First-Touch Persistence** | `landing_page` and `referrer` captured once and never overwritten on internal navigation | `test_initial_touch_landing_page_and_referrer_are_preserved` | **PASS** |
| 11 | **Latest-Touch Parameters** | UTMs and click IDs (`gclid`, `gbraid`, `wbraid`, `fbclid`) update on new campaign visit | `test_primary_campaign_attribution_persistence_from_landing_to_booking` | **PASS** |
| 12 | **Meta Cookie Capture** | `_fbp` -> `fbp`, `_fbc` -> `fbc` captured from request cookies | `test_meta_browser_cookies_fbp_and_fbc_persisted` | **PASS** |
| 13 | **Safe Truncation Bounds** | Control chars stripped, string lengths bounded to exact migration column widths | `test_attribution_parameters_are_strictly_truncated_to_column_lengths` | **PASS** |
| 14 | **Admin & Filament Isolation** | Tracking scripts and attribution middleware excluded from `/admin` and `/livewire` | `test_admin_and_filament_endpoints_exclude_marketing_tracking`, `test_attribution_middleware_skips_admin_and_livewire` | **PASS** |
| 15 | **Admin Read-Only Infolist** | All 13 attribution fields visible read-only in Filament Booking infolist | `test_filament_booking_infolist_displays_attribution_fields` | **PASS** |
| 16 | **No Public Route Leaks** | No tracking endpoints, no `/vi` route leaks, no navigation regression | `test_booking_cta_and_primary_nav_regression`, `route-list.txt` | **PASS** |

---

## Test Execution Summary

```text
Tests: 325
Passed: 325
Assertions: 1552
Failures: 0
Errors: 0
Duration: 19.85s
```
