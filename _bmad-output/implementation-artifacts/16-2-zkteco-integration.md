# Story 16.2: ZKTeco Integration

**Story Key:** 16-2-zkteco-integration
**Priority:** P0
**Status:** done

## User Story

As an HR/Admin Staff, I want to import attendance logs from ZKTeco devices so that machine attendance can be reconciled into attendance records without manual re-entry.

## Acceptance Criteria

1. Admin can register ZKTeco device metadata with code, name, IP address, port, and active status.
2. Admin can create/import ZKTeco attendance logs with device, employee identifier, punch timestamp, and punch type.
3. Import maps log employee identifier to `employees.employee_id`.
4. Import creates or updates `attendance_records` for the log date:
   - first `check_in` fills `check_in_at`
   - `check_out` fills `check_out_at` and calculates `work_hours`
   - status becomes `incomplete` until checkout, then `present`
5. Duplicate raw logs are ignored using a deterministic unique key.
6. Admin can view import log list with filters by device, employee identifier, mapped status, and date range.
7. Source logs remain auditable and do not mutate employee master data.

## MVP Scope

- Device registry CRUD-lite: list/create/show/deactivate.
- Manual import form for individual ZKTeco logs.
- Raw log table with mapping status.
- Attendance record synchronization from imported log.
- Feature tests for device creation, log import, attendance sync, duplicate guard.

## Out of Scope

- Direct hardware SDK/network polling.
- Background sync scheduler.
- Biometric templates.
- Shift/overtime rules.

## Technical Notes

- Use `employees.employee_id` as ZKTeco user identifier.
- Use `attendance_records` from Story 16.1 as sync target.
- Use `punch_type` values `check_in` and `check_out` for MVP.
- Preserve raw log audit even when employee mapping fails.

## Definition of Done

- [x] ZKTeco device/log migrations and models created
- [x] Import controller/routes/pages implemented
- [x] Attendance sync from raw logs implemented
- [x] Duplicate log guard implemented
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

ZKTeco integration MVP selesai: device registry, import raw log, mapping employee_id, sinkron check-in/check-out ke attendance record, duplicate guard by source_key, dan audit log list/detail.

### File List

- `_bmad-output/implementation-artifacts/16-2-zkteco-integration.md`
- `database/migrations/2026_05_18_120140_create_zkteco_devices_table.php`
- `database/migrations/2026_05_18_120140_create_zkteco_attendance_logs_table.php`
- `app/Models/ZktecoDevice.php`
- `app/Models/ZktecoAttendanceLog.php`
- `app/Http/Controllers/ZktecoAttendanceController.php`
- `database/factories/ZktecoDeviceFactory.php`
- `database/factories/ZktecoAttendanceLogFactory.php`
- `resources/js/Pages/ZktecoDevices/Index.jsx`
- `resources/js/Pages/ZktecoDevices/Create.jsx`
- `resources/js/Pages/ZktecoDevices/Show.jsx`
- `resources/js/Pages/ZktecoAttendance/Index.jsx`
- `resources/js/Pages/ZktecoAttendance/Create.jsx`
- `resources/js/Pages/ZktecoAttendance/Show.jsx`
- `tests/Feature/ZktecoAttendanceTest.php`
- `routes/web.php`

### Validation

- `php artisan test tests/Feature/ZktecoAttendanceTest.php` passed (4 tests)
- `composer test` PHPUnit passed (328 tests) — wrapper masih exit code 1 (known issue)
- `npm run build` passed — warning Vite `esbuild` deprecated, migrate ke `oxc`

### Change Log

- 2026-05-18: Implemented Story 16.2 ZKTeco Integration MVP.

