# Story 26.3: Critical Route Coverage Expansion

**Story Key:** `26-3-critical-route-coverage-expansion`  
**Epic:** 26  
**Priority:** P1  
**Status:** ready-for-dev

## User Story
Sebagai QA/Engineering, saya ingin smoke coverage diperluas ke modul sales, purchase, finance, HR, dan governance agar blank page/runtime error cepat tertangkap.

## Acceptance Criteria
1. Smoke test mencakup route utama sales dan purchase yang baru selesai.
2. Smoke test mencakup finance/accounting dan HR dashboard/menu penting.
3. Console error dan HTTP 500 pada route kritis menggagalkan test.
4. Daftar route kritis mudah diperbarui.
5. CI summary menunjukkan route mana yang gagal.

## MVP Scope
- Refactor `tests/e2e/critical-routes.spec.js` menjadi route matrix.
- Tambah route sales/purchase/customer/supplier/payment/report/dashboard utama.
- Tambah assertion judul/body visible dan console-error-free.
- Tetap Chromium-only.

## Out of Scope
- Form submission E2E penuh.
- Visual regression screenshot baseline.
- Browser matrix lengkap.

## Definition of Done
- [ ] Route matrix smoke tersedia.
- [ ] Sales/purchase/finance/governance route utama tercakup.
- [ ] `npm run smoke:ui` pass lokal.
- [ ] CI tetap menjalankan smoke.

## Dev Agent Record
### Completion Notes
- Pending implementation.

### File List
- Pending.

### Validation
- Pending.

## Change Log
- 2026-05-20: Story created (ready-for-dev).
