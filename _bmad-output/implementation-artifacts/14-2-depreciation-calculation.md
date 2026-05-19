# Story 14.2: Depreciation Calculation

**Story Key:** `14-2-depreciation-calculation`  
**Priority:** P0  
**Status:** done

## User Story
As a Finance Staff, I want to calculate monthly depreciation for active fixed assets so that periodic depreciation values are ready before journal posting.

## Acceptance Criteria
1. User can run depreciation calculation by period (month/year).
2. System calculates straight-line depreciation per active asset: `(acquisition_cost - residual_value) / useful_life_months`.
3. System stores depreciation result per asset and period with unique key (asset + period).
4. Calculation skips disposed assets and assets with zero remaining life.
5. Page shows summary total depreciation and detail rows per asset.
6. Re-run same period updates existing calculation rows (idempotent upsert).

## MVP Scope
- Depreciation calculation run and persisted records.
- Inertia page for run form and result table.
- No journal posting in this story.

## Out of Scope
- Multi-method depreciation (declining, units of production).
- Automatic posting to journal.
- Tax depreciation differences.

## Technical Notes
- Source data from `fixed_assets`.
- Store calculations in dedicated table `asset_depreciations`.
- Use period key format `YYYY-MM`.

## Definition of Done
- [x] Migration + model for depreciation records implemented.
- [x] Controller + route + page for run and result implemented.
- [x] Feature tests for calculation correctness and idempotent rerun implemented.
- [x] `composer test` and `npm run build` executed.

## Dev Agent Record
### Completion Notes
- Implemented depreciation run by period with straight-line formula for active assets.
- Added idempotent `updateOrCreate` per `fixed_asset_id + period`.
- Added depreciation result page with run action, summary totals, and detail rows.
- Added feature tests for run creation and rerun non-duplication.

### File List
- _bmad-output/implementation-artifacts/14-2-depreciation-calculation.md
- app/Http/Controllers/AssetDepreciationController.php
- app/Models/AssetDepreciation.php
- database/factories/AssetDepreciationFactory.php
- database/migrations/2026_05_18_061648_create_asset_depreciations_table.php
- resources/js/Pages/AssetDepreciations/Index.jsx
- routes/web.php
- tests/Feature/AssetDepreciationTest.php

### Validation
- `php artisan test tests/Feature/AssetDepreciationTest.php` passed (3 tests).
- `composer test` PHPUnit passed (293 tests), composer wrapper still returns known exit code 1.
- `npm run build` passed with existing Vite warning about deprecated `esbuild` option.

### Change Log
- 2026-05-18: Implemented Story 14.2 Depreciation Calculation MVP.

