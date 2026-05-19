# Story 19.1: Financial Reports

**Story Key:** 19-1-financial-reports
**Priority:** P0
**Status:** review

## User Story

As a finance user, I want core financial reports by period so I can monitor company financial position and performance.

## Acceptance Criteria

1. User can open financial reports page with period filter (`date_from`, `date_to`).
2. Trial Balance section shows account code/name with debit, credit, and ending balance totals.
3. Profit & Loss section summarizes revenue, expense, and net profit/loss.
4. Balance Sheet section summarizes assets, liabilities, equity using account balances.
5. Data source is posted journal entries and chart of accounts; report is read-only.
6. Report shows generated timestamp and does not mutate accounting data.

## MVP Scope

- Single page with three sections: Trial Balance, Profit & Loss, Balance Sheet.
- Account balances computed from journal lines in selected period.
- Uses account `type`/`category` metadata for grouping.
- No export, no comparative period, no consolidation.

## Out of Scope

- Multi-period comparison.
- Cash flow statement.
- Segment/branch consolidated statements.

## Technical Notes

- Reuse existing `accounts`, `journal_entries`, `journal_entry_lines`.
- Use account normal balance logic (`debit` for assets/expenses, `credit` for liabilities/equity/revenue).
- Keep computation in controller/service read-only.

## Definition of Done

- [x] Financial report controller and route implemented
- [x] Financial report page implemented
- [x] Trial Balance, P&L, Balance Sheet summaries computed
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Implemented financial report read-only endpoint and page for Trial Balance, Profit & Loss, and Balance Sheet.
- Added period filtering and generated timestamp on report output.
- Calculated account balances from posted journal entries only, then grouped by account type for summary sections.
- Added feature tests for report rendering, financial summary calculation, and period filtering.

### File List

- `_bmad-output/implementation-artifacts/19-1-financial-reports.md`
- `app/Http/Controllers/FinancialReportController.php`
- `resources/js/Pages/FinancialReports/Index.jsx`
- `routes/web.php`
- `tests/Feature/FinancialReportTest.php`

### Validation

- `php artisan test tests/Feature/FinancialReportTest.php` ✅ pass (3 tests)
- `composer test` ✅ PHPUnit pass (368 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-18: Implemented Story 19.1 Financial Reports MVP and validations.
