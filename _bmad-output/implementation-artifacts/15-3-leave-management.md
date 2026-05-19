# Story 15.3: Leave Management

Story Key: `15-3-leave-management`
Priority: P0

**Status:** done

## User Story
Sebagai HR Staff dan Atasan, saya ingin mengelola pengajuan cuti karyawan agar proses approval, monitoring saldo, dan histori cuti tercatat rapi.

## Acceptance Criteria
1. Sistem dapat membuat pengajuan cuti untuk karyawan aktif dengan field minimum: employee, leave_type, start_date, end_date, reason.
2. Sistem menghitung otomatis total hari cuti dari rentang tanggal (MVP: hitung kalender sederhana, tanpa kalender libur nasional).
3. Status pengajuan minimal mencakup `pending`, `approved`, `rejected`, `cancelled`.
4. Pengajuan `pending` bisa di-approve atau reject oleh user login; status perubahan tersimpan dengan timestamp dan approver/rejector.
5. Pengajuan `pending` bisa dibatalkan oleh pengguna operasi (cancel).
6. List pengajuan menyediakan filter status, employee search, dan date range.
7. Detail pengajuan menampilkan metadata approval/rejection/cancellation.
8. Fitur bersifat read/write pada modul leave saja, tidak mengubah payroll.

## MVP Scope
- Tabel `leave_requests`.
- CRUD terbatas: index/create/store/show + action approve/reject/cancel.
- Perhitungan total hari cuti sederhana dari selisih tanggal + 1.
- Integrasi dengan `employees`.

## Out of Scope
- Saldo cuti tahunan otomatis per kebijakan perusahaan.
- Integrasi payroll/attendance.
- Kalender hari libur nasional/cuti bersama.
- Multi-level approval.

## Definition of Done
- [x] Migration + model leave request selesai.
- [x] Controller + routes untuk create/list/detail + approve/reject/cancel selesai.
- [x] Halaman Inertia `LeaveRequests/Index`, `Create`, `Show` selesai.
- [x] Feature tests leave workflow (create, approve, reject, cancel, filter) hijau.
- [x] `composer test` dijalankan.
- [x] `npm run build` dijalankan.

## Dev Agent Record
### Completion Notes
- Implemented Leave Management MVP with `leave_requests` table, Employee relation, pending/approved/rejected/cancelled workflow, and date-range day calculation.
- Added Inertia pages for list, create, and detail workflow actions.
- Added feature tests for create, approve, reject, cancel, and index filtering.

### File List
- `_bmad-output/implementation-artifacts/15-3-leave-management.md`
- `database/migrations/2026_05_18_090723_create_leave_requests_table.php`
- `app/Models/LeaveRequest.php`
- `database/factories/LeaveRequestFactory.php`
- `app/Http/Controllers/LeaveRequestController.php`
- `resources/js/Pages/LeaveRequests/Index.jsx`
- `resources/js/Pages/LeaveRequests/Create.jsx`
- `resources/js/Pages/LeaveRequests/Show.jsx`
- `tests/Feature/LeaveRequestTest.php`
- `routes/web.php`

### Validation
- `php artisan test tests/Feature/LeaveRequestTest.php` passed 5 tests / 16 assertions.
- `composer test` PHPUnit passed 316 tests / 987 assertions; composer wrapper still returned known code 1 after pass.
- `npm run build` passed with existing Vite `esbuild` deprecation warning.

### Change Log
- `2026-05-18`: Implemented Story 15.3 Leave Management MVP.

