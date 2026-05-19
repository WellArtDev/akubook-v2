# Story 24.2: Governance Reliability Jobs

**Story Key:** `24-2-governance-reliability-jobs`  
**Epic:** 24  
**Priority:** P1  
**Status:** ready-for-dev

## User Story
Sebagai Compliance Ops, saya ingin job terjadwal untuk governance artifact agar monitoring dan evidence tetap konsisten tanpa trigger manual.

## Acceptance Criteria
1. Ada scheduler untuk generate sensitive alert periodik.
2. Ada scheduler untuk generate compliance export pack periodik.
3. Job mencatat hasil sukses/gagal ke audit log sensitif.
4. Job idempotent untuk window yang sama.
5. Tersedia perintah manual (command) untuk trigger job bila diperlukan.

## MVP Scope
- Artisan command untuk trigger alert/export job.
- Penjadwalan command pada Kernel schedule.
- Audit log untuk outcome job.
- Feature test untuk command execution path.

## Out of Scope
- External notification channel (email/slack).
- Multi-tenant schedule policy.

## Definition of Done
- [ ] Semua AC terpenuhi.
- [ ] Command dan schedule tervalidasi.
- [ ] Test command/job pass.
- [ ] Story status di-update ke `review` setelah verifikasi.

## Dev Agent Record
### Completion Notes
- _TBD_

### File List
- _TBD_

### Validation
- _TBD_

## Change Log
- 2026-05-19: Story created (ready-for-dev).
