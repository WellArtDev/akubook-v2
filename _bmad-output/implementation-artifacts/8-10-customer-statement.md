# Story 8.10: Customer Statement

**Epic:** 8 - Customer & Sales Management  
**Story ID:** 8.10  
**Story Key:** 8-10-customer-statement  
**Status:** review  
**Created:** 2026-05-14  
**Priority:** P2 (Nice to Have)

---

## User Story

**Sebagai** Finance Staff  
**Saya ingin** generate customer statements  
**Sehingga** customer dapat see transaction history dan outstanding balance

---

## Acceptance Criteria

### AC1: Statement Generation
- Select customer and date range
- Show opening balance
- List all transactions: invoices, payments, credit notes
- Show closing balance
- Show aging breakdown

### AC2: Statement Format
- Customer details
- Transaction table with date, type, reference, debit, credit, balance
- Summary section with total invoices, payments/credits, balance
- Aging table

### AC3: Send Statement MVP
- Generate PDF-ready payload endpoint
- Statement output can be downloaded/consumed by print/email workflow later
- Feature tests cover statement and PDF-ready payload

---

## MVP Scope

- Customer statement controller with filterable statement page
- Customer statement Inertia page
- PDF-ready JSON endpoint
- Opening balance, closing balance, aging, and transaction rows from sales invoices, posted customer payments, and sales returns
- Feature tests for page shape, balance calculation, and PDF-ready endpoint

## Out of Scope

- Binary PDF renderer dependency
- Email delivery integration
- Persistent statement sent log table
- Branded PDF template

---

## Definition of Done

- [x] Customer statement controller created
- [x] Statement generation logic implemented
- [x] PDF-ready endpoint implemented
- [x] Customer statement page implemented
- [x] Tests added for page, balance, and PDF-ready payload
- [x] `composer test` passes
- [x] `npm run build` passes
- [x] Story and sprint status updated to review

---

## Notes

- Mirrors Story 9.10 Supplier Statement pattern.
- MVP avoids new PDF/email dependencies until renderer/mail requirements are confirmed.

---

## Dev Agent Record

### Completion Notes

- Added customer statement route/controller and Inertia page.
- Statement combines invoices as debits, posted payments as credits, and sales returns as credit-note rows.
- Added opening balance, running balance, closing balance, totals, and AR aging buckets.
- Added PDF-ready JSON endpoint for future PDF/email workflow.
- Added feature tests with explicit AR fixtures to avoid fragile nested factories.

### File List

- `app/Http/Controllers/CustomerStatementController.php`
- `resources/js/Pages/CustomerStatements/Index.jsx`
- `routes/web.php`
- `tests/Feature/CustomerStatementTest.php`
- `_bmad-output/implementation-artifacts/8-10-customer-statement.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation

- `php artisan test tests/Feature/CustomerStatementTest.php` passed: 3 tests, 28 assertions.
- `composer test` passed: 471 tests, 471 passed, 2120 assertions.
- `npm run build` passed.

### Change Log

- 2026-05-19: Implemented customer statement MVP and moved story to review.
