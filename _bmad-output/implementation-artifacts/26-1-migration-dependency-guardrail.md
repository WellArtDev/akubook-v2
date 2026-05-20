# Story 26.1: Migration Dependency Guardrail

**Story Key:** `26-1-migration-dependency-guardrail`  
**Epic:** 26  
**Priority:** P0  
**Status:** ready-for-dev

## User Story
Sebagai Engineering Lead, saya ingin guardrail migrasi yang mendeteksi urutan migration rusak agar environment baru tidak gagal saat setup database.

## Acceptance Criteria
1. Ada command/test yang menjalankan fresh migration path pada database bersih.
2. Guardrail gagal jika migration child berjalan sebelum parent table tersedia.
3. Known fragile migrations (`sales_return_lines`, stock opname, payroll, attendance) tercakup dalam verifikasi.
4. CI menjalankan guardrail sebagai bagian quality gate.
5. Hasil failure memberi petunjuk migration yang perlu diperbaiki.

## MVP Scope
- Tambah test/command migration dependency smoke berbasis SQLite atau database test terisolasi.
- Tambah CI step ringan untuk migration dependency guard.
- Dokumentasikan daftar migration dependency risk yang ditemukan dari recovery lokal.

## Out of Scope
- Refactor seluruh histori migration lama.
- Production data migration.
- Cross-database matrix lengkap.

## Definition of Done
- [ ] Guardrail migration dependency tersedia.
- [ ] CI menjalankan guardrail.
- [ ] Test gagal pada migration order error.
- [ ] Dokumentasi risiko migration diperbarui.

## Dev Agent Record
### Completion Notes
- Pending implementation.

### File List
- Pending.

### Validation
- Pending.

## Change Log
- 2026-05-20: Story created (ready-for-dev).
