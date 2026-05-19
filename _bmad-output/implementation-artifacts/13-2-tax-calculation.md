# Story 13.2: Tax Calculation

**Story Key:** `13-2-tax-calculation`  
**Priority:** P0  
**Status:** done

## User Story
Sebagai Finance Staff, saya ingin menghitung pajak transaksi penjualan/pembelian secara konsisten dari konfigurasi pajak agar nilai DPP, pajak, dan total transaksi akurat.

## Acceptance Criteria
1. Sistem dapat menghitung pajak berdasarkan `tax_configurations` aktif/default per `tax_type`.
2. Perhitungan mendukung input `taxable_amount` dan menghasilkan `tax_amount` serta `grand_total`.
3. Mendukung mode `tax_exclusive` dan `tax_inclusive`.
4. Validasi nilai negatif/invalid dan konfigurasi pajak tidak ditemukan.
5. Ada halaman kalkulasi pajak dengan filter tipe pajak dan ringkasan hasil.
6. Tidak mengubah data transaksi sumber (hanya kalkulasi/preview MVP).

## MVP Scope
- Endpoint/page kalkulasi pajak berbasis `tax_configurations`.
- Input: `tax_type`, `taxable_amount`, `is_inclusive`.
- Output: `rate`, `dpp`, `tax_amount`, `grand_total`, `tax_configuration_id`.
- Simpan histori kalkulasi sederhana untuk audit ringan.

## Out of Scope
- Posting jurnal otomatis dari hasil kalkulasi.
- Integrasi final ke faktur/e-faktur.
- Multi-rate compound tax.

## Technical Notes
- Reuse pola CRUD + Inertia dari modul laporan sebelumnya.
- Gunakan default tax config aktif per tipe, fallback bisa pilih config spesifik.
- Pembulatan gunakan 2 desimal konsisten.

## Definition of Done
- [x] Migration + model untuk histori kalkulasi pajak.
- [x] Controller + route + halaman Inertia untuk kalkulasi pajak.
- [x] Logic inclusive/exclusive + validasi.
- [x] Feature tests untuk skenario utama dan edge case.
- [x] `composer test` dan `npm run build` berjalan (warning existing dicatat).

## Dev Agent Record
### Completion Notes
- Implement halaman Tax Calculation (`tax-calculations.index`) untuk preview perhitungan pajak berdasarkan tax configuration aktif/default.
- Tambah API kalkulasi (`tax-calculations.calculate`) untuk pemakaian programatik/validasi.
- Simpan histori kalkulasi ke tabel `tax_calculations` tanpa mutasi transaksi sumber.
- Mendukung mode exclusive dan inclusive dengan hasil `dpp`, `tax_amount`, `grand_total`.

### File List
- _bmad-output/implementation-artifacts/13-2-tax-calculation.md
- database/migrations/2026_05_18_052758_create_tax_calculations_table.php
- app/Models/TaxCalculation.php
- database/factories/TaxCalculationFactory.php
- app/Http/Controllers/TaxCalculationController.php
- routes/web.php
- resources/js/Pages/TaxCalculations/Index.jsx
- tests/Feature/TaxCalculationTest.php

### Validation
- `php artisan test tests/Feature/TaxCalculationTest.php` ✅ pass (4 tests, 6 assertions)
- `composer test` ✅ PHPUnit pass (277 tests, 863 assertions) — wrapper masih return code 1 (known existing issue)
- `npm run build` ✅ pass (existing warning vite `esbuild` deprecated -> `oxc`)

### Change Log
- 2026-05-18: Implemented Story 13.2 Tax Calculation MVP.

