# Story 8.11: Sales Dashboard

**Epic:** 8 - Customer & Sales Management  
**Story ID:** 8.11  
**Story Key:** 8-11-sales-dashboard  
**Status:** review  
**Created:** 2026-05-14  
**Priority:** P2 (Nice to Have)

---

## User Story

**Sebagai** Sales Manager  
**Saya ingin** view sales dashboard  
**Sehingga** saya dapat monitor real-time sales performance

---

## Acceptance Criteria

### AC1: KPI Cards
- Today's sales
- This month's sales
- This year's sales
- Pending quotations
- Pending approvals
- Overdue invoices

### AC2: Sales Trend
- Last 12 months trend
- Previous year comparison per month

### AC3: Top 10 Lists
- Top customers by sales amount
- Top products by quantity/revenue
- Top salespeople by sales amount

### AC4: Recent Activity
- Recent quotations
- Recent orders
- Recent invoices
- Recent payments

### AC5: Alerts
- Pending approvals count
- Overdue invoices count

---

## MVP Scope

- Add sales dashboard controller + route.
- Build Inertia sales dashboard page with filters and KPI cards.
- Provide trend/top/recent datasets from sales tables.
- Add feature tests for page shape and populated data scenario.

## Out of Scope

- Chart.js integration
- Realtime polling/websocket updates
- Dashboard PDF export
- Dedicated cache layer

---

## Definition of Done

- [x] Sales dashboard controller & route
- [x] React sales dashboard page
- [x] KPI cards
- [x] Trend dataset
- [x] Top 10 lists
- [x] Recent activity section
- [x] Alerts section
- [x] Feature tests added
- [x] `composer test` passes
- [x] `npm run build` passes
- [x] Story and sprint status updated to review

---

## Notes

- Uses DB-agnostic query patterns (no vendor-specific SQL date formatting).
- Added `valid_until` field in recent quotation query to avoid `is_expired` accessor null error.

---

## Dev Agent Record

### Completion Notes

- Implemented `SalesDashboardController` with filter validation and all required dashboard sections.
- Added `SalesDashboard/Index.jsx` for KPI cards, trend, top lists, recent activity, and alerts.
- Added feature tests with explicit fixture setup (order, quotation, invoice, payment).
- Fixed quotation serialization bug by selecting `valid_until` in recent quotation payload.

### File List

- `app/Http/Controllers/SalesDashboardController.php`
- `resources/js/Pages/SalesDashboard/Index.jsx`
- `routes/web.php`
- `tests/Feature/SalesDashboardTest.php`
- `_bmad-output/implementation-artifacts/8-11-sales-dashboard.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation

- `php artisan test tests/Feature/SalesDashboardTest.php` passed: 2 tests, 46 assertions.
- `composer test` passed: 473 tests, 473 passed, 2166 assertions.
- `npm run build` passed.

### Change Log

- 2026-05-19: Implemented sales dashboard MVP and moved story to review.
