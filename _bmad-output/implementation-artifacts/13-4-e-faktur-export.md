# Story 13.4: E-Faktur Export

**Story Key:** `13-4-e-faktur-export`  
**Priority:** P0  
**Status:** review

## User Story
As a Finance/Tax Staff, I want to export issued Faktur Pajak into e-Faktur compatible file format so that tax reporting submission can be prepared quickly and consistently.

## Acceptance Criteria
1. User can generate export batch from issued Faktur Pajak by date range.
2. Export batch has unique number format `EF-{YYYY}-{NNNN}`.
3. Export captures required faktur fields (number, date, customer, DPP, PPN, total) into structured file payload.
4. Export batch status supports `draft` and `generated`.
5. User can view export history and detail rows included in each batch.
6. Source Faktur Pajak data is not mutated by export process.

## MVP Scope
- Add export batch entity and export row entity.
- Generate CSV payload stored in batch metadata/content.
- Include only Faktur Pajak with status `issued` in selected period.
- Inertia pages for list, create, and show export batch.

## Out of Scope
- Direct integration API e-Faktur official.
- Digital signing and upload to DJP.
- Multi-format export beyond MVP CSV.

## Technical Notes
- Reuse FakturPajak domain (`faktur_pajaks`) and existing customer/invoice relations.
- Persist snapshot rows so later changes in faktur source do not alter historical export batch.
- Keep idempotency simple: same faktur can appear in multiple batches in MVP.

## Definition of Done
- [x] Migration + Model for e-faktur exports and lines done.
- [x] Export generation flow implemented with `EF-YYYY-NNNN` numbering.
- [x] CSV content generated and stored.
- [x] Inertia pages list/create/show working.
- [x] Feature tests cover generate and history/detail.
- [x] `composer test` and `npm run build` executed.

## Dev Agent Record
### Completion Notes
- Implemented e-faktur export batch with `EF-YYYY-NNNN` numbering and status generated.
- Added export line snapshots from issued faktur (number/date/customer/tax ID/DPP/PPN/total).
- Added CSV generation, storage, and download endpoint.
- Added Inertia pages for export history, generate form, and detail batch.
- Added feature tests for generate flow, filtering source faktur status, and CSV download.

### File List
- _bmad-output/implementation-artifacts/13-4-e-faktur-export.md
- app/Http/Controllers/EFakturExportController.php
- app/Models/EFakturExport.php
- app/Models/EFakturExportLine.php
- database/factories/EFakturExportFactory.php
- database/factories/EFakturExportLineFactory.php
- database/migrations/2026_05_18_055038_create_e_faktur_exports_table.php
- database/migrations/2026_05_18_055038_create_e_faktur_export_lines_table.php
- resources/js/Pages/EFakturExports/Index.jsx
- resources/js/Pages/EFakturExports/Create.jsx
- resources/js/Pages/EFakturExports/Show.jsx
- routes/web.php
- tests/Feature/EFakturExportTest.php

### Validation
- `php artisan test tests/Feature/EFakturExportTest.php` passed (4 tests).
- `composer test` PHPUnit passed (284 tests), composer wrapper still returns known exit code 1.
- `npm run build` passed with existing Vite warning about deprecated `esbuild` option.

### Change Log
- 2026-05-18: Implemented Story 13.4 E-Faktur Export MVP.
