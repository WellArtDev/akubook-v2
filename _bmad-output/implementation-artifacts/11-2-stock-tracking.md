# Story 11.2: Stock Tracking

**Story Key:** `11-2-stock-tracking`  
**Priority:** P0  
**Status:** review

## User Story

As an Inventory Staff, I want stock movement tracked per item so current on-hand stock and movement history are visible and auditable.

## Acceptance Criteria

1. System records stock movements in a dedicated stock ledger table.
2. Movement types support at minimum: purchase_receipt, purchase_return, sales_delivery, sales_return, adjustment.
3. Each movement stores item, quantity in/out, reference type/id, date, notes, and user.
4. System can show current stock balance per item from movement aggregation.
5. Stock tracking page supports filtering by item, movement type, and date range.
6. Existing purchasing/sales flows remain compatible.

## MVP Scope

- Introduce stock transaction ledger and simple stock balance calculation.
- Add manual adjustment endpoint/page for adjustment movement.
- Add read pages for movement history and item stock balance.
- Keep costing/valuation out (Story 11.5).

## Out of Scope

- FIFO/LIFO costing layer.
- Multi-warehouse stock separation.
- Batch/serial-level tracking.

## Technical Notes

- Build additive ledger model (`stock_transactions`) not direct mutable stock field only.
- Keep aggregation query-based for MVP.
- Use existing item master from Story 11.1.

## Definition of Done

- [x] Migration/model/controller for stock transactions done.
- [x] Stock history + stock balance pages implemented.
- [x] Manual adjustment flow implemented.
- [x] Feature tests pass.
- [x] `composer test` and `npm run build` executed.

## Dev Agent Record

### Completion Notes
- Added stock transaction ledger table and model for inventory movement tracking.
- Implemented movement types: purchase_receipt, purchase_return, sales_delivery, sales_return, adjustment.
- Added stock tracking index page with balance summary and movement history filters.
- Added manual movement form for adjustment-style entries.
- Added feature tests for index, create, filtering, and validation.

### File List
- _bmad-output/implementation-artifacts/11-2-stock-tracking.md
- database/migrations/2026_05_18_005837_create_stock_transactions_table.php
- app/Models/StockTransaction.php
- database/factories/StockTransactionFactory.php
- app/Http/Controllers/StockTransactionController.php
- routes/web.php
- resources/js/Pages/StockTransactions/Index.jsx
- resources/js/Pages/StockTransactions/Create.jsx
- tests/Feature/StockTransactionTest.php

### Validation
- `php artisan test tests/Feature/StockTransactionTest.php` -> pass (4 tests).
- `composer test` -> PHPUnit pass (240 tests), wrapper still return code 1 known issue.
- `npm run build` -> pass, existing Vite warning (`esbuild` deprecated by `vite:react-babel`, use `oxc`).

### Change Log
- 2026-05-18: Implement Story 11.2 Stock Tracking MVP.
