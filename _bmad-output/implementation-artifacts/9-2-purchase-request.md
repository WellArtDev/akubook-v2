# Story 9.2: Purchase Request

**Epic:** 9 - Supplier & Purchase Management  
**Story ID:** 9.2  
**Story Key:** 9-2-purchase-request  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** P1 (Core)

---

## User Story

**Sebagai** Department Staff  
**Saya ingin** create purchase requests  
**Sehingga** purchasing dapat process procurement

---

## Business Context

Purchase Request (PR) adalah permintaan pembelian:
- **Requisition**: Department request untuk purchase
- **Approval**: Manager approval sebelum PO
- **Budget Control**: Check budget availability
- **Consolidation**: Multiple PRs → 1 PO

---

## Acceptance Criteria

### AC1: PR Creation
- PR number (PR-YYYY-NNNN)
- Requesting department
- Required date
- Line items (product, qty, estimated price)
- Justification/notes

### AC2: PR Approval
- Submit for approval
- Manager approve/reject
- Approval workflow (if amount > threshold)

### AC3: PR Status
- Draft, Pending Approval, Approved, Rejected, Converted to PO, Cancelled

### AC4: Convert to PO
- Select approved PRs
- Group by supplier
- Create PO dari multiple PRs

---

## Database Schema

`sql
CREATE TABLE purchase_requests (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    pr_number VARCHAR(50) UNIQUE NOT NULL,
    pr_date DATE NOT NULL,
    department_id BIGINT NOT NULL,
    required_date DATE NOT NULL,
    justification TEXT,
    status ENUM('draft', 'pending_approval', 'approved', 'rejected', 'converted', 'cancelled') DEFAULT 'draft',
    total_estimated_amount DECIMAL(15,2) DEFAULT 0,
    approved_by BIGINT NULL,
    approved_at TIMESTAMP NULL,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE purchase_request_lines (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    purchase_request_id BIGINT NOT NULL,
    line_number INT NOT NULL,
    product_id BIGINT NOT NULL,
    description TEXT,
    quantity DECIMAL(15,3) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    estimated_price DECIMAL(15,2) NOT NULL,
    line_total DECIMAL(15,2) NOT NULL,
    notes TEXT
);
`

---

## Definition of Done

- [ ] Migrations, models, controller
- [ ] PR number generation
- [ ] Approval workflow
- [ ] Convert to PO
- [ ] Tests (80%+ coverage)
- [ ] Merged to main

---

## Notes

- PR number: PR-YYYY-NNNN
- Approval threshold: Rp 5,000,000
- Multiple PRs can be consolidated into 1 PO
