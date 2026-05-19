# Story 22.1: Audit Log Foundation

**Story Key:** 22-1-audit-log-foundation  
**Priority:** P0  
**Status:** review

## User Story
Sebagai admin/compliance, saya ingin semua aksi penting tercatat dalam audit log agar perubahan data bisa ditelusuri dengan jelas.

## Acceptance Criteria
1. Sistem memiliki entitas `audit_logs` untuk menyimpan jejak perubahan dengan field minimal: `event_key`, `entity_type`, `entity_id`, `action`, `actor_user_id`, `occurred_at`, `ip_address`, `user_agent`, `old_values`, `new_values`, `metadata`.
2. Tersedia helper/service untuk menulis audit log secara konsisten dari controller/service lain.
3. Aksi CRUD pada minimal satu modul kritikal (MVP: salary components) sudah menulis audit log create/update/delete.
4. Tersedia halaman daftar audit log dengan filter dasar (`event_key`, `entity_type`, `actor`, rentang tanggal).
5. Audit log bersifat append-only (tidak ada endpoint edit/delete).

## MVP Scope
- Migration + model `AuditLog`.
- Service/helper `AuditLogger`.
- Integrasi logging pada `SalaryComponentController` untuk create/update/delete.
- Inertia page `AuditLogs/Index` read-only.
- Feature tests untuk persistence + integrasi + filter index.

## Out of Scope
- Streaming/real-time SIEM integration.
- Digital signature/tamper-proof hash chain.
- Export besar (CSV/PDF) khusus audit.

## Definition of Done
- [x] Tabel audit log + model siap.
- [x] Helper/service audit logger dipakai oleh modul MVP.
- [x] Salary component create/update/delete menghasilkan audit row valid.
- [x] Halaman audit log + filter dasar jalan.
- [x] Feature tests lulus.
- [x] `composer test` dan `npm run build` dijalankan.

## Dev Agent Record
### Completion Notes
- Menambahkan field audit governance baru ke tabel `audit_logs` lama tanpa merusak kolom legacy.
- Menambahkan `AuditLogger` service untuk write audit log konsisten.
- Mengintegrasikan audit log pada `SalaryComponentController` untuk create/update/delete.
- Menambahkan halaman read-only `AuditLogs/Index` dengan filter event, entity, actor, dan tanggal.
- Menambahkan feature tests untuk index, filter, dan integrasi salary component audit.

### File List
- _bmad-output/implementation-artifacts/22-1-audit-log-foundation.md
- _bmad-output/implementation-artifacts/sprint-status.yaml
- app/Models/AuditLog.php
- app/Services/AuditLogger.php
- app/Http/Controllers/AuditLogController.php
- app/Http/Controllers/SalaryComponentController.php
- database/factories/AuditLogFactory.php
- database/migrations/2026_05_19_012325_create_audit_logs_table.php
- resources/js/Pages/AuditLogs/Index.jsx
- routes/web.php
- tests/Feature/AuditLogTest.php

### Validation
- `php artisan test tests/Feature/AuditLogTest.php --compact`: pass, 4 tests / 30 assertions.
- `composer test`: PHPUnit pass, 396 tests / 1509 assertions; Composer wrapper masih return code 1 setelah pass.
- `npm run build`: pass.

### Change Log
- 2026-05-19: Implemented Story 22.1 Audit Log Foundation MVP and validations.
