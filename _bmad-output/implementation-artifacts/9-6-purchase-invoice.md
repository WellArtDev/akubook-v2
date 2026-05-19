# Story 9.6: Purchase Invoice
**Epic:** 9 | **Story ID:** 9.6 | **Key:** 9-6-purchase-invoice | **Priority:** P0
**Status:** done

## User Story
**Sebagai** Finance Staff, **Saya ingin** record supplier invoices, **Sehingga** track accounts payable

## Acceptance Criteria
- Create invoice dari GR
- Invoice number (PINV-YYYY-NNNN)
- 3-way matching (PO, GR, Invoice)
- Journal entry (DR: Inventory/Expense, DR: Tax, CR: AP)
- AP tracking

## Notes
- Mirror sales invoice (Story 8.6)
- 3-way matching validation

## Dev Agent Record
### Completion Notes
- Implement Purchase Invoice flow from Goods Receipt (`received`) dengan line source dari GR line accepted qty.
- Implement invoice number generator `PINV-YYYY-NNNN`.
- Implement 3-way matching validation: invoice line wajib berasal dari GR line selected, qty invoice tidak boleh lebih dari remaining uninvoiced qty, dan relasi PO/GR/invoice tersambung.
- Implement posting flow: update `purchase_order_lines.invoiced_quantity`, status invoice `draft -> posted`, dan create jurnal otomatis.
- Implement journal mapping sesuai AC: DR Inventory/Expense (`1-1400`), DR Tax (`1-1500`), CR AP (`2-1100`) dengan type `auto_purchase`.
- Implement AP tracking fields (`paid_amount`, `outstanding_amount`, status posted/partially_paid/paid/cancelled) pada schema/model dan total recalculation.
- Add Inertia pages: list/create/show purchase invoice.

### File List
- app/Http/Controllers/PurchaseInvoiceController.php
- app/Models/PurchaseInvoice.php
- app/Models/PurchaseInvoiceLine.php
- database/migrations/2026_05_17_221253_create_purchase_invoices_table.php
- database/migrations/2026_05_17_221254_create_purchase_invoice_lines_table.php
- database/factories/PurchaseInvoiceFactory.php
- database/factories/PurchaseInvoiceLineFactory.php
- resources/js/Pages/PurchaseInvoices/Index.jsx
- resources/js/Pages/PurchaseInvoices/Create.jsx
- resources/js/Pages/PurchaseInvoices/Show.jsx
- routes/web.php
- tests/Feature/PurchaseInvoiceTest.php

### Validation
- `php artisan test tests/Feature/PurchaseInvoiceTest.php` ✅ (3 tests, 12 assertions)
- `composer test` ✅ PHPUnit pass (215 tests, 667 assertions) with known wrapper exit code 1 in this repo
- `npm run build` ✅ (existing warning: Vite `esbuild` deprecated by `vite:react-babel`, use `oxc`)

### Change Log
- 2026-05-18: Implemented Story 9.6 Purchase Invoice MVP with GR-based creation, 3-way matching validation, posting + journal automation, AP tracking, and feature tests.

