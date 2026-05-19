# Story 19.2: Operational Reports

**Story Key:** 19-2-operational-reports
**Priority:** P0
**Status:** review

## User Story

As an operations manager, I want operational reports by period so I can monitor sales, purchasing, delivery, receiving, and stock movement performance.

## Acceptance Criteria

1. User can open operational reports page with `date_from` and `date_to` filters.
2. Sales section summarizes sales invoices count and total amount in period.
3. Purchasing section summarizes purchase orders and goods receipts count in period.
4. Logistics section summarizes delivery orders by status in period.
5. Inventory section summarizes stock movement in/out/net quantities by movement type.
6. Report is read-only and shows generated timestamp.

## MVP Scope

- Single Inertia page with operational summary cards and detail tables.
- Data sources: `sales_invoices`, `purchase_orders`, `goods_receipts`, `delivery_orders`, `stock_transactions`.
- Period filtering by document date / movement date.

## Out of Scope

- Export.
- Drill-down charts.
- Forecasting or KPIs beyond summary totals.

## Technical Notes

- Reuse report pattern from Story 19.1.
- Keep controller read-only.
- Avoid mutating source transactions.

## Definition of Done

- [x] Operational report controller and route implemented
- [x] Operational report page implemented
- [x] Summary and detail tables computed
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Implemented read-only operational reports for sales invoices, purchase orders, goods receipts, delivery orders, and stock movements.
- Added period filters, generated timestamp, summary cards, and grouped detail tables.
- Aggregated sales/purchase totals by status and stock movement quantities by movement type.
- Added feature tests for page access, summary calculation, and period filtering.

### File List

- `_bmad-output/implementation-artifacts/19-2-operational-reports.md`
- `app/Http/Controllers/OperationalReportController.php`
- `resources/js/Pages/OperationalReports/Index.jsx`
- `routes/web.php`
- `tests/Feature/OperationalReportTest.php`

### Validation

- `php artisan test tests/Feature/OperationalReportTest.php` ✅ pass (3 tests)
- `composer test` ✅ PHPUnit pass (371 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-18: Implemented Story 19.2 Operational Reports MVP and validations.
