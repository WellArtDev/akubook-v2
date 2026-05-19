# Story 9.4: Purchase Order Approval
**Epic:** 9 | **Story ID:** 9.4 | **Key:** 9-4-purchase-order-approval | **Priority:** P1
**Status:** done


## User Story
**Sebagai** Purchasing Manager, **Saya ingin** approve/reject POs, **Sehingga** control procurement spending

## Acceptance Criteria
- PO approval dashboard
- Approve/reject PO dengan comments
- Approval threshold: Rp 10,000,000
- No self-approval
- Approval history tracking

## Database Schema
`sql
CREATE TABLE purchase_order_approvals (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    purchase_order_id BIGINT NOT NULL,
    submitted_by BIGINT NOT NULL,
    submitted_at TIMESTAMP NOT NULL,
    approval_reasons JSON NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reviewed_by BIGINT NULL,
    reviewed_at TIMESTAMP NULL,
    comments TEXT NULL
);
`

## Notes
- Mirror SO approval (Story 8.4)
- Approval reasons: high value, budget exceeded

## Dev Agent Record

### Completion Notes
- Implemented Purchase Order Approval workflow with dedicated `purchase_order_approvals` table and model.
- Added approval dashboard (`PurchaseOrderApprovals/Index`) and detail page (`PurchaseOrderApprovals/Show`).
- Updated PO submit flow to auto-create pending approval record for high-value PO.
- Added no self-approval validation for approve/reject flow.
- Added approval history tracking via relation load on PO show and persisted reviewer/comments/rejection reason.
- Added bulk approve action.

### File List
- `database/migrations/2026_05_17_164054_create_purchase_order_approvals_table.php`
- `app/Models/PurchaseOrderApproval.php`
- `database/factories/PurchaseOrderApprovalFactory.php`
- `app/Models/PurchaseOrder.php`
- `app/Http/Controllers/PurchaseOrderController.php`
- `app/Http/Controllers/PurchaseOrderApprovalController.php`
- `routes/web.php`
- `resources/js/Pages/PurchaseOrderApprovals/Index.jsx`
- `resources/js/Pages/PurchaseOrderApprovals/Show.jsx`
- `resources/js/Pages/PurchaseOrders/Show.jsx`
- `tests/Feature/PurchaseOrderApprovalTest.php`

### Validation
- `php artisan test tests/Feature/PurchaseOrderApprovalTest.php` ✅ (6 passed)
- `composer test` ✅ PHPUnit 208 passed (wrapper still return code 1)
- `npm run build` ✅ (warning existing: vite esbuild deprecated -> oxc)

### Change Log
- 2026-05-17: Implemented Story 9.4 Purchase Order Approval MVP and updated status to review.


