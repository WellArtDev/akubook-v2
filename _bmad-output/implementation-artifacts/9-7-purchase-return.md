# Story 9.7: Purchase Return
**Epic:** 9 | **Story ID:** 9.7 | **Key:** 9-7-purchase-return | **Priority:** P1
**Status:** review

## User Story
**Sebagai** Warehouse Staff, **Saya ingin** return goods ke supplier, **Sehingga** handle defective/wrong items

## Acceptance Criteria
- Create return dari GR
- Return number (PRET-YYYY-NNNN)
- Return reason
- Debit note generation
- Inventory update (reduce stock)
- AP reduction

## Notes
- Mirror sales return (Story 8.7)

## Dev Agent Record

### Completion Notes
- Implement Purchase Return flow dari Purchase Invoice (source dokumen hasil GR/PO) dengan nomor otomatis `PRET-YYYY-NNNN`.
- Tambah workflow status: `draft -> approved -> received -> completed` serta opsi `rejected`.
- Receive step mendukung accepted/rejected quantity per line dan validasi agar tidak melebihi qty return.
- Complete step generate jurnal pengurang AP (`DR AP`, `CR Purchase Return/Inventory`, `CR Input Tax` bila ada) untuk AP reduction.
- Implement validasi qty return tidak boleh melebihi sisa qty invoice line.
- Inventory stock movement belum diimplementasi karena modul/entitas stock transaction belum tersedia di repo saat ini.

### File List
- `app/Models/PurchaseReturn.php`
- `app/Models/PurchaseReturnLine.php`
- `app/Http/Controllers/PurchaseReturnController.php`
- `database/migrations/2026_05_17_230458_create_purchase_returns_table.php`
- `database/migrations/2026_05_17_230459_create_purchase_return_lines_table.php`
- `database/factories/PurchaseReturnFactory.php`
- `database/factories/PurchaseReturnLineFactory.php`
- `resources/js/Pages/PurchaseReturns/Index.jsx`
- `resources/js/Pages/PurchaseReturns/Create.jsx`
- `resources/js/Pages/PurchaseReturns/Show.jsx`
- `routes/web.php`
- `tests/Feature/PurchaseReturnTest.php`

### Validation
- `php artisan test tests/Feature/PurchaseReturnTest.php` ✅ (3 tests passed)
- `composer test` ✅ (PHPUnit 218 passed; wrapper masih return code 1 seperti issue sebelumnya)
- `npm run build` ✅ (build pass; warning vite `esbuild` deprecated, pakai `oxc`)

### Change Log
- 2026-05-18: Implemented Story 9.7 Purchase Return MVP, tests, routes, pages, and journal automation.
