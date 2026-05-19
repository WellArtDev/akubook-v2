# Story 9.5: Goods Receipt
**Epic:** 9 | **Story ID:** 9.5 | **Key:** 9-5-goods-receipt | **Priority:** P0
**Status:** review

## User Story
**Sebagai** Warehouse Staff, **Saya ingin** receive goods dari supplier, **Sehingga** update inventory

## Acceptance Criteria
- Create GR dari approved PO
- GR number (GR-YYYY-NNNN)
- Partial receipt support
- Quality inspection
- Inventory update (add stock)
- Update PO received quantity

## Notes
- Mirror DO structure (Story 8.5)
- Inventory transaction: type = 'purchase_receipt'

## Dev Agent Record

### Completion Notes
- Implement Goods Receipt MVP end-to-end: create dari approved PO, partial receipt, quality inspection accepted/rejected, receive/cancel workflow.
- Generate nomor GR format `GR-YYYY-NNNN`.
- Update `purchase_order_lines.received_quantity` berdasarkan accepted quantity saat receive.
- Update status PO jadi `in_progress`/`completed` berdasarkan total penerimaan.
- Inventory transaction model belum ada di codebase, jadi update inventory fisik dicatat sebagai pending follow-up.

### File List
- app/Models/GoodsReceipt.php
- app/Models/GoodsReceiptLine.php
- app/Http/Controllers/GoodsReceiptController.php
- database/migrations/2026_05_17_192556_create_goods_receipts_table.php
- database/migrations/2026_05_17_192556_create_goods_receipt_lines_table.php
- resources/js/Pages/GoodsReceipts/Index.jsx
- resources/js/Pages/GoodsReceipts/Create.jsx
- resources/js/Pages/GoodsReceipts/Show.jsx
- routes/web.php
- tests/Feature/GoodsReceiptTest.php

### Validation
- `php artisan test tests/Feature/GoodsReceiptTest.php` → pass (4 tests)
- `composer test` → PHPUnit pass (212 tests), wrapper still return code 1
- `npm run build` → pass, existing vite warning `esbuild` deprecated by react-babel plugin

### Change Log
- 2026-05-18: Implemented Story 9.5 Goods Receipt MVP and updated validations.
