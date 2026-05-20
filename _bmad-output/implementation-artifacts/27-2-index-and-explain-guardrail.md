# Story 27.2: Index and Explain Guardrail

**Story Key:** `27-2-index-and-explain-guardrail`  
**Epic:** 27  
**Priority:** P0  
**Status:** ready-for-dev

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
- [ ] Plan guardrail tersedia.
- [ ] Baseline query plan terdokumentasi.
- [ ] CI menjalankan guardrail.
- [ ] Laporan regresi plan tersedia.

## Dev Agent Record
### Completion Notes
- Pending implementation.

### File List
- Pending.

### Validation
- Pending.

## Change Log
- 2026-05-20: Story created (ready-for-dev).
