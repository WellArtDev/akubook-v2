# Story 14.3: Depreciation Journal

**Story Key:** `14-3-depreciation-journal`  
**Priority:** P0  
**Status:** done

## User Story
Sebagai Finance Staff, saya ingin generate jurnal depresiasi dari hasil perhitungan depresiasi bulanan supaya posting beban dan akumulasi depresiasi konsisten dan terkontrol.

## Acceptance Criteria
1. User dapat generate jurnal depresiasi berdasarkan period (`YYYY-MM`) dari data `asset_depreciations`.
2. Setiap baris depresiasi membuat jurnal berpasangan:
   - **Debit** akun `depreciation_expense_account_id`
   - **Credit** akun `accumulated_depreciation_account_id`
3. Jurnal dibuat dalam satu header per period (`journal_entries`) dengan total debit=credit.
4. Idempotent: rerun period sama tidak membuat duplikasi jurnal untuk baris yang sama.
5. Status proses terlihat (draft/posted) dan referensi ke period/report tersedia.
6. Tidak mengubah nilai perhitungan depresiasi sumber (`asset_depreciations`).

## MVP Scope
- Endpoint/page untuk run jurnal depresiasi period.
- Create satu `journal_entries` posted + multi `journal_entry_lines` dari `asset_depreciations` period terkait.
- Simpan penanda bahwa baris depresiasi sudah diposting jurnal (referensi line/header).
- Laporan ringkas hasil run (jumlah aset, total nilai depresiasi, nomor jurnal).

## Out of Scope
- Reversal otomatis untuk period terdahulu.
- Multi-book/parallel depreciation standard.
- Workflow approval jurnal.

## Definition of Done
- [x] Data model menyimpan relasi posting jurnal depresiasi.
- [x] Proses run jurnal period berjalan dan idempotent.
- [x] Header/line jurnal sesuai akun mapping aset.
- [x] Halaman run+hasil tersedia.
- [x] Feature tests mencakup sukses run, idempotent rerun, validasi akun.
- [x] `composer test` dan `npm run build` dijalankan.

## Dev Agent Record
- Implement jurnal depresiasi periodik dari `asset_depreciations` yang belum diposting.
- Tambah relasi `journal_entry_id` + `journal_posted_at` di tabel depresiasi untuk idempotent rerun.
- Tambah halaman run jurnal period + ringkasan dan status posting per aset.

### File List
- _bmad-output/implementation-artifacts/14-3-depreciation-journal.md
- database/migrations/2026_05_18_062644_add_journal_fields_to_asset_depreciations_table.php
- app/Models/AssetDepreciation.php
- app/Http/Controllers/AssetDepreciationJournalController.php
- resources/js/Pages/AssetDepreciations/Index.jsx
- resources/js/Pages/AssetDepreciationJournals/Index.jsx
- routes/web.php
- tests/Feature/AssetDepreciationJournalTest.php

### Validation
- `php artisan test tests/Feature/AssetDepreciationJournalTest.php` -> pass (3 tests).
- `composer test` -> PHPUnit pass (296 tests), wrapper masih return code 1 (known issue).
- `npm run build` -> pass, warning lama Vite `esbuild` deprecated.

### Change Log
- 2026-05-18: Implemented Story 14.3 Depreciation Journal MVP.

