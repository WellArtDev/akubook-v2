# Epic 13 Retrospective: Tax Management & Compliance

**Status:** done  
**Date:** 2026-05-18

## Epic Summary
Epic 13 delivered tax configuration, calculation, Faktur Pajak, e-Faktur export, and tax reporting MVP.

| Story | Status | Outcome |
| --- | --- | --- |
| 13.1 Tax Configuration | review | Tax config CRUD, default per tax type, account mapping. |
| 13.2 Tax Calculation | review | Tax calculation page/API, inclusive/exclusive logic, persisted history. |
| 13.3 Faktur Pajak | review | Faktur creation from Sales Invoice, issue/cancel workflow. |
| 13.4 E-Faktur Export | review | Export batch + line snapshots, CSV generation/download. |
| 13.5 Tax Reporting | review | Period tax summary and detail rows for PPN out/in/withholding. |

## Outcomes
- Tax master data now exists via `tax_configurations`.
- Tax calculations can be tested independently before deeper transaction integration.
- Faktur Pajak records are separated from source Sales Invoice data.
- E-Faktur export stores historical snapshots, not live mutable source data.
- Tax reporting gives period-level PPN Output, PPN Input, Withholding, and Net VAT.

## Validation Evidence
- Latest targeted Story 13 feature tests passed.
- Latest `composer test` PHPUnit passed 286 tests, but composer wrapper still returned known exit code 1.
- Latest `npm run build` passed with existing Vite `esbuild` warning.

## What Went Well
- Reused existing Laravel/Inertia CRUD and report patterns.
- Snapshot strategy reduced risk of source transaction mutation.
- Tests covered calculation rules, issue/export/report summaries.
- Story chain stayed coherent: config -> calculation -> faktur -> export -> report.

## Challenges
- E-Faktur MVP is CSV-only, not official DJP integration.
- Tax reporting is summary-level, not full SPT/e-Faktur compliance package.
- Tax calculation is not yet wired deeply into all sales/purchase transaction creation flows.
- Persistent repo issues remain: composer wrapper exit code and Vite warning.

## Technical Debt / Follow-ups
1. Wire default tax configurations into Sales Invoice and Purchase Invoice calculations.
2. Add official e-Faktur export format if required by deployment context.
3. Add tax reporting export and SPT-style sections.
4. Add reversal/audit rules for cancelled Faktur Pajak and exported batches.
5. Fix composer wrapper returning code 1 after passing PHPUnit.
6. Replace deprecated Vite `esbuild` config with `oxc` path.

## Lessons Learned
- Keep tax records as snapshots where reporting/export accuracy matters.
- Persist calculation history before reporting; reports then become deterministic.
- Default-per-tax-type rule prevents ambiguous tax lookup.
- Regulatory features need clear separation between MVP internal reporting and official submission artifacts.

## Next Epic Prep
Epic 14 Fixed Assets should reuse the same pattern:
- Master setup first.
- Transaction/calculation second.
- Report/export last.
- Keep snapshot records for audit-sensitive outputs.
