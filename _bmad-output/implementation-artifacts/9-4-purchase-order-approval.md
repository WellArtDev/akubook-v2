# Story 9.4: Purchase Order Approval
**Epic:** 9 | **Story ID:** 9.4 | **Key:** 9-4-purchase-order-approval | **Priority:** P1

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
