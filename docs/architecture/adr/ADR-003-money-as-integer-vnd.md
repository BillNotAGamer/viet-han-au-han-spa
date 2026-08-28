# ADR-003: Store Money and Tuition in Integer VND

**Status:** ACCEPTED  
**Context:**  
Service pricing and course tuition must be stored reliably. Vietnamese Đồng (VND) is a zero-decimal currency (e.g. 390,000 VND). Using floating-point types (`FLOAT`, `DOUBLE`) introduces catastrophic precision rounding errors in totals and reporting.

**Decision:**  
Store all monetary amounts as `unsignedBigInteger` values representing exact VND amounts (e.g., `390000`).

**Consequences:**  
- **Positives:**
  - Exact precision in all database aggregations (`SUM`, `AVG`).
  - Native SQLite and MySQL compatibility.
  - Formatted easily via Blade/PHP helpers (`number_format($price, 0, ',', '.') . ' ₫'`).
- **Negatives:**
  - If international multi-currency pricing is introduced in future phases, a currency conversion rate or explicit currency code column would be required (out of scope for MVP).
