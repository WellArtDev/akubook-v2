# Story 29.2: Audit Log Retention Integrity Check

**Story Key:** `29-2-audit-log-retention-integrity-check`  
**Epic:** 29  
**Priority:** P1  
**Status:** review

## User Story
Sebagai Compliance Lead, saya ingin integrity check yang memverifikasi audit log retention berjalan konsisten agar bukti audit tidak hilang atau tertahan tanpa deteksi.

## Acceptance Criteria
1. Tersedia command integrity check yang membandingkan retention policy dan kondisi audit log aktual.
2. Integrity check melaporkan anomaly utama (stale records, missing execution, execution mismatch).
3. Command menghasilkan artifact summary yang konsisten per run.
4. Command menulis event audit sensitif untuk hasil integrity check.
5. Command dapat fail berdasarkan threshold severity (`--fail-on`).

## MVP Scope
- Command `app:check-audit-retention-integrity`.
- Artifact report JSON: `_bmad-output/implementation-artifacts/performance-baselines/audit-retention-integrity-latest.json`.
- Severity model: `low|medium|high` dengan fail threshold.
- Feature test untuk healthy path dan fail path (simulated high anomaly).

## Out of Scope
- Auto-remediation terhadap anomaly.
- Multi-entity retention integrity check di luar `audit_log`.
- Integrasi notifikasi eksternal (Slack/email).

## Definition of Done
- [x] Integrity command tersedia.
- [x] Artifact report JSON dihasilkan.
- [x] Sensitive audit event tercatat.
- [x] Fail threshold `--fail-on` berfungsi.
- [x] Feature tests healthy + anomaly pass.

## Dev Agent Record
### Completion Notes
- Menambahkan command integrity check untuk policy `audit_log` aktif, termasuk deteksi anomaly stale records, missing execution, dan execution mismatch.
- Menambahkan opsi `--simulate-high-anomaly` untuk verifikasi fail-path guardrail.
- Menambahkan artifact output terstruktur untuk baseline integrity retention.
- Menambahkan logging event sensitif `audit_retention.integrity_check` via `AuditLogger`.

### File List
- `app/Console/Commands/CheckAuditRetentionIntegrityCommand.php`
- `tests/Feature/CheckAuditRetentionIntegrityCommandTest.php`
- `_bmad-output/implementation-artifacts/29-2-audit-log-retention-integrity-check.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation
- `php artisan test tests/Feature/CheckAuditRetentionIntegrityCommandTest.php` pass (2 tests, 5 assertions).
- `composer test` pass (495 tests, 495 passed, 2260 assertions).
- `npm run build` pass.

## Change Log
- 2026-05-20: Story created (ready-for-dev).
- 2026-05-20: Implemented audit retention integrity check and moved story to review.
