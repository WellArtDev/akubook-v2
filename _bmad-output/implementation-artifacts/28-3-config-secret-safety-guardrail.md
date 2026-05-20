# Story 28.3: Config and Secret Safety Guardrail

**Story Key:** `28-3-config-secret-safety-guardrail`  
**Epic:** 28  
**Priority:** P1  
**Status:** review

## User Story
Sebagai Engineering Lead, saya ingin guardrail config/secret agar credential atau konfigurasi berbahaya tidak ikut commit atau masuk CI.

## Acceptance Criteria
1. Guardrail memeriksa file konfigurasi repo untuk pola secret umum.
2. Guardrail mengabaikan placeholder aman di `.env.example` dan test fixtures.
3. CI menjalankan guardrail dan gagal jika secret-like value ditemukan.
4. Output menyebut file dan jenis temuan tanpa mencetak secret penuh.
5. Dokumentasi cara menambah allowlist tersedia.

## MVP Scope
- Script/command secret/config scan ringan.
- Pattern untuk API key/token/password/private key umum.
- Allowlist aman untuk placeholder.
- Feature/unit tests untuk pass/fail path.

## Out of Scope
- Full DLP engine.
- External secret manager integration.
- Git history scanning.

## Definition of Done
- [x] Guardrail scan tersedia.
- [x] CI menjalankan scan.
- [x] Tests cover pass/fail/allowlist.
- [x] Story status ke review setelah validasi.

## Dev Agent Record
### Completion Notes
- Added `app:guard-config-secrets` command to scan config, workflow, package, and env example files for secret-like committed values.
- Added `config/secret_guard.php` allowlist for safe placeholders and patterns.
- Added CI `Config secret guardrail` step with summary output.
- Added tests for safe placeholder pass path and secret-like failure path.
- Guard output masks values and reports file, line, and key only.

### File List
- `app/Console/Commands/GuardConfigSecretsCommand.php`
- `config/secret_guard.php`
- `tests/Feature/GuardConfigSecretsCommandTest.php`
- `.github/workflows/governance-ci-gate.yml`
- `_bmad-output/implementation-artifacts/28-3-config-secret-safety-guardrail.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation
- `php artisan test tests/Feature/GuardConfigSecretsCommandTest.php` pass (2 tests, 4 assertions).
- `php artisan app:guard-config-secrets` pass.
- `composer test` pass (490 tests, 490 passed, 2248 assertions).
- `npm run build` pass.

## Change Log
- 2026-05-20: Story created (ready-for-dev).
- 2026-05-20: Implemented config secret safety guardrail and moved story to review.
