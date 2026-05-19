# Story 8.8: Customer Payment
**Epic:** 8 | **Story ID:** 8.8 | **Key:** 8-8-customer-payment | **Priority:** P0
**Status:** done

## User Story
**Sebagai** Finance Staff, **Saya ingin** record customer payments dengan allocation ke invoice, **Sehingga** AR accurate dan cash receipts tercatat benar

## Acceptance Criteria
- Payment number `PAY-YYYY-NNNN`
- Support payment methods: `cash`, `bank_transfer`, `check`, `credit_card`, `giro`
- Allocation ke invoice support partial payment
- Overpayment tetap tersimpan sebagai unapplied amount
- Posting payment membuat jurnal DR Cash/Bank dan CR Accounts Receivable
- Status lifecycle support `draft`, `posted`, `reconciled`, `voided`

## MVP Scope
- Use existing customer payment CRUD + posting + void flow.
- Add dedicated feature tests for numbering, allocation, unapplied overpayment, and posting journal.
- Keep existing Inertia pages (`Index`, `Create`, `Show`) and API unpaid invoice endpoint.

## Out of Scope
- Auto reconciliation with bank statement.
- Payment gateway integration.
- Multi-currency customer receipts.

## Definition of Done
- [x] Customer payment creation supports required payment methods.
- [x] Payment number format validated by automated test.
- [x] Partial allocation updates invoice paid/due/status correctly.
- [x] Overpayment keeps unapplied amount on payment.
- [x] Posting creates journal entry with expected cash/AR lines.
- [x] Feature tests pass.
- [x] Full regression (`composer test`) pass.
- [x] Frontend build (`npm run build`) pass.

## Notes
- Existing implementation already covered most AC; this story closes testing and traceability gaps.

## Dev Agent Record
### Completion Notes
- Added dedicated `CustomerPaymentTest` feature suite to validate Story 8.8 acceptance criteria.
- Used explicit `SalesOrder` and `SalesInvoice` setup in tests to avoid brittle nested factory dependencies.
- Added accounting fixture setup (`FiscalPeriod`, accounts `1-1100`, `1-1200`, `2-1300`) to validate posting journal behavior.

### File List
- `tests/Feature/CustomerPaymentTest.php`
- `_bmad-output/implementation-artifacts/8-8-customer-payment.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation
- `php artisan test tests/Feature/CustomerPaymentTest.php` (4 passed, 20 assertions)
- `composer test` (465 passed, 2052 assertions)
- `npm run build` (passed)

### Change Log
- 2026-05-19: Added customer payment feature tests, validated full flow, moved story to review.
