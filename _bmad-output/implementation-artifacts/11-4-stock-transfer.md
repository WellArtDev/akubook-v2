# Story 11.4: Stock Transfer

**Story Key:** `11-4-stock-transfer`  
**Priority:** P0  
**Status:** review

## User Story

As an Inventory Staff, I want to record stock transfer between locations so movement out/in is tracked and auditable.

## Acceptance Criteria

1. User can create stock transfer document with from_location and to_location.
2. User can add transfer lines per item and quantity.
3. Confirming transfer creates two stock transactions per line: out from source and in to destination.
4. Transfer has status draft/confirmed and history page.
5. Source stock must be sufficient before confirm.

## Definition of Done

- [x] Migration/models/controller/pages for stock transfer done.
- [x] Confirm action writes balanced in/out stock transactions.
- [x] Feature tests pass.
- [x] `composer test` and `npm run build` executed.

## Dev Agent Record

### Completion Notes

- Implemented Stock Transfer MVP with branch-based source/destination locations.
- Added `stock_transfers` and `stock_transfer_lines` tables plus `branch_id` on stock transaction ledger for location-aware stock movement.
- Confirm action validates source branch stock before writing paired `transfer_out` and `transfer_in` transactions.
- Added Inertia pages for stock transfer list, create, and detail/confirm flow.

### File List

- _bmad-output/implementation-artifacts/11-4-stock-transfer.md
- database/migrations/2026_05_18_011324_create_stock_transfers_table.php
- database/migrations/2026_05_18_011325_create_stock_transfer_lines_table.php
- database/migrations/2026_05_18_011326_add_branch_id_to_stock_transactions_table.php
- app/Models/StockTransfer.php
- app/Models/StockTransferLine.php
- app/Models/StockTransaction.php
- app/Http/Controllers/StockTransferController.php
- database/factories/StockTransferFactory.php
- database/factories/StockTransferLineFactory.php
- resources/js/Pages/StockTransfers/Index.jsx
- resources/js/Pages/StockTransfers/Create.jsx
- resources/js/Pages/StockTransfers/Show.jsx
- tests/Feature/StockTransferTest.php
- routes/web.php
- _bmad-output/implementation-artifacts/sprint-status.yaml

### Validation

- `php artisan test tests/Feature/StockTransferTest.php` passed: 3 tests, 12 assertions.
- `composer test` PHPUnit passed: 246 tests, 739 assertions. Composer wrapper still returned known error code 1 after pass.
- `npm run build` passed with existing Vite warning: `esbuild` option deprecated by `vite:react-babel`, use `oxc`.

### Change Log

- 2026-05-18: Implement Story 11.4 Stock Transfer MVP.
