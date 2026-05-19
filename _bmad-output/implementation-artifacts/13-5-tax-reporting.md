# Story 13.5: Tax Reporting

**Story Key:** `13-5-tax-reporting`  
**Priority:** P0  
**Status:** review

## User Story
As a Finance/Tax Staff, I want a tax reporting page that summarizes output VAT, input VAT, and withholding tax by period so that monthly tax preparation is faster and consistent.

## Acceptance Criteria
1. User can view tax summary by date range.
2. Report includes Output VAT (PPN Out) from issued Faktur Pajak.
3. Report includes Input VAT (PPN In) and Withholding from Tax Calculation history.
4. Report shows breakdown totals and net VAT payable/refundable.
5. User can filter by tax type and see detail rows.
6. Source transactions are read-only (report does not mutate data).

## MVP Scope
- One Inertia report page with period filters.
- Data sources:
  - `faktur_pajaks` status issued for output VAT.
  - `tax_calculations` grouped by tax_type for input/withholding.
- Summary cards + detail tables.

## Out of Scope
- Official SPT file generation.
- Auto-posting tax payable journals.
- Multi-company consolidation.

## Technical Notes
- Keep calculations deterministic from persisted records.
- Use SQL aggregation for performance, then map to report structure.
- Reuse report filtering patterns from other report pages.

## Definition of Done
- [x] Tax reporting controller + route implemented.
- [x] Inertia page for summary and details implemented.
- [x] Feature tests for period filter and totals implemented.
- [x] `composer test` and `npm run build` executed.

## Dev Agent Record
### Completion Notes
- Implemented tax reporting summary page by period with source data from issued Faktur Pajak and Tax Calculation history.
- Added summary metrics for PPN Output, PPN Input, Withholding, and Net VAT.
- Added filters date range and tax type with detailed transaction rows.
- Added feature tests for page access and summary calculations.

### File List
- _bmad-output/implementation-artifacts/13-5-tax-reporting.md
- app/Http/Controllers/TaxReportingController.php
- resources/js/Pages/TaxReports/Index.jsx
- routes/web.php
- tests/Feature/TaxReportingTest.php

### Validation
- `php artisan test tests/Feature/TaxReportingTest.php` passed (2 tests).
- `composer test` PHPUnit passed (286 tests), composer wrapper still returns known exit code 1.
- `npm run build` passed with existing Vite warning about deprecated `esbuild` option.

### Change Log
- 2026-05-18: Implemented Story 13.5 Tax Reporting MVP.
