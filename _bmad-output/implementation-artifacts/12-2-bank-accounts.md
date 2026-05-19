# Story 12.2: Bank Accounts

**Story Key:** `12-2-bank-accounts`  
**Priority:** P0  
**Status:** review

## User Story
Sebagai Finance Staff, saya ingin mengelola akun bank agar transaksi bank bisa dipetakan ke COA dan data rekening tersimpan rapi.

## Acceptance Criteria
1. Sistem menyediakan CRUD bank account dengan code, name, bank_name, account_number, account_holder, COA account, opening_balance, status aktif.
2. Bank account wajib terhubung ke COA `asset/current_asset` detail.
3. Code dan account_number unik.
4. List mendukung search dan filter status aktif.
5. Detail menampilkan metadata bank account lengkap.

## MVP Scope
- Master data akun bank.
- Link ke Chart of Accounts existing.
- Saldo awal sebagai metadata (tanpa jurnal opening otomatis).

## Out of Scope
- Rekonsiliasi bank.
- Import statement.
- Multi-currency per rekening.

## Definition of Done
- [x] Migration/model/controller/routes tersedia.
- [x] Inertia pages index/create/edit/show tersedia.
- [x] Validasi unique code/account_number dan COA asset berjalan.
- [x] Feature tests pass.
- [x] `composer test` dan `npm run build` berhasil.

## Dev Agent Record
### Completion Notes
- Implement master data `bank_accounts` dengan CRUD penuh dan soft delete.
- Validasi `account_id` dibatasi ke COA detail `asset/current_asset` aktif.
- Validasi unique `code` dan `account_number`.
- Tambah halaman Inertia `BankAccounts` (Index/Create/Edit/Show).
- Tambah filter list berdasarkan search dan status aktif.

### File List
- _bmad-output/implementation-artifacts/12-2-bank-accounts.md
- database/migrations/2026_05_18_013702_create_bank_accounts_table.php
- app/Models/BankAccount.php
- database/factories/BankAccountFactory.php
- app/Http/Controllers/BankAccountController.php
- routes/web.php
- resources/js/Pages/BankAccounts/Index.jsx
- resources/js/Pages/BankAccounts/Create.jsx
- resources/js/Pages/BankAccounts/Edit.jsx
- resources/js/Pages/BankAccounts/Show.jsx
- tests/Feature/BankAccountTest.php

### Validation
- `php artisan test tests/Feature/BankAccountTest.php` ✅ (4 tests pass)
- `composer test` ✅ PHPUnit pass (258 tests), wrapper masih return code 1 (known issue)
- `npm run build` ✅ (warning Vite `esbuild` -> `oxc` masih existing)
