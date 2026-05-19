# Story 7.2: Master Data Import

Status: review

## Story

As a Finance Admin,
I want import master data (Customers, Suppliers, Items) dari sumber Accurate,
so that data bisnis inti siap dipakai modul sales, purchasing, dan inventory di AkuBook.

## Acceptance Criteria

1. Diberikan user buka menu migrasi master data, saat upload sumber data valid (offline export/API payload), sistem validasi format lalu tampilkan preview per entitas: Customers, Suppliers, Items.
2. Diberikan preview tersedia, saat user jalankan import, sistem mapping field sumber ke schema AkuBook dengan validasi required fields, enum, panjang kode, dan unique key:
   - Customers -> `customers` (`code`, `name`, `customer_type`, `contact_person`, `email`, `phone`, `address`, `city`, `tax_id`, `credit_limit`, `payment_terms_days`, `is_active`, `notes`)
   - Suppliers -> `suppliers` (`code`, `name`, `contact_person`, `email`, `phone`, `address`, `city`, `tax_id`, `payment_terms_days`, `is_active`, `notes`)
   - Items -> `items` (`code`, `name`, `description`, `item_type`, `unit`, `purchase_price`, `selling_price`, `is_active`)
3. Import jalan transactional per entitas/batch: record valid tersimpan (insert/update), record invalid di-skip dengan alasan yang jelas, tanpa korup data existing.
4. Setelah import selesai, sistem tampilkan ringkasan detail: total/valid/imported/skipped/error per entitas + durasi proses.
5. Data hasil import kompatibel dengan modul existing: sales (customer), purchasing (supplier), inventory (item) tanpa perubahan kontrak route/model existing.

## Tasks / Subtasks

- [x] Bangun service import Master Data terpisah (AC: 1,2,3,4)
  - [x] Buat parser input migrasi multi-entitas (customers/suppliers/items)
  - [x] Buat layer mapping sumber -> schema `customers`, `suppliers`, `items`
  - [x] Tambah validator bisnis per entitas (required, enum, unique, panjang kode, numeric)
- [x] Implement persistence transactional per entitas + batch (AC: 3)
  - [x] Definisikan strategy insert/update untuk data existing
  - [x] Simpan error per-row + per-entity untuk summary
- [x] Sediakan endpoint/controller action migrasi Master Data (AC: 1,4)
  - [x] Endpoint preview
  - [x] Endpoint execute import
- [x] Tambah coverage test fitur + unit (AC: 1-5)
  - [x] Success path import customers/suppliers/items
  - [x] Invalid row skip path per entitas
  - [x] Duplicate code handling per entitas
  - [x] Enum/type validation (`customer_type`, `item_type`)
  - [x] Kompatibilitas minimal dengan query model existing

## Dev Notes

- Lanjutkan pola Story 7.1 (`app/Services/ChartOfAccountsImportService.php`, controller + request + routes) untuk konsistensi arsitektur ETL/validation.
- Stack wajib tetap: Laravel 13 + PHP 8.3 (`composer.json`).
- Gunakan schema/model existing sebagai source of truth:
  - `database/migrations/2026_05_14_002319_create_customers_table.php`, `app/Models/Customer.php`
  - `database/migrations/2026_05_14_002802_create_suppliers_table.php`, `app/Models/Supplier.php`
  - `database/migrations/2026_05_14_002320_create_items_table.php`, `app/Models/Item.php`
- Catatan penting: `Supplier` model saat ini punya field fillable berbeda (`supplier_code`, dll) dibanding migration awal (`code`, dll). Implementasi 7.2 harus tentukan mapping final yang kompatibel dengan state DB aktual sebelum coding.
- Arsitektur migrasi wajib ikut `_bmad-output/planning-artifacts/architecture.md` (ETL pipeline, validation layer, rollback capability).

### Project Structure Notes

- Tempat utama implementasi backend:
  - `app/Services/` untuk orchestration import
  - `app/Http/Controllers/` untuk trigger preview/import
  - `app/Http/Requests/` untuk validasi request
  - `tests/Feature/` dan `tests/Unit/` untuk test coverage
- Hindari perubahan kontrak route/report existing di luar scope migrasi master data.

### References

- Sprint status target: `_bmad-output/implementation-artifacts/sprint-status.yaml`
- Story sebelumnya: `_bmad-output/implementation-artifacts/7-1-chart-of-accounts-import.md`
- Epic migration AC sumber: `_bmad-output/planning-artifacts/epics.md:639-663`
- Architecture migration pattern: `_bmad-output/planning-artifacts/architecture.md:277-281`
- Customer schema/model: `database/migrations/2026_05_14_002319_create_customers_table.php`, `app/Models/Customer.php`
- Supplier schema/model: `database/migrations/2026_05_14_002802_create_suppliers_table.php`, `app/Models/Supplier.php`
- Item schema/model: `database/migrations/2026_05_14_002320_create_items_table.php`, `app/Models/Item.php`

## Dev Agent Record

### Agent Model Used

9router/pp

### Debug Log References

### Completion Notes List

- Story dibuat dari target eksplisit user: `7-2-master-data-import`.
- Konteks planning `epics.md` tetap pakai penomoran migration lama; sprint status dipakai sebagai source of truth eksekusi Epic 7.
- Story 7.1 dijadikan baseline pola implementasi untuk preview/import + summary + test structure.
- Implementasi Master Data import selesai: parser multi-entitas, mapping customers/suppliers/items, validasi required/enum/duplicate/numeric, transactional upsert, summary per entitas.
- Endpoint migrasi ditambah: preview dan execute import untuk master data.
- Test baru lulus: `tests/Unit/Services/MasterDataImportServiceTest.php` dan `tests/Feature/MasterDataImportControllerTest.php`.
- `composer test` dan `vendor/bin/pint --test` masih gagal karena isu baseline existing project di file lain dan formatting baseline luas.

### File List

- _bmad-output/implementation-artifacts/7-2-master-data-import.md
- app/Services/MasterDataImportService.php
- app/Http/Requests/MasterDataImportRequest.php
- app/Http/Controllers/MasterDataImportController.php
- routes/web.php
- tests/Unit/Services/MasterDataImportServiceTest.php
- tests/Feature/MasterDataImportControllerTest.php

## Change Log

- 2026-05-17: Implementasi Story 7.2 selesai dan status dipindah ke review.
