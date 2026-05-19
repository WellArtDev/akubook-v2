# Story 9.11: Purchase Dashboard
**Epic:** 9 | **Story ID:** 9.11 | **Key:** 9-11-purchase-dashboard | **Priority:** P2
**Status:** review

## User Story
**Sebagai** Purchasing Manager, **Saya ingin** view purchase dashboard, **Sehingga** monitor procurement performance

## Acceptance Criteria
- Show this month's purchases KPI.
- Show pending PRs KPI.
- Show pending PO approvals KPI.
- Show overdue invoices KPI.
- Show purchase trend for 12 months.
- Show top 10 suppliers.
- Show top 10 products.

## MVP Scope
- Add purchase dashboard route/controller/page.
- Aggregate KPIs from purchase requests, purchase orders, and purchase invoices.
- Aggregate chart datasets for monthly purchase trend, top suppliers, and top products.
- Add date range filtering.
- Add feature tests for page shape and aggregate data.

## Out of Scope
- Interactive chart library.
- Drilldown report pages.
- Export dashboard to PDF.

## KPIs
- This month's purchases
- Pending PRs
- Pending PO approvals
- Overdue invoices

## Charts
- Purchase trend (12 months)
- Top 10 suppliers
- Top 10 products

## Definition of Done
- [x] Purchase dashboard route/controller implemented.
- [x] Dashboard page renders KPI cards and chart tables.
- [x] Date range filters implemented.
- [x] KPI and chart aggregation covered by feature tests.
- [x] Full backend tests pass.
- [x] Frontend build passes.

## Notes
- Mirror sales dashboard (Story 8.11)

## Dev Agent Record
### Completion Notes
- Added `PurchaseDashboardController` with procurement KPIs and chart dataset aggregation.
- Added `PurchaseDashboard/Index` Inertia page with filters, KPI cards, and simple chart tables.
- Added route `purchase-dashboard.index`.
- Added feature coverage for dashboard shape and seeded procurement aggregate data.

### File List
- `app/Http/Controllers/PurchaseDashboardController.php`
- `resources/js/Pages/PurchaseDashboard/Index.jsx`
- `routes/web.php`
- `tests/Feature/PurchaseDashboardTest.php`
- `_bmad-output/implementation-artifacts/9-11-purchase-dashboard.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation
- `php artisan test tests/Feature/PurchaseDashboardTest.php` (2 passed, 32 assertions)
- `composer test` (460 passed, 2027 assertions)
- `npm run build` (passed)

### Change Log
- 2026-05-19: Implemented purchase dashboard MVP and moved story to review.
