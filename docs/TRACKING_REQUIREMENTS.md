# MARKETING TRACKING & ATTRIBUTION SPECIFICATION

**Project:** Việt Hàn Âu Hàn Spa  
**Document Version:** 1.0.0 (Phase 0 Baseline)  

---

## 1. Tracking Philosophy & Governance

1. **Centralized Script Injection:** Tracking vendor scripts (Google Tag Manager, GA4, Meta Pixel) must be managed centrally via a single Blade component or injected via the system settings module. Never scatter raw `<script>` tags across individual Blade templates.
2. **Zero Secrets in Repository:** Real GTM container IDs, GA4 Measurement IDs, Meta Pixel IDs, or Conversions API access tokens must **never** be hardcoded in source control. They must reside in `.env` and be managed via Admin Settings.
3. **Server-Side Conversion Triggering:** Lead and Booking conversion events must be recorded server-side upon successful persistence in the database before triggering client-side conversion tags or dispatching to Meta Conversions API (CAPI).

---

## 2. Supported Vendor Integrations

| Vendor | Primary Purpose | Integration Method |
| :--- | :--- | :--- |
| **Google Tag Manager (GTM)** | Master tag container | Centralized `<head>` / `<body>` snippet |
| **Google Analytics 4 (GA4)** | Web analytics & user journey | Via GTM Data Layer events |
| **Google Ads** | Paid search & remarketing conversion tracking | Via GTM (`conversion` event on booking success) |
| **Meta Pixel** | Facebook/Instagram ad attribution | Client-side pixel + GTM Data Layer |
| **Meta Conversions API (CAPI)** | Resilient server-to-server conversion dispatch | Asynchronous Laravel Queue Job (Phase 12) |

---

## 3. Attribution Parameter Data Schema

Every inbound visitor request must parse and persist the following marketing attribution parameters into session storage, and attach them to the `Booking` record when a request is submitted:

### 3.1 Standard UTM Parameters
* `utm_source` (e.g., `facebook`, `google`, `tiktok`, `zalo`)
* `utm_medium` (e.g., `cpc`, `cpm`, `organic`, `referral`)
* `utm_campaign` (e.g., `tet_promotion_2026`, `herbal_body_therapy`)
* `utm_content` (e.g., `video_ad_1`, `carousel_gold`)
* `utm_term` (Search keywords)

### 3.2 Click Identifiers
* `gclid` (Google Click ID)
* `gbraid` / `wbraid` (Google iOS/App Click IDs)
* `fbclid` (Facebook Click ID)

### 3.3 Browser & Attribution Metadata
* `fbp` (Facebook browser ID cookie `_fbp`)
* `fbc` (Facebook click ID cookie `_fbc`)
* `landing_page` (Full URL of first arrival)
* `referrer` (`HTTP_REFERER` header)
* `user_agent` & `ip_address` (Hashed/anonymized where required by privacy regulations)

---

## 4. Standard Data Layer Events

```javascript
window.dataLayer = window.dataLayer || [];

// Booking Step 1: Initiated
window.dataLayer.push({
    event: 'booking_initiated',
    service_id: 12,
    service_name: 'Trị Liệu Cổ Vai Gáy Đông Y'
});

// Booking Step 2: Form Completed & Confirmed
window.dataLayer.push({
    event: 'generate_lead',
    booking_id: 'BK-20260820-0042',
    currency: 'VND',
    value: 450000,
    service_name: 'Trị Liệu Cổ Vai Gáy Đông Y'
});
```
