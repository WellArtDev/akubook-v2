# Story 24.2: Governance Reliability Jobs

**Story Key:** `24-2-governance-reliability-jobs`  
**Epic:** 24  
**Priority:** P1  
**Status:** review

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
- [x] Semua AC terpenuhi.
- [x] Command dan schedule tervalidasi.
- [x] Test command/job pass.
- [x] Story status di-update ke `review` setelah verifikasi.

## Dev Agent Record
### Completion Notes
- Menambahkan command `governance:run-reliability-jobs` untuk trigger sensitive alert dan compliance export pack dalam satu jalur manual/scheduler.
- Menambahkan schedule hourly untuk command reliability jobs di `routes/console.php`.
- Menambahkan audit log sensitif untuk outcome command: `governance.reliability_jobs.completed` dan `governance.reliability_jobs.failed`.
- Menjaga idempotensi window melalui behavior existing pada sensitive alert dan guard period pada compliance export pack (reuse pack existing untuk period sama).

### File List
- _bmad-output/implementation-artifacts/24-2-governance-reliability-jobs.md
- _bmad-output/implementation-artifacts/sprint-status.yaml
- app/Console/Commands/RunGovernanceReliabilityJobsCommand.php
- routes/console.php
- tests/Feature/GovernanceReliabilityJobsCommandTest.php

### Validation
- `php artisan test tests/Feature/GovernanceReliabilityJobsCommandTest.php` ✅ (3 tests, 11 assertions)
- `composer test` ✅ (432 tests, 1841 assertions)
- `npm run build` ✅

## Change Log
- 2026-05-19: Story created (ready-for-dev).
- 2026-05-19: Implemented governance reliability jobs command, schedule, tests, and moved story to review.
