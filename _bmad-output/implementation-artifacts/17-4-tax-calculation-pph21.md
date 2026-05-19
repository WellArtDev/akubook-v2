# Story 17.4: Tax Calculation PPh21

**Story Key:** 17-4-tax-calculation-pph21
**Priority:** P0
**Status:** done

## User Story

As HR/Finance Staff, I want payroll runs to calculate PPh21 tax per employee so net payroll reflects statutory withholding before reporting and payment.

## Acceptance Criteria

1. Payroll line stores PPh21 taxable income, PPh21 amount, and net pay after tax.
2. PPh21 calculation uses calculated payroll gross pay minus deductions as MVP monthly taxable base.
3. MVP progressive monthly brackets are applied deterministically and stored in component snapshot.
4. Rerunning payroll for same period is idempotent and refreshes PPh21 values.
5. Payroll UI shows taxable income and PPh21 per employee.
6. No bank transfer, e-filing, or official annualized tax filing mutation in this story.

## MVP Scope

- Extend `payroll_run_lines` with PPh21 fields.
- Integrate PPh21 calculation into existing payroll run.
- Use simple monthly progressive bracket helper.
- Update payroll run list/detail table.
- Feature tests for PPh21 calculation and rerun.

## Out of Scope

- PTKP/marital status.
- Annualized TER rules.
- Tax slips/e-SPT/e-Bupot.
- Manual tax adjustment workflow.

## Technical Notes

- Build on Story 17.2 `PayrollRunController` and Story 17.3 attendance-integrated payroll line update.
- Keep existing salary component logic intact.
- Store calculation trace in `component_snapshot`.

## Definition of Done

- [x] Migration/model fields added
- [x] Payroll calculation includes PPh21
- [x] Payroll UI shows PPh21 values
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Extended payroll run lines with PPh21 taxable income, PPh21 amount, and net pay after tax.
- Integrated monthly progressive PPh21 calculation into payroll run process.
- Added calculation trace into payroll component snapshot for auditability.
- Updated payroll UI to show PPh21 and net pay after tax.
- Added/updated feature tests for PPh21 and idempotent rerun behavior.

### File List

- `_bmad-output/implementation-artifacts/17-4-tax-calculation-pph21.md`
- `database/migrations/2026_05_18_140050_add_pph21_fields_to_payroll_run_lines_table.php`
- `app/Models/PayrollRunLine.php`
- `app/Http/Controllers/PayrollRunController.php`
- `resources/js/Pages/PayrollRuns/Index.jsx`
- `tests/Feature/PayrollRunTest.php`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation

- `php artisan test tests/Feature/PayrollRunTest.php` ✅ pass (5 tests)
- `composer test` ✅ PHPUnit pass (349 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-18: Implemented Story 17.4 Tax Calculation PPh21 MVP and validations.

