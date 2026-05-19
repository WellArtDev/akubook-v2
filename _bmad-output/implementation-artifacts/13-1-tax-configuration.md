# Story 13.1: Tax Configuration

**Story Key:** `13-1-tax-configuration`  
**Priority:** P0  
**Status:** done

## User Story
Sebagai **Finance/Tax Admin**, saya ingin mengelola konfigurasi pajak agar persentase PPN/withholding dan akun pajak dapat digunakan konsisten oleh modul penjualan, pembelian, dan pelaporan pajak.

## Acceptance Criteria
1. User dapat membuat konfigurasi pajak dengan kode, nama, tipe pajak, rate, akun pajak, status aktif, dan flag default.
2. Kode pajak unik dan tipe pajak wajib salah satu: `ppn_out`, `ppn_in`, `withholding`.
3. Hanya satu konfigurasi default aktif per tipe pajak.
4. User dapat list/filter konfigurasi berdasarkan tipe, status, dan pencarian kode/nama.
5. User dapat melihat detail konfigurasi pajak dan mengubah/menghapus konfigurasi non-default jika tidak dipakai.

## MVP Scope
- CRUD konfigurasi pajak.
- Integrasi ke `accounts` untuk akun pajak.
- Default tax config per tipe sebagai basis Story 13.2.

## Out of Scope
- Kalkulasi pajak transaksi otomatis.
- Faktur pajak / e-Faktur.
- Multi-rate per effective date.

## Definition of Done
- [x] Migration + model TaxConfiguration selesai.
- [x] Controller + routes + pages CRUD selesai.
- [x] Default uniqueness per tax type terjaga.
- [x] Feature tests tax config selesai.
- [x] `composer test` dan `npm run build` lulus.

## Dev Agent Record

### Completion Notes
- Implemented `tax_configurations` master data with code/name/type/rate/account/default/active fields.
- Added default uniqueness handling per tax type when create/update marks a config as default.
- Added CRUD controller, routes, Inertia pages, and feature tests.
- Prepared default tax config basis for Story 13.2 tax calculation.

### File List
- `database/migrations/2026_05_18_050457_create_tax_configurations_table.php`
- `app/Models/TaxConfiguration.php`
- `database/factories/TaxConfigurationFactory.php`
- `app/Http/Controllers/TaxConfigurationController.php`
- `resources/js/Pages/TaxConfigurations/Index.jsx`
- `resources/js/Pages/TaxConfigurations/Create.jsx`
- `resources/js/Pages/TaxConfigurations/Edit.jsx`
- `resources/js/Pages/TaxConfigurations/Show.jsx`
- `tests/Feature/TaxConfigurationTest.php`
- `routes/web.php`
- `_bmad-output/implementation-artifacts/13-1-tax-configuration.md`

### Validation
- `php artisan test tests/Feature/TaxConfigurationTest.php` passed: 4 tests, 8 assertions.
- `composer test` PHPUnit passed: 273 tests, 857 assertions. Wrapper still returns known code 1 after PHPUnit pass.
- `npm run build` passed with existing Vite warning: `esbuild` deprecated by `vite:react-babel`, use `oxc`.

### Change Log
- 2026-05-18: Implemented Story 13.1 Tax Configuration MVP.

