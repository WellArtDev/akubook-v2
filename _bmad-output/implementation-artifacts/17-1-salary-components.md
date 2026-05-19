# Story 17.1: Salary Components

**Story Key:** 17-1-salary-components
**Priority:** P0
**Status:** review

## User Story

As an HR/Payroll Admin, I want to manage salary component master data so payroll formulas can reference consistent earning and deduction components.

## Acceptance Criteria

1. Admin can CRUD salary component with unique code and name.
2. Salary component supports type (`earning|deduction`) and calculation method (`fixed|percentage`).
3. Salary component supports taxable flag and active status.
4. Salary component can optionally map to COA account for payroll journal preparation.
5. List page supports search and filters by type and active status.
6. Existing payroll process remains untouched in this story (master-only).

## MVP Scope

- Salary component master CRUD pages.
- Validation: unique code, valid type/method, percentage in 0-100.
- Optional `account_id` mapping to active detail account.
- Feature tests for CRUD and validation.

## Out of Scope

- Payroll run using components.
- Formula engine beyond fixed/percentage metadata.
- Journal posting and payslip generation.

## Technical Notes

- Reuse CRUD patterns from `TaxConfiguration`, `CashAccount`, and `BankAccount`.
- Keep this story as master-data foundation for Story 17.2.

## Definition of Done

- [x] Salary component migration/model/factory created
- [x] Salary component controller/routes/pages implemented
- [x] Feature tests added and passed
- [x] `composer test` executed
- [x] `npm run build` executed

## Dev Agent Record

### Completion Notes

- Implemented salary component master CRUD with unique code and earning/deduction type.
- Added fixed/percentage calculation metadata with normalization guard (`fixed` zeroes percentage, `percentage` zeroes amount).
- Added optional COA account mapping for future payroll journal use.
- Added search/type/status filtering and detail page.
- Added feature tests for CRUD and key validations.

### File List

- `_bmad-output/implementation-artifacts/17-1-salary-components.md`
- `database/migrations/2026_05_18_131020_create_salary_components_table.php`
- `app/Models/SalaryComponent.php`
- `database/factories/SalaryComponentFactory.php`
- `app/Http/Controllers/SalaryComponentController.php`
- `resources/js/Pages/SalaryComponents/Index.jsx`
- `resources/js/Pages/SalaryComponents/Create.jsx`
- `resources/js/Pages/SalaryComponents/Edit.jsx`
- `resources/js/Pages/SalaryComponents/Show.jsx`
- `routes/web.php`
- `tests/Feature/SalaryComponentTest.php`

### Validation

- `php artisan test tests/Feature/SalaryComponentTest.php` ✅ pass (4 tests)
- `composer test` ✅ PHPUnit pass (344 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-18: Implemented Story 17.1 Salary Components MVP and validations.
