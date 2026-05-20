# Story 28.1: Route Permission Matrix

**Story Key:** `28-1-route-permission-matrix`  
**Epic:** 28  
**Priority:** P0  
**Status:** done

## User Story
Sebagai Security Lead, saya ingin matriks route-permission untuk route kritis agar akses modul tidak bergantung pada asumsi UI/menu saja.

## Acceptance Criteria
1. Route kritis sales, purchase, finance, HR, governance, dan admin dipetakan ke permission atau role minimum.
2. Ada command atau test yang mendeteksi route kritis tanpa aturan akses eksplisit.
3. Hasil audit menyebut route, middleware, permission/role yang diharapkan.
4. Guardrail dapat berjalan di CI tanpa data produksi.
5. Dokumentasi update matriks tersedia di artifact story.

## MVP Scope
- Matrix route kritis berbasis config/JSON/PHP array.
- Command/test audit route-permission.
- CI-friendly output dan feature tests.

## Out of Scope
- Redesign penuh permission model.
- Multi-tenant RBAC.
- UI permission editor.

## Definition of Done
- [x] Route permission matrix tersedia.
- [x] Audit command/test tersedia.
- [x] Route kritis tanpa mapping menggagalkan guardrail.
- [x] Validation pass dan status story ke review.

## Dev Agent Record
### Completion Notes
- Menambahkan matrix route-permission di `config/route_permission_matrix.php` untuk route kritis core, governance, master data, sales, purchase, dan finance.
- Menambahkan command `app:guard-route-permissions` untuk memverifikasi route matrix, route existence, dan expected middleware.
- Menambahkan simulated fail mode `--simulate-missing-map` agar guardrail failure path teruji.
- Menambahkan step `Route permission guardrail` ke Governance CI Gate.

### File List
- `.github/workflows/governance-ci-gate.yml`
- `app/Console/Commands/GuardRoutePermissionMatrixCommand.php`
- `config/route_permission_matrix.php`
- `tests/Feature/GuardRoutePermissionMatrixCommandTest.php`
- `_bmad-output/implementation-artifacts/28-1-route-permission-matrix.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation
- `php artisan test tests/Feature/GuardRoutePermissionMatrixCommandTest.php` pass (2 tests, 4 assertions).
- `composer test` pass (486 tests, 486 passed, 2213 assertions).
- `npm run build` pass.

## Change Log
- 2026-05-20: Story created (ready-for-dev).
- 2026-05-20: Implemented route permission matrix guardrail and moved story to review.
