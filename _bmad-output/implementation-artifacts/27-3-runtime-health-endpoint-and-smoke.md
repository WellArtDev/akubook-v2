# Story 27.3: Runtime Health Endpoint and Smoke

**Story Key:** `27-3-runtime-health-endpoint-and-smoke`  
**Epic:** 27  
**Priority:** P1  
**Status:** ready-for-dev

## User Story
Sebagai operator aplikasi, saya ingin health endpoint dan smoke runtime yang cepat agar insiden route/database/service bisa dideteksi sebelum user terdampak.

## Acceptance Criteria
1. Ada endpoint health internal yang mengecek app + database connectivity.
2. Smoke check memanggil endpoint health dan route kritis.
3. CI gagal jika health endpoint atau smoke check gagal.
4. Output smoke jelas menyebut endpoint/route yang gagal.
5. Dokumen ringkas troubleshooting disediakan di artifact story.

## MVP Scope
- Endpoint `/healthz` sederhana (app + DB ping).
- Smoke command/test untuk health + route matrix inti.
- Integrasi ke workflow CI existing.

## Out of Scope
- External observability stack.
- Alert channel integrations (Slack/PagerDuty).
- Full synthetic monitoring.

## Definition of Done
- [ ] Health endpoint tersedia.
- [ ] Smoke health check tersedia.
- [ ] CI menjalankan health smoke.
- [ ] Troubleshooting notes tersedia.

## Dev Agent Record
### Completion Notes
- Pending implementation.

### File List
- Pending.

### Validation
- Pending.

## Change Log
- 2026-05-20: Story created (ready-for-dev).
