# Story 11.1: Item Master

**Story Key:** `11-1-item-master`  
**Priority:** P0 - Foundation  
**Status:** review

## User Story

As an Inventory Admin, I want to manage item master data so all stock transactions use consistent SKU, unit, and inventory settings.

## Acceptance Criteria

1. User can CRUD item master data.
2. Item code must be unique and required.
3. Item has core fields: code, name, category, unit, purchase price, selling price, minimum stock, active flag.
4. Item has inventory attributes: inventory type (stock/non-stock), valuation method marker, and reorder point.
5. List page supports search and active/inactive filter.
6. Item detail shows all item attributes and current transactional hints (basic usage counts if available).
7. Changes to item master do not break existing sales/purchase flows.

## MVP Scope

- Extend existing `items` entity rather than creating a second item table.
- Add missing inventory fields to support Epic 11 stories.
- Keep valuation method as metadata only (actual valuation processing in Story 11.5).
- Keep usage stats simple (count from sales/purchase related lines if relation exists).

## Out of Scope

- Multi-UOM conversion engine.
- Barcode/serial number management.
- Warehouse-specific item attributes.

## Technical Notes

- Reuse existing Items CRUD pattern in project.
- Preserve compatibility with existing sales/purchase line references to `items`.
- Validate unique code on create/update.

## Definition of Done

- [x] Migration for additional item master fields completed.
- [x] Item model/controller validation updated.
- [x] Item list/form/detail pages updated for new inventory fields.
- [x] Feature tests for create/update/filter pass.
- [x] `composer test` and `npm run build` executed.

## Dev Agent Record

### Completion Notes
- Added inventory extension fields to `items` table: category, inventory_type, valuation_method, minimum_stock, reorder_point.
- Implemented full Item Master CRUD backend via `ItemController` with validation, filters, and usage summary counts.
- Added Inertia pages `Items/Index`, `Create`, `Edit`, `Show` for item master workflow.
- Added feature tests for create, unique code, and filtering.

### File List
- _bmad-output/implementation-artifacts/11-1-item-master.md
- database/migrations/2026_05_18_005041_add_inventory_fields_to_items_table.php
- app/Models/Item.php
- app/Http/Controllers/ItemController.php
- routes/web.php
- resources/js/Pages/Items/Index.jsx
- resources/js/Pages/Items/Create.jsx
- resources/js/Pages/Items/Edit.jsx
- resources/js/Pages/Items/Show.jsx
- tests/Feature/ItemTest.php

### Validation
- `php artisan test tests/Feature/ItemTest.php` -> pass (4 tests).
- `composer test` -> PHPUnit pass (236 tests), wrapper still return code 1 known issue.
- `npm run build` -> pass, existing Vite warning (`esbuild` deprecated by `vite:react-babel`, use `oxc`).

### Change Log
- 2026-05-18: Implement Story 11.1 Item Master MVP.
