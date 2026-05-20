# Story 27.2: Index and Explain Guardrail

**Story Key:** `27-2-index-and-explain-guardrail`  
**Epic:** 27  
**Priority:** P0  
**Status:** review

## User Story
Sebagai backend engineer, saya ingin guardrail EXPLAIN/index untuk query kritis agar regresi performa terdeteksi saat perubahan schema/query.

## Acceptance Criteria
1. Ada daftar query kritis dengan EXPLAIN snapshot.
2. Guardrail mendeteksi query plan yang memburuk.
3. CI bisa menjalankan guardrail plan check.
4. Laporan menyebut query mana yang perlu index/tuning.
5. Dokumentasi minimal cara update baseline query plan tersedia.

## MVP Scope
- Script/command plan-check untuk subset query kritis.
- Baseline file plan sederhana.
- CI integration step ringan.

## Out of Scope
- Auto rewrite query.
- Auto migration index production.
- Multi-database optimizer tuning.

## Definition of Done
- [x] Plan guardrail tersedia.
- [x] Baseline query plan terdokumentasi.
- [x] CI menjalankan guardrail.
- [x] Laporan regresi plan tersedia.

## Dev Agent Record
### Completion Notes
- Menambahkan command `app:guard-query-plan` untuk menjalankan EXPLAIN pada query kritis sales, purchase, dan governance.
- Command menulis baseline otomatis saat baseline belum ada dan membandingkan scan count saat baseline tersedia.
- Menambahkan simulasi regresi `--simulate-regression` untuk membuktikan failure mode.
- CI governance gate kini menjalankan query plan guardrail.
- Baseline dapat di-refresh manual dengan `php artisan app:guard-query-plan --write-baseline`.

### File List
- `app/Console/Commands/GuardQueryPlanCommand.php`
- `tests/Feature/GuardQueryPlanCommandTest.php`
- `.github/workflows/governance-ci-gate.yml`
- `_bmad-output/implementation-artifacts/27-2-index-and-explain-guardrail.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation
- `php artisan test tests/Feature/GuardQueryPlanCommandTest.php` pass (2 tests, 6 assertions).
- `php artisan app:guard-query-plan` pass and writes/uses baseline path.
- `composer test` pass (483 tests, 483 passed, 2205 assertions).
- `npm run build` pass.

## Change Log
- 2026-05-20: Story created (ready-for-dev).
- 2026-05-20: Implemented query plan guardrail and moved story to review.
