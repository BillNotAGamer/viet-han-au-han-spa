# DATABASE ENTITY RELATIONSHIP DIAGRAM (ERD)

**Project:** Việt Hàn Âu Hàn Spa (`viethanauhanspa.com`)  
**Phase:** Phase 1A — Domain Model & Database Architecture Design (Closure Patch)  
**Status:** ACCEPTED (Architect-Approved)  

---

```mermaid
erDiagram
    users ||--o{ posts : "authors"
    users ||--o{ media : "uploads"

    service_categories ||--o{ service_category_translations : "translates"
    service_categories ||--o{ services : "categorizes"
    
    services ||--o{ service_translations : "translates"
    services ||--o{ service_prices : "offers"
    services ||--o{ service_media : "has_gallery"
    services ||--o{ bookings : "booked_service"
    services }o--|| media : "hero_image"

    service_prices ||--o{ service_price_translations : "translates"
    service_prices ||--o{ bookings : "selected_tier"

    training_courses ||--o{ training_course_translations : "translates"
    training_courses ||--o{ training_course_media : "has_gallery"
    training_courses ||--o{ training_inquiries : "inquiries_for"
    training_courses }o--|| media : "hero_image"

    post_categories ||--o{ post_category_translations : "translates"
    post_categories ||--o{ posts : "categorizes"

    posts ||--o{ post_translations : "translates"
    posts ||--o{ post_media : "has_gallery"
    posts }o--|| media : "hero_image"

    pages ||--o{ page_translations : "translates"
    pages ||--o{ page_media : "has_gallery"

    media ||--o{ media_translations : "translates"
    media ||--o{ service_media : "gallery_item"
    media ||--o{ training_course_media : "gallery_item"
    media ||--o{ post_media : "gallery_item"
    media ||--o{ page_media : "gallery_item"

    service_categories {
        bigint id PK
        varchar status
        int sort_order
    }

    service_category_translations {
        bigint id PK
        bigint service_category_id FK
        varchar locale
        varchar name
        varchar slug
        text description
        varchar seo_title
        varchar seo_description
    }

    services {
        bigint id PK
        bigint service_category_id FK
        bigint hero_media_id FK
        varchar status
        boolean is_featured
        int sort_order
    }

    service_translations {
        bigint id PK
        bigint service_id FK
        varchar locale
        varchar name
        varchar slug
        text excerpt
        longtext content
        json benefits
        json process_steps
        json faqs
        varchar seo_title
        varchar seo_description
    }

    service_prices {
        bigint id PK
        bigint service_id FK
        int duration_minutes
        bigint price_amount
        int sort_order
        boolean is_active
    }

    service_price_translations {
        bigint id PK
        bigint service_price_id FK
        varchar locale
        varchar label
    }

    training_courses {
        bigint id PK
        bigint hero_media_id FK
        bigint tuition_fee
        varchar status
        boolean is_featured
        int sort_order
        timestamp published_at
    }

    training_course_translations {
        bigint id PK
        bigint training_course_id FK
        varchar locale
        varchar title
        varchar slug
        text excerpt
        longtext content
        varchar duration_display
        varchar schedule_display
        text target_audience
        json curriculum_modules
        json benefits
        json faqs
        varchar seo_title
        varchar seo_description
    }

    training_inquiries {
        bigint id PK
        varchar reference UK
        bigint training_course_id FK
        varchar customer_name
        varchar phone
        varchar phone_normalized
        varchar email
        text message
        varchar status
        varchar locale
        text admin_note
        timestamp contacted_at
        timestamp enrolled_at
        timestamp closed_at
        varchar utm_source
        varchar utm_medium
        varchar utm_campaign
        varchar utm_content
        varchar utm_term
        varchar gclid
        varchar gbraid
        varchar wbraid
        varchar fbclid
        varchar fbp
        varchar fbc
        varchar landing_page
        varchar referrer
        timestamp created_at
        timestamp deleted_at
    }

    post_categories {
        bigint id PK
        varchar status
        int sort_order
    }

    post_category_translations {
        bigint id PK
        bigint post_category_id FK
        varchar locale
        varchar name
        varchar slug
        text description
        varchar seo_title
        varchar seo_description
    }

    posts {
        bigint id PK
        bigint post_category_id FK
        bigint author_id FK
        bigint hero_media_id FK
        varchar status
        boolean is_featured
        timestamp published_at
    }

    post_translations {
        bigint id PK
        bigint post_id FK
        varchar locale
        varchar title
        varchar slug
        text excerpt
        longtext content
        varchar seo_title
        varchar seo_description
    }

    post_media {
        bigint id PK
        bigint post_id FK
        bigint media_id FK
        int sort_order
    }

    pages {
        bigint id PK
        varchar key UK
        varchar status
    }

    page_translations {
        bigint id PK
        bigint page_id FK
        varchar locale
        varchar title
        varchar slug
        longtext content
        varchar seo_title
        varchar seo_description
    }

    page_media {
        bigint id PK
        bigint page_id FK
        bigint media_id FK
        int sort_order
    }

    media {
        bigint id PK
        varchar disk
        varchar path
        varchar file_name
        varchar mime_type
        varchar extension
        bigint size_bytes
        int width
        int height
        bigint uploaded_by FK
    }

    media_translations {
        bigint id PK
        bigint media_id FK
        varchar locale
        varchar alt_text
        varchar caption
    }

    service_media {
        bigint id PK
        bigint service_id FK
        bigint media_id FK
        int sort_order
    }

    training_course_media {
        bigint id PK
        bigint training_course_id FK
        bigint media_id FK
        int sort_order
    }

    bookings {
        bigint id PK
        varchar reference UK
        bigint service_id FK
        bigint service_price_id FK
        varchar service_name_snapshot
        varchar service_price_label_snapshot
        int duration_minutes_snapshot
        bigint price_amount_snapshot
        varchar customer_name
        varchar phone
        varchar phone_normalized
        varchar email
        date preferred_date
        time preferred_time
        int guest_count
        text customer_note
        text admin_note
        varchar status
        varchar locale
        timestamp contacted_at
        timestamp confirmed_at
        timestamp completed_at
        timestamp cancelled_at
        varchar utm_source
        varchar utm_medium
        varchar utm_campaign
        varchar utm_content
        varchar utm_term
        varchar gclid
        varchar gbraid
        varchar wbraid
        varchar fbclid
        varchar fbp
        varchar fbc
        varchar landing_page
        varchar referrer
        timestamp created_at
        timestamp deleted_at
    }

    site_settings {
        bigint id PK
        varchar key UK
        longtext value
        varchar type
        varchar group
        boolean is_public
    }
```
