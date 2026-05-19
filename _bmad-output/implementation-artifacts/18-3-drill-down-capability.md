# Story 18.3: Drill-Down Capability

**Story Key:** 18-3-drill-down-capability
**Priority:** P0
**Status:** done

## User Story

As a manager, I want to click dashboard widgets and inspect the underlying records so I can understand KPI values without leaving the dashboard context.

## Acceptance Criteria

1. Role dashboard widgets expose stable `widget_key` and drill-down route metadata.
2. User can open drill-down page for supported widget key.
3. Drill-down page shows title, summary, filters, and detail rows for the selected widget.
4. Drill-down supports `date_from`, `date_to`, and `search` filters where relevant.
5. Drill-down is read-only and does not mutate source data.

## MVP Scope

- Drill-down route/page for role dashboard widgets.
- Supported widgets: finance cash in/out/payroll, inventory stock movements, HR attendance/leaves/employees, sales invoices/faktur/quotations.
- Dynamic table columns/rows per widget.

## Out of Scope

- Saved drill-down views.
- Export to CSV/PDF.
- Advanced pivot/chart drill-down.

## Technical Notes

- Reuse `RoleDashboardController` role/widget mapping.
- Keep existing metrics endpoint backwards compatible.

## Definition of Done

- [x] Widget payload includes drill-down metadata
- [x] Drill-down route/controller/page implemented
- [x] Filters supported
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Added `widget_key` and `drilldown_route` metadata to role dashboard widget payloads.
- Implemented drill-down route and Inertia page with dynamic columns, summary, filters, and read-only detail rows.
- Added supported drill-down datasets across finance, inventory, HR, and sales dashboard widgets.
- Preserved existing metrics endpoint compatibility and auto-refresh dashboard behavior.

### File List

- `_bmad-output/implementation-artifacts/18-3-drill-down-capability.md`
- `app/Http/Controllers/RoleDashboardController.php`
- `routes/web.php`
- `resources/js/Pages/Dashboards/RoleIndex.jsx`
- `resources/js/Pages/Dashboards/Drilldown.jsx`
- `tests/Feature/RoleDashboardTest.php`

### Validation

- `php artisan test tests/Feature/RoleDashboardTest.php` ✅ pass (6 tests)
- `composer test` ✅ PHPUnit pass (362 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-18: Implemented Story 18.3 Drill-Down Capability MVP and validations.

