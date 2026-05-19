# Story 12.3: Bank Reconciliation

**Story Key:** 12-3-bank-reconciliation
**Priority:** P0
**Status:** done

## User Story

As a Finance Staff, I want to reconcile bank statement lines against system bank transactions so cash/bank balances can be verified and differences tracked.

## Acceptance Criteria

1. Create bank reconciliation for one bank account and statement period.
2. Store statement opening balance, closing balance, and reconciliation date.
3. Add statement lines with date, description, debit, credit, reference.
4. Mark statement lines as matched/unmatched.
5. Store matched system reference metadata without mutating original transaction records.
6. Show reconciliation summary: statement closing, system balance, matched total, unreconciled difference.
7. List/filter reconciliations by bank account, status, and period.

## MVP Scope

- Bank reconciliation header and statement line tables.
- Manual statement line entry.
- Manual match/unmatch metadata.
- Status: draft, reconciled.
- Simple system balance from bank account opening balance plus matched statement movement.

## Out of Scope

- Bank statement import.
- Automated matching rules.
- Real payment voucher integration.
- Multi-currency reconciliation.

## Technical Notes

- Reuse Laravel resource controller + Inertia CRUD patterns.
- Use `bank_accounts` from Story 12.2.
- Keep source transactions immutable; reconciliation only stores metadata.

## Definition of Done

- [x] Migration/model for bank reconciliation header.
- [x] Migration/model for statement lines.
- [x] Controller/routes/pages for list/create/show.
- [x] Manual match/unmatch flow.
- [x] Feature tests for create, match, reconcile summary.
- [x] `composer test` and `npm run build` executed.

## Dev Agent Record

### Completion Notes

- Implemented Bank Reconciliation MVP with header/statement-line schema, draft/reconciled workflow, manual match/unmatch metadata, and summary recalculation.
- Added Inertia list/create/show pages with filters by bank, status, and period.
- Reconciliation stores matched reference metadata only and does not mutate source transaction records.

### File List

- `_bmad-output/implementation-artifacts/12-3-bank-reconciliation.md`
- `database/migrations/2026_05_18_032947_create_bank_reconciliations_table.php`
- `database/migrations/2026_05_18_032948_create_bank_reconciliation_lines_table.php`
- `app/Models/BankReconciliation.php`
- `app/Models/BankReconciliationLine.php`
- `database/factories/BankReconciliationFactory.php`
- `database/factories/BankReconciliationLineFactory.php`
- `app/Http/Controllers/BankReconciliationController.php`
- `resources/js/Pages/BankReconciliations/Index.jsx`
- `resources/js/Pages/BankReconciliations/Create.jsx`
- `resources/js/Pages/BankReconciliations/Show.jsx`
- `tests/Feature/BankReconciliationTest.php`
- `routes/web.php`

### Validation

- `php artisan test tests/Feature/BankReconciliationTest.php` passed: 4 tests, 11 assertions.
- `composer test` PHPUnit passed: 262 tests, 799 assertions. Composer wrapper still returned known error code 1 after PHPUnit pass.
- `npm run build` passed with existing Vite warning: `esbuild` option deprecated by `vite:react-babel`, use `oxc`.

### Change Log

- 2026-05-18: Created Story 12.3 Bank Reconciliation.
- 2026-05-18: Implemented Story 12.3 Bank Reconciliation MVP.

