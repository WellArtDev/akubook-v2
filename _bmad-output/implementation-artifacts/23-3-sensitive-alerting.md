# Story 23.3: Sensitive Alerting

**Story Key:** `23-3-sensitive-alerting`  
**Epic:** 23  
**Priority:** P1  
**Status:** review

## User Story
Sebagai Compliance Officer, saya ingin menerima alert untuk aksi sensitif berisiko tinggi agar bisa melakukan tindak lanjut cepat ketika ada aktivitas mencurigakan.

## Acceptance Criteria
1. Sistem mengidentifikasi event sensitif berdasarkan audit log (`is_sensitive=true`) dan level risiko.
2. Rule alert MVP mendukung threshold sederhana (contoh: jumlah event high dalam rentang waktu).
3. Alert batch terbentuk dengan ringkasan (`window`, `count`, `top_entities`, `status`).
4. Alert tidak duplikat untuk jendela yang sama (idempotent key).
5. Alert generation tercatat di audit log.
6. Tersedia halaman list alert untuk monitoring internal.

## MVP Scope
- Model/table `sensitive_alerts`.
- Job/service untuk generate alert dari audit log.
- Rule MVP:
  - `high` sensitivity count >= threshold per window.
- Halaman list alert (`SensitiveAlerts/Index`).
- Feature tests untuk generate/no-generate/idempotency.

## Out of Scope
- Integrasi email/Slack/WhatsApp.
- Real-time websocket push.
- ML anomaly detection.

## Definition of Done
- [x] Sensitive alert table/model dibuat.
- [x] Alert generator service/job berjalan.
- [x] Rule threshold high sensitivity bekerja.
- [x] List alert page tersedia.
- [x] Feature tests pass.
- [x] `composer test` dan `npm run build` dijalankan.

## Dev Agent Record
### Completion Notes
- Implemented `sensitive_alerts` table/model for alert batches with idempotency key, window, high count, threshold, top entities, status, and generator metadata.
- Added `SensitiveAlertService` to generate high-risk sensitive alerts from `audit_logs` using configurable threshold/window.
- Added sensitive audit logging for alert generation with event key `sensitive_alert.generated`.
- Added internal list/generate page at `SensitiveAlerts/Index` and authenticated routes.
- Added feature coverage for generate, no-generate, idempotency, and list page.

### File List
- `_bmad-output/implementation-artifacts/23-3-sensitive-alerting.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`
- `database/migrations/2026_05_19_040000_create_sensitive_alerts_table.php`
- `app/Models/SensitiveAlert.php`
- `app/Services/SensitiveAlertService.php`
- `app/Http/Controllers/SensitiveAlertController.php`
- `routes/web.php`
- `resources/js/Pages/SensitiveAlerts/Index.jsx`
- `tests/Feature/SensitiveAlertTest.php`

### Validation
- `php artisan test tests/Feature/SensitiveAlertTest.php` passed: 4 tests, 31 assertions.
- `composer test` PHPUnit payload passed: 423 tests, 1730 assertions; Composer wrapper still returned error code 1 after pass.
- `npm run build` passed: Vite build completed with 1182 modules transformed.

### Change Log
- 2026-05-19: Story 23.3 dibuat.
- 2026-05-19: Implemented sensitive alerting MVP and moved story to review.
