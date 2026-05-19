# Epic 12 Retrospective - Cash & Bank Management

**Epic:** 12  
**Date:** 2026-05-18  
**Status:** done

## Ringkasan Status Story

| Story | Judul | Status | Catatan |
|---|---|---|---|
| 12.1 | Cash Accounts | review | CRUD akun kas + validasi COA current_asset |
| 12.2 | Bank Accounts | review | CRUD akun bank + validasi account number/code unique |
| 12.3 | Bank Reconciliation | review | Header+line rekonsiliasi, match/unmatch, mark reconciled |
| 12.4 | Payment & Receipt Vouchers | review | Voucher payment/receipt + posting jurnal otomatis |
| 12.5 | Cash Flow Report | review | Laporan arus kas dari voucher posted + filter periode |

## Hasil Utama Epic 12
- Fondasi kas & bank sudah terbentuk: master cash account + bank account.
- Alur transaksi inti kas-bank ada: voucher payment/receipt dengan posting jurnal `auto_payment` / `auto_receipt`.
- Rekonsiliasi bank MVP tersedia: input statement line, match/unmatch manual, status reconciled.
- Laporan cash flow MVP tersedia: opening balance + cash in/out + closing balance dari voucher posted.

## Bukti Validasi
- Targeted test story 12.1–12.5 semuanya lulus.
- `composer test`: PHPUnit pass **269 tests / 849 assertions**, namun wrapper masih return code 1 (known issue).
- `npm run build`: pass, warning lama Vite `esbuild` -> `oxc`.

## Yang Berjalan Baik
- Reuse pola CRUD Laravel/Inertia dari epic sebelumnya mempercepat delivery.
- Integrasi ke jurnal konsisten dengan model accounting existing.
- Filter/list/report patterns konsisten antar modul cash, bank, voucher, reconciliation.
- Test feature berhasil jaga regression saat nambah modul baru.

## Tantangan & Gap
- Rekonsiliasi masih manual, belum ada import bank statement / auto-match.
- Cancel voucher belum reversal jurnal otomatis.
- Cash flow masih direct dari voucher, belum klasifikasi operasi/investasi/pendanaan.
- Known infra debt belum beres: composer wrapper false-fail, warning Vite `esbuild`.

## Technical Debt & Action Items
1. Tambah reversal jurnal otomatis saat voucher cancel (dengan guard period/journal status).
2. Tambah import statement + engine auto-match untuk bank reconciliation.
3. Tambah klasifikasi arus kas per kategori untuk laporan cash flow lebih akuntansi-ready.
4. Rapikan pipeline test wrapper supaya hasil pass tidak return error code 1.
5. Migrasi config Vite dari `esbuild` ke `oxc`.

## Kesiapan Epic 13 (Tax)
- Dependency cash-bank untuk pajak cash movement sudah cukup untuk lanjut.
- Pastikan mapping akun pajak + posting rules dipastikan sejak awal Epic 13.
