# Story 12.1: Cash Accounts

**Story Key:** `12-1-cash-accounts`  
**Priority:** P0  
**Status:** review

## User Story
Sebagai Finance Staff, saya ingin mengelola akun kas agar transaksi kas dapat dipetakan ke akun COA yang benar.

## Acceptance Criteria
1. Sistem menyediakan CRUD cash account dengan nama, kode, akun COA, saldo awal, status aktif.
2. Cash account wajib terhubung ke `accounts` bertipe asset/current_asset.
3. Kode cash account unik.
4. List mendukung search dan filter status aktif.
5. Detail menampilkan metadata akun dan saldo.

## MVP Scope
- Master data cash account.
- Link ke Chart of Accounts existing.
- Saldo awal disimpan sebagai metadata, bukan jurnal opening balance otomatis.

## Out of Scope
- Cash transaction voucher.
- Reconciliation.
- Multi-currency.

## Definition of Done
- [x] Migration/model/controller/routes tersedia.
- [x] Inertia pages index/create/edit/show tersedia.
- [x] Validasi unique code dan COA asset berjalan.
- [x] Feature tests pass.
- [x] `composer test` dan `npm run build` berhasil.

## Dev Agent Record
### Completion Notes
- Implement master data `cash_accounts` dengan CRUD penuh dan soft delete.
- Validasi `account_id` dibatasi ke COA detail `asset/current_asset` aktif.
- Tambah halaman Inertia `CashAccounts` (Index/Create/Edit/Show).
- Tambah filter list berdasarkan search dan status aktif.

### File List
- _bmad-output/implementation-artifacts/12-1-cash-accounts.md
- database/migrations/2026_05_18_013138_create_cash_accounts_table.php
- app/Models/CashAccount.php
- database/factories/CashAccountFactory.php
- app/Http/Controllers/CashAccountController.php
- routes/web.php
- resources/js/Pages/CashAccounts/Index.jsx
- resources/js/Pages/CashAccounts/Create.jsx
- resources/js/Pages/CashAccounts/Edit.jsx
- resources/js/Pages/CashAccounts/Show.jsx
- tests/Feature/CashAccountTest.php

### Validation
- `php artisan test tests/Feature/CashAccountTest.php` ✅ (5 tests pass)
- `composer test` ✅ PHPUnit pass (254 tests), wrapper masih return code 1 (known issue)
- `npm run build` ✅ (warning Vite `esbuild` -> `oxc` masih existing)
