# Story 14.1: Asset Registration

**Story Key:** `14-1-asset-registration`  
**Priority:** P0  
**Status:** review

## User Story
As a Finance/Asset Staff, I want to register fixed assets with core metadata and accounting mapping so that assets are ready for depreciation and reporting processes.

## Acceptance Criteria
1. User can create fixed asset master with unique asset code.
2. Asset stores name, category, acquisition date, acquisition cost, useful life (months), residual value, and status.
3. Asset stores related GL accounts: asset account, accumulated depreciation account, depreciation expense account.
4. Asset list supports search and status filter.
5. Asset detail page shows full metadata and depreciation-ready fields.
6. Source transaction mutation is out of scope; registration is standalone master entry.

## MVP Scope
- Fixed asset CRUD (index/create/show/edit/delete).
- Core fields for depreciation setup.
- Active/inactive lifecycle state.
- Basic validation (unique code, positive amounts, useful life > 0).

## Out of Scope
- Automated depreciation calculation.
- Depreciation journal posting.
- Asset disposal accounting.

## Technical Notes
- Reuse existing master CRUD Inertia pattern.
- Use `accounts` table for GL mapping.
- Keep status simple: `active|inactive|disposed`.

## Definition of Done
- [x] Migration + Model fixed assets implemented.
- [x] Controller + routes + Inertia pages implemented.
- [x] Feature tests cover CRUD and validations.
- [x] `composer test` and `npm run build` executed.

## Dev Agent Record
### Completion Notes
- Implemented fixed asset registration CRUD with GL account mapping and depreciation setup fields.
- Added auto-code generation `FA-YYYY-NNNN` when asset code is empty.
- Added status/search filters in listing and detail metadata page.
- Added feature tests for create, unique code validation, and update status flow.

### File List
- _bmad-output/implementation-artifacts/14-1-asset-registration.md
- app/Http/Controllers/FixedAssetController.php
- app/Models/FixedAsset.php
- database/factories/FixedAssetFactory.php
- database/migrations/2026_05_18_061051_create_fixed_assets_table.php
- resources/js/Pages/FixedAssets/Index.jsx
- resources/js/Pages/FixedAssets/Create.jsx
- resources/js/Pages/FixedAssets/Edit.jsx
- resources/js/Pages/FixedAssets/Show.jsx
- routes/web.php
- tests/Feature/FixedAssetTest.php

### Validation
- `php artisan test tests/Feature/FixedAssetTest.php` passed (4 tests).
- `composer test` PHPUnit passed (290 tests), composer wrapper still returns known exit code 1.
- `npm run build` passed with existing Vite warning about deprecated `esbuild` option.

### Change Log
- 2026-05-18: Implemented Story 14.1 Asset Registration MVP.
