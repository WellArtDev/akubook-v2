# Story 28.1: Route Permission Matrix

**Story Key:** `28-1-route-permission-matrix`  
**Epic:** 28  
**Priority:** P0  
**Status:** ready-for-dev

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
- [ ] Route permission matrix tersedia.
- [ ] Audit command/test tersedia.
- [ ] Route kritis tanpa mapping menggagalkan guardrail.
- [ ] Validation pass dan status story ke review.

## Dev Agent Record
### Completion Notes
- Pending implementation.

### File List
- Pending.

### Validation
- Pending.

## Change Log
- 2026-05-20: Story created (ready-for-dev).
