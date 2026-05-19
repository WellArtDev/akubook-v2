# Story 14.5: Asset Reports

**Story Key:** 14-5-asset-reports
**Priority:** P0
**Status:** done

## User Story

As a Finance Manager, I want fixed asset reports so I can review asset register, accumulated depreciation, book value, and disposals by period.

## Acceptance Criteria

1. User can open asset report page with period/date filters.
2. Report shows active/disposed asset register with acquisition cost, accumulated depreciation, book value, and status.
3. Report summarizes total acquisition cost, accumulated depreciation, book value, and disposal proceeds.
4. Report includes disposal rows within selected period.
5. Report is read-only and does not mutate fixed asset, depreciation, or disposal records.

## MVP Scope

- One Inertia report page.
- Uses `fixed_assets`, `asset_depreciations`, and `asset_disposals`.
- Filter by status and date range.
- Latest depreciation per asset determines accumulated depreciation/book value.

## Out of Scope

- PDF/export.
- Tax depreciation report.
- Multi-book depreciation.

## Definition of Done

- [x] Asset report controller/page implemented
- [x] Asset register and disposal summaries implemented
- [x] Filters implemented
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Implemented read-only Asset Reports page.
- Asset register uses latest depreciation per asset for accumulated depreciation and book value.
- Disposal history and summary totals included.
- Filters support status, date range, and search.

### File List

- `_bmad-output/implementation-artifacts/14-5-asset-reports.md`
- `app/Http/Controllers/AssetReportController.php`
- `resources/js/Pages/AssetReports/Index.jsx`
- `routes/web.php`
- `tests/Feature/AssetReportTest.php`

### Validation

- `php artisan test tests/Feature/AssetReportTest.php` passed 3 tests / 35 assertions.
- `composer test` PHPUnit passed 302 tests / 953 assertions; composer wrapper still returned known code 1 after pass.
- `npm run build` passed with existing Vite warning: `esbuild` option deprecated by `vite:react-babel`, use `oxc`.

### Change Log

- 2026-05-18: Created Story 14.5 Asset Reports.
- 2026-05-18: Implemented Story 14.5 Asset Reports MVP.

