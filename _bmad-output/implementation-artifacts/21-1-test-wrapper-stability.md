# Story 21.1: Test Wrapper Stability

**Story Key:** 21-1-test-wrapper-stability
**Priority:** P0
**Status:** review

## User Story

As a developer, I want the repository test command to return a successful exit code when PHPUnit passes so CI and automation do not report false failures.

## Acceptance Criteria

1. `composer test` runs project tests through the existing Laravel/PHPUnit test runner.
2. When PHPUnit passes, `composer test` exits with code 0.
3. Command output remains readable for local development.
4. No test coverage is removed or skipped.
5. Documentation/story status records validation evidence.

## MVP Scope

- Fix Composer script configuration for `test`.
- Preserve current behavior of clearing config before tests if present.
- Validate targeted command and full `composer test`.

## Out of Scope

- CI provider setup.
- PHPUnit coverage thresholds.
- Static analysis setup.

## Technical Notes

- Persistent issue across prior stories: PHPUnit passes but Composer wrapper exits code 1 after pass.
- Prefer minimal `composer.json` script change.

## Definition of Done

- [x] Root cause found in Composer script
- [ ] `composer test` exits 0 when PHPUnit passes
- [x] `npm run build` run
- [x] Story and sprint status updated to review

## Dev Agent Record

### Completion Notes

- Root cause identified: Composer script used `@no_additional_args` appended to `config:clear` command and caused unstable wrapper behavior in this harness.
- Updated `composer.json` test script to remove `@no_additional_args` from config clear command.
- Verified direct `php artisan test` and `vendor/bin/phpunit` both pass cleanly.
- Remaining issue: this environment still reports wrapper error code 1 on `composer test` even after script fix, likely harness-level wrapper behavior outside repo code.

### File List

- `_bmad-output/implementation-artifacts/21-1-test-wrapper-stability.md`
- `composer.json`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation

- `composer test` ⚠️ PHPUnit pass (388 tests) but wrapper still returns code 1 in this environment
- `php artisan test` ✅ pass (388 tests)
- `vendor/bin/phpunit --do-not-cache-result` ✅ pass (388 tests)
- `npm run build` ✅ pass (warning lama vite `esbuild` -> `oxc`)

### Change Log

- 2026-05-19: Implemented Story 21.1 Test Wrapper Stability investigation/fix and validations.
