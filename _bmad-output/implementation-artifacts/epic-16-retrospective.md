# Epic 16 Retrospective: Attendance Management & ZKTeco Integration

**Status:** done
**Date:** 2026-05-18

## Epic Summary

Epic 16 completed attendance foundation from manual attendance entry through ZKTeco log ingestion, shift assignment, overtime tracking, and read-only attendance reports.

| Story | Status | Outcome |
| --- | --- | --- |
| 16.1 Online Attendance | review | Manual check-in/check-out, work hours, attendance filters |
| 16.2 ZKTeco Integration | review | Device registry, raw log import, employee mapping, attendance sync |
| 16.3 Shift Management | review | Shift master and one-active-shift assignment per employee |
| 16.4 Overtime Tracking | review | Overtime records, hours calculation, overlap guard, workflow |
| 16.5 Attendance Reports | review | Attendance detail/summary report with approved overtime totals |

## Outcomes

- HR attendance lifecycle now has core entities: `attendance_records`, `zkteco_devices`, `zkteco_attendance_logs`, `work_shifts`, `employee_shift_assignments`, `overtime_records`.
- ZKTeco MVP is hardware-free but import-safe: source key dedupe, employee identifier mapping, source log audit.
- Shift and overtime modules prepare clean inputs for payroll without mutating payroll data.
- Attendance reporting gives period-level present/incomplete/absent/work/overtime visibility.

## Validation Evidence

- Targeted feature tests passed for attendance, ZKTeco integration, shift management, overtime tracking, and attendance reports.
- Latest `composer test`: PHPUnit passed 340 tests / 1077 assertions; composer wrapper still exits code 1 after pass.
- Latest `npm run build`: passed; existing Vite warning remains (`esbuild` option deprecated by `vite:react-babel`, use `oxc`).

## What Went Well

- Existing Employee master and assignment patterns made HR extensions fast and consistent.
- Source log dedupe prevented duplicate ZKTeco imports.
- One-active-shift rule reused one-active-assignment transaction pattern.
- Overtime overlap guard catches a common payroll-prep data issue early.
- Report layer stayed read-only and did not alter source attendance/overtime data.

## Challenges / Gaps

- ZKTeco integration is manual import MVP, not real SDK/network polling.
- Shift assignments do not yet calculate lateness/early leave on attendance records.
- Overtime is manually created; no automatic generation from attendance + shift rules.
- Attendance report has no export and no payroll amount calculation.
- Composer wrapper code 1 and Vite warning remain unresolved platform debt.

## Action Items

1. Add ZKTeco polling/import scheduler when hardware/network access exists.
2. Add attendance rule engine: lateness, early leave, absence, overnight shift handling.
3. Add automatic overtime suggestion from checkout after shift end.
4. Add attendance report export before payroll rollout.
5. Fix composer test wrapper so green PHPUnit returns green process.
6. Migrate Vite React plugin config from deprecated `esbuild` option to `oxc`.

## Next Epic 17 Prep

- Payroll can now consume employees, assignments, attendance records, approved overtime, and attendance report summary.
- Before payroll calculation, define salary component model, overtime rate policy, absence deduction policy, and PPh21 integration boundary.
