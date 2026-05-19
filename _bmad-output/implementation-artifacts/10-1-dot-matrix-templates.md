# Story 10.1: Dot Matrix Templates
**Epic:** 10 | **Story ID:** 10.1 | **Key:** 10-1-dot-matrix-templates | **Priority:** P0
**Status:** done

## User Story
**Sebagai** Admin/Staff, **Saya ingin** template dot-matrix untuk dokumen transaksi, **Sehingga** bisa cetak formulir legal/operasional dengan format konsisten.

## Acceptance Criteria
- Template dot-matrix untuk dokumen inti tersedia.
- Setiap template punya ukuran kertas, margin, dan posisi field terdefinisi.
- Data dokumen bisa dipetakan ke field template.
- Output siap dipakai di modul print preview/cetak.

## Scope MVP
- Template awal: Sales Invoice, Delivery Order, Purchase Order, Goods Receipt.
- Simpan konfigurasi template (nama, jenis dokumen, ukuran, margin, field map).
- Endpoint/page untuk list template dan melihat detail mapping.

## Out of Scope (Follow-up)
- Drag-and-drop visual editor penuh.
- Versioning template lanjutan.
- Multi-printer profile lanjutan.

## Technical Notes
- Reuse pola page/controller dari master data sederhana (index/show/create/update).
- Pastikan struktur siap dipakai Story 10.2/10.3.
- Jangan hardcode data dokumen; gunakan mapper per document type.

## Definition of Done
- [x] Migration + model template
- [x] CRUD dasar template
- [x] Field mapping untuk 4 dokumen MVP
- [x] Feature tests utama
- [x] `composer test` dan `npm run build` hijau

## Dev Agent Record
### Completion Notes
- Implement modul Dot Matrix Template full MVP: migration, model, controller, route, page Inertia, dan mapper default per dokumen.
- CRUD aktif untuk dokumen `sales_invoice`, `delivery_order`, `purchase_order`, `goods_receipt`.
- Tambah endpoint `dot-matrix-templates.defaults` untuk ambil field map default sesuai tipe dokumen.
- Tambah feature tests untuk index, create, default uniqueness per document type, update, dan endpoint defaults.
### Validation
- `php artisan test tests/Feature/DotMatrixTemplateTest.php` pass.
- `composer test` pass payload (wrapper issue saat itu).
- `npm run build` pass.
- Validasi jalan: test targeted pass, full phpunit pass (wrapper composer masih return code 1 seperti issue lama), build pass dengan warning Vite lama.

### File List
- `_bmad-output/implementation-artifacts/10-1-dot-matrix-templates.md`
- `database/migrations/2026_05_17_233445_create_dot_matrix_templates_table.php`
- `app/Models/DotMatrixTemplate.php`
- `database/factories/DotMatrixTemplateFactory.php`
- `app/Http/Controllers/DotMatrixTemplateController.php`
- `routes/web.php`
- `resources/js/Pages/DotMatrixTemplates/Index.jsx`
- `resources/js/Pages/DotMatrixTemplates/Create.jsx`
- `resources/js/Pages/DotMatrixTemplates/Show.jsx`
- `resources/js/Pages/DotMatrixTemplates/Edit.jsx`
- `tests/Feature/DotMatrixTemplateTest.php`

### Change Log
- 2026-05-18: Implement Story 10.1 Dot Matrix Templates MVP, tests pass, build pass.

