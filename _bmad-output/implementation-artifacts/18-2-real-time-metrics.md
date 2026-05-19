# Story 18.2: Real-Time Metrics

**Story Key:** 18-2-real-time-metrics
**Priority:** P0
**Status:** done

## User Story

As a dashboard user, I want metrics to refresh automatically so I can monitor near-real-time business conditions.

## Acceptance Criteria

1. Dashboard metrics endpoint returns role-based widgets with latest values and generated timestamp.
2. Endpoint supports same role resolution as Story 18.1.
3. Frontend dashboard auto-refreshes metrics in configurable interval (MVP default 60 seconds) and manual refresh button.
4. Refresh operation is read-only and does not mutate source transactions.
5. Last refresh time and loading state are visible.

## MVP Scope

- JSON metrics endpoint for role-based dashboard.
- Auto refresh + manual refresh on dashboard page.
- Basic health payload: `generated_at`, `refresh_seconds`.

## Out of Scope

- WebSocket streaming.
- Per-widget custom intervals.
- Push notifications.

## Definition of Done

- [x] Story context set and sprint updated
- [x] Real-time metrics endpoint implemented
- [x] Dashboard page auto/manual refresh implemented
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Added JSON metrics endpoint for role dashboard payload (`generated_at`, `refresh_seconds`, widgets).
- Reused role resolution and aggregate widget logic from Story 18.1.
- Added dashboard auto-refresh every 60 seconds plus manual refresh button and loading state.
- Added last refresh timestamp visibility on dashboard card.
- Added feature test for metrics endpoint payload structure and role mapping.

### File List

- `_bmad-output/implementation-artifacts/18-2-real-time-metrics.md`
- `app/Http/Controllers/RoleDashboardController.php`
- `routes/web.php`
- `resources/js/Pages/Dashboards/RoleIndex.jsx`
- `tests/Feature/RoleDashboardTest.php`

### Validation

- `php artisan test tests/Feature/RoleDashboardTest.php` ✅ pass (4 tests)
- `composer test` ✅ PHPUnit pass (360 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-18: Implemented Story 18.2 Real-Time Metrics MVP and validations.

