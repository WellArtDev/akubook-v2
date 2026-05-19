# Story 17.2: Payroll Calculation

**Story Key:** 17-2-payroll-calculation
**Priority:** P0
**Status:** done

## User Story

As an HR/Payroll Admin, I want to run monthly payroll calculation per employee using salary components so I can generate gross pay, deductions, and net pay consistently.

## Acceptance Criteria

1. Admin can run payroll by period (`YYYY-MM`) for active employees.
2. Payroll detail stores employee, earning total, deduction total, gross pay, net pay, and status.
3. Payroll uses `salary_components` metadata:
   - `fixed` uses `default_amount`
   - `percentage` uses percentage against base gross fixed earnings.
4. Payroll run is idempotent per employee + period (rerun updates, not duplicate).
5. Payroll list supports filter by period, employee search, and status.
6. No bank transfer file/tax PPh21 mutation in this story.

## MVP Scope

- Payroll run endpoint/page for one period.
- Payroll header/detail persistence.
- Simple formula engine fixed + percentage.
- Status lifecycle minimal `draft|calculated`.
- Feature tests for calculation and idempotent rerun.

## Out of Scope

- PPh21 computation integration.
- Attendance/overtime integration weighting.
- Approval workflow and posting journals.

## Technical Notes

- Reuse idempotent period-run pattern from depreciation stories.
- Use `employees` with `employment_status=active` only.
- Salary component earning/deduction split drives totals.

## Definition of Done

- [x] Payroll run migration/model/controller created
- [x] Payroll calculation logic fixed+percentage implemented
- [x] Payroll report/list page implemented
- [x] Feature tests added and passing
- [x] `composer test` executed
- [x] `npm run build` executed

## Dev Agent Record

### Completion Notes

- Implemented monthly payroll run with idempotent period-based upsert.
- Implemented salary component formula engine (`fixed` + `percentage` over base fixed earnings).
- Stored payroll header totals and per-employee payroll lines with component snapshot.
- Added payroll run page with period runner, summary cards, detail rows, and run history.
- Added feature tests for calculation and idempotent rerun.

### File List

- `_bmad-output/implementation-artifacts/17-2-payroll-calculation.md`
- `database/migrations/2026_05_18_132915_create_payroll_runs_table.php`
- `database/migrations/2026_05_18_132916_create_payroll_run_lines_table.php`
- `app/Models/PayrollRun.php`
- `app/Models/PayrollRunLine.php`
- `database/factories/PayrollRunFactory.php`
- `database/factories/PayrollRunLineFactory.php`
- `app/Http/Controllers/PayrollRunController.php`
- `resources/js/Pages/PayrollRuns/Index.jsx`
- `routes/web.php`
- `tests/Feature/PayrollRunTest.php`

### Validation

- `php artisan test tests/Feature/PayrollRunTest.php` ✅ pass (3 tests)
- `composer test` ✅ PHPUnit pass (347 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-18: Implemented Story 17.2 Payroll Calculation MVP and validations.

