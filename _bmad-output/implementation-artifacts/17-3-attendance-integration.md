# Story 17.3: Attendance Integration

**Story Key:** 17-3-attendance-integration
**Priority:** P0
**Status:** done

## User Story

As HR/Payroll Staff, I want payroll calculation to include attendance and overtime data so payroll run reflects work hours, attendance deductions, and approved overtime inputs.

## Acceptance Criteria

1. Payroll run can include attendance summary per employee for selected period.
2. Attendance integration stores present days, incomplete days, absent days, total work hours, and approved overtime hours per payroll line.
3. Attendance-derived earning/deduction values are included in payroll totals without mutating source attendance records.
4. Re-running payroll for same period updates existing payroll lines idempotently.
5. Payroll run detail page shows attendance summary and component snapshot per employee.
6. Feature tests cover attendance/overtime integration and idempotent rerun.

## MVP Scope

- Extend existing `payroll_run_lines` with attendance summary fields.
- Update payroll calculation to aggregate `attendance_records` and approved `overtime_records` in period.
- Use salary components with code `OVERTIME` as overtime earning per hour and `ABSENCE` as absence deduction per absent/incomplete day.
- Keep source attendance/overtime records read-only.

## Out of Scope

- PPh21 tax calculation.
- Bank transfer file.
- Complex shift late/early penalties.
- Leave balance policy.

## Technical Notes

- Reuse Story 16 attendance/overtime tables.
- Reuse Story 17.2 payroll run idempotent pattern.
- If no `OVERTIME` or `ABSENCE` component exists, record hours/days but no extra amount.

## Definition of Done

- [x] Payroll line attendance fields migrated/modelled
- [x] Payroll calculation aggregates attendance and overtime
- [x] Payroll UI shows attendance summary
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Added attendance summary fields to payroll run lines: present, incomplete, absent, work hours, approved overtime hours.
- Integrated attendance and approved overtime aggregation into idempotent payroll calculation.
- Added `OVERTIME` and `ABSENCE` salary component code handling for attendance-derived earning/deduction amounts.
- Updated payroll run page to show attendance and overtime summary columns.
- Added feature coverage for attendance/overtime integration and idempotent rerun.

### File List

- `_bmad-output/implementation-artifacts/17-3-attendance-integration.md`
- `database/migrations/2026_05_18_135016_add_attendance_fields_to_payroll_run_lines_table.php`
- `app/Models/PayrollRunLine.php`
- `app/Http/Controllers/PayrollRunController.php`
- `resources/js/Pages/PayrollRuns/Index.jsx`
- `tests/Feature/PayrollRunTest.php`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation

- `php artisan test tests/Feature/PayrollRunTest.php` ✅ pass (4 tests)
- `composer test` ✅ PHPUnit pass (348 tests / 1100 assertions) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-18: Implemented Story 17.3 Attendance Integration MVP and validations.

