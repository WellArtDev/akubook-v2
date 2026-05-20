# Story 26.2: Deterministic Local Bootstrap

**Story Key:** `26-2-deterministic-local-bootstrap`  
**Epic:** 26  
**Priority:** P0  
**Status:** ready-for-dev

## User Story
Sebagai developer, saya ingin satu command bootstrap lokal yang menyiapkan database, admin user, role, dan smoke-ready state agar dev server bisa langsung dipakai.

## Acceptance Criteria
1. Command bootstrap membuat atau memperbarui admin `admin@akubook.com` dengan password dev yang terdokumentasi.
2. Command memastikan role Administrator dan permission minimum tersedia.
3. Command idempotent dan aman dijalankan berulang.
4. Command memverifikasi tabel governance/sales/purchase utama tersedia setelah migration.
5. Dokumentasi singkat cara menjalankan bootstrap tersedia di story artifact.

## MVP Scope
- Artisan command `app:bootstrap-local` atau sejenis.
- Admin/role/permission idempotent setup.
- Schema availability check untuk critical modules.
- Feature test command path.

## Out of Scope
- Seeder data demo lengkap semua modul.
- Production credential handling.
- Multi-tenant bootstrap.

## Definition of Done
- [ ] Bootstrap command tersedia.
- [ ] Admin dan role dibuat idempotent.
- [ ] Critical schema check berjalan.
- [ ] Feature test command pass.

## Dev Agent Record
### Completion Notes
- Pending implementation.

### File List
- Pending.

### Validation
- Pending.

## Change Log
- 2026-05-20: Story created (ready-for-dev).
