# Story 27.1: Query Performance Baseline v2

**Story Key:** `27-1-query-performance-baseline-v2`  
**Epic:** 27  
**Priority:** P0  
**Status:** review

## User Story
Sebagai Engineering Lead, saya ingin baseline performa query lintas modul agar bottleneck utama terukur sebelum optimasi lanjutan.

## Acceptance Criteria
1. Ada command yang mengukur latency query kritis sales/purchase/governance.
2. Hasil benchmark tersimpan sebagai artifact JSON dengan timestamp dan periode.
3. Ada threshold per kategori query (fast/slow/critical).
4. Command idempotent dan bisa dijalankan berulang.
5. Output menampilkan rekomendasi prioritas optimasi.

## MVP Scope
- Benchmark command v2 untuk query agregasi utama.
- Artifact output ke `_bmad-output/implementation-artifacts/performance-baselines/`.
- Threshold dan summary rekomendasi sederhana.

## Out of Scope
- Auto index creation.
- Database tuning production.
- Distributed load testing.

## Definition of Done
- [x] Benchmark command v2 tersedia.
- [x] Artifact JSON baseline dihasilkan.
- [x] Threshold + rekomendasi tersedia.
- [x] Validasi command pass.

## Dev Agent Record
### Completion Notes
- Added `app:benchmark-query-baseline-v2` command for cross-module sales, purchase, and governance query latency baselines.
- Command writes deterministic JSON artifacts under `_bmad-output/implementation-artifacts/performance-baselines/`.
- Added fast/slow/critical threshold options and recommendation output by module.
- Added feature tests for artifact generation and invalid threshold failure.

### File List
- `app/Console/Commands/RunQueryPerformanceBaselineV2Command.php`
- `tests/Feature/RunQueryPerformanceBaselineV2CommandTest.php`
- `_bmad-output/implementation-artifacts/27-1-query-performance-baseline-v2.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation
- `php artisan test tests/Feature/RunQueryPerformanceBaselineV2CommandTest.php` pass (2 tests, 10 assertions).
- `composer test` pass (481 tests, 481 passed, 2199 assertions).
- `npm run build` pass.

## Change Log
- 2026-05-20: Story created (ready-for-dev).
- 2026-05-20: Implemented query performance baseline v2 and moved story to review.
