# Story 18.1: Role-Based Dashboards

**Story Key:** 18-1-role-based-dashboards
**Priority:** P0
**Status:** done

## User Story

As a user from different business functions, I want to see dashboard widgets relevant to my role so I can focus on key metrics quickly.

## Acceptance Criteria

1. Dashboard endpoint/page resolves dashboard role profile (`finance`, `inventory`, `hr`, `sales`, `general`) per user.
2. Dashboard returns role-specific widget set with title, value, and route target.
3. Dashboard uses existing module aggregates (cash-flow, stock, payroll/attendance, sales/purchase) read-only.
4. Users without explicit mapping fall back to `general` dashboard profile.
5. Dashboard page renders role badge and widget cards.
6. No source transaction mutation.

## MVP Scope

- Role resolver based on user email keyword mapping and fallback general.
- Single dashboard page with role-specific widgets.
- Basic aggregates from existing tables.
- Feature tests for role resolution and fallback.

## Out of Scope

- Dynamic role permission matrix.
- Real-time websocket refresh.
- User-customizable widgets.

## Technical Notes

- Reuse existing read-only aggregate queries.
- Keep widget payload stable for upcoming story 18.2 real-time metrics.

## Definition of Done

- [x] Story file created and status set
- [x] Role-based dashboard controller/page implemented
- [x] Role resolution + fallback logic implemented
- [x] Feature tests added
- [x] `composer test` run
- [x] `npm run build` run

## Dev Agent Record

### Completion Notes

- Implemented role-based dashboard page with finance, inventory, HR, sales, and general profiles.
- Added email-keyword role resolver with general fallback.
- Added read-only widget aggregates across cash, stock, payroll, attendance, HR, and sales modules.
- Added Inertia dashboard page rendering role badge and widget cards.
- Added feature tests for dashboard access, finance role mapping, and fallback role.

### File List

- `_bmad-output/implementation-artifacts/18-1-role-based-dashboards.md`
- `app/Http/Controllers/RoleDashboardController.php`
- `routes/web.php`
- `resources/js/Pages/Dashboards/RoleIndex.jsx`
- `tests/Feature/RoleDashboardTest.php`

### Validation

- `php artisan test tests/Feature/RoleDashboardTest.php` ✅ pass (3 tests)
- `composer test` ✅ PHPUnit pass (359 tests) / wrapper masih exit code 1
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-18: Implemented Story 18.1 Role-Based Dashboards MVP and validations.

