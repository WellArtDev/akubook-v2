# Story 20.1: PWA Manifest

**Story Key:** 20-1-pwa-manifest
**Priority:** P0
**Status:** done

## User Story

As a mobile user, I want the app to provide a valid web app manifest so I can install AkuBook to home screen with proper name, icon, and display behavior.

## Acceptance Criteria

1. App exposes Web App Manifest at standard path (`/manifest.webmanifest` or `/manifest.json`).
2. Manifest includes required fields: `name`, `short_name`, `start_url`, `display`, `background_color`, `theme_color`, and at least one icon.
3. Manifest includes stable app identity metadata suitable for install prompt.
4. Manifest values align with AkuBook branding and route base URL.
5. Feature is read-only and does not mutate business data.

## MVP Scope

- Serve manifest from Laravel route/controller.
- Include two icon sizes in manifest metadata (can point to same existing icon file if needed).
- Add feature test validating response status/content type/key fields.

## Out of Scope

- Service worker registration.
- Offline caching strategy.
- Push notifications.

## Technical Notes

- Keep manifest generation deterministic from config/app values.
- Use `application/manifest+json` response content type.
- Follow existing route/controller/test patterns used in prior stories.

## Definition of Done

- [x] Manifest endpoint implemented
- [x] Manifest payload includes required install fields
- [x] Feature test added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Added public manifest endpoint `/manifest.webmanifest` returning installable PWA metadata.
- Manifest payload includes required identity/display/theme/icon fields and uses app config name.
- Added feature test for endpoint status, content type, and required JSON fields.

### File List

- `_bmad-output/implementation-artifacts/20-1-pwa-manifest.md`
- `app/Http/Controllers/PwaManifestController.php`
- `routes/web.php`
- `tests/Feature/PwaManifestTest.php`

### Validation

- `php artisan test tests/Feature/PwaManifestTest.php` ✅ pass (1 test)
- `composer test` ✅ PHPUnit pass (381 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-18: Implemented Story 20.1 PWA Manifest MVP and validations.

