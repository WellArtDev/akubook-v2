# Story 28.2: Admin Activity Review Dashboard

**Story Key:** `28-2-admin-activity-review-dashboard`  
**Epic:** 28  
**Priority:** P1  
**Status:** review

## User Story
Sebagai Compliance Officer, saya ingin dashboard aktivitas admin agar perubahan sensitif bisa ditinjau cepat.

## Acceptance Criteria
1. Dashboard menampilkan audit log aktivitas admin/sensitive action terbaru.
2. Filter tersedia untuk actor, event, entity, sensitivity level, dan tanggal.
3. KPI menampilkan jumlah sensitive action, high severity, dan failed/blocked action jika tersedia.
4. Detail aktivitas menampilkan metadata tanpa membocorkan secret.
5. Halaman terlindungi auth dan tercakup smoke test.

## MVP Scope
- Controller + Inertia page admin activity review.
- Query dari `audit_logs` dan sensitive fields existing.
- Filter dasar + KPI cards + table.
- Feature test page shape.

## Out of Scope
- Real-time alert stream.
- SIEM integration.
- Advanced anomaly detection ML.

## Definition of Done
- [x] Admin activity dashboard tersedia.
- [x] Filter dan KPI dasar bekerja.
- [x] Metadata aman ditampilkan.
- [x] Tests/build pass dan story ke review.

## Dev Agent Record
### Completion Notes
- Added `AdminActivityReviewController` for sensitive/admin audit activity review.
- Added Inertia page with KPI cards, filters, activity table, and sanitized metadata rendering.
- Added metadata masking for secret-like keys (`password`, `secret`, `token`, `api_key`, `authorization`) including nested metadata.
- Added feature tests for page shape and secret masking behavior.

### File List
- `app/Http/Controllers/AdminActivityReviewController.php`
- `resources/js/Pages/AdminActivityReview/Index.jsx`
- `routes/web.php`
- `tests/Feature/AdminActivityReviewTest.php`
- `_bmad-output/implementation-artifacts/28-2-admin-activity-review-dashboard.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation
- `php artisan test tests/Feature/AdminActivityReviewTest.php` pass (2 tests, 31 assertions).
- `composer test` pass (488 tests, 488 passed, 2244 assertions).
- `npm run build` pass.

## Change Log
- 2026-05-20: Story created (ready-for-dev).
- 2026-05-20: Implemented admin activity review dashboard and moved story to review.
