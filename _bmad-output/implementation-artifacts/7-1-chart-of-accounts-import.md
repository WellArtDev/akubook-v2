# Story 7.1: Chart of Accounts Import

**Status:** done

## Story

As a Finance Admin,
I want import Chart of Accounts dari sumber Accurate,
so that struktur akun historis pindah ke AkuBook akurat dan siap dipakai transaksi.

## Acceptance Criteria

1. Diberikan user buka menu migrasi COA, saat upload sumber data valid (offline export/API payload), sistem validasi format lalu tampilkan preview struktur akun.
2. Diberikan preview tersedia, saat user jalankan import, sistem mapping field sumber ke `accounts` AkuBook (`code`, `name`, `type`, `category`, `parent_id`, `level`, `is_header`, `is_active`, `description`) dengan validasi parent-child dan unique code.
3. Import jalan transactional per batch: akun valid tersimpan, akun invalid ditandai skip dengan alasan, proses tidak korup data existing.
4. Setelah import selesai, sistem tampilkan ringkasan: total record, imported, skipped, error detail, durasi.
5. Hasil import kompatibel modul existing (journal/reporting): akun header/detail sesuai aturan dan dapat dipakai query/report tanpa perubahan kontrak.

## Tasks / Subtasks

- [x] Bangun service import COA terpisah (AC: 1,2,3,4)
  - [x] Buat parser input migrasi (offline/API payload normalisasi)
  - [x] Buat layer mapping Accurate -> schema `accounts`
  - [x] Tambah validator bisnis (unique code, parent exists, enum type/category)
- [x] Implement persistence transactional + batch (AC: 3)
  - [x] Upsert/insert strategy jelas untuk data existing
  - [x] Simpan error per-row untuk summary
- [x] Sediakan endpoint/controller action migrasi COA (AC: 1,4)
  - [x] Endpoint preview
  - [x] Endpoint execute import
- [x] Tambah coverage test fitur + unit (AC: 1-5)
  - [x] Success path import hierarchy
  - [x] Invalid row skip path
  - [x] Duplicate code handling
  - [x] Parent-child ordering/cycle guard
  - [x] Kompatibilitas minimal dengan query account existing

## Dev Notes

- Gunakan stack existing: Laravel 13 + PHP 8.3 (`composer.json`) dan pola controller/service yang sudah ada (`app/Http/Controllers/JournalEntryController.php`).
- Schema target akun wajib ikut `database/migrations/2026_05_13_152357_create_accounts_table.php`.
- Model target pakai `App\Models\Account` dan constraint perilaku existing (`app/Models/Account.php`).
- CoA seeder existing contoh struktur hirarki: `database/seeders/CoA/GeneralCoASeeder.php`.
- Integrasi migrasi mengikuti arsitektur: ETL + validation layer + rollback capability (`_bmad-output/planning-artifacts/architecture.md`, bagian Accurate Migration).
- Story ini bagian Epic 7 pada sprint status: `_bmad-output/implementation-artifacts/sprint-status.yaml`.

### Project Structure Notes

- Tempat utama implementasi backend:
  - `app/Services/` untuk orchestration import
  - `app/Http/Controllers/` untuk trigger preview/import
  - `app/Http/Requests/` untuk validasi request
  - `tests/Feature/` dan `tests/Unit/` untuk test coverage
- Jangan ubah kontrak route/report existing kecuali perlu endpoint baru migrasi.
- Tidak ada git repo aktif di workspace ini; verifikasi berbasis test lokal.

### References

- Sprint tracking dan target story: `_bmad-output/implementation-artifacts/sprint-status.yaml`
- Account schema: `database/migrations/2026_05_13_152357_create_accounts_table.php`
- Account model behavior: `app/Models/Account.php`
- CoA hierarchy pattern: `database/seeders/CoA/GeneralCoASeeder.php`
- Architecture migration pattern: `_bmad-output/planning-artifacts/architecture.md` (lines 277-281)
- Accurate research COA capability: `_bmad-output/accurate-research/02-general-ledger.md`

## Dev Agent Record

### Agent Model Used

9router/pp

### Debug Log References

### Completion Notes List

- Story dibuat dari target eksplisit user: `7-1-chart-of-accounts-import`.
- Konteks epic di `epics.md` tidak sinkron dengan sprint key; sprint status dijadikan sumber kebenaran eksekusi.
- Implementasi COA import selesai: parser payload, mapping field, validasi parent/duplicate/cycle, transactional upsert, summary hasil import.
- Endpoint migrasi ditambah: preview dan execute import.
- Test baru lulus: `tests/Unit/Services/ChartOfAccountsImportServiceTest.php` dan `tests/Feature/ChartOfAccountsImportControllerTest.php`.
- `composer test` dan `vendor/bin/pint --test` masih gagal karena isu baseline existing project di file lain yang tidak terkait story ini.

### File List

- _bmad-output/implementation-artifacts/7-1-chart-of-accounts-import.md
- app/Services/ChartOfAccountsImportService.php
- app/Http/Requests/ChartOfAccountsImportRequest.php
- app/Http/Controllers/ChartOfAccountsImportController.php
- routes/web.php
- tests/Unit/Services/ChartOfAccountsImportServiceTest.php
- tests/Feature/ChartOfAccountsImportControllerTest.php

## Change Log

- 2026-05-17: Implementasi Story 7.1 selesai dan status dipindah ke review.

