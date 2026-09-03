# Phase 11 Invariant Map

| Invariant | Status |
| --- | --- |
| Existing Booking domain reused | VERIFIED |
| No BookingRequest duplicate table created | VERIFIED |
| New migration count is zero | VERIFIED |
| VI GET `/dat-lich` exists | VERIFIED |
| VI POST `/dat-lich` exists | VERIFIED |
| EN GET `/en/booking` exists | VERIFIED |
| EN POST `/en/booking` exists | VERIFIED |
| `/vi/dat-lich` absent | VERIFIED |
| Public submission creates request, not confirmed appointment | VERIFIED |
| Initial status forced to `NEW` | VERIFIED |
| Locale derives from canonical route context | VERIFIED |
| Public cannot set status/admin_note/locale | VERIFIED |
| Service selection required because existing `bookings.service_id` is non-null | VERIFIED |
| Selected Service requires `PUBLISHED` + exact requested-locale translation | VERIFIED |
| Wrong-locale Service title does not leak in booking form | VERIFIED |
| Draft/archived/nonexistent Service rejected | VERIFIED |
| Preferred date uses Asia/Ho_Chi_Minh wall-clock validation | VERIFIED |
| Today/future accepted and past rejected | VERIFIED |
| CSRF retained | VERIFIED |
| POST rate-limited by `booking-submissions` | VERIFIED |
| PRG success flow implemented | VERIFIED |
| No public Booking read/list/show/edit/delete route | VERIFIED |
| No Contact POST added | VERIFIED |
| Filament Booking resource added | VERIFIED |
| Admin status management available | VERIFIED |
| Destructive admin delete action absent | VERIFIED |
| Floating/mobile booking CTA targets booking routes | VERIFIED |
| Primary six-item navigation unchanged | VERIFIED |
| Realtime availability not implemented | VERIFIED |
| Notifications not implemented | VERIFIED |
| Payment not implemented | VERIFIED |
| Public Booking Blade has no direct DB/Eloquent query | VERIFIED |
