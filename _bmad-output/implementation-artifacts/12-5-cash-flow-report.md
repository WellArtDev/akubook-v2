# Story 12.5: Cash Flow Report

**Story Key:** `12-5-cash-flow-report`  
**Priority:** P0  
**Status:** review

## User Story
Sebagai **Finance Staff**, saya ingin melihat laporan arus kas periode tertentu agar bisa memantau arus masuk/keluar kas-bank dan posisi saldo bersih operasional.

## Acceptance Criteria
1. User dapat membuka laporan arus kas berdasarkan rentang tanggal (`date_from`, `date_to`).
2. Laporan menghitung total **cash in** dan **cash out** dari voucher posted:
   - Receipt voucher -> cash in
   - Payment voucher -> cash out
3. Laporan menampilkan ringkasan: opening balance, total in, total out, closing balance.
4. Laporan menampilkan detail transaksi voucher (tanggal, nomor, tipe, akun kas/bank, lawan akun, amount).
5. Filter tambahan: jenis akun kas/bank (`cash`/`bank`) dan akun kas/bank tertentu.

## MVP Scope
- Data source utama: tabel `vouchers` status `posted`.
- Opening balance sederhana: sum opening balance akun kas/bank terpilih.
- Closing balance: opening + in - out.
- Halaman Inertia tunggal `CashFlowReports/Index` dengan filter + tabel detail.

## Out of Scope
- Klasifikasi arus kas operasi/investasi/pendanaan.
- Integrasi langsung ke rekonsiliasi bank otomatis.
- Export PDF/Excel khusus cash flow.

## Definition of Done
- [x] Endpoint/controller cash flow report selesai.
- [x] Halaman Inertia cash flow report selesai.
- [x] Feature tests validasi kalkulasi in/out/opening/closing.
- [x] `composer test` dan `npm run build` lulus.

## Dev Agent Record

### Completion Notes
- Added `CashFlowReportController@index` with filters: period, cash/bank type, account.
- Report source uses posted vouchers, with summary opening/in/out/closing balances.
- Added Inertia page `CashFlowReports/Index` for filter, summary cards, and transaction detail table.
- Added feature tests for page access, summary calculation, and bank-type filtering.

### File List
- `app/Http/Controllers/CashFlowReportController.php`
- `resources/js/Pages/CashFlowReports/Index.jsx`
- `tests/Feature/CashFlowReportTest.php`
- `routes/web.php`
- `_bmad-output/implementation-artifacts/12-5-cash-flow-report.md`

### Validation
- `php artisan test tests/Feature/CashFlowReportTest.php` passed: 3 tests, 35 assertions.
- `composer test` PHPUnit passed: 269 tests, 849 assertions. Wrapper still returns known code 1 after PHPUnit pass.
- `npm run build` passed with existing Vite warning: `esbuild` deprecated by `vite:react-babel`, use `oxc`.

### Change Log
- 2026-05-18: Implemented Story 12.5 Cash Flow Report MVP.
