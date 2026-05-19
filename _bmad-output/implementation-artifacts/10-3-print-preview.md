# Story 10.3: Print Preview

**Story Key:** `10-3-print-preview`  
**Priority:** P0  
**Status:** review

## User Story
As Admin/Staff,
I want melihat hasil dokumen dalam preview dot matrix sebelum print,
so that saya bisa verifikasi layout dan isi sebelum kirim ke printer.

## Acceptance Criteria
1. User bisa buka print preview dari Print Draft.
2. Preview render memakai `dot_matrix_template.field_map` + `print_draft.override_payload`.
3. Preview menampilkan output monospaced (grid berbasis `columns` x `rows`) untuk simulasi dot matrix.
4. Preview tidak mengubah source transaction maupun print draft payload.
5. User bisa trigger aksi "Siap Print" (ubah status draft -> ready) dari halaman preview.

## MVP Scope
- Endpoint/page preview berbasis `print_drafts/{id}/preview`.
- Render text grid server-side atau client-side sederhana dari field map.
- Data sumber value dari `override_payload.header` fallback ke label field jika tidak ada override.
- Tombol set status `ready`.

## Out of Scope
- Integrasi printer fisik.
- Multi-page pagination kompleks.
- WYSIWYG canvas editor.

## Technical Notes
- Reuse model `PrintDraft` dan `DotMatrixTemplate`.
- Gunakan output `<pre>` monospace agar alignment stabil.
- Keep deterministic render (mudah dites).

## Definition of Done
- [x] Route dan controller preview tersedia.
- [x] Preview page Inertia dibuat.
- [x] Grid renderer berfungsi untuk field map dasar.
- [x] Status `ready` update dari preview tersedia.
- [x] Feature tests untuk preview + ready action lulus.
- [x] `composer test` dan `npm run build` lulus.

## Dev Agent Record
### Completion Notes
- Added print preview route/page for `PrintDraft` using linked `DotMatrixTemplate`.
- Added deterministic server-side monospaced grid renderer using template `columns`, `rows`, and `field_map` positions.
- Added `Siap Print` action from preview that updates draft status to `ready` without mutating source transaction or override payload.
- Added feature tests for preview page and ready action.

### File List
- `_bmad-output/implementation-artifacts/10-3-print-preview.md`
- `app/Http/Controllers/PrintDraftController.php`
- `resources/js/Pages/PrintDrafts/Show.jsx`
- `resources/js/Pages/PrintDrafts/Preview.jsx`
- `routes/web.php`
- `tests/Feature/PrintDraftTest.php`

### Validation
- `php artisan test tests/Feature/PrintDraftTest.php` passed: 6 tests, 13 assertions.
- `composer test` PHPUnit passed: 229 tests, 701 assertions. Composer wrapper still returned known code 1 after pass.
- `npm run build` passed with existing Vite `esbuild` deprecation warning.

### Change Log
- 2026-05-18: Implement Story 10.3 Print Preview MVP.
