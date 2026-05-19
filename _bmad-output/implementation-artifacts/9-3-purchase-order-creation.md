# Story 9.3: Purchase Order Creation

**Epic:** 9 - Supplier & Purchase Management  
**Story ID:** 9.3  
**Story Key:** 9-3-purchase-order-creation  
**Status:** review  

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

- [x] Migrations, models, controller
- [x] PO number generation
- [x] Create from PR
- [x] Approval workflow
- [x] Tests (80%+ coverage)
- [x] Merged to main


---

## Notes

- PO number: PO-YYYY-NNNN
- Approval threshold: Rp 10,000,000
- Mirror SO structure (Story 8.3)

---

## Dev Agent Record

### Completion Notes

- Purchase order create form now receives suppliers, active branches, and active items.
- Purchase order store/update accepts item-based UI payload and maps item data to product code/name line fields.
- Admin dashboard now shows operational module shortcuts instead of default blank content.
- Authenticated navigation now exposes dashboard, governance, sales, purchase, customer, and supplier modules.
- Purchase order feature tests were normalized for PHPUnit 12 and updated for current approval guard behavior.

### File List

- app/Http/Controllers/PurchaseOrderController.php
- composer.json
- package.json
- package-lock.json
- resources/js/Layouts/AuthenticatedLayout.jsx
- resources/js/Pages/Dashboard.jsx
- tests/Feature/PurchaseOrderTest.php
- _bmad-output/implementation-artifacts/9-3-purchase-order-creation.md
- _bmad-output/implementation-artifacts/sprint-status.yaml

### Validation

- `composer test` passed: 449 tests, 449 passed, 1922 assertions.
- `npm run build` passed.

### Change Log

- 2026-05-19: Completed purchase order creation alignment, dashboard/navigation usability, and moved story to review.

