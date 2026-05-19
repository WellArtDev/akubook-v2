# Story 19.3: HR Reports

**Story Key:** 19-3-hr-reports
**Priority:** P0
**Status:** done

## User Story

As an HR manager, I want HR reports by period so I can monitor workforce status, attendance quality, leave usage, overtime load, and document compliance.

## Acceptance Criteria

1. User can open HR reports page with period filter (`date_from`, `date_to`) and optional employee search.
2. Employee summary section shows total active employees, inactive/resigned, and employees with active assignment.
3. Attendance summary section shows present/incomplete/absent counts and total work hours in selected period.
4. Leave summary section shows leave requests grouped by status and total leave days.
5. Overtime summary section shows overtime counts by status and total approved overtime hours.
6. Document compliance section shows active documents, expired documents, and documents expiring soon (next 30 days).
7. Report is read-only, generated timestamp visible, and no HR transactional data is mutated.

## MVP Scope

- One read-only Inertia page with section cards + detail tables.
- Data sources: `employees`, `employee_shift_assignments`, `attendance_records`, `leave_requests`, `overtime_records`, `employee_documents`.
- Filters: period (`date_from`, `date_to`) and employee search (name/ID).
- No export and no drill-down navigation requirement in this story.

## Out of Scope

- Payroll impact analysis.
- Leave balance accrual policy.
- Attendance anomaly rule engine.

## Technical Notes

- Reuse reporting pattern from Story 19.1 and 19.2.
- Use `whereDate` filters for date-range consistency across SQLite/MySQL.
- Keep query logic read-only and aggregation-oriented.

## Definition of Done

- [x] HR report controller and route implemented
- [x] HR report page implemented
- [x] Employee, attendance, leave, overtime, and document compliance summaries computed
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Implemented read-only HR report controller with date range and employee search filter.
- Added summary sections for employee status, attendance quality, leave, overtime, and document compliance.
- Added employee snapshot detail table for quick HR period review.
- Added feature tests for summary accuracy and period filtering behavior.

### File List

- `_bmad-output/implementation-artifacts/19-3-hr-reports.md`
- `app/Http/Controllers/HrReportController.php`
- `resources/js/Pages/HrReports/Index.jsx`
- `routes/web.php`
- `tests/Feature/HrReportTest.php`

### Validation

- `php artisan test tests/Feature/HrReportTest.php` ✅ pass (3 tests)
- `composer test` ✅ PHPUnit pass (374 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-18: Implemented Story 19.3 HR Reports MVP and validations.

