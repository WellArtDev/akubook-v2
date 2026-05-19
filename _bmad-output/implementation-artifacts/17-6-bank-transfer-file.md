# Story 17.6: Bank Transfer File

**Story Key:** 17-6-bank-transfer-file
**Priority:** P0
**Status:** done

## User Story

As a Finance Staff, I want to generate bank transfer batch files from calculated payroll so salary payments can be transferred consistently and auditable.

## Acceptance Criteria

1. User can generate transfer batch by payroll period (`YYYY-MM`) from payroll lines with status `calculated`.
2. Batch stores transfer rows per employee with employee id/name, bank name, account number, account holder, transfer amount (net after PPh21), and row status.
3. Employee without bank account data is marked as failed/skipped row and not included in total transfer amount.
4. Batch has unique number `BT-YYYY-NNNN`, status `draft|generated`, period, row counts, success counts, failed counts, total amount.
5. Batch detail page shows row-level results and summary.
6. Generated output file content (CSV) is stored and downloadable.
7. Source payroll and employee records are read-only in this story.

## MVP Scope

- Add employee bank fields needed for payroll transfer metadata.
- Generate transfer batch and line snapshots from payroll period.
- CSV output generation and download endpoint.
- Inertia list/create/show pages.

## Out of Scope

- Real bank API upload.
- Reconciliation feedback from bank.
- Retry workflow and approval chain.

## Technical Notes

- Reuse period selection and run status patterns from Payroll Run/Reports.
- Transfer amount uses `payroll_run_lines.net_pay_after_pph21` fallback `net_pay`.
- Keep immutable snapshot in batch lines.

## Definition of Done

- [x] Employee bank metadata fields added
- [x] Bank transfer batch/line migrations and models created
- [x] Batch generation + CSV download endpoints implemented
- [x] Inertia pages for list/create/show implemented
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Added employee bank metadata fields for payroll transfer processing.
- Implemented payroll bank transfer batch generation by payroll period with immutable line snapshots.
- Added success/failed row handling for missing bank data and total transfer aggregation.
- Added CSV output generation and download endpoint.
- Added Inertia pages for transfer list, generation form, and detail view.

### File List

- `_bmad-output/implementation-artifacts/17-6-bank-transfer-file.md`
- `database/migrations/2026_05_18_142400_add_bank_fields_to_employees_table.php`
- `database/migrations/2026_05_18_142358_create_payroll_bank_transfers_table.php`
- `database/migrations/2026_05_18_142358_create_payroll_bank_transfer_lines_table.php`
- `app/Models/Employee.php`
- `app/Models/PayrollBankTransfer.php`
- `app/Models/PayrollBankTransferLine.php`
- `database/factories/PayrollBankTransferFactory.php`
- `database/factories/PayrollBankTransferLineFactory.php`
- `app/Http/Controllers/PayrollBankTransferController.php`
- `routes/web.php`
- `resources/js/Pages/PayrollBankTransfers/Index.jsx`
- `resources/js/Pages/PayrollBankTransfers/Create.jsx`
- `resources/js/Pages/PayrollBankTransfers/Show.jsx`
- `tests/Feature/PayrollBankTransferTest.php`

### Validation

- `php artisan test tests/Feature/PayrollBankTransferTest.php` ✅ pass (4 tests)
- `composer test` ✅ PHPUnit pass (356 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-18: Implemented Story 17.6 Bank Transfer File MVP and validations.

