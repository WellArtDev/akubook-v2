# Epic 20 Retrospective: Progressive Web App & Offline Capability

**Status:** done
**Date:** 2026-05-19

## Epic Summary

Epic 20 delivered the PWA/offline foundation for AkuBook, focused on installability, service worker support, offline attendance capture, generic sync, and encrypted offline sync payload storage.

| Story | Status | Outcome |
| --- | --- | --- |
| 20.1 PWA Manifest | review | Manifest endpoint with AkuBook app identity and icons |
| 20.2 Service Worker | review | Service worker route and production registration |
| 20.3 Offline Clock-In/Out | review | Local queue + attendance sync endpoint |
| 20.4 Offline Data Sync | review | Generic offline sync event queue and attendance adapter |
| 20.5 Encryption | review | Encrypted payload storage for offline sync events |

## Validation Evidence

- Targeted PWA/offline feature tests pass.
- Latest `composer test`: PHPUnit pass 388 tests / 1431 assertions; composer wrapper still exits code 1 after pass.
- Latest `npm run build`: pass; existing Vite warning remains (`esbuild` option deprecated by `vite:react-babel`, use `oxc`).

## What Went Well

- PWA manifest and service worker were added without changing business flows.
- Offline clock-in/out sync reuses existing `attendance_records` safely.
- Generic `offline_sync_events` model gives future offline actions a shared audit path.
- Idempotency by client event key prevents duplicate offline submissions.
- Encrypted payload cast protects sensitive sync payloads at rest.

## Challenges

- Browser offline behavior is hard to fully verify with backend feature tests.
- Service worker MVP caches app shell only, not full offline Inertia pages.
- Offline sync currently supports attendance only.
- Client-side queue still uses localStorage without client-side encryption.
- Composer wrapper false-failure and Vite warning remain unresolved.

## Technical Debt

1. Add E2E browser tests for offline mode and service worker lifecycle.
2. Expand generic offline sync adapters beyond attendance.
3. Add conflict resolution UI for failed/duplicate offline events.
4. Add client-side encrypted local queue storage.
5. Add service worker versioning strategy tied to build assets.
6. Fix `composer test` wrapper exit code.
7. Migrate Vite React plugin config from `esbuild` to `oxc`.

## Lessons Learned

- Offline features need idempotency before broad scope.
- Server-side encrypted payloads are easy with Laravel casts and low-risk to validate.
- Keeping source mutations narrow avoids data corruption from retries.
- Offline UX should expose queue count and sync results clearly.

## Next Phase Prep

Epic 20 closes the current tracked sprint scope. Before starting another major phase, stabilize cross-cutting tech debt: test wrapper, Vite warning, offline E2E coverage, and role/permission foundations.
