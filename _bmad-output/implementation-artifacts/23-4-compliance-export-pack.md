# Story 23.4: Compliance Export Pack

**Story Key:** `23-4-compliance-export-pack`  
**Epic:** 23  
**Priority:** P1  
**Status:** review

## User Story
Sebagai Compliance Officer, saya ingin mengekspor paket bukti compliance agar proses audit periodik bisa dilakukan cepat dan konsisten.

## Acceptance Criteria
1. User terautentikasi dapat generate export pack berdasarkan periode tanggal.
2. Export pack menggabungkan data inti compliance: audit logs sensitif, retention executions, workflow decisions.
3. File output memiliki metadata paket (`generated_at`, `generated_by`, `period`, `record_counts`).
4. Export tersimpan sebagai record batch dan bisa diunduh ulang.
5. Proses export tercatat di audit log sensitif.
6. Tersedia halaman list/detail export pack.

## MVP Scope
- Model/table `compliance_export_packs`.
- Service generator export (JSON/ZIP sederhana).
- Sumber data MVP:
  - `audit_logs` (sensitive)
  - `data_retention_executions`
  - approval/workflow enforcement log snapshot
- Controller run/list/show/download.
- Feature tests untuk generate + re-download.

## Out of Scope
- Format regulator-specific (XBRL, SAF-T).
- Digital signature/PKI.
- Incremental delta export.

## Definition of Done
- [x] Compliance export pack table/model dibuat.
- [x] Generator export pack berjalan.
- [x] List/detail/download page tersedia.
- [x] Audit log export tercatat.
- [x] Feature tests pass.
- [x] `composer test` dan `npm run build` dijalankan.

## Dev Agent Record
### Completion Notes
- Implemented compliance export pack batch model/table with generated pack numbering, period, metadata, record counts, payload JSON, generator, and status.
- Added service generator that snapshots sensitive audit logs, retention executions, and workflow enforcement decisions for selected period.
- Added list, generate, detail, and JSON download flows.
- Added sensitive audit log entry for every generated compliance export pack.
- Added feature tests for generation metadata/counts/audit log, list/detail pages, and re-download.

### File List
- `_bmad-output/implementation-artifacts/23-4-compliance-export-pack.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`
- `database/migrations/2026_05_19_050000_create_compliance_export_packs_table.php`
- `app/Models/ComplianceExportPack.php`
- `app/Services/ComplianceExportPackService.php`
- `app/Http/Controllers/ComplianceExportPackController.php`
- `routes/web.php`
- `resources/js/Pages/ComplianceExportPacks/Index.jsx`
- `resources/js/Pages/ComplianceExportPacks/Show.jsx`
- `tests/Feature/ComplianceExportPackTest.php`

### Validation
- `php artisan test tests/Feature/ComplianceExportPackTest.php` passed: 3 tests, 39 assertions.
- `composer test` PHPUnit payload passed: 426 tests, 426 passed, 1769 assertions; Composer wrapper still returned error code 1 after pass.
- `npm run build` passed: Vite v8.0.12, 1184 modules transformed.

### Change Log
- 2026-05-19: Story 23.4 dibuat.
- 2026-05-19: Implemented compliance export pack and moved story to review.
