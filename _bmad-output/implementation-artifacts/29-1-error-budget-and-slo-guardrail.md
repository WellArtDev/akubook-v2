# Story 29.1: Error Budget & SLO Guardrail

**Story Key:** `29-1-error-budget-and-slo-guardrail`  
**Epic:** 29  
**Priority:** P0  
**Status:** review

## User Story
Sebagai Engineering Lead, saya ingin guardrail SLO dan error budget agar stabilitas runtime bisa diukur dan release berisiko bisa diblokir sebelum produksi.

## Acceptance Criteria
1. Ada definisi SLO minimum untuk endpoint kritis (`/healthz`, `/dashboard`, `/role-dashboard`, `/governance-dashboard-v2`).
2. Error budget dihitung dari hasil smoke/health check terbaru.
3. Command/report menandai status `healthy`, `warning`, atau `breach`.
4. CI dapat mengeksekusi guardrail dan gagal saat status `breach`.
5. Output mencantumkan metrik per endpoint dan rekomendasi singkat.

## MVP Scope
- Command SLO guardrail berbasis hasil smoke + health endpoint.
- Konfigurasi threshold latency/error sederhana.
- Integrasi ke workflow CI existing.
- Ringkasan status SLO pada artifact JSON.

## Out of Scope
- Integrasi APM eksternal.
- SLO multi-region.
- Auto-remediation.

## Definition of Done
- [x] Guardrail SLO/error budget tersedia.
- [x] CI step SLO guardrail aktif.
- [x] Mode breach tervalidasi gagal.
- [x] Dokumentasi threshold baseline diperbarui.

## Dev Agent Record
### Completion Notes
- Added `app:guard-slo-error-budget` command to evaluate smoke result JSON against configured endpoint SLO thresholds.
- Added `config/slo.php` for endpoint target/warning latency and artifact path.
- Expanded Playwright smoke test to emit `test-results/slo-smoke-results.json` with per-endpoint status, duration, console error count, and ok flag.
- Added CI step `SLO error budget guardrail` after UI smoke to block release on breach.
- Command writes latest report to `_bmad-output/implementation-artifacts/performance-baselines/slo-error-budget-latest.json`.

### File List
- `.github/workflows/governance-ci-gate.yml`
- `app/Console/Commands/GuardSloErrorBudgetCommand.php`
- `config/slo.php`
- `tests/Feature/GuardSloErrorBudgetCommandTest.php`
- `tests/e2e/critical-routes.spec.js`
- `_bmad-output/implementation-artifacts/29-1-error-budget-and-slo-guardrail.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation
- `php artisan test tests/Feature/GuardSloErrorBudgetCommandTest.php` pass (3 tests, 7 assertions).
- `npm run smoke:ui` pass (2 Playwright tests).
- `php artisan app:guard-slo-error-budget` pass (`healthy`, error budget 100%).
- `composer test` pass (493 tests, 493 passed, 2255 assertions).
- `npm run build` pass.

## Change Log
- 2026-05-20: Story created (ready-for-dev).
- 2026-05-20: Implemented SLO/error budget guardrail and moved story to review.
