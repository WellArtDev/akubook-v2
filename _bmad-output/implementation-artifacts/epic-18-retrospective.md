# Epic 18 Retrospective: Dashboard & Analytics

**Status:** done
**Date:** 2026-05-18

## Epic Summary

Epic 18 delivered dashboard and analytics MVP: role-based dashboard widgets, refreshable metrics, drill-down pages, and per-user refresh preferences.

| Story | Status | Outcome |
| --- | --- | --- |
| 18.1 Role-Based Dashboards | review | Role profile resolver and role-specific widgets for finance, inventory, HR, sales, and general users. |
| 18.2 Real-Time Metrics | review | JSON metrics endpoint, auto-refresh dashboard behavior, manual refresh control. |
| 18.3 Drill-Down Capability | review | Widget drill-down route/page with filters, summary, and detail rows. |
| 18.4 Dashboard Refresh | review | Per-user refresh interval and auto-refresh preference persisted and honored by metrics endpoint. |

## Outcomes

- Dashboard now resolves role from authenticated user context.
- Metrics payload supports role widgets, generated timestamp, refresh interval, auto-refresh flag, and drill-down metadata.
- Users can manually refresh, auto-refresh, change interval, and disable auto-refresh.
- Widgets provide drill-down pages backed by read-only operational/finance/inventory/HR/sales data.

## Validation Evidence

- Targeted dashboard tests pass:
  - `RoleDashboardTest` pass after 18.4.
- Latest full suite:
  - `composer test`: PHPUnit pass 365 tests / 1221 assertions.
  - Composer wrapper still exits code 1 after PHPUnit pass.
- Frontend:
  - `npm run build` pass.
  - Existing Vite warning persists: `esbuild` option deprecated by `vite:react-babel`, use `oxc`.

## What Went Well

- Built incrementally on one controller/page instead of inventing parallel dashboard systems.
- Widget payload remained backward compatible while gaining `widget_key`, drilldown metadata, and refresh preferences.
- Drill-down pages are read-only and reusable across roles.
- Tests guarded role resolution, metrics JSON, drilldown route, and refresh preference persistence.

## Challenges / Gaps

- Role resolution is email-keyword based, not permission/role-table based.
- Metrics are polling-based, not WebSocket/SSE real-time.
- Drill-down queries are MVP summaries; no saved views, export, or pivoting.
- Some widget datasets have simple filters only.
- Composer wrapper code 1 and Vite warning remain unresolved.

## Action Items

1. Replace email-keyword role detection with real role/permission model.
2. Add export from drill-down tables after reporting epic stabilizes.
3. Add saved dashboard preferences beyond refresh settings: default role, widget ordering, hidden widgets.
4. Fix Composer test wrapper so process exit matches PHPUnit pass state.
5. Migrate Vite React plugin config from deprecated `esbuild` option to `oxc`.

## Next Epic 19 Prep

- Reuse drill-down table/filter patterns for comprehensive reports.
- Keep report pages read-only unless explicit workflow requires mutation.
- Normalize summary card + detail row payload shapes.
- Avoid duplicating dashboard queries when creating formal reports.
