# Epic 9 Retrospective: Supplier & Purchasing Management

**Date:** 2026-05-18
**Epic:** 9
**Status:** done

## Epic Review

Epic 9 membangun jalur purchasing inti dari supplier sampai return:

| Story | Artifact | Sprint Status | Result |
| --- | --- | --- | --- |
| 9.1 | Supplier CRUD | done | Baseline supplier master tersedia |
| 9.2 | Purchase Request | done | Baseline PR tersedia |
| 9.3 | Purchase Order Creation | review | PO flow tersedia, tapi artifact status masih `ready-for-dev` |
| 9.4 | Purchase Order Approval | review | Approval dashboard, history, no self-approval |
| 9.5 | Goods Receipt | review | GR dari PO, partial receipt, quality inspection, PO received qty update |
| 9.6 | Purchase Invoice | review | Invoice dari GR, 3-way matching, AP journal automation |
| 9.7 | Purchase Return | review | Return dari purchase invoice, AP reduction journal automation |

## Outcomes

- Purchasing flow tersambung: PO → Approval → Goods Receipt → Purchase Invoice → Purchase Return.
- Approval pattern dari Sales Order berhasil dipakai ulang untuk PO Approval.
- Journal automation purchase invoice dan purchase return memakai account mapping:
  - Inventory/Expense: `1-1400`
  - Input Tax: `1-1500`
  - AP: `2-1100`
- Quantity tracking mulai konsisten di purchase flow:
  - `purchase_order_lines.received_quantity`
  - `purchase_order_lines.invoiced_quantity`
  - GR accepted/rejected quantity
  - Invoice remaining quantity
  - Return accepted/rejected quantity

## Validation Evidence

- `php artisan test tests/Feature/PurchaseOrderApprovalTest.php` passed.
- `php artisan test tests/Feature/GoodsReceiptTest.php` passed.
- `php artisan test tests/Feature/PurchaseInvoiceTest.php` passed.
- `php artisan test tests/Feature/PurchaseReturnTest.php` passed.
- Latest `composer test`: PHPUnit 218/218 passed, wrapper still returned code 1.
- Latest `npm run build`: passed, with existing Vite warning: `esbuild` option deprecated by `vite:react-babel`, use `oxc`.

## What Went Well

- Reuse pattern kuat dari Epic 8 mempercepat PO approval, GR, purchase invoice, dan purchase return.
- Tests jadi guardrail utama untuk validation rules, status transitions, and journal creation.
- Purchase document numbers standardized:
  - `GR-YYYY-NNNN`
  - `PINV-YYYY-NNNN`
  - `PRET-YYYY-NNNN`
- Partial receipt and partial invoice logic sudah ada sebagai foundation untuk inventory/AP.

## Challenges

- Sprint key/artifact mismatch masih besar:
  - Sprint `9-4-goods-receipt` ternyata artifact `9-4-purchase-order-approval.md`.
  - Sprint `9-5-purchase-invoice` ternyata artifact `9-5-goods-receipt.md`.
  - Sprint `9-6-purchase-return` ternyata artifact `9-6-purchase-invoice.md`.
  - Sprint `9-7-supplier-payments` ternyata artifact `9-7-purchase-return.md`.
- `9-3-purchase-order-creation.md` masih berstatus `ready-for-dev` meski sprint `9-3-purchase-order` sudah `review`.
- Inventory stock transaction module belum ada, jadi GR/return baru update document quantities, belum stock ledger fisik.
- Composer wrapper masih return code 1 walau PHPUnit pass.
- Vite build warning tetap belum dibersihkan.

## Technical Debt

1. Rapikan mapping sprint key vs artifact filename untuk Epic 9.
2. Update status `9-3-purchase-order-creation.md` agar sinkron dengan sprint status.
3. Implement inventory transaction/stock ledger untuk:
   - `purchase_receipt`
   - `purchase_return`
   - sales delivery/return impacts bila dibutuhkan.
4. Implement Supplier Payment story sebenarnya, karena sprint key `9-7-supplier-payments` dipakai untuk purchase return akibat mismatch.
5. Add journal reversal/cancel behavior untuk posted purchase invoices/returns.
6. Fix `composer test` wrapper exit code.
7. Fix Vite warning (`esbuild` → `oxc`).

## Lessons Learned

- Naming drift antara sprint dan story artifact membuat risiko salah implementasi tinggi. Harus ada preflight alignment sebelum coding.
- Reusing established sales patterns efektif, tapi perlu explicit accounting differences antara AR/AP.
- Quantity fields harus dirancang sebagai ledger-like flow; document quantity update cukup untuk MVP, tapi belum cukup untuk inventory valuation.
- Minimal feature tests per workflow menangkap mayoritas regression di status transition dan accounting journal.

## Action Items

| Priority | Action | Owner | Success Criteria |
| --- | --- | --- | --- |
| High | Normalize Epic 9 sprint/artifact mapping | Product/PM | sprint key cocok dengan story file |
| High | Create real Supplier Payments story/artifact | Product/PM | supplier payment scope tidak hilang |
| High | Add inventory transaction module | Architect/Dev | GR and purchase return create stock ledger entries |
| Medium | Add cancel/reversal journals | Dev | posted purchase invoice/return cancel creates reversing journal |
| Medium | Fix `composer test` wrapper | DevOps/Dev | command exits 0 when PHPUnit passes |
| Low | Fix Vite `esbuild` warning | Frontend/Dev | build output clean |

## Next Epic Preparation

Epic 10 Document Printing System should start after:

- Document names/statuses are aligned enough for print templates.
- Core purchase/sales document pages expose stable IDs and show views.
- Print candidates are prioritized: quotation, SO, DO, invoice, GR, PO, purchase invoice, return documents.
- Dot-matrix layout requirements are confirmed.

## Retrospective Decision

Epic 9 ready to close as implementation review complete, with known follow-up debt tracked above.
