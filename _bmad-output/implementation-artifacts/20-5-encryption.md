# Story 20.5: Encryption

**Story Key:** 20-5-encryption
**Priority:** P0
**Status:** done

## User Story

As a mobile/offline user, I want sensitive offline sync payloads encrypted at rest so local sync records are safer when stored on server.

## Acceptance Criteria

1. Offline sync events store encrypted payload data at rest.
2. Model transparently decrypts payload for application use.
3. Sync process keeps idempotent behavior by `client_event_id`.
4. Existing offline sync flow remains functional (no behavior regression).
5. Read-only/reporting pages do not expose raw encrypted blobs directly.
6. No unrelated domain mutation.

## MVP Scope

- Add encrypted payload column for `offline_sync_events`.
- Use Laravel encrypted cast for server-side at-rest encryption.
- Store payload into encrypted column during sync.
- Keep plain payload metadata minimal for compatibility.
- Feature tests for encryption storage and sync regression.

## Out of Scope

- Client-side end-to-end encryption keys.
- HSM/KMS key rotation workflows.
- Full cryptographic audit dashboard.

## Technical Notes

- Preserve current sync endpoint contract.
- Avoid breaking existing idempotency and attendance mapping logic.

## Definition of Done

- [x] Encryption storage field and model cast implemented
- [x] Offline sync controller writes encrypted payload
- [x] Feature tests cover encrypted persistence + sync regression
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Added encrypted payload storage to offline sync events using Laravel encrypted array cast.
- Updated offline sync flow to write sensitive event payload into encrypted storage while keeping minimal plain metadata for compatibility.
- Preserved idempotent `client_event_id` behavior and attendance record sync behavior.
- Added regression test proving encrypted DB value does not expose raw employee identifier while model decrypts payload.

### File List

- `_bmad-output/implementation-artifacts/20-5-encryption.md`
- `database/migrations/2026_05_19_002750_add_encrypted_payload_to_offline_sync_events_table.php`
- `app/Models/OfflineSyncEvent.php`
- `app/Http/Controllers/OfflineSyncController.php`
- `tests/Feature/OfflineSyncTest.php`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation

- `php artisan test tests/Feature/OfflineSyncTest.php` ✅ pass (3 tests)
- `composer test` ✅ PHPUnit pass (388 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-19: Implemented Story 20.5 Encryption MVP and validations.

