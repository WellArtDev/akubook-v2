# Epic 14 Retrospective

**Epic:** 14 - Fixed Assets Management  
**Status:** done  
**Date:** 2026-05-18

## Story Status Summary

| Story | Title | Status | Highlight |
|---|---|---|---|
| 14.1 | Asset Registration | review | Fixed asset master + account mapping |
| 14.2 | Depreciation Calculation | review | Period run straight-line + idempotent upsert |
| 14.3 | Depreciation Journal | review | Period posting to journal_entries + idempotent rerun |
| 14.4 | Asset Disposal | review | Disposal flow + gain/loss journal + mark asset disposed |
| 14.5 | Asset Reports | review | Asset register + latest depreciation + disposal summary |

## Outcomes

- End-to-end fixed asset lifecycle tersedia: register -> depreciation run -> depreciation journal -> disposal -> reporting.
- Journal integration sudah terhubung ke general ledger untuk depreciation dan disposal.
- Asset status lifecycle konsisten (`active`, `inactive`, `disposed`) dan tercermin di report.

## Validation Evidence

- Targeted feature tests story 14.1-14.5 pass.
- Latest `composer test`: PHPUnit pass **302 tests / 953 assertions**, wrapper masih exit code 1 (known issue).
- Latest `npm run build`: pass, warning lama Vite `esbuild` deprecated by `vite:react-babel` (migrate ke `oxc`).

## What Went Well

- Reuse pattern controller + Inertia + feature test cepat dan konsisten.
- Idempotent logic untuk depreciation run dan journal run menekan duplicate posting.
- Disposal posting otomatis update status aset ke disposed.
- Report read-only menjaga source records tidak berubah.

## Challenges / Gaps

- Belum ada reversal flow untuk disposal/depreciation journal.
- Belum ada multi-book depreciation (fiscal vs commercial).
- Asset report belum ada export (PDF/Excel).
- Composer wrapper issue dan warning Vite masih terbawa lintas epic.

## Technical Debt / Action Items

1. Tambah reversal workflow untuk depreciation journal dan disposal posting.
2. Tambah schedule job untuk period-end depreciation run + monitoring.
3. Tambah export/reporting output (Excel/PDF) untuk asset register dan depreciation movement.
4. Tambah guardrail test untuk edge case residual value >= acquisition cost.
5. Fix `composer test` wrapper exit-code mismatch.
6. Migrate Vite config dari `esbuild` option ke `oxc`.

## Epic 15 Preparation Notes

- Gunakan pola status lifecycle + audit fields dari Epic 14 untuk HR modules (employee/leave/attendance).
- Reuse report/table filtering pattern untuk attendance and payroll reporting.
- Pastikan resolver untuk numbering/idempotent process dipakai juga di payroll batch.
