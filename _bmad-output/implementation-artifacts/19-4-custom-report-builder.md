# Story 19.4: Custom Report Builder

**Story Key:** 19-4-custom-report-builder
**Priority:** P0
**Status:** review

## User Story

As a management user, I want to define simple custom reports from approved data sources so I can monitor specific KPIs without requesting code changes.

## Acceptance Criteria

1. User can CRUD custom report definition with unique code and name.
2. Report definition stores source key, selected columns, and default filters in JSON.
3. Only whitelisted read-only sources can be used (`employees`, `sales_invoices`, `purchase_orders`, `vouchers`, `attendance_records`).
4. User can run preview for a report and get rows based on selected columns and filters.
5. Report list supports search and active/inactive filter.
6. No source transaction tables are mutated.

## MVP Scope

- Report definition master CRUD.
- Source whitelist and column whitelist validation.
- Report preview endpoint/page (top 200 rows) using definition filters + ad-hoc search.
- Read-only execution only.

## Out of Scope

- Join across multiple sources.
- Formula columns.
- Scheduled report jobs.

## Technical Notes

- Keep SQL generation controlled by whitelist mapping in controller/service.
- Store `selected_columns` and `default_filters` as JSON arrays/objects.
- Reuse Inertia index/create/show/edit patterns.

## Definition of Done

- [x] Custom report definition migration/model created
- [x] Custom report builder controller/routes/pages implemented
- [x] Source and column whitelist validation implemented
- [x] Preview execution implemented (read-only)
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Implemented Custom Report Builder master with whitelisted data sources and selected columns.
- Added read-only preview execution with date/search filters and default filter metadata.
- Added Inertia CRUD and preview pages.
- Added feature tests for CRUD, unique code validation, source filter preview, and selected columns.

### File List

- `_bmad-output/implementation-artifacts/19-4-custom-report-builder.md`
- `database/migrations/2026_05_18_231635_create_custom_reports_table.php`
- `app/Models/CustomReport.php`
- `database/factories/CustomReportFactory.php`
- `app/Http/Controllers/CustomReportController.php`
- `routes/web.php`
- `resources/js/Pages/CustomReports/Index.jsx`
- `resources/js/Pages/CustomReports/Create.jsx`
- `resources/js/Pages/CustomReports/Edit.jsx`
- `resources/js/Pages/CustomReports/Show.jsx`
- `tests/Feature/CustomReportTest.php`

### Validation

- `php artisan test tests/Feature/CustomReportTest.php` ✅ pass (4 tests)
- `composer test` ✅ PHPUnit pass (378 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-18: Implemented Story 19.4 Custom Report Builder MVP and validations.
