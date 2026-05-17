# Story 8.7: Sales Return

**Epic:** 8 - Customer & Sales Management  
**Story ID:** 8.7  
**Story Key:** 8-7-sales-return  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** P1 (Core)

---

## User Story

**Sebagai** Warehouse Staff  
**Saya ingin** process sales returns dari customer  
**Sehingga** saya dapat handle returned goods dan issue credit notes

---

## Business Context

Sales Return untuk handle barang yang dikembalikan customer:
- **Return Authorization**: RMA process
- **Inventory Update**: Add stock back
- **Credit Note**: Reduce AR
- **Quality Check**: Inspect returned items
- **Refund/Credit**: Process customer refund atau credit

---

## Acceptance Criteria

### AC1: Return Authorization
- Create return dari invoice
- Select items untuk return
- Return quantity <= invoiced quantity
- Return reason (defective, wrong item, etc.)
- RMA number generation (RMA-YYYY-NNNN)

### AC2: Return Inspection
- Receive returned items
- Quality check (accept/reject)
- Update inventory (accepted items only)
- Record inspection notes

### AC3: Credit Note
- Auto-generate credit note
- Reduce customer AR
- Create journal entry (DR: Sales Return, CR: AR)
- Link to original invoice

### AC4: Return Status
- Pending, Approved, Received, Completed, Rejected

---

## Database Schema

`sql
CREATE TABLE sales_returns (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    rma_number VARCHAR(50) UNIQUE NOT NULL,
    return_date DATE NOT NULL,
    sales_invoice_id BIGINT NOT NULL,
    customer_id BIGINT NOT NULL,
    return_reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'received', 'completed', 'rejected') DEFAULT 'pending',
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    credit_note_id BIGINT NULL,
    journal_entry_id BIGINT NULL,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE sales_return_lines (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sales_return_id BIGINT NOT NULL,
    sales_invoice_line_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    return_quantity DECIMAL(15,3) NOT NULL,
    accepted_quantity DECIMAL(15,3) DEFAULT 0,
    rejected_quantity DECIMAL(15,3) DEFAULT 0,
    unit_price DECIMAL(15,2) NOT NULL,
    line_total DECIMAL(15,2) NOT NULL,
    inspection_notes TEXT
);
`

---

## Definition of Done

- [ ] Migrations, models, controller
- [ ] RMA number generation
- [ ] Return authorization workflow
- [ ] Inventory update
- [ ] Credit note generation
- [ ] Journal entry creation
- [ ] Tests (80%+ coverage)
- [ ] Merged to main

---

## Notes

- RMA number: RMA-YYYY-NNNN
- Return window: 30 days dari invoice date
- Quality check required
- Credit note auto-generated saat completed
