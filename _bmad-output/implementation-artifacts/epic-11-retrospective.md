# Epic 11 Retrospective: Inventory Management & Valuation

**Epic:** 11  
**Status:** done  
**Date:** 2026-05-18

## Epic Review

| Story | Status | Outcome |
| --- | --- | --- |
| 11.1 Item Master | review | Item CRUD, inventory metadata, usage counts |
| 11.2 Stock Tracking | review | Stock ledger, balances, manual adjustment |
| 11.3 Stock Opname | review | Count document, variance, adjustment posting |
| 11.4 Stock Transfer | review | Branch transfer, source stock guard, paired stock transactions |
| 11.5 Inventory Valuation | review | Valuation list, stock aggregation, purchase-price fallback |

## Outcomes
- Inventory master data available through `items` CRUD.
- Stock movement ledger available through `stock_transactions`.
- Opname and transfer flows create auditable movement references.
- Branch-level stock transfer support added using `stock_transactions.branch_id`.
- Inventory valuation MVP available from current stock x item purchase price.

## Validation Evidence
- Latest targeted Story 11 tests passed:
  - `tests/Feature/ItemTest.php`
  - `tests/Feature/StockTransactionTest.php`
  - `tests/Feature/StockOpnameTest.php`
  - `tests/Feature/StockTransferTest.php`
  - `tests/Feature/InventoryValuationTest.php`
- Latest `composer test`: PHPUnit passed 249 tests / 771 assertions; composer wrapper still returns code 1 after pass.
- Latest `npm run build`: pass; existing Vite warning remains (`esbuild` option deprecated, use `oxc`).

## What Went Well
- Inventory modules reused existing Laravel/Inertia patterns cleanly.
- Stock ledger became shared foundation for tracking, opname, transfer, and valuation.
- Tests caught critical branch/source stock and valuation calculations.
- Epic 8/9 transaction lessons carried forward into clearer stock references.

## Challenges
- Earlier sales/purchase flows still do not automatically write stock transactions for every real movement.
- Valuation is MVP only: purchase-price fallback, not true moving-average cost layers.
- Branch-level stock existed only after Story 11.4; earlier stock balances are global unless branch_id exists.
- Composer wrapper still reports error code 1 despite PHPUnit success.
- Vite config warning persists.

## Technical Debt
1. Wire stock transactions into Goods Receipt, Purchase Return, Delivery Order, and Sales Return flows.
2. Add cost layer data for real moving-average valuation.
3. Add inventory valuation by date/cutoff.
4. Add branch-aware stock balance views to stock tracking and valuation.
5. Fix composer test wrapper exit code.
6. Fix Vite `esbuild` warning.

## Lessons Learned
- Stock ledger should be introduced before dependent operational flows when possible.
- Branch/location dimension must exist early if transfer and warehouse stock matter.
- MVP valuation needs explicit scope boundaries to avoid pretending full costing exists.
- Story files created just-in-time work, but better pre-generated story context would reduce ambiguity.

## Action Items
- Backend: connect operational documents to `stock_transactions` automatically.
- Finance: define moving-average costing rules and journal requirements.
- QA: add cross-flow stock regression tests from PO receipt through sales delivery.
- Tech debt: fix test wrapper and Vite warning before next large epic.

## Next Epic Preparation
Epic 12 Cash & Bank starts with cash accounts and bank accounts. Before starting:
- Confirm chart-of-accounts mappings for cash/bank.
- Reuse payment/journal patterns from customer/supplier payment modules.
- Keep audit trail pattern consistent with print/inventory history pages.
