# Story 9.10: Supplier Statement
**Epic:** 9 | **Story ID:** 9.10 | **Key:** 9-10-supplier-statement | **Priority:** P2
**Status:** done

## User Story
**Sebagai** Finance Staff, **Saya ingin** generate supplier statements, **Sehingga** reconcile AP dengan supplier

## Acceptance Criteria
- Select supplier & date range
- Show transactions (invoices, payments, debit notes)
- Show closing balance
- Generate PDF

## MVP Scope
- Build supplier statement endpoint/page with supplier selector + date range filter.
- Include AP transactions from `purchase_invoices`, `supplier_payments` (`posted` only), and `purchase_returns`.
- Compute opening balance, running balance, totals debit/credit, and closing balance.
- Provide PDF-ready output endpoint (JSON payload for MVP).
- Add feature tests for page access, balance calculation, and PDF endpoint output.

## Out of Scope
- Branded PDF binary renderer.
- Multi-currency statement support.
- Email statement dispatch.

## Definition of Done
- [x] Supplier and period filter implemented.
- [x] Statement transaction timeline includes invoice/payment/debit note.
- [x] Closing balance calculation validated by automated test.
- [x] PDF-ready endpoint available.
- [x] Feature tests added and passing.
- [x] Full regression (`composer test`) passing.
- [x] Frontend build (`npm run build`) passing.

## Notes
- Mirror customer statement (Story 8.10)

## Dev Agent Record
### Completion Notes
- Added `SupplierStatementController` with statement aggregator across AP invoice, supplier payment, and purchase return flows.
- Added `SupplierStatements/Index` page with supplier/date filters, summary cards, transaction table, and PDF JSON action.
- Added PDF-ready endpoint (`supplier-statements.pdf`) returning statement payload format for downstream renderer.
- Stabilized test data setup to avoid nested factory issues by creating explicit purchase return records.

### File List
- `app/Http/Controllers/SupplierStatementController.php`
- `resources/js/Pages/SupplierStatements/Index.jsx`
- `routes/web.php`
- `tests/Feature/SupplierStatementTest.php`
- `_bmad-output/implementation-artifacts/9-10-supplier-statement.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation
- `php artisan test tests/Feature/SupplierStatementTest.php` (3 passed, 28 assertions)
- `composer test` (458 passed, 1995 assertions)
- `npm run build` (passed)

### Change Log
- 2026-05-19: Implemented supplier statement MVP and moved story to review.
