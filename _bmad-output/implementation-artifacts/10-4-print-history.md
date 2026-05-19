# Story 10.4: Print History

**Story Key:** `10-4-print-history`  
**Priority:** P0  
**Status:** review

## User Story
As Admin/Staff,
I want melihat histori aktivitas print dokumen,
so that saya bisa audit siapa print apa, kapan, dan pakai draft/template mana.

## Acceptance Criteria
1. Sistem mencatat event print dari print draft (minimal saat user klik aksi print/record print).
2. Data histori menyimpan: draft, document type/id, template, user, timestamp, dan metadata output.
3. User bisa melihat list print history dengan filter minimal document_type, user, date range.
4. User bisa membuka detail print history.
5. Histori tidak mengubah data source transaksi.

## MVP Scope
- Tabel `print_histories` + model + relasi ke `print_drafts` dan `users`.
- Endpoint aksi untuk mencatat print event dari preview/show.
- Inertia page list + detail history.

## Out of Scope
- Integrasi spooler/printer driver real.
- Retry/antrian print.
- Export audit report.

## Technical Notes
- Reuse pattern CRUD/list dari modul sebelumnya.
- Timestamp gunakan server time.
- Metadata output json sederhana: rows, columns, status draft saat dicetak.

## Definition of Done
- [x] Migration/model print history tersedia.
- [x] Aksi record print event tersedia.
- [x] Halaman list + detail history tersedia.
- [x] Feature test record print + filter/list lulus.
- [x] `composer test` dan `npm run build` lulus.

## Dev Agent Record
### Completion Notes
- Added `print_histories` audit table with links to print draft, template, user, source document, printed timestamp, and output metadata.
- Added `recordPrint` action from print preview to create history without mutating source transactions.
- Added Print History list/detail Inertia pages with document type, user, and date filters.
- Added feature tests for record print, filtered index, detail page, plus re-ran print draft preview tests.

### File List
- `_bmad-output/implementation-artifacts/10-4-print-history.md`
- `database/migrations/2026_05_18_003118_create_print_histories_table.php`
- `app/Models/PrintHistory.php`
- `database/factories/PrintHistoryFactory.php`
- `app/Http/Controllers/PrintHistoryController.php`
- `app/Http/Controllers/PrintDraftController.php`
- `resources/js/Pages/PrintDrafts/Preview.jsx`
- `resources/js/Pages/PrintHistories/Index.jsx`
- `resources/js/Pages/PrintHistories/Show.jsx`
- `routes/web.php`
- `tests/Feature/PrintHistoryTest.php`

### Validation
- `php artisan test tests/Feature/PrintHistoryTest.php` passed: 3 tests, 5 assertions.
- `php artisan test tests/Feature/PrintDraftTest.php` passed: 6 tests, 13 assertions.
- `composer test` PHPUnit passed: 232 tests, 706 assertions. Composer wrapper still returned known code 1 after pass.
- `npm run build` passed with existing Vite `esbuild` deprecation warning.

### Change Log
- 2026-05-18: Implement Story 10.4 Print History MVP.
