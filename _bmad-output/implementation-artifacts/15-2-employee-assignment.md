# Story 15.2: Employee Assignment

**Story Key:** `15-2-employee-assignment`  
**Priority:** P0 - Core HR Structure  
**Status:** review

## User Story
As an HR Admin, I want to assign employees to branch/department/position with effective date so that organizational structure is tracked and ready for attendance/payroll modules.

## Acceptance Criteria
1. Employee assignment dapat dibuat untuk employee aktif.
2. Assignment menyimpan minimal: employee_id, branch_id, department, position, effective_date, status.
3. Satu employee hanya punya satu assignment `active` pada saat yang sama.
4. Saat assignment baru diaktifkan, assignment aktif lama otomatis jadi `inactive`.
5. List assignment punya filter search employee/status/branch.
6. Detail assignment tampilkan metadata dasar (created/updated).

## MVP Scope
- Buat tabel assignment terpisah (`employee_assignments`).
- CRUD basic: list/create/show/edit; deactivate via status.
- Integrasi relasi ke `employees` dan `branches`.

## Out of Scope
- Payroll grade/scale.
- Approval workflow assignment.
- Historical complex transfer workflow lintas policy.

## Technical Notes
- Ikuti pola CRUD existing (`Employees`, `CashAccounts`, `FixedAssets`).
- Inertia pages: Index/Create/Edit/Show.
- Gunakan transaksi saat aktivasi assignment baru agar status assignment aktif konsisten.

## Definition of Done
- [x] Migration + Model EmployeeAssignment selesai.
- [x] Controller + routes CRUD selesai.
- [x] Inertia pages CRUD selesai.
- [x] Feature tests assignment flow lulus.
- [x] `composer test` dan `npm run build` dijalankan.

## Dev Agent Record
### Notes
- Implemented assignment master dengan relasi `employees` dan `branches`.
- Aktivasi assignment baru otomatis menonaktifkan assignment aktif lama untuk employee sama.
- Deactivate action pakai update status `inactive` via destroy endpoint.

### File List
- _bmad-output/implementation-artifacts/15-2-employee-assignment.md
- database/migrations/2026_05_18_090007_create_employee_assignments_table.php
- app/Models/EmployeeAssignment.php
- app/Models/Employee.php
- database/factories/EmployeeAssignmentFactory.php
- app/Http/Controllers/EmployeeAssignmentController.php
- resources/js/Pages/EmployeeAssignments/Index.jsx
- resources/js/Pages/EmployeeAssignments/Create.jsx
- resources/js/Pages/EmployeeAssignments/Edit.jsx
- resources/js/Pages/EmployeeAssignments/Show.jsx
- tests/Feature/EmployeeAssignmentTest.php
- routes/web.php

### Validation
- `php artisan test tests/Feature/EmployeeAssignmentTest.php` ✅ (4 tests)
- `composer test` ✅ PHPUnit pass (311 tests), wrapper masih exit code 1 (known issue)
- `npm run build` ✅ (warning Vite lama: esbuild -> oxc)

### Change Log
- 2026-05-18: Implemented Story 15.2 Employee Assignment MVP.
