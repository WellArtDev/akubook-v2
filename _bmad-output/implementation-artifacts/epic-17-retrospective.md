# Epic 17 Retrospective: Payroll Processing & Integration

**Status:** done  
**Date:** 2026-05-18

## Epic Summary

| Story | Title | Status | Outcome |
| --- | --- | --- | --- |
| 17.1 | Salary Components | review | Salary component master with earning/deduction, fixed/percentage, taxable/account metadata. |
| 17.2 | Payroll Calculation | review | Period payroll run with idempotent employee lines and component snapshots. |
| 17.3 | Attendance Integration | review | Attendance and approved overtime totals integrated into payroll lines. |
| 17.4 | Tax Calculation PPh21 | review | Monthly progressive PPh21 MVP added to payroll line totals and snapshots. |
| 17.5 | Payroll Reports | review | Read-only payroll summary and employee detail report by period. |
| 17.6 | Bank Transfer File | review | Payroll bank transfer batch with row status, CSV output, and download. |

## Outcomes

- Payroll foundation complete from component setup to payroll run, attendance/overtime integration, PPh21 calculation, reporting, and bank transfer file.
- Payroll run is idempotent by period and employee.
- Component snapshot, attendance fields, PPh21 fields, and transfer line snapshots preserve auditability.
- Bank transfer file handles incomplete employee bank data without mutating payroll source rows.

## Validation Evidence

- Targeted feature tests for all Epic 17 stories passed.
- Latest `composer test`: PHPUnit passed 356 tests / 1152 assertions; composer wrapper still returns code 1 after pass.
- Latest `npm run build`: passed with existing Vite warning (`esbuild` option deprecated by `vite:react-babel`, use `oxc`).

## What Went Well

- Existing HR/attendance modules gave strong base for payroll integration.
- Idempotent payroll run and bank transfer generation reduced duplicate risk.
- Feature tests caught calculation and CSV response edge cases quickly.
- Payroll reports and transfer pages reused existing Inertia/report patterns cleanly.

## Challenges / Gaps

- PPh21 logic is monthly MVP, not annualized Indonesian full compliance.
- Salary components support fixed/percentage only; no formula engine yet.
- Bank transfer CSV is generic, not bank-specific format.
- Payroll posting to accounting journals is not implemented.
- Composer wrapper and Vite warning remain unresolved cross-cutting issues.

## Action Items

1. Add annualized PPh21/PTKP rules before production payroll compliance.
2. Add payroll posting journal workflow for salary expense, tax payable, and cash/bank clearing.
3. Add bank-specific transfer formats and approval/download audit.
4. Add formula-based salary components if payroll rules need allowances tied to attendance/position.
5. Fix Composer test wrapper exit code.
6. Migrate Vite config/plugin warning from `esbuild` to `oxc`.

## Epic 18 Prep

- Dashboard work can reuse payroll report summaries, attendance report summaries, voucher/cash reports, and stock/asset reports.
- Define role-based dashboard widgets first: finance, inventory, HR/payroll, sales/purchase.
- Prefer read-only aggregate endpoints and avoid dashboard mutation.
