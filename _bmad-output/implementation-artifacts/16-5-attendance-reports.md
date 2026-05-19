# Story 16.5: Attendance Reports

**Story Key:** 16-5-attendance-reports
**Priority:** P0
**Status:** review

## User Story

As an HR/Admin Staff, I want attendance summary and detail reports by employee and date range so I can evaluate presence, incomplete attendance, and overtime readiness before payroll processing.

## Acceptance Criteria

1. User can open attendance report page with filter date range, employee search, and status.
2. Report shows attendance detail rows from `attendance_records` with employee, date, check-in, check-out, work hours, status.
3. Report shows summary totals: total records, present count, incomplete count, absent count, total work hours.
4. Report includes overtime totals by joining overtime records (`approved` only) for selected range.
5. Report is read-only and does not mutate attendance/overtime source data.
6. Existing attendance and overtime workflows remain unchanged.

## MVP Scope

- One Inertia report page with filters + summary + detail table.
- Data source: `attendance_records`, `employees`, `overtime_records`.
- Overtime aggregation only from approved overtime in selected date range.
- Feature tests for summary and filtering.

## Out of Scope

- PDF/Excel export.
- Shift lateness scoring.
- Payroll deduction/overtime amount calculation.

## Technical Notes

- Reuse report pattern from AssetReport and CashFlowReport stories.
- Keep date filtering consistent with previous modules (`date_from`, `date_to`).

## Definition of Done

- [x] Attendance report controller/route/page implemented
- [x] Summary and overtime aggregation implemented
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Implemented read-only Attendance Reports page with date, employee search, and status filters.
- Added attendance detail rows from `attendance_records` with employee, check-in/out, work hours, and status.
- Added summary totals for record count, present/incomplete/absent counts, total work hours, and approved overtime hours.
- Aggregates approved overtime from `overtime_records` by employee/date without mutating source data.

### File List

- `_bmad-output/implementation-artifacts/16-5-attendance-reports.md`
- `app/Http/Controllers/AttendanceReportController.php`
- `routes/web.php`
- `resources/js/Pages/AttendanceReports/Index.jsx`
- `tests/Feature/AttendanceReportTest.php`

### Validation

- `php artisan test tests/Feature/AttendanceReportTest.php` ✅ pass (3 tests)
- `composer test` ✅ PHPUnit pass (340 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-18: Implemented Story 16.5 Attendance Reports MVP and validations.
