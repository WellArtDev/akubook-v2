# Story 29.3: Release Readiness Auto-Report v2

**Story Key:** `29-3-release-readiness-auto-report-v2`  
**Epic:** 29  
**Priority:** P1  
**Status:** review

## User Story
Sebagai Release Manager, saya ingin auto-report readiness yang merangkum test/build/smoke/guardrail agar keputusan release lebih cepat dan konsisten.

## Acceptance Criteria
1. Ada command yang mengumpulkan status terbaru: backend test, frontend build, UI smoke, migration guard, route permission guard, secret guard.
2. Output report menyajikan pass/fail per gate + timestamp.
3. Report memiliki keputusan final: `ready`, `warning`, `blocked`.
4. CI summary dapat memakai output report.
5. Report artifact tersimpan untuk audit historis.

## MVP Scope
- Command release readiness v2 menghasilkan JSON report.
- Rule sederhana untuk final decision.
- Integrasi dengan workflow summary.
- Feature test command output shape.

## Out of Scope
- Deployment otomatis.
- Approval workflow multi-level.
- Integrasi ticketing eksternal.

## Definition of Done
- [x] Command readiness report tersedia.
- [x] Final decision rule tervalidasi.
- [x] Artifact historis tersimpan.
- [x] Test output shape pass.

## Dev Agent Record
### Completion Notes
- Menambahkan command `app:release-readiness-report-v2` untuk menghasilkan report readiness terpadu dengan decision `ready|warning|blocked`.
- Menambahkan gate override option `--gate=key:status` untuk uji skenario warning/fail.
- Menyimpan artifact report di `_bmad-output/implementation-artifacts/release-readiness/release-readiness-latest.json`.
- Menambahkan step CI `Release readiness report v2` dan summary outcome.

### File List
- `app/Console/Commands/GenerateReleaseReadinessReportV2Command.php`
- `tests/Feature/GenerateReleaseReadinessReportV2CommandTest.php`
- `.github/workflows/governance-ci-gate.yml`
- `_bmad-output/implementation-artifacts/29-3-release-readiness-auto-report-v2.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation
- `php artisan test tests/Feature/GenerateReleaseReadinessReportV2CommandTest.php` pass (3 tests, 9 assertions).
- `php artisan app:release-readiness-report-v2` pass (`decision: warning` pada local artifact state).
- `composer test` pass (498 tests, 498 passed, 2269 assertions).
- `npm run build` pass.

## Change Log
- 2026-05-20: Story created (ready-for-dev).
- 2026-05-20: Implemented release readiness auto-report v2 and moved story to review.
