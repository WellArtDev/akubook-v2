# Epic 15 Retrospective: Employee & HR Management

Status: done
Date: 2026-05-18

## Epic Summary

| Story | Status | Outcome |
| --- | --- | --- |
| 15.1 Employee CRUD | review | Employee master data, status lifecycle, search/filter, detail metadata. |
| 15.2 Employee Assignment | review | Branch/department/position assignment with one active assignment per employee. |
| 15.3 Leave Management | review | Leave request creation, day calculation, approve/reject/cancel workflow. |
| 15.4 Employee Documents | review | Employee document metadata tracking, expiry visibility, deactivate workflow. |

## Outcomes
- HR master foundation completed: employees, assignments, leave requests, and employee documents.
- Employee lifecycle can now connect to branch assignment, leave workflow, and document compliance.
- All modules use consistent Laravel/Inertia CRUD patterns and feature tests.

## Validation Evidence
- Latest targeted tests passed for Employee, Employee Assignment, Leave Request, and Employee Document modules.
- Latest `composer test` PHPUnit result: 320 tests / 996 assertions passed; composer wrapper still exits code 1 after pass.
- Latest `npm run build` passed with existing Vite warning: `esbuild` option deprecated by `vite:react-babel`, use `oxc`.

## What Went Well
- Existing CRUD and workflow patterns made HR modules fast and consistent.
- One-active-assignment rule was handled transactionally.
- Leave workflow covers core approval state transitions without touching payroll.
- Document module chose metadata-first scope, avoiding premature file storage complexity.

## Challenges / Gaps
- Leave balance policy not implemented.
- Employee document attachment upload/storage not implemented.
- No notification/reminder for document expiry or leave approval.
- HR modules not yet integrated with attendance/payroll.
- Composer wrapper code 1 and Vite warning remain unresolved platform debt.

## Action Items
1. Add leave balance policy table and accrual rules before payroll dependency.
2. Add employee document file upload/versioning after storage decision.
3. Add reminders for expiring documents and pending leave approvals.
4. Connect employee assignment to attendance/shift modules in Epic 16.
5. Fix composer wrapper exit code after PHPUnit pass.
6. Migrate Vite config from deprecated `esbuild` option to `oxc`.

## Epic 16 Prep
- Use Employee and active assignment as base context for attendance records.
- Decide whether attendance belongs to branch assignment, shift, or direct employee only.
- Keep audit fields consistent with prior HR modules.
