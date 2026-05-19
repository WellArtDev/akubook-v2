# Story 22.4: Sensitive Action Tracking

**Story Key:** `22-4-sensitive-action-tracking`  
**Epic:** 22  
**Priority:** P0  
**Status:** done

## User Story
Sebagai Compliance Officer, saya ingin aksi sensitif terekam dengan flag khusus agar audit high-risk bisa difilter dan ditinjau cepat.

## Acceptance Criteria
1. Sistem menandai audit event sebagai sensitive/non-sensitive.
2. Aksi sensitif menyimpan konteks tambahan (`sensitivity_level`, `sensitivity_reason`).
3. Tersedia halaman tracking aksi sensitif dengan filter event/entity/actor/date.
4. Integrasi minimal pada aksi berisiko (contoh: delete master, cancel/post transaksi kritikal) agar tercatat sebagai sensitive.
5. Fitur bersifat append-only pada audit log; tidak mengubah data bisnis domain.

## MVP Scope
- Extend `audit_logs` dengan kolom sensitif.
- Extend `AuditLogger` untuk flag sensitive.
- Integrasi pada aksi sensitif utama yang sudah ada:
  - `salary_components.destroy`
  - `vouchers.cancel`
  - `vouchers.destroy`
  - `payroll-runs` execute run (`run=1`)
- Halaman Inertia `SensitiveActions/Index` read-only.
- Feature tests validasi filter dan sensitive flag creation.

## Out of Scope
- Workflow approval blocking runtime.
- Real-time alert/email.
- Risk scoring engine kompleks.

## Definition of Done
- [x] Migration sensitive fields pada `audit_logs` selesai.
- [x] `AuditLogger` support sensitive metadata.
- [x] Aksi sensitif utama ter-log dengan flag.
- [x] Halaman tracking sensitif tersedia dengan filter.
- [x] Feature tests pass.
- [x] `composer test` dan `npm run build` dijalankan.

## Dev Agent Record
### Completion Notes
- Menambahkan flag sensitif pada audit log: `is_sensitive`, `sensitivity_level`, `sensitivity_reason`.
- `AuditLogger` mendukung metadata sensitif tanpa memutus legacy audit fields.
- Menandai aksi sensitif: salary component delete, voucher cancel/delete, payroll run execution.
- Menambahkan halaman read-only Sensitive Actions dengan filter event/entity/level/actor/tanggal.
- Menambahkan feature test untuk sensitive action tracking dan filter.

### File List
- `_bmad-output/implementation-artifacts/22-4-sensitive-action-tracking.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`
- `database/migrations/2026_05_19_020155_add_sensitive_fields_to_audit_logs_table.php`
- `app/Models/AuditLog.php`
- `app/Services/AuditLogger.php`
- `app/Http/Controllers/SensitiveActionController.php`
- `app/Http/Controllers/SalaryComponentController.php`
- `app/Http/Controllers/VoucherController.php`
- `app/Http/Controllers/PayrollRunController.php`
- `database/factories/AuditLogFactory.php`
- `resources/js/Pages/SensitiveActions/Index.jsx`
- `routes/web.php`
- `tests/Feature/SensitiveActionTest.php`

### Validation
- `php artisan test tests/Feature/SensitiveActionTest.php --compact` passed: 5 tests, 30 assertions.
- `composer test` PHPUnit passed: 409 tests, 1584 assertions; Composer wrapper masih return code 1 seperti isu lama.
- `npm run build` passed.
- Catatan: `php artisan migrate --force` pada DB lokal lama gagal di migrasi Sales Return terdahulu karena ordering tabel existing; fresh test database migration lewat PHPUnit pass.

### Change Log
- 2026-05-19: Story 22.4 dibuat dan diimplementasikan.

