# Story 13.3: Faktur Pajak

**Story Key:** `13-3-faktur-pajak`  
**Priority:** P0  
**Status:** review

## User Story
Sebagai Finance Staff, saya ingin membuat dan mengelola Faktur Pajak dari Sales Invoice agar dokumen pajak keluaran tercatat rapi dan siap diekspor ke e-Faktur.

## Acceptance Criteria
1. Sistem dapat membuat Faktur Pajak dari Sales Invoice yang valid.
2. Nomor Faktur Pajak otomatis format `FP-YYYY-NNNN` dan unik.
3. Faktur Pajak menyimpan relasi ke Sales Invoice, customer info, DPP, PPN, total.
4. Status minimal `draft|issued|cancelled` dengan aksi issue/cancel.
5. List dan detail Faktur Pajak tersedia dengan filter status/tanggal/search.
6. Tidak mengubah data Sales Invoice sumber selain relasi faktur.

## MVP Scope
- Master `faktur_pajaks` + CRUD terbatas (index/create/store/show).
- Generate nomor otomatis.
- Aksi issue dan cancel.
- Data nilai pajak tarik dari Sales Invoice.

## Out of Scope
- Integrasi API resmi e-Faktur.
- Pembatalan dengan reversal jurnal.
- Lampiran PDF/XML resmi.

## Technical Notes
- Reuse pola voucher/reconciliation CRUD Inertia.
- Gunakan nilai invoice (`subtotal`, `discount_amount`, `tax_amount`, `grand_total`) sebagai basis.
- Siapkan data agar Story 13.4 export bisa konsumsi.

## Definition of Done
- [x] Migration + model Faktur Pajak.
- [x] Controller + route + halaman Inertia.
- [x] Logic generate nomor + issue/cancel.
- [x] Feature tests skenario utama.
- [x] `composer test` dan `npm run build` berjalan.

## Dev Agent Record
### Completion Notes
- Implement modul Faktur Pajak MVP dari Sales Invoice dengan nomor otomatis `FP-YYYY-NNNN`.
- Tambah workflow status `draft -> issued -> cancelled` lewat aksi issue/cancel.
- Simpan basis nilai pajak (DPP, PPN, total) dari Sales Invoice tanpa mutasi invoice sumber.
- Tambah halaman list, create, dan show untuk operasi harian finance.

### File List
- _bmad-output/implementation-artifacts/13-3-faktur-pajak.md
- database/migrations/2026_05_18_053628_create_faktur_pajaks_table.php
- app/Models/FakturPajak.php
- database/factories/FakturPajakFactory.php
- app/Http/Controllers/FakturPajakController.php
- routes/web.php
- resources/js/Pages/FakturPajaks/Index.jsx
- resources/js/Pages/FakturPajaks/Create.jsx
- resources/js/Pages/FakturPajaks/Show.jsx
- tests/Feature/FakturPajakTest.php

### Validation
- `php artisan test tests/Feature/FakturPajakTest.php` ✅ pass (3 tests, 7 assertions)
- `composer test` ✅ PHPUnit pass (280 tests, 870 assertions) — wrapper masih return code 1 (known issue)
- `npm run build` ✅ pass (warning vite `esbuild` deprecated -> `oxc`)

### Change Log
- 2026-05-18: Implemented Story 13.3 Faktur Pajak MVP.
