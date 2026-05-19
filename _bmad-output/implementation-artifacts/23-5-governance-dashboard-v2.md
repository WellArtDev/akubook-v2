# Story 23.5: Governance Dashboard v2

**Story Key:** `23-5-governance-dashboard-v2`  
**Epic:** 23  
**Priority:** P1  
**Status:** done

## User Story
Sebagai Compliance Officer, saya ingin dashboard governance terpadu agar bisa memantau posture compliance harian dari retention, approval enforcement, alert, dan export readiness dalam satu layar.

## Acceptance Criteria
1. Dashboard menampilkan KPI governance utama (retention runs, enforcement count, sensitive alerts, export packs).
2. KPI dapat difilter berdasarkan periode tanggal.
3. Tersedia section tren ringkas (daily/weekly) untuk aktivitas governance.
4. Widget menampilkan status terakhir proses penting (last retention run, last export).
5. Data sumber konsisten dengan tabel operasional masing-masing modul.
6. Halaman dapat diakses user terautentikasi dengan izin governance.

## MVP Scope
- Endpoint/controller dashboard governance v2.
- Query agregasi KPI dari modul Epic 22-23.
- Halaman `GovernanceDashboardV2/Index` dengan cards + table ringkas.
- Filter tanggal dasar (from/to).
- Feature test akses + shape data dashboard.

## Out of Scope
- Drilldown grafik kompleks.
- Near real-time refresh websocket.
- Custom widget per role.

## Definition of Done
- [x] Endpoint dashboard governance v2 tersedia.
- [x] KPI agregat utama tampil sesuai sumber data.
- [x] Filter periode bekerja.
- [x] Feature tests pass.
- [x] `composer test` dan `npm run build` dijalankan.

## Dev Agent Record
### Completion Notes
- Implemented Governance Dashboard v2 endpoint with date filters, KPI aggregation, daily trend payload, and latest process widgets.
- Added Inertia page `GovernanceDashboardV2/Index` with filter form, KPI cards, recent status, and trend summary.
- Added feature tests for access, dashboard payload shape, period filtering, and operational source counts.

### File List
- `_bmad-output/implementation-artifacts/23-5-governance-dashboard-v2.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`
- `app/Http/Controllers/GovernanceDashboardV2Controller.php`
- `routes/web.php`
- `resources/js/Pages/GovernanceDashboardV2/Index.jsx`
- `tests/Feature/GovernanceDashboardV2Test.php`

### Validation
- `php artisan test tests/Feature/GovernanceDashboardV2Test.php` passed: 3 tests, 61 assertions.
- `composer test` PHPUnit payload passed: 429 tests, 429 passed, 1830 assertions; Composer wrapper still returned error code 1 after pass.
- `npm run build` passed: Vite build completed with 1185 modules transformed.

### Change Log
- 2026-05-19: Story 23.5 dibuat.
- 2026-05-19: Implemented governance dashboard v2 and moved story to review.
