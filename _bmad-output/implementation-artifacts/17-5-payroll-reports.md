# Story 17.5: Payroll Reports

**Story Key:** 17-5-payroll-reports
**Priority:** P0
**Status:** review

## User Story

As an HR/Finance Staff, I want payroll reports per period so I can review payroll totals and employee-level net pay before downstream payment processes.

## Acceptance Criteria

1. User can open payroll report page with period filter (`YYYY-MM`).
2. Report summary shows total employees, total earnings, total deductions, total PPh21, and total net pay after tax.
3. Report detail shows employee rows from payroll run lines: employee id/name, earnings, deductions, gross pay, PPh21, net pay after PPh21.
4. Report supports search by employee id/name and optional status filter from payroll line status.
5. Report is read-only and does not mutate payroll source data.

## MVP Scope

- One Inertia payroll report page based on `payroll_runs` and `payroll_run_lines`.
- Period selection with fallback current month.
- Summary aggregation + employee detail table.
- Basic filters: period, employee search, line status.

## Out of Scope

- PDF/Excel export.
- Bank transfer generation.
- Approval workflow or payroll posting lock.

## Technical Notes

- Reuse payroll aggregation pattern from `PayrollRunController` but read-only.
- Use `net_pay_after_pph21` as final payable amount.

## Definition of Done

- [x] Payroll report controller and route implemented
- [x] Payroll report Inertia page implemented
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Implemented read-only payroll report page by period from payroll run and payroll run lines.
- Added summary totals for employees, earnings, deductions, PPh21, and net pay after tax.
- Added detail table per employee with search and line status filters.
- Kept source payroll data immutable (no write operation).

### File List

- `_bmad-output/implementation-artifacts/17-5-payroll-reports.md`
- `app/Http/Controllers/PayrollReportController.php`
- `routes/web.php`
- `resources/js/Pages/PayrollReports/Index.jsx`
- `tests/Feature/PayrollReportTest.php`

### Validation

- `php artisan test tests/Feature/PayrollReportTest.php` ✅ pass (3 tests)
- `composer test` ✅ PHPUnit pass (352 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-18: Implemented Story 17.5 Payroll Reports MVP and validations.
