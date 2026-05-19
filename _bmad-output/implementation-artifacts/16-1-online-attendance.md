# Story 16.1: Online Attendance

Story Key: `16-1-online-attendance`
Priority: P0

## Status
`review`

## User Story
Sebagai Karyawan dan HR Staff, saya ingin mencatat absensi online check-in/check-out agar kehadiran harian tercatat rapi sebagai dasar operasional HR.

## Acceptance Criteria
1. Sistem dapat mencatat check-in untuk employee aktif dengan tanggal/waktu dan catatan opsional.
2. Sistem dapat mencatat check-out pada record attendance yang sama di hari yang sama.
3. Employee tidak bisa check-in dua kali pada hari yang sama jika belum checkout.
4. Status attendance minimal: `present`, `incomplete`, `absent` (MVP fokus present/incomplete dari transaksi online).
5. List attendance menyediakan filter employee search, date range, dan status.
6. Detail attendance menampilkan jam check-in, check-out, durasi kerja sederhana (jam), dan metadata user pencatat.
7. Fitur tidak mengubah payroll langsung; hanya menyimpan data attendance.

## MVP Scope
- Tabel `attendance_records`.
- Endpoint/page untuk check-in, check-out, list, detail.
- Perhitungan durasi sederhana dari check-in dan check-out.
- Integrasi relasi ke `employees`.

## Out of Scope
- Integrasi ZKTeco (Story 16.2).
- Shift rules, overtime, lateness policy.
- Geolocation, selfie/photo validation.

## Definition of Done
- [x] Migration + model attendance records selesai.
- [x] Controller + routes check-in/check-out/list/show selesai.
- [x] Halaman Inertia `AttendanceRecords/Index`, `Create` (check-in), `Show` selesai.
- [x] Feature tests attendance workflow hijau.
- [x] `composer test` dijalankan.
- [x] `npm run build` dijalankan.

## Dev Agent Record
### Completion Notes
- Implemented Online Attendance MVP with `attendance_records` table, employee relation, online check-in/check-out flow, and work hour calculation.
- Added duplicate open check-in guard per employee per attendance date.
- Added list/detail Inertia pages with status/date/employee filters.

### File List
- `_bmad-output/implementation-artifacts/16-1-online-attendance.md`
- `database/migrations/2026_05_18_113959_create_attendance_records_table.php`
- `app/Models/AttendanceRecord.php`
- `database/factories/AttendanceRecordFactory.php`
- `app/Http/Controllers/AttendanceRecordController.php`
- `resources/js/Pages/AttendanceRecords/Index.jsx`
- `resources/js/Pages/AttendanceRecords/Create.jsx`
- `resources/js/Pages/AttendanceRecords/Show.jsx`
- `tests/Feature/AttendanceRecordTest.php`
- `routes/web.php`

### Validation
- `php artisan test tests/Feature/AttendanceRecordTest.php` passed 4 tests / 9 assertions.
- `composer test` PHPUnit passed 324 tests / 1005 assertions; composer wrapper still returned known code 1 after pass.
- `npm run build` passed with existing Vite `esbuild` deprecation warning.

### Change Log
- `2026-05-18`: Implemented Story 16.1 Online Attendance MVP.
