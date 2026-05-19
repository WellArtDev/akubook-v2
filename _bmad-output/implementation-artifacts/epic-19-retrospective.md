# Epic 19 Retrospective: Comprehensive Reporting System

**Status:** done
**Date:** 2026-05-18

## Epic Summary

Epic 19 delivered core reporting foundation across finance, operations, HR, custom reporting, and CSV export.

| Story | Status | Outcome |
| --- | --- | --- |
| 19.1 Financial Reports | review | Trial Balance, Profit & Loss, Balance Sheet from posted journals |
| 19.2 Operational Reports | review | Sales, purchasing, logistics, and stock movement summaries |
| 19.3 HR Reports | review | Employee, attendance, leave, overtime, and document compliance summaries |
| 19.4 Custom Report Builder | review | Whitelisted custom report definitions with read-only preview |
| 19.5 Export Functionality | review | CSV export for custom, financial, and payroll reports |

## Validation Evidence

- Targeted feature tests pass for all Epic 19 stories.
- Latest `composer test`: PHPUnit pass 380 tests / 1386 assertions; composer wrapper still exits code 1 after pass.
- Latest `npm run build`: pass; existing Vite warning remains (`esbuild` option deprecated by `vite:react-babel`, use `oxc`).

## What Went Well

- Report pages reuse consistent Inertia filter/table patterns.
- Financial report uses posted journal source of truth, not duplicated totals.
- Operational and HR reports stay read-only and avoid source mutation.
- Custom report builder uses strict whitelisted sources/columns.
- CSV export landed with guarded query paths and regression tests.

## Challenges

- Export MVP covers key paths but not every report page yet.
- Custom builder remains single-source only; no joins/formula columns.
- Report calculations are controller-level and may need service extraction as scope grows.
- Composer wrapper false-failure and Vite warning still unresolved.

## Technical Debt

1. Extract report aggregation/export logic into services.
2. Add export links for operational and HR report pages.
3. Add XLSX/PDF export when reporting shape stabilizes.
4. Add saved filters and scheduled report delivery.
5. Fix `composer test` wrapper exit code.
6. Migrate Vite React plugin config from `esbuild` to `oxc`.

## Lessons Learned

- Whitelist-first report builder prevents unsafe ad-hoc SQL.
- Read-only report pages are faster to validate and safer to extend.
- Tests should assert both summary totals and filtered detail rows.
- CSV export needs explicit header/row contracts per report type.

## Epic 20 Prep

Epic 20 PWA/offline work should reuse report/dashboard patterns for read-only cached views and avoid offline mutation until sync/conflict rules exist. Highest prep item: identify which surfaces need offline availability first, likely attendance clock-in/out and dashboard/report read-only snapshots.
