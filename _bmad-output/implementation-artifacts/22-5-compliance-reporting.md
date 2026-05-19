# Story 22.5: Compliance Reporting

**Story Key:** `22-5-compliance-reporting`  
**Epic:** 22  
**Priority:** P0  
**Status:** done

## User Story
Sebagai Compliance Officer, saya ingin melihat ringkasan compliance dalam satu report agar status audit, sensitive actions, retention policy, dan workflow governance bisa dipantau cepat.

## Acceptance Criteria
1. Tersedia halaman compliance report read-only untuk user terautentikasi.
2. Report menampilkan summary metrik utama:
   - total audit logs (period)
   - total sensitive actions (period)
   - total active retention policies
   - total active approval workflows
3. Report menampilkan breakdown:
   - sensitive actions by level
   - audit logs by entity_type
   - retention policies by action
4. Report mendukung filter periode (`date_from`, `date_to`) untuk data berbasis waktu.
5. Tidak ada mutasi data domain; report hanya agregasi.

## MVP Scope
- Controller report compliance + route.
- Halaman Inertia `ComplianceReports/Index`.
- Sumber data:
  - `audit_logs`
  - `data_retention_policies`
  - `approval_workflows`
- Feature test untuk validasi summary dan filter.

## Out of Scope
- Export PDF/Excel.
- Alerting otomatis.
- Scoring compliance kompleks.

## Definition of Done
- [x] Halaman compliance report tersedia dan terlindungi auth.
- [x] Summary metrik utama tampil benar.
- [x] Breakdown sensitif/audit/retention tampil.
- [x] Filter periode berjalan.
- [x] Feature tests pass.
- [x] `composer test` dan `npm run build` dijalankan.

## Dev Agent Record
### Completion Notes
- Menambahkan endpoint read-only compliance report untuk agregasi governance data.
- Menambahkan summary metrik compliance: audit logs, sensitive actions, retention policies aktif, approval workflows aktif.
- Menambahkan breakdown compliance: sensitive actions by level, audit by entity, retention by action.
- Menambahkan filter periode (`date_from`, `date_to`) pada data audit-based.
- Menambahkan halaman Inertia `ComplianceReports/Index` untuk visualisasi summary dan breakdown.

### File List
- `_bmad-output/implementation-artifacts/22-5-compliance-reporting.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`
- `app/Http/Controllers/ComplianceReportController.php`
- `resources/js/Pages/ComplianceReports/Index.jsx`
- `routes/web.php`
- `tests/Feature/ComplianceReportTest.php`

### Validation
- `php artisan test tests/Feature/ComplianceReportTest.php --compact` passed: 3 tests, 52 assertions.
- `composer test` PHPUnit passed: 412 tests, 1636 assertions; Composer wrapper masih return code 1 seperti isu lama.
- `npm run build` passed.

### Change Log
- 2026-05-19: Story 22.5 dibuat dan diimplementasikan.

