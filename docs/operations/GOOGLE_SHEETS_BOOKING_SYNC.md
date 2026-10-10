# Google Sheets Booking Sync Operations

The public Booking request commits the Booking and its durable synchronization
intent atomically, runs the existing notification behavior, and returns without
making a Google API call. Delivery to the client-owned `A:F` schema is
scheduler-only. Google latency and failures therefore remain isolated from the
customer response.

This synchronization is asynchronous: a customer can receive the normal
Booking confirmation before the row appears in Google Sheets. With a healthy
once-per-minute cron, pending records are normally picked up by a subsequent
scheduler run, but there is no strict delivery-time SLA. If cron is absent or
stops running, pending records remain durable in the database and do not
synchronize automatically.

## Worksheet contract and duplicate key

The configured destination is spreadsheet
`1JQKtj4wsrdx5vKOd1vbkTsmVqBGcpokHwyCR5MMI8AI`, tab `Trang tính1`. The exact
header must be:

| Column | Header | Persisted Booking source |
|---|---|---|
| A | NGÀY | `preferred_date`, formatted `dd/mm/yyyy` |
| B | DỊCH VỤ | `service_name_snapshot` |
| C | TÊN KHÁCH HÀNG | `customer_name` |
| D | SỐ ĐIỆN THOẠI | `phone`, written with `RAW` input |
| E | GIỜ MONG MUỐN | `preferred_time`, formatted `HH:mm` |
| F | MÃ ĐẶT LỊCH | immutable outbox `booking_reference` |

The owner may hide column F manually. The application does not alter headers,
formatting, visibility, or existing rows. Before every append it validates
`A1:F1` and reads only `F2:F`, comparing Booking references as exact strings.
One match confirms synchronization without appending; multiple matches are a
duplicate anomaly requiring an operator. Reading column F is linear in the
number of Sheet rows and is appropriate for the expected low-volume workload.
If the sheet grows to tens of thousands of rows or API latency becomes
material, review this design before enabling more destinations.

Google Sheets append does not provide a server-enforced unique constraint, so
absolute exactly-once delivery cannot be guaranteed by the API alone. The
database uniqueness constraints, claim lease, pre-append lookup, and uncertain
state substantially reduce duplicate risk without pretending to eliminate it.

## Durable outbox states

New Bookings create one `booking_google_sheets_syncs` row in the same database
transaction only when synchronization is enabled. Existing/historical Bookings
are never backfilled automatically.

- `pending`: committed intent that has not yet been claimed.
- `processing`: one process owns a time-limited database lease.
- `synced`: the append succeeded or exactly one matching F value was found.
- `failed`: a known failure; `append_safe`, `retryable`, and
  `requires_manual_intervention` distinguish a safe confirmed Admin retry,
  automatic retry eligibility, and configuration/operator blocks.
- `uncertain`: an append may have succeeded, or a processing lease expired.
  It is reconciliation-only and is never blindly appended again.

Safe pre-write failures and HTTP 429 use bounded exponential retry scheduling.
Authentication/configuration errors, header mismatches, HTTP 403/404, duplicate
references, and exhausted retries require an operator. Network failures, HTTP
408, and 5xx responses during append become `uncertain`. A negative lookup
immediately after an ambiguous append leaves the record uncertain and blocks
automatic resubmission because delayed visibility cannot be ruled out.

The lease is longer than the configured Google HTTP timeouts. A process checks
its database lease again immediately before append. An expired `processing`
row becomes `uncertain`, never `pending`, and is reconciled by reference.

## Credential sources

The preferred source is an absolute service-account JSON file path:

```dotenv
GOOGLE_SHEETS_CREDENTIALS_FILE=/home/viethana/.secrets/viethan-google-booking.json
```

The file is read by `GoogleBookingSheetsClient` only when a synchronization is
attempted. Its JSON contents, including the private key, are never placed in
Laravel configuration and therefore are not written to
`bootstrap/cache/config.php`.

`GOOGLE_SHEETS_CREDENTIALS_JSON` remains available as a legacy runtime
fallback. The file setting has deterministic precedence whenever it is
non-empty. If a configured file is missing, unreadable, invalid, inside the
public web root, or not a service-account credential, synchronization fails
safely; the client does not silently fall back to raw JSON.

Do not log, display, paste into a shell command, or commit either credential
source.

## Vietnix/cPanel installation

Deploy the application first with synchronization disabled:

```dotenv
GOOGLE_SHEETS_BOOKING_ENABLED=false
GOOGLE_SHEETS_SPREADSHEET_ID=1JQKtj4wsrdx5vKOd1vbkTsmVqBGcpokHwyCR5MMI8AI
GOOGLE_SHEETS_TAB_NAME="Trang tính1"
GOOGLE_SHEETS_BOOKING_BATCH_LIMIT=25
GOOGLE_SHEETS_CREDENTIALS_FILE=/home/viethana/.secrets/viethan-google-booking.json
GOOGLE_SHEETS_CREDENTIALS_JSON=
```

Create a private directory outside `public`, `public_html`, and every hosted
document root. Run these commands as the cPanel account that executes PHP:

```bash
install -d -m 700 /home/viethana/.secrets
chmod 700 /home/viethana/.secrets
chmod 600 /home/viethana/.secrets/viethan-google-booking.json
test -f /home/viethana/.secrets/viethan-google-booking.json
test -r /home/viethana/.secrets/viethan-google-booking.json
```

Upload the downloaded JSON with cPanel File Manager or SFTP directly into that
directory. Do not place an intermediate copy in `public_html`, application
`public`, a deployment archive, or the Git repository. The credential file and
directory should be owned by the cPanel account; do not grant group or world
read access. The service-account email must have Editor access to the target
spreadsheet.

## Laravel configuration cache

After changing production `.env`, refresh configuration from the application
root while synchronization is still disabled:

```bash
php artisan config:clear
php artisan config:cache
```

If the deployment runs persistent queue workers, restart them after caching:

```bash
php artisan queue:restart
```

The cached value contains only the non-secret file path. After verifying file
ownership, permissions, and the target settings, change only
`GOOGLE_SHEETS_BOOKING_ENABLED=true`, then repeat `config:clear` and
`config:cache` (and `queue:restart` when applicable).

Submit one controlled Booking through the normal public form and first verify
the committed Booking and `pending` outbox row. Allow the next scheduler run (or
run the synchronization command once under controlled operator supervision),
then verify exactly one appended Sheet row and a `synced` outbox state. Confirm
the values are date, persisted service-name snapshot, customer name,
text-preserved phone, preferred time, and Booking reference. If the check
fails, disable the integration and refresh the configuration cache again;
existing Booking persistence, XLSX export, and notifications remain
independent.

## Command, scheduler, and cron

The bounded reconciliation command requires no persistent queue worker:

```bash
php artisan bookings:sync-google-sheets --limit=25
php artisan bookings:sync-google-sheets --limit=25 --dry-run
```

Dry-run performs no database mutation, Google read, or Google write. Normal
runs continue after isolated record failures and print only sanitized counts.
The Laravel schedule runs the command every minute with overlap prevention.
The project defaults to the database cache store, so `withoutOverlapping()`
uses the existing `cache` table for its process lock.
Production still requires the owner to install and verify this cPanel cron;
the repository cannot prove that a production cron already exists:

```cron
* * * * * cd /home/viethana/apps/viethanauhanspa && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

Replace the application path and PHP binary with the exact Vietnix values. Use
`php artisan schedule:list` and cPanel cron logs to verify execution. No queue
worker, Redis, or Horizon is required for this synchronization path.

As a simple operational check, treat `pending` records older than 10 minutes as
requiring investigation. The Admin Booking list/view exposes synchronization
status and attempt data. An operator may also obtain a PII-free count from the
application root without changing data:

```bash
php artisan tinker --execute="echo App\\Models\\BookingGoogleSheetsSyncRecord::query()->where('status', 'pending')->where('created_at', '<=', now()->subMinutes(10))->count().PHP_EOL;"
```

A non-zero count should prompt checks of cPanel cron history,
`php artisan schedule:list`, application logs, and the integration-enabled
configuration. Do not convert old pending or uncertain records manually before
checking their reference in column F.

## Admin operations and incidents

The Filament Booking view shows sync state, attempt count, last attempt, synced
time, sanitized error, and manual-intervention flag. Authorized administrators
have these guarded actions:

- `Kiểm tra đồng bộ` performs header/reference reads only. It never appends.
- `Thử đồng bộ lại` is confirmation-gated and appears only for `pending` or a
  `failed` record explicitly marked retry-safe. It is unavailable for
  `uncertain`, `processing`, or `synced` records.

For `uncertain`, run reconciliation. One exact F match resolves to `synced`;
multiple matches are a duplicate incident; no match stays uncertain. Compare
the Sheet and Google audit history before any owner-approved manual recovery.
Never delete an existing row to make a retry convenient.

For a duplicate anomaly, disable sync if the anomaly is systemic, retain all
rows, record the affected Booking references, and investigate before making a
manual correction. Logs and outbox errors intentionally omit customer PII,
credentials, tokens, and raw exception messages.

To stop Google synchronization without stopping Booking intake, set
`GOOGLE_SHEETS_BOOKING_ENABLED=false`, refresh Laravel configuration cache, and
leave the cron installed. Existing pending records remain durable and no new
outbox rows are created while disabled.

## Safe deployment order

The GitHub workflow invokes a server-local `deploy.sh`, but that file is not in
this repository, so its migration behavior cannot be independently verified
from source. Use this staged sequence:

1. Keep `GOOGLE_SHEETS_BOOKING_ENABLED=false` and deploy the additive migration.
2. Run `php artisan migrate --force`; do not use fresh/reset/wipe commands.
3. Verify the new table and run `php artisan bookings:sync-google-sheets --dry-run`.
4. Install and verify the one-minute scheduler cron.
5. Install the credential file and refresh configuration cache as above.
6. Confirm the six Sheet headers and Editor sharing manually.
7. Enable synchronization, refresh configuration cache, and submit one
   controlled Booking.
8. Verify one Booking, one outbox row, and one A:F Sheet row with the same
   reference; then inspect the Admin status surface.

Migration-before-code is safe because the table is additive. Code-before-
migration is safe only while synchronization remains disabled; enabling before
the migration would make transactional outbox creation fail and correctly roll
back the Booking rather than accept it without durable intent.
