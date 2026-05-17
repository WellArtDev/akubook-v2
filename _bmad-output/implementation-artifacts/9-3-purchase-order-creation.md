# Story 9.3: Purchase Order Creation

**Epic:** 9 - Supplier & Purchase Management  
**Story ID:** 9.3  
**Story Key:** 9-3-purchase-order-creation  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** P0 (Foundation)

---

## User Story

**Sebagai** Purchasing Staff  
**Saya ingin** create dan manage purchase orders  
**Sehingga** saya dapat order goods dari suppliers

---

## Business Context

Purchase Order untuk order barang dari supplier:
- **Procurement**: Formal order ke supplier
- **Price Agreement**: Lock in prices
- **Delivery Schedule**: Expected delivery date
- **Approval Workflow**: PO > threshold perlu approval
- **GR Trigger**: Trigger goods receipt process

---

## Acceptance Criteria

### AC1: PO Creation
- PO number (PO-YYYY-NNNN)
- Supplier, delivery address
- Payment terms, delivery terms
- Line items (product, qty, unit price)
- Expected delivery date

### AC2: Create from PR
- Select approved PRs
- Group by supplier
- Copy line items

### AC3: PO Status
- Draft, Pending Approval, Approved, In Progress, Completed, Cancelled

### AC4: Approval Workflow
- PO > Rp 10,000,000 perlu approval
- Similar to SO approval (Story 8.4)

---

## Database Schema

`sql
CREATE TABLE purchase_orders (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    po_number VARCHAR(50) UNIQUE NOT NULL,
    po_date DATE NOT NULL,
    supplier_id BIGINT NOT NULL,
    delivery_address_id BIGINT NOT NULL,
    payment_terms VARCHAR(50),
    delivery_terms VARCHAR(100),
    expected_delivery_date DATE,
    notes TEXT,
    status ENUM('draft', 'pending_approval', 'approved', 'in_progress', 'completed', 'cancelled') DEFAULT 'draft',
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    grand_total DECIMAL(15,2) NOT NULL DEFAULT 0,
    approval_required BOOLEAN DEFAULT FALSE,
    approved_by BIGINT NULL,
    approved_at TIMESTAMP NULL,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE purchase_order_lines (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    purchase_order_id BIGINT NOT NULL,
    line_number INT NOT NULL,
    product_id BIGINT NOT NULL,
    description TEXT,
    quantity DECIMAL(15,3) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    line_total DECIMAL(15,2) NOT NULL,
    received_quantity DECIMAL(15,3) DEFAULT 0,
    invoiced_quantity DECIMAL(15,3) DEFAULT 0
);
`

---

## Definition of Done

- [ ] Migrations, models, controller
- [ ] PO number generation
- [ ] Create from PR
- [ ] Approval workflow
- [ ] Tests (80%+ coverage)
- [ ] Merged to main

---

## Notes

- PO number: PO-YYYY-NNNN
- Approval threshold: Rp 10,000,000
- Mirror SO structure (Story 8.3)
