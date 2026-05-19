# Story 10.2: Edit Before Print

**Story Key:** `10-2-edit-before-print`  
**Priority:** P0  
**Status:** review

## User Story
As Admin/Staff, I want edit dokumen sementara sebelum print, so that hasil cetak sesuai kebutuhan operasional tanpa ubah data transaksi asli.

## Acceptance Criteria
1. User bisa buka mode edit-before-print dari dokumen didukung (Sales Invoice, Delivery Order, Purchase Order, Goods Receipt).
2. Edit disimpan sebagai draft print (snapshot), tidak mengubah tabel transaksi asli.
3. Draft simpan field override (header/line text sederhana) dan referensi ke template dot matrix.
4. User bisa list draft, lihat detail draft, update draft, hapus draft.
5. Draft siap dipakai Story 10.3 print preview.

## MVP Scope
- Dokumen didukung: sales_invoice, delivery_order, purchase_order, goods_receipt.
- Simpan metadata draft: document_type, document_id, template_id, override payload, status draft.
- CRUD draft print sederhana + endpoint sample payload per document_type.

## Out of Scope
- Approval workflow draft print.
- Collaborative editing.
- Version diff advanced.

## Technical Notes
- Reuse pattern CRUD + Inertia seperti master data lain.
- Override payload format JSON fleksibel untuk dipakai print preview.
- Jangan mutasi data transaksi sumber.

## Definition of Done
- [x] Migration + model print draft dibuat.
- [x] Controller + routes edit-before-print CRUD jalan.
- [x] Inertia pages list/create/show/edit draft jalan.
- [x] Feature test cover create/update/delete dan source immutable.
- [x] `composer test` dan `npm run build` pass.

## Dev Agent Record
### Completion Notes
- Implement Edit Before Print MVP pakai entitas `print_drafts` untuk simpan snapshot override cetak.
- Draft tidak mutasi data sumber transaksi (sales invoice/do/po/gr).
- Tambah CRUD Inertia `PrintDrafts` dan relasi ke `dot_matrix_templates`.
- Tambah source lookup per document type untuk pilih dokumen saat buat/edit draft.

### File List
- _bmad-output/implementation-artifacts/10-2-edit-before-print.md
- database/migrations/2026_05_18_000543_create_print_drafts_table.php
- app/Models/PrintDraft.php
- app/Http/Controllers/PrintDraftController.php
- database/factories/PrintDraftFactory.php
- resources/js/Pages/PrintDrafts/Index.jsx
- resources/js/Pages/PrintDrafts/Create.jsx
- resources/js/Pages/PrintDrafts/Show.jsx
- resources/js/Pages/PrintDrafts/Edit.jsx
- tests/Feature/PrintDraftTest.php
- routes/web.php

### Validation
- `php artisan test tests/Feature/PrintDraftTest.php` pass (4 tests).
- `composer test` pass di phpunit (227 tests) tapi wrapper masih return code 1 (existing issue).
- `npm run build` pass, warning lama Vite `esbuild` -> `oxc`.

### Change Log
- 2026-05-18: Implement Story 10.2 Edit Before Print MVP.
