# Story 20.4: Offline Data Sync

**Story Key:** 20-4-offline-data-sync
**Priority:** P0
**Status:** done

## User Story

As a mobile user, I want queued offline actions to sync safely when connection is restored so my app data stays consistent without duplicate writes.

## Acceptance Criteria

1. System provides generic offline sync endpoint receiving queued actions (`entity`, `action`, `payload`, `client_event_id`).
2. Sync processing is idempotent by unique `client_event_id`; duplicates are ignored.
3. Each synced event is stored in audit log with status (`synced|duplicate|failed`) and failure reason when relevant.
4. Frontend can submit local offline queue in batch and receive per-event result.
5. Sync currently supports attendance events and can be extended to more entities.
6. Sync process does not mutate unrelated domains.

## MVP Scope

- Offline sync log table for generic events.
- Batch sync API endpoint.
- Frontend helper page to view queue and trigger sync.
- Adapter support for attendance record events.
- Feature tests for idempotent sync and failure handling.

## Out of Scope

- Background queue workers.
- Conflict-resolution UI.
- Multi-device merge policy.

## Technical Notes

- Keep deterministic idempotency key from `client_event_id`.
- Reuse attendance processing logic from Story 20.3 where applicable.
- Use read-safe, whitelisted entity handlers only.

## Definition of Done

- [x] Offline sync migration/model created
- [x] Sync controller/route implemented
- [x] Frontend sync helper page implemented
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Implemented generic offline sync event audit table with idempotent `client_event_id` guard.
- Added batch sync endpoint for whitelisted attendance events.
- Added attendance adapter that creates or updates `attendance_records` without touching unrelated domains.
- Added Offline Sync page with localStorage queue helper and manual sync action.
- Added feature tests for successful sync, duplicate handling, and failed employee lookup.

### File List

- `_bmad-output/implementation-artifacts/20-4-offline-data-sync.md`
- `database/migrations/2026_05_19_002023_create_offline_sync_events_table.php`
- `app/Models/OfflineSyncEvent.php`
- `database/factories/OfflineSyncEventFactory.php`
- `app/Http/Controllers/OfflineSyncController.php`
- `routes/web.php`
- `resources/js/Pages/OfflineSync/Index.jsx`
- `tests/Feature/OfflineSyncTest.php`

### Validation

- `php artisan test tests/Feature/OfflineSyncTest.php` ✅ pass (3 tests)
- `composer test` ✅ PHPUnit pass (388 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-19: Implemented Story 20.4 Offline Data Sync MVP and validations.

