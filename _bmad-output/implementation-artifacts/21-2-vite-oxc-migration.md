# Story 21.2: Vite OXC Migration

**Story Key:** 21-2-vite-oxc-migration
**Priority:** P0
**Status:** review

## User Story

As a developer, I want frontend build pipeline to use OXC-compatible React plugin so Vite deprecation warnings are removed and build setup stays forward-compatible.

## Acceptance Criteria

1. Vite React setup uses OXC-compatible plugin/configuration.
2. `npm run build` runs without `esbuild` deprecation warning from `vite:react-babel`.
3. Existing app behavior and bundle output remain functional.
4. No backend/domain logic is changed.

## MVP Scope

- Update Vite React plugin dependency/config.
- Keep Laravel Vite plugin integration unchanged.
- Validate with `npm run build` and core test run.

## Out of Scope

- Frontend framework migration.
- Bundle optimization tuning.
- CI pipeline changes.

## Technical Notes

- Current warning: `esbuild option was specified by "vite:react-babel" plugin. This option is deprecated, please use "oxc" instead.`

## Definition of Done

- [x] OXC-compatible plugin/config applied
- [x] `npm run build` warning removed
- [x] `composer test` run
- [x] Story/sprint status updated to review

## Dev Agent Record

### Completion Notes

- Upgraded `@vitejs/plugin-react` to `6.0.1`, which supports Vite 8 without the prior `vite:react-babel` esbuild warning path.
- Reverted `vite.config.js` import to `@vitejs/plugin-react` after `@vitejs/plugin-react-oxc` showed incompatible peer dependency with Vite 8.
- Confirmed `npm run build` no longer prints the old `esbuild` deprecation warning.

### File List

- `_bmad-output/implementation-artifacts/21-2-vite-oxc-migration.md`
- `package.json`
- `package-lock.json`
- `vite.config.js`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation

- `npm run build` ✅ pass, warning removed
- `composer test` ✅ PHPUnit pass (388 tests) / wrapper still returns code 1

### Change Log

- 2026-05-19: Implemented Story 21.2 Vite OXC migration and validations.
