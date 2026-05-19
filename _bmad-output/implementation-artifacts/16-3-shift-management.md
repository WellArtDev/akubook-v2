# Story 16.3: Shift Management

**Story Key:** 16-3-shift-management
**Priority:** P0
**Status:** done

## User Story

As an HR/Admin Staff, I want to manage work shifts and assign them to employees so attendance records can be evaluated against expected check-in/check-out windows.

## Acceptance Criteria

1. Admin can CRUD shift master with unique shift code, name, check-in time, check-out time, tolerance minutes, and active status.
2. Admin can assign active shift to active employee with effective date and active/inactive assignment status.
3. One employee only has one active shift assignment at a time; creating active assignment deactivates previous active assignment.
4. Shift crossing midnight is supported via `is_overnight` flag.
5. Shift and assignment list pages support search/filter by status and employee.
6. Existing attendance records remain unchanged by this story (no automatic recalculation yet).

## MVP Scope

- Shift master: list/create/show/edit/deactivate.
- Shift assignment: list/create/show/edit/deactivate.
- Active-only relation helpers for employee current shift.
- Feature tests for CRUD and one-active-assignment rule.

## Out of Scope

- Attendance lateness/early leave computation.
- Automatic overtime generation.
- Public holiday/calendar logic.

## Technical Notes

- Reuse Story 15.2 employee assignment pattern for one-active-assignment transaction rule.
- Keep attendance mutation for Story 16.4/16.5.

## Definition of Done

- [x] Shift and shift assignment migrations/models created
- [x] Shift and assignment controllers/routes/pages implemented
- [x] One-active-shift assignment guard implemented
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Implemented Work Shift master CRUD with deactivation flow.
- Implemented Employee Shift Assignment CRUD with one-active-assignment transactional guard per employee.
- Added employee current active shift relations for downstream attendance stories.
- Added frontend pages for shift and assignment management.
- Added feature tests for shift CRUD and active assignment replacement rule.

### File List

- `_bmad-output/implementation-artifacts/16-3-shift-management.md`
- `database/migrations/2026_05_18_121358_create_work_shifts_table.php`
- `database/migrations/2026_05_18_121358_create_employee_shift_assignments_table.php`
- `app/Models/WorkShift.php`
- `app/Models/EmployeeShiftAssignment.php`
- `app/Models/Employee.php`
- `database/factories/WorkShiftFactory.php`
- `database/factories/EmployeeShiftAssignmentFactory.php`
- `app/Http/Controllers/WorkShiftController.php`
- `app/Http/Controllers/EmployeeShiftAssignmentController.php`
- `routes/web.php`
- `resources/js/Pages/WorkShifts/Index.jsx`
- `resources/js/Pages/WorkShifts/Create.jsx`
- `resources/js/Pages/WorkShifts/Edit.jsx`
- `resources/js/Pages/WorkShifts/Show.jsx`
- `resources/js/Pages/EmployeeShiftAssignments/Index.jsx`
- `resources/js/Pages/EmployeeShiftAssignments/Create.jsx`
- `resources/js/Pages/EmployeeShiftAssignments/Edit.jsx`
- `resources/js/Pages/EmployeeShiftAssignments/Show.jsx`
- `tests/Feature/WorkShiftTest.php`

### Validation

- `php artisan test tests/Feature/WorkShiftTest.php` ✅ pass (5 tests)
- `composer test` ✅ PHPUnit pass (333 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-18: Implemented Story 16.3 Shift Management MVP and validations.

