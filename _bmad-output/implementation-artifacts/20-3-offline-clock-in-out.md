# Story 20.3: Offline Clock-In/Out

**Story Key:** 20-3-offline-clock-in-out
**Priority:** P0
**Status:** review

## User Story

As an employee using unstable internet, I want to clock in/out while offline so attendance is not lost and can sync when connection returns.

## Acceptance Criteria

1. App provides offline clock-in/out queue for authenticated user.
2. When offline, clock events are stored locally with timestamp, type (`check_in|check_out`), and employee identifier.
3. App can sync queued events to server when online using a sync endpoint.
4. Server processes queued events idempotently and maps to `attendance_records` check-in/out logic.
5. Successful synced events are marked processed and not re-applied.
6. Feature is read-only against other domains (no payroll/leave mutation).

## MVP Scope

- Local queue helper in frontend (`localStorage`) with pending sync count.
- Backend sync endpoint for batch clock events.
- Persistence table for sync logs to guarantee idempotency.
- UI controls on attendance page for manual sync + status.
- Feature tests for sync/idempotent behavior.

## Out of Scope

- Background Sync API integration.
- Conflict resolution UI beyond basic error message.
- Geolocation/photo validation.

## Technical Notes

- Reuse attendance update rules from Story 16.1 and ZKTeco mapping patterns from Story 16.2.
- Idempotency key should include employee + datetime + type + source.

## Definition of Done

- [x] Offline attendance sync migration/model/controller implemented
- [x] Frontend queue + sync UI implemented
- [x] Idempotent sync behavior implemented and tested
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Implemented offline attendance sync endpoint with idempotent sync key and attendance upsert logic.
- Added `offline_attendance_syncs` tracking table for synced/failed/duplicate event handling.
- Added offline queue + manual sync UI on attendance check-in page using localStorage.
- Added feature tests for sync success, duplicate idempotency, and failed mapping.

### File List

- `_bmad-output/implementation-artifacts/20-3-offline-clock-in-out.md`
- `database/migrations/2026_05_19_001324_create_offline_attendance_syncs_table.php`
- `app/Models/OfflineAttendanceSync.php`
- `database/factories/OfflineAttendanceSyncFactory.php`
- `app/Http/Controllers/OfflineAttendanceSyncController.php`
- `resources/js/Pages/AttendanceRecords/Create.jsx`
- `routes/web.php`
- `tests/Feature/OfflineAttendanceSyncTest.php`

### Validation

- `php artisan test tests/Feature/OfflineAttendanceSyncTest.php` ✅ pass (3 tests)
- `composer test` ✅ PHPUnit pass (385 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-19: Implemented Story 20.3 Offline Clock-In/Out MVP and validations.
