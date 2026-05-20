# Story 28.2: Admin Activity Review Dashboard

**Story Key:** `28-2-admin-activity-review-dashboard`  
**Epic:** 28  
**Priority:** P1  
**Status:** ready-for-dev

## User Story
Sebagai Compliance Officer, saya ingin dashboard aktivitas admin agar perubahan sensitif bisa ditinjau cepat.

## Acceptance Criteria
1. Dashboard menampilkan audit log aktivitas admin/sensitive action terbaru.
2. Filter tersedia untuk actor, event, entity, sensitivity level, dan tanggal.
3. KPI menampilkan jumlah sensitive action, high severity, dan failed/blocked action jika tersedia.
4. Detail aktivitas menampilkan metadata tanpa membocorkan secret.
5. Halaman terlindungi auth dan tercakup smoke test.

## MVP Scope
- Controller + Inertia page admin activity review.
- Query dari `audit_logs` dan sensitive fields existing.
- Filter dasar + KPI cards + table.
- Feature test page shape.

## Out of Scope
- Real-time alert stream.
- SIEM integration.
- Advanced anomaly detection ML.

## Definition of Done
- [ ] Admin activity dashboard tersedia.
- [ ] Filter dan KPI dasar bekerja.
- [ ] Metadata aman ditampilkan.
- [ ] Tests/build pass dan story ke review.

## Dev Agent Record
### Completion Notes
- Pending implementation.

### File List
- Pending.

### Validation
- Pending.

## Change Log
- 2026-05-20: Story created (ready-for-dev).
