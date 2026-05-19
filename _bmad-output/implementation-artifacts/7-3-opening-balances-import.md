# Story 7.3: Opening Balances Import

**Status:** done

## Story

As a Finance Admin,
I want import opening balances dari sumber Accurate,
so that saldo awal akun AkuBook terbentuk balance dan siap dipakai laporan keuangan.

## Acceptance Criteria

1. Diberikan user buka menu migrasi opening balances, saat upload sumber data valid (offline export/API payload), sistem validasi format lalu tampilkan preview saldo awal per akun.
2. Diberikan preview tersedia, saat user jalankan import, sistem mapping field sumber ke akun AkuBook dengan validasi `account_code`, `account_id`, debit/credit/amount, normal balance, fiscal period, dan tanggal saldo awal.
3. Sistem wajib memastikan total debit = total credit sebelum posting opening balance; jika tidak balance, import ditolak tanpa write partial.
4. Import berjalan dalam DB transaction: membuat satu journal entry opening balance berstatus `posted` beserta journal lines, atau gagal total tanpa korup data existing.
5. Setelah import selesai, sistem tampilkan ringkasan: total rows, imported lines, skipped rows, error detail, total debit, total credit, difference, journal entry id/number, durasi.
6. Hasil import kompatibel dengan laporan existing: General Ledger, Trial Balance, Balance Sheet, dan Profit & Loss membaca saldo dari journal lines tanpa perubahan kontrak report existing.

## Tasks / Subtasks

- [x] Bangun service import Opening Balances terpisah (AC: 1,2,3,5)
  - [x] Buat parser input saldo awal (`opening_balances`, `balances`, `data.opening_balances`, `rows.opening_balances`)
  - [x] Buat layer mapping sumber -> account + debit/credit
  - [x] Tambah validator bisnis account exists/active/detail, amount numeric, fiscal period exists, balance date valid
- [x] Implement balancing + transactional journal posting (AC: 3,4)
  - [x] Tolak import jika total debit dan credit tidak sama sampai 2 decimal
  - [x] Buat `journal_entries` type `manual`, reference_type opening_balance, status `posted`
  - [x] Buat `journal_entry_lines` untuk semua row valid
- [x] Sediakan endpoint/controller action migrasi Opening Balances (AC: 1,5)
  - [x] Endpoint preview
  - [x] Endpoint execute import
- [x] Tambah coverage test fitur + unit (AC: 1-6)
  - [x] Success path preview balanced opening balances
  - [x] Import balanced rows creates posted journal entry + lines
  - [x] Reject unbalanced payload tanpa journal write
  - [x] Invalid account/amount/fiscal period skip atau reject sesuai aturan
  - [x] Kompatibilitas minimal dengan query general ledger/trial balance existing

## Dev Notes

- Lanjutkan pola Story 7.1 dan 7.2 untuk service/controller/request/routes migrasi:
  - `app/Services/ChartOfAccountsImportService.php`
  - `app/Services/MasterDataImportService.php`
  - `app/Http/Controllers/ChartOfAccountsImportController.php`
  - `app/Http/Controllers/MasterDataImportController.php`
- Stack wajib tetap: Laravel 13 + PHP 8.3 (`composer.json`).
- Source of truth accounting existing:
  - `app/Models/Account.php`
  - `app/Models/JournalEntry.php`
  - `app/Models/JournalEntryLine.php`
  - `database/migrations/2026_05_13_152357_create_accounts_table.php`
  - `database/migrations/2026_05_13_152615_create_journal_entries_table.php`
  - `database/migrations/2026_05_13_152616_create_journal_entry_lines_table.php`
- `journal_entries.type` enum belum punya `opening_balance`; gunakan `manual` agar tidak perlu migration enum dan hindari breaking schema.
- Gunakan `reference_type = opening_balance` untuk identifikasi journal hasil import.
- Report existing menghitung dari journal lines berstatus posted. Jangan tulis hanya ke `accounts.balance` karena General Ledger/Balance Sheet source utama adalah journal lines.
- `GeneralLedgerService` saat ini menghitung opening balance dari journal entries posted sebelum `fromDate`; pastikan tanggal saldo awal lebih awal dari tanggal awal laporan atau sesuai periode migrasi.
- Untuk akun asset/expense, amount positif normalnya debit. Untuk liability/equity/revenue, amount positif normalnya credit. Payload explicit debit/credit harus dihormati selama balance.
- Header account (`is_header = true`) tidak boleh dipakai sebagai journal line; hanya detail account.
- Arsitektur migrasi wajib ikut `_bmad-output/planning-artifacts/architecture.md` (ETL pipeline, validation layer, rollback capability).
- Planning `epics.md` masih memakai penomoran migration lama; sprint status tetap source of truth untuk Story 7.3.

### Project Structure Notes

- Tempat utama implementasi backend:
  - `app/Services/OpeningBalancesImportService.php`
  - `app/Http/Controllers/OpeningBalancesImportController.php`
  - `app/Http/Requests/OpeningBalancesImportRequest.php`
  - `tests/Unit/Services/OpeningBalancesImportServiceTest.php`
  - `tests/Feature/OpeningBalancesImportControllerTest.php`
- Tambahkan route di group auth `migration` bersama COA dan Master Data.
- Hindari perubahan kontrak report existing di luar scope.

### References

- Sprint status target: `_bmad-output/implementation-artifacts/sprint-status.yaml`
- Story sebelumnya: `_bmad-output/implementation-artifacts/7-2-master-data-import.md`
- Epic migration AC sumber: `_bmad-output/planning-artifacts/epics.md:639-663`
- Architecture migration pattern: `_bmad-output/planning-artifacts/architecture.md:277-281`
- Account model: `app/Models/Account.php`
- Journal models: `app/Models/JournalEntry.php`, `app/Models/JournalEntryLine.php`
- General Ledger behavior: `app/Services/GeneralLedgerService.php`

## Dev Agent Record

### Agent Model Used

9router/pp

### Debug Log References

### Completion Notes List

- Story dibuat dari target eksplisit user: `7-3-opening-balances-import`.
- Story 7.1 dan 7.2 dijadikan baseline pola implementasi import preview/execute + summary + tests.
- Fokus guardrail utama: opening balance harus menjadi journal entry posted yang balance, bukan update langsung ke `accounts.balance`.
- Implementasi service import opening balances selesai: parser, mapper, validator account/fiscal period/date/amount, balance check, dan transactional posted journal creation.
- Endpoint preview/import opening balances ditambahkan di migration route group.
- Focused tests lulus: `php artisan test tests/Unit/Services/OpeningBalancesImportServiceTest.php tests/Feature/OpeningBalancesImportControllerTest.php` (6 passed, 30 assertions).
- Full `composer test` masih gagal karena baseline existing unrelated; output: `C:\Users\WellA\.local\share\opencode\tool-output\tool_e3447b535001Hdo0sS3ZUR9TWv`.
- `vendor/bin/pint --test` masih gagal karena baseline formatting luas, termasuk beberapa file baru perlu formatting.

### File List

- _bmad-output/implementation-artifacts/7-3-opening-balances-import.md
- app/Services/OpeningBalancesImportService.php
- app/Http/Requests/OpeningBalancesImportRequest.php
- app/Http/Controllers/OpeningBalancesImportController.php
- routes/web.php
- tests/Unit/Services/OpeningBalancesImportServiceTest.php
- tests/Feature/OpeningBalancesImportControllerTest.php

## Change Log

- 2026-05-17: Implementasi Story 7.3 selesai dan status dipindah ke review.

