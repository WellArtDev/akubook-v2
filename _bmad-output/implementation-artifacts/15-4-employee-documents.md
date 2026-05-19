# Story 15.4: Employee Documents

Story Key: `15-4-employee-documents`
Priority: P0

## Status
`review`

## User Story
Sebagai HR Staff, saya ingin mencatat dokumen penting karyawan agar data kontrak, identitas, dan legal dokumen terkelola rapi dan mudah dipantau masa berlaku.

## Acceptance Criteria
1. Sistem dapat menambah dokumen untuk karyawan aktif dengan field minimal: employee, document_type, document_number, issue_date, expiry_date (opsional), notes.
2. Status dokumen minimal `active` dan `inactive`.
3. List dokumen menyediakan filter employee search, document_type, status, dan expiry range.
4. Detail dokumen menampilkan metadata lengkap serta status kedaluwarsa sederhana (`expired` jika expiry_date < hari ini).
5. Dokumen bisa diubah (metadata) dan dinonaktifkan tanpa menghapus histori.
6. Fitur tidak mengubah payroll, attendance, atau proses leave.

## MVP Scope
- Tabel `employee_documents`.
- CRUD terbatas: index/create/store/show/edit/update + deactivate (soft business deactivate via status).
- Integrasi relasi ke `employees`.
- Tidak menyimpan file attachment fisik, hanya metadata dokumen.

## Out of Scope
- Upload/preview file binary (PDF/image).
- Versioning dokumen multi-revisi.
- Reminder otomatis notifikasi kedaluwarsa.

## Definition of Done
- [x] Migration + model employee documents selesai.
- [x] Controller + routes CRUD metadata dokumen selesai.
- [x] Halaman Inertia `EmployeeDocuments/Index`, `Create`, `Edit`, `Show` selesai.
- [x] Feature tests employee documents workflow hijau.
- [x] `composer test` dijalankan.
- [x] `npm run build` dijalankan.

## Dev Agent Record
### Completion Notes
- Implemented Employee Documents MVP metadata module with active/inactive status lifecycle.
- Added employee-linked document CRUD with type/number/date/expiry filters and expired indicator on detail.
- Added feature tests for create, unique validation, filter/index access, and deactivation workflow.

### File List
- `_bmad-output/implementation-artifacts/15-4-employee-documents.md`
- `database/migrations/2026_05_18_111624_create_employee_documents_table.php`
- `app/Models/EmployeeDocument.php`
- `database/factories/EmployeeDocumentFactory.php`
- `app/Http/Controllers/EmployeeDocumentController.php`
- `resources/js/Pages/EmployeeDocuments/Index.jsx`
- `resources/js/Pages/EmployeeDocuments/Create.jsx`
- `resources/js/Pages/EmployeeDocuments/Edit.jsx`
- `resources/js/Pages/EmployeeDocuments/Show.jsx`
- `tests/Feature/EmployeeDocumentTest.php`
- `routes/web.php`

### Validation
- `php artisan test tests/Feature/EmployeeDocumentTest.php` passed 4 tests / 9 assertions.
- `composer test` PHPUnit passed 320 tests / 996 assertions; composer wrapper still returned known code 1 after pass.
- `npm run build` passed with existing Vite `esbuild` deprecation warning.

### Change Log
- `2026-05-18`: Implemented Story 15.4 Employee Documents MVP.
