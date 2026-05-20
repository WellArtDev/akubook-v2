# Story 28.3: Config and Secret Safety Guardrail

**Story Key:** `28-3-config-secret-safety-guardrail`  
**Epic:** 28  
**Priority:** P1  
**Status:** ready-for-dev

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
- [ ] Guardrail scan tersedia.
- [ ] CI menjalankan scan.
- [ ] Tests cover pass/fail/allowlist.
- [ ] Story status ke review setelah validasi.

## Dev Agent Record
### Completion Notes
- Pending implementation.

### File List
- Pending.

### Validation
- Pending.

## Change Log
- 2026-05-20: Story created (ready-for-dev).
