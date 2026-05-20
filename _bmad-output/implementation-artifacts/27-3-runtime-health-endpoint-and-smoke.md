# Story 27.3: Runtime Health Endpoint and Smoke

**Story Key:** `27-3-runtime-health-endpoint-and-smoke`  
**Epic:** 27  
**Priority:** P1  
**Status:** done

## User Story
Sebagai operator aplikasi, saya ingin health endpoint dan smoke runtime yang cepat agar insiden route/database/service bisa dideteksi sebelum user terdampak.

## Acceptance Criteria
1. Ada endpoint health internal yang mengecek app + database connectivity.
2. Smoke check memanggil endpoint health dan route kritis.
3. CI gagal jika health endpoint atau smoke check gagal.
4. Output smoke jelas menyebut endpoint/route yang gagal.
5. Dokumen ringkas troubleshooting disediakan di artifact story.

## MVP Scope
- Endpoint `/healthz` sederhana (app + DB ping).
- Smoke command/test untuk health + route matrix inti.
- Integrasi ke workflow CI existing.

## Out of Scope
- External observability stack.
- Alert channel integrations (Slack/PagerDuty).
- Full synthetic monitoring.

## Definition of Done
- [x] Health endpoint tersedia.
- [x] Smoke health check tersedia.
- [x] CI menjalankan health smoke.
- [x] Troubleshooting notes tersedia.

## Dev Agent Record
### Completion Notes
- Menambahkan `HealthCheckController` sebagai endpoint `/healthz` untuk verifikasi status aplikasi dan koneksi database.
- Menambahkan feature test health endpoint.
- Memperluas Playwright smoke agar mencakup health check endpoint.
- Memperbaiki helper login smoke test agar tahan kondisi session/login-page fallback.
- Menambahkan hardening Playwright webServer command untuk menghapus `public/hot` stale file sebelum smoke run.

### Troubleshooting Notes
- Jika smoke gagal pada login dengan error `input email not found`, periksa file `public/hot` stale dari Vite dev mode.
- Jalankan ulang smoke dengan memastikan `public/hot` dihapus atau gunakan command smoke yang sudah membersihkan file itu.
- Jika `/healthz` gagal 503, cek koneksi DB dan hasil `php artisan migrate --force`.

### File List
- `app/Http/Controllers/HealthCheckController.php`
- `tests/Feature/HealthCheckTest.php`
- `tests/e2e/critical-routes.spec.js`
- `playwright.config.js`
- `routes/web.php`
- `_bmad-output/implementation-artifacts/27-3-runtime-health-endpoint-and-smoke.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation
- `php artisan test tests/Feature/HealthCheckTest.php` pass (1 test, 4 assertions).
- `npm run smoke:ui` pass (2 tests: health endpoint + critical authenticated routes).
- `composer test` pass (484 tests, 484 passed, 2209 assertions).
- `npm run build` pass.

## Change Log
- 2026-05-20: Story created (ready-for-dev).
- 2026-05-20: Implemented runtime health endpoint and smoke hardening, moved story to review.
