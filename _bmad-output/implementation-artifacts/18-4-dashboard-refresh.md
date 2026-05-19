# Story 18.4: Dashboard Refresh

**Story Key:** 18-4-dashboard-refresh
**Priority:** P0
**Status:** done

## User Story

As a dashboard user, I want configurable refresh controls so dashboard metrics stay current without losing manual control.

## Acceptance Criteria

1. User can view current dashboard refresh interval and auto-refresh state.
2. User can change refresh interval between supported values.
3. User can enable/disable auto-refresh without changing dashboard role widgets.
4. Metrics endpoint honors refresh preference in returned payload.
5. Manual refresh remains available and read-only.
6. Preferences persist per authenticated user.

## MVP Scope

- Store dashboard refresh preference per user.
- Supported intervals: 15, 30, 60, 120, 300 seconds.
- Add settings endpoint and UI controls to role dashboard.
- Preserve real-time metrics and drill-down behavior.

## Out of Scope

- WebSocket/server-sent events.
- Per-widget refresh intervals.
- Notification-based refresh.

## Technical Notes

- Build on Story 18.2 `role-dashboard/metrics` payload.
- Keep metrics read-only.
- Use user ID as preference owner.

## Definition of Done

- [x] Preference migration/model implemented
- [x] Metrics payload uses preference
- [x] Dashboard UI controls implemented
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Added per-user dashboard refresh preference (interval + auto refresh toggle).
- Extended metrics payload with persisted preference and allowed refresh options.
- Added preference endpoint for saving refresh settings from dashboard UI.
- Updated role dashboard UI with auto toggle, interval selector, save/loading states.
- Added feature tests for preference persistence and metrics preference output.

### File List

- `_bmad-output/implementation-artifacts/18-4-dashboard-refresh.md`
- `database/migrations/2026_05_18_210617_create_dashboard_preferences_table.php`
- `app/Models/DashboardPreference.php`
- `database/factories/DashboardPreferenceFactory.php`
- `app/Http/Controllers/RoleDashboardController.php`
- `routes/web.php`
- `resources/js/Pages/Dashboards/RoleIndex.jsx`
- `tests/Feature/RoleDashboardTest.php`

### Validation

- `php artisan test tests/Feature/RoleDashboardTest.php` ✅ pass (8 tests)
- `composer test` ✅ PHPUnit pass (365 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-18: Implemented Story 18.4 Dashboard Refresh MVP and validations.

