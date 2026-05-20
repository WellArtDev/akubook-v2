# Story 25.1: Critical Route Smoke Automation

**Epic:** 25 - UI Hardening & Smoke Automation  
**Story ID:** 25.1  
**Story Key:** 25-1-critical-route-smoke-automation  
**Status:** done  
**Created:** 2026-05-19  
**Priority:** P0 (Critical)

---

## User Story

**Sebagai** Engineering Team  
**Saya ingin** CI menjalankan smoke test route kritikal setelah build  
**Sehingga** regression runtime tertangkap cepat sebelum merge

---

## Acceptance Criteria

- CI mengeksekusi smoke test login + route kritikal (`/dashboard`, `/role-dashboard`, `/governance-dashboard-v2`, `/suppliers`)
- Smoke gagal jika ada console error
- Smoke jalan stabil di CI dengan setup DB deterministic
- Local operator dapat menjalankan smoke dengan `npm run smoke:ui`

---

## MVP Scope

- Playwright config untuk critical route smoke.
- E2E spec login + critical route traversal.
- CI workflow step untuk install browser dan menjalankan smoke.
- CI database preparation memakai SQLite + migration + admin user creation.
- Sprint skeleton Epic 25.

## Out of Scope

- Full visual regression.
- Browser matrix beyond Chromium.
- Per-module deep workflow E2E.

---

## Definition of Done

- [x] Playwright config + smoke spec tersedia
- [x] Workflow CI menyiapkan DB + user login smoke
- [x] Smoke route kritikal jalan di local
- [x] `composer test` dan `npm run build` pass
- [x] Story status update ke review

---

## Dev Agent Record

### Completion Notes

- Added Playwright smoke config and critical authenticated route spec.
- Added `npm run smoke:ui` script.
- Extended governance CI gate with Playwright browser installation and UI smoke step.
- Added CI smoke DB setup using SQLite migration and admin user creation before smoke.
- Added Epic 25 sprint skeleton.

### File List

- `.github/workflows/governance-ci-gate.yml`
- `package.json`
- `playwright.config.js`
- `tests/e2e/critical-routes.spec.js`
- `_bmad-output/implementation-artifacts/25-1-critical-route-smoke-automation.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation

- `npm run smoke:ui` passed: 1 test, Chromium.
- `composer test` passed: 474 tests, 474 passed, 2171 assertions.
- `npm run build` passed.

### Change Log

- 2026-05-19: Implemented critical route smoke automation and moved story to review.
