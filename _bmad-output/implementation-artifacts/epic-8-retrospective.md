# Epic 8 Retrospective: Customer & Sales Management

**Date:** 2026-05-17  
**Epic:** 8 - Customer & Sales Management  
**Status:** done  
**Facilitator:** Amelia (Developer)

## Epic Review

Epic 8 moved sales flow from customer setup through quotation, sales order approval, delivery, invoice, and return handling. All Epic 8 story keys in sprint tracking are now ready for review, and retrospective is complete.

| Sprint Key | Implemented Artifact | Status |
| --- | --- | --- |
| 8-1-customer-crud | Customer CRUD | review |
| 8-2-quotation | Sales Quotation | review |
| 8-3-sales-order | Sales Order Creation | review |
| 8-4-delivery-order | Sales Order Approval | review |
| 8-5-sales-invoice | Delivery Order | review |
| 8-6-sales-return | Sales Invoice | review |
| 8-7-customer-payments | Sales Return | review |

## Outcomes

- Sales Quotation MVP implemented with draft CRUD, line calculations, status workflow, revision/duplicate, and quote-to-order conversion.
- Sales Order workflow expanded with approval, reject, cancel, duplicate, and workflow audit fields.
- Sales Order Approval dashboard implemented with pending review, reject, approve, and bulk approve.
- Delivery Order MVP implemented with SO line fulfillment and delivered quantity tracking.
- Sales Invoice flow aligned to delivered DO lines with partial invoicing guardrails, tax invoice option, and journal posting.
- Sales Return MVP implemented with RMA generation, return quantity validation, approval/receive/complete/reject workflow, and return journal generation.
- Feature tests added across new sales flows.

## Validation Evidence

- `php artisan test tests/Feature/SalesReturnTest.php`: passed 4 tests / 14 assertions.
- `composer test`: PHPUnit reported 202 tests / 619 assertions passed; composer wrapper still returned error code 1 after pass output.
- `npm run build`: passed.
- Existing build warning remains: Vite `esbuild` option deprecated by `vite:react-babel`; use `oxc`.

## What Went Well

- Existing Laravel/Inertia patterns were reusable: resource controllers, factories, feature tests, and status workflows stayed consistent.
- Sales documents now connect better as one operational chain: Quotation -> Sales Order -> Approval -> Delivery Order -> Sales Invoice -> Sales Return.
- Feature tests caught real issues early: enum mismatch, SQLite constraint rebuild needs, missing factory/schema mismatch, return line NOT NULL totals, and invalid journal type.
- Journal mapping improved toward consistent AR/revenue/tax behavior.

## Challenges

- Sprint keys and story files diverged for multiple Epic 8 items:
  - `8-4-delivery-order` sprint key mapped to Sales Order Approval story.
  - `8-5-sales-invoice` sprint key mapped to Delivery Order story.
  - `8-6-sales-return` sprint key mapped to Sales Invoice story.
  - `8-7-customer-payments` sprint key mapped to Sales Return story.
- SQLite test migrations needed table rebuilds to preserve enum/check constraints after schema changes.
- Some acceptance criteria depended on modules not yet present or not modeled deeply enough.
- Composer wrapper returns non-zero despite PHPUnit pass output, creating noisy validation signal.

## Gaps And Technical Debt

1. Story/sprint mapping cleanup needed.
   - Current sprint keys no longer match implemented artifact names for 8-4 through 8-7.
   - Risk: future agents may implement wrong feature or duplicate work.

2. Sales Return inventory update deferred.
   - Return receive/complete workflow records accepted/rejected quantity, but does not adjust stock because inventory ledger module is not yet available.

3. Dedicated credit note entity deferred.
   - Sales Return creates accounting journal but not a separate credit note document/module.

4. Customer Payment story remains only partly represented by existing payment code.
   - Sprint key said customer payments, but actual story file was sales return.
   - Need dedicated customer payment artifact or status correction later.

5. Build warning remains.
   - Vite React plugin warning should be handled before it becomes noise in CI.

## Lessons Learned

- File artifact is source of implementation truth when sprint key mismatches story filename, but the mismatch must be recorded immediately.
- Tests should cover full business transitions, not only create/read paths.
- Accounting workflows need valid enum/type values checked against migration constraints before writing model logic.
- SQLite constraint behavior requires careful migration strategy when altering tables in Laravel tests.
- Placeholder/deferred ACs should be explicit in story Dev Agent Record, not silently marked complete.

## Action Items

| Priority | Action | Owner | Success Criteria |
| --- | --- | --- | --- |
| High | Normalize Epic 8 sprint keys and artifact filenames | Product/Dev | Sprint keys match story filenames and implemented modules. |
| High | Create follow-up story for Sales Return inventory adjustment | Product/Inventory Dev | Accepted returned qty creates stock movement once inventory ledger exists. |
| High | Create follow-up story for Credit Note module | Product/Accounting Dev | Sales return can generate linked credit note and AR adjustment document. |
| Medium | Create or restore Customer Payments story artifact | Product | Sprint `8-7-customer-payments` has matching story or is renamed accurately. |
| Medium | Investigate `composer test` non-zero wrapper despite PHPUnit pass | DevOps/Dev | `composer test` exits 0 when PHPUnit passes. |
| Low | Fix Vite React plugin warning | Frontend Dev | `npm run build` completes without deprecated `esbuild` warning. |

## Next Epic Preparation

Epic 9 is Supplier & Purchasing Management. Current sprint state shows:

- `9-1-supplier-crud`: done
- `9-2-purchase-request`: done
- `9-3-purchase-order`: review
- `9-4-goods-receipt`: backlog
- `9-5-purchase-invoice`: backlog
- `9-6-purchase-return`: backlog
- `9-7-supplier-payments`: backlog

Preparation before deeper Epic 9 work:

- Apply Epic 8 lesson: verify story filename, story key, and sprint key before implementation starts.
- Reuse sales flow patterns for purchasing equivalents, but avoid copying naming mismatches.
- Confirm inventory ledger boundaries before Goods Receipt and Purchase Return.
- Confirm AP account mappings before Purchase Invoice and Supplier Payments.

## Retrospective Decision

Epic 8 retrospective complete. Epic 8 remains with stories in `review`; next best workflow is code review/final QA for the sales chain, then cleanup follow-up stories before or alongside Epic 9 purchasing work.
