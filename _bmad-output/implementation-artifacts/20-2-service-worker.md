# Story 20.2: Service Worker

**Story Key:** 20-2-service-worker
**Priority:** P0
**Status:** review

## User Story

As a mobile/web user, I want app shell assets cached by a service worker so the app can load faster and remain partially available when connection is unstable.

## Acceptance Criteria

1. Service worker is served from public scope path and can be registered by browser.
2. App registers service worker only in production context and on browser that supports it.
3. Service worker caches core app shell assets (manifest, app root, Vite asset requests via same-origin GET fallback strategy).
4. Service worker uses safe cache versioning and activate cleanup of old cache names.
5. Existing online behavior remains unchanged when service worker unavailable.
6. No business data mutation inside service worker logic.

## MVP Scope

- Add service worker script endpoint/file.
- Add client-side registration in app bootstrap.
- Cache-first for static assets and network-first fallback for HTML/doc requests.
- Feature tests for service worker endpoint and registration marker in built app entry.

## Out of Scope

- Background sync queue.
- Offline write queues for attendance.
- Push notifications.

## Technical Notes

- Keep caching read-only and limited to safe GET requests.
- Use cache key with version constant to support future invalidation.
- Prepare baseline for Story 20.3 offline clock-in/out.

## Definition of Done

- [x] Service worker endpoint/file implemented
- [x] Registration logic added to frontend app bootstrap
- [x] Basic cache versioning + activate cleanup implemented
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Added service worker endpoint `/service-worker.js` with cache install/activate/fetch handlers.
- Added production-only service worker registration in `resources/js/app.jsx`.
- Implemented cache versioning and old cache cleanup via `CACHE_NAME` + activate phase delete.
- Added feature tests for manifest and service worker endpoint response/content.

### File List

- `_bmad-output/implementation-artifacts/20-2-service-worker.md`
- `app/Http/Controllers/ServiceWorkerController.php`
- `routes/web.php`
- `resources/js/app.jsx`
- `tests/Feature/ServiceWorkerTest.php`
- `tests/Feature/PwaManifestTest.php`

### Validation

- `php artisan test tests/Feature/PwaManifestTest.php tests/Feature/ServiceWorkerTest.php` ✅ pass (2 tests)
- `composer test` ✅ PHPUnit pass (382 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-18: Implemented Story 20.2 Service Worker MVP and validations.
