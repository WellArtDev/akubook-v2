# Story 11.3: Stock Opname

**Story Key:** `11-3-stock-opname`  
**Priority:** P0  
**Status:** review

## User Story

As an Inventory Staff, I want to run stock opname so physical count differences can be recorded and adjusted into stock ledger.

## Acceptance Criteria

1. User can create stock opname document with date and notes.
2. User can add item count lines: system_qty, physical_qty, variance.
3. User can submit/confirm opname to create stock adjustment movements.
4. Positive variance creates stock `in`, negative variance creates stock `out` in `stock_transactions` with movement_type `adjustment`.
5. List and detail pages show opname history and status.

## Definition of Done

- [x] Migration/models/controller/pages for stock opname done.
- [x] Confirm action generates stock transactions.
- [x] Feature tests pass.
- [x] `composer test` and `npm run build` executed.

## Dev Agent Record

### Completion Notes
- Added stock opname header + line tables with draft/confirmed workflow.
- Implemented stock opname CRUD subset (index/create/store/show) and confirm action.
- Confirm action creates `stock_transactions` adjustment entries from variance lines.
- Added Inertia pages for list, create with physical count input, and detail/confirm.
- Added feature tests for create, confirm, and page access.

### File List
- _bmad-output/implementation-artifacts/11-3-stock-opname.md
- database/migrations/2026_05_18_010534_create_stock_opnames_table.php
- database/migrations/2026_05_18_010534_create_stock_opname_lines_table.php
- app/Models/StockOpname.php
- app/Models/StockOpnameLine.php
- app/Http/Controllers/StockOpnameController.php
- routes/web.php
- resources/js/Pages/StockOpnames/Index.jsx
- resources/js/Pages/StockOpnames/Create.jsx
- resources/js/Pages/StockOpnames/Show.jsx
- tests/Feature/StockOpnameTest.php

### Validation
- `php artisan test tests/Feature/StockOpnameTest.php` -> pass (3 tests).
- `composer test` -> PHPUnit pass (243 tests), wrapper still return code 1 known issue.
- `npm run build` -> pass, existing Vite warning (`esbuild` deprecated by `vite:react-babel`, use `oxc`).

### Change Log
- 2026-05-18: Implement Story 11.3 Stock Opname MVP.
