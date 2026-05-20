# Story 27.1: Query Performance Baseline v2

**Story Key:** `27-1-query-performance-baseline-v2`  
**Epic:** 27  
**Priority:** P0  
**Status:** ready-for-dev

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
- [ ] Benchmark command v2 tersedia.
- [ ] Artifact JSON baseline dihasilkan.
- [ ] Threshold + rekomendasi tersedia.
- [ ] Validasi command pass.

## Dev Agent Record
### Completion Notes
- Pending implementation.

### File List
- Pending.

### Validation
- Pending.

## Change Log
- 2026-05-20: Story created (ready-for-dev).
