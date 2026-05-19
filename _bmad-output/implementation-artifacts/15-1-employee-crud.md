# Story 15.1: Employee CRUD

**Story Key:** `15-1-employee-crud`  
**Priority:** P0 - Core HR Foundation  
**Status:** done

## User Story
As an HR Admin, I want to create, view, update, and deactivate employee data so that employee records are centralized and ready for assignment, leave, and payroll workflows.

## Acceptance Criteria
1. Employee master CRUD tersedia (create, list, detail, edit, deactivate).
2. Employee ID/NIK unik dan wajib.
3. Data minimum: employee_id, full_name, email, phone, join_date, employment_status, department, position.
4. Status employment mendukung minimal: `active`, `inactive`, `resigned`.
5. List memiliki filter search dan status.
6. Detail menampilkan metadata dasar (created/updated).

## MVP Scope
- Build model/migration/controller/pages/tests untuk Employee master.
- Soft delete/deactivate via status update (tanpa hard-delete wajib).
- Integrasi minimal ke `users` optional (belum wajib relasi auth employee).

## Out of Scope
- Attendance mapping.
- Leave entitlement.
- Payroll setup.
- Document attachments.

## Technical Notes
- Ikuti pola CRUD existing (`CashAccount`, `BankAccount`, `FixedAsset`).
- Gunakan Inertia React page pattern: Index/Create/Edit/Show.
- Validasi uniqueness untuk employee_id dan email.

## Definition of Done
- [x] Migration + Model Employee selesai.
- [x] Controller + routes CRUD selesai.
- [x] Inertia pages CRUD selesai.
- [x] Feature tests utama CRUD + validation lulus.
- [x] `composer test` dan `npm run build` dijalankan.

## Dev Agent Record
### Notes
- Implemented Employee CRUD master dengan status lifecycle `active|inactive|resigned`.
- Deactivate flow pakai update status via destroy endpoint, tanpa hard delete.
- Search + status filter tersedia di list page.

### File List
- _bmad-output/implementation-artifacts/15-1-employee-crud.md
- database/migrations/2026_05_18_084913_create_employees_table.php
- app/Models/Employee.php
- database/factories/EmployeeFactory.php
- app/Http/Controllers/EmployeeController.php
- resources/js/Pages/Employees/Index.jsx
- resources/js/Pages/Employees/Create.jsx
- resources/js/Pages/Employees/Edit.jsx
- resources/js/Pages/Employees/Show.jsx
- tests/Feature/EmployeeTest.php
- routes/web.php

### Validation
- `php artisan test tests/Feature/EmployeeTest.php` ✅ (5 tests)
- `composer test` ✅ PHPUnit pass (307 tests), wrapper masih exit code 1 (known issue)
- `npm run build` ✅ (warning Vite lama: esbuild -> oxc)

### Change Log
- 2026-05-18: Implemented Story 15.1 Employee CRUD MVP.

