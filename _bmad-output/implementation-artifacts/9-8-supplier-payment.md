# Story 9.8: Supplier Payment
**Epic:** 9 | **Story ID:** 9.8 | **Key:** 9-8-supplier-payment | **Priority:** P0
**Status:** review

## User Story
**Sebagai** Finance Staff, **Saya ingin** record supplier payments, **Sehingga** track AP dan cash flow

## Acceptance Criteria
- Payment number (SPAY-YYYY-NNNN)
- Payment methods (cash, bank transfer, check, giro)
- Invoice allocation
- Partial payment support
- Journal entry (DR: AP, CR: Cash/Bank)

## MVP Scope
- Supplier payment header + allocation persistence (`supplier_payments`, `supplier_payment_allocations`)
- Numbering generator `SPAY-YYYY-NNNN`
- Allocation flow ke purchase invoice dengan update `paid_amount` / `outstanding_amount` / status
- Posting flow supplier payment + auto journal entry
- Basic Inertia pages (index/create/show)
- Feature tests create/allocation/posting

## Out of Scope
- Payment reversal journal automation
- Multi-currency supplier payment
- Bulk payment batch file integration

## Definition of Done
- [x] Migration supplier payment + allocation dibuat
- [x] Model + relasi + business logic payment/alokasi/posting tersedia
- [x] Controller + routes supplier payment tersedia
- [x] UI basic (index/create/show) tersedia untuk flow utama
- [x] Feature tests untuk create/allocation/posting lulus
- [x] `composer test` lulus
- [x] `npm run build` lulus

## Dev Agent Record

### Completion Notes
- Implemented Supplier Payment end-to-end with payment number format `SPAY-YYYY-NNNN`.
- Added payment method enforcement (`cash`, `bank_transfer`, `check`, `giro`) and optional reference fields.
- Implemented invoice allocation including partial payment support and purchase invoice balance/status updates.
- Implemented posting flow that creates AP vs Cash/Bank journal entry and links `journal_entry_id`.
- Added basic Inertia pages for finance flow and API endpoint for unpaid purchase invoices.
- Added feature tests for creation, partial allocation behavior, and posting+journal creation.

### File List
- `database/migrations/2026_05_19_090000_create_supplier_payments_table.php`
- `app/Models/SupplierPayment.php`
- `app/Models/SupplierPaymentAllocation.php`
- `app/Http/Controllers/SupplierPaymentController.php`
- `resources/js/Pages/SupplierPayments/Index.jsx`
- `resources/js/Pages/SupplierPayments/Create.jsx`
- `resources/js/Pages/SupplierPayments/Show.jsx`
- `routes/web.php`
- `database/factories/SupplierPaymentFactory.php`
- `tests/Feature/SupplierPaymentTest.php`
- `_bmad-output/implementation-artifacts/9-8-supplier-payment.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation
- `php artisan test tests/Feature/SupplierPaymentTest.php` ✅ (3 passed, 11 assertions)
- `composer test` ✅ (452 passed)
- `npm run build` ✅

### Change Log
- 2026-05-19: Implemented Story 9.8 Supplier Payment MVP and moved story status to review.

## Notes
- Mirror customer payment (Story 8.8)
