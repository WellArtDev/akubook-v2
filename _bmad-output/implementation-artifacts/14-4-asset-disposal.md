# Story 14.4: Asset Disposal

**Story Key:** `14-4-asset-disposal`  
**Priority:** P0  
**Status:** review

## User Story
Sebagai Finance Staff, saya ingin mencatat pelepasan aset tetap supaya status aset, nilai buku, dan jurnal pelepasan tercatat rapi untuk audit.

## Acceptance Criteria
1. User dapat membuat transaksi disposal untuk aset berstatus `active` dengan input tanggal disposal, nilai jual (opsional), dan catatan.
2. Disposal otomatis menghitung nilai buku terakhir dari data depresiasi terakhir aset.
3. Disposal membuat jurnal pelepasan aset dengan skenario minimal:
   - Debit Akumulasi Depresiasi
   - Debit/Kredit Kas/Bank (jika ada nilai jual)
   - Debit/Kredit Gain/Loss Disposal
   - Credit Aset Tetap
4. Setelah disposal diposting, status aset berubah menjadi `disposed`.
5. List/detail disposal tersedia dengan filter status/periode.
6. Proses idempotent untuk posting: disposal yang sudah posted tidak bisa dipost ulang.

## MVP Scope
- CRUD terbatas disposal: index/create/store/show/post.
- Simpan snapshot nilai aset: acquisition_cost, accumulated_depreciation, book_value, proceeds.
- Generate jurnal via `journal_entries` + `journal_entry_lines`.
- Ubah status `fixed_assets.status` menjadi `disposed` saat post.

## Out of Scope
- Reversal/void disposal.
- Integrasi pajak pelepasan.
- Multi-asset bulk disposal.

## Definition of Done
- [x] Model + migration disposal tersedia.
- [x] Posting disposal membuat jurnal balanced.
- [x] Status fixed asset update ke disposed saat posted.
- [x] Halaman disposal (list/create/show) tersedia.
- [x] Feature tests: create, post success, prevent repost.
- [x] `composer test` dan `npm run build` dijalankan.

## Dev Agent Record
- Implement transaksi disposal aset dengan snapshot nilai, posting jurnal, dan update status aset.
- Posting disposal membuat jurnal balanced per period open dan mencegah repost.

### File List
- _bmad-output/implementation-artifacts/14-4-asset-disposal.md
- database/migrations/2026_05_18_071048_create_asset_disposals_table.php
- app/Models/AssetDisposal.php
- database/factories/AssetDisposalFactory.php
- app/Http/Controllers/AssetDisposalController.php
- routes/web.php
- resources/js/Pages/AssetDisposals/Index.jsx
- resources/js/Pages/AssetDisposals/Create.jsx
- resources/js/Pages/AssetDisposals/Show.jsx
- tests/Feature/AssetDisposalTest.php

### Validation
- `php artisan test tests/Feature/AssetDisposalTest.php` -> pass (3 tests).
- `composer test` -> PHPUnit pass (299 tests), wrapper masih return code 1 (known issue).
- `npm run build` -> pass, warning lama Vite `esbuild` deprecated.

### Change Log
- 2026-05-18: Implemented Story 14.4 Asset Disposal MVP.
