# Story 16.4: Overtime Tracking

**Story Key:** 16-4-overtime-tracking
**Priority:** P0
**Status:** done

## User Story

As an HR/Admin Staff, I want to record overtime based on attendance and shift context so extra working hours can be tracked transparently for payroll preparation.

## Acceptance Criteria

1. Admin can create overtime record for active employee with attendance date reference, overtime start/end, reason, and status.
2. System calculates overtime hours automatically from start/end (decimal hours) and rejects invalid range.
3. Overtime status supports `pending|approved|rejected|cancelled` with approve/reject/cancel actions and audit fields.
4. One attendance record can have multiple overtime records, but overlapping overtime range for same employee/date is rejected.
5. Overtime list supports filter by employee/status/date range and shows calculated hours.
6. Story does not mutate payroll tables; only overtime tracking data is created.

## MVP Scope

- Overtime CRUD subset: list/create/show + workflow actions (approve/reject/cancel).
- Uses existing `employees`, `attendance_records`, and optional shift context for validation display.
- Feature tests for hours calculation, overlap guard, and workflow transitions.

## Out of Scope

- Payroll amount calculation from overtime hours.
- Automatic overtime generation from late check-out.
- Holiday/weekend overtime rate multipliers.

## Technical Notes

- Reuse approval workflow style from LeaveRequest and Attendance stories.
- Keep calculation simple: `(end - start)` in hours, rounded 2 decimals.

## Definition of Done

- [x] Overtime migration/model created
- [x] Overtime controller/routes/pages implemented
- [x] Hours calculation and overlap guard implemented
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Implemented Overtime Tracking MVP with pending/approved/rejected/cancelled workflow.
- Added automatic overtime hour calculation from start/end datetime.
- Added overlap guard for same employee/date across pending/approved overtime ranges.
- Added Inertia list/create/show pages and workflow buttons.
- Added feature tests for creation, overlap rejection, workflow actions, and filters.

### File List

- `_bmad-output/implementation-artifacts/16-4-overtime-tracking.md`
- `database/migrations/2026_05_18_122625_create_overtime_records_table.php`
- `app/Models/OvertimeRecord.php`
- `database/factories/OvertimeRecordFactory.php`
- `app/Http/Controllers/OvertimeRecordController.php`
- `routes/web.php`
- `resources/js/Pages/OvertimeRecords/Index.jsx`
- `resources/js/Pages/OvertimeRecords/Create.jsx`
- `resources/js/Pages/OvertimeRecords/Show.jsx`
- `tests/Feature/OvertimeRecordTest.php`

### Validation

- `php artisan test tests/Feature/WorkShiftTest.php tests/Feature/OvertimeRecordTest.php` ✅ pass (9 tests)
- `composer test` ✅ PHPUnit pass (337 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-18: Implemented Story 16.4 Overtime Tracking MVP and validations.

