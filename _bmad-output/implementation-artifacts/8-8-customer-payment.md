# Story 8.8: Customer Payment

**Epic:** 8 - Customer & Sales Management  
**Story ID:** 8.8  
**Story Key:** 8-8-customer-payment  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** P0 (Foundation)

---

## User Story

**Sebagai** Finance Staff  
**Saya ingin** record customer payments  
**Sehingga** saya dapat track AR dan reconcile bank accounts

---

## Business Context

Customer Payment untuk record pembayaran dari customer:
- **Payment Recording**: Record cash/bank/transfer
- **Invoice Allocation**: Allocate payment ke invoices
- **AR Reduction**: Reduce customer outstanding
- **Bank Reconciliation**: Match dengan bank statement
- **Journal Entry**: Auto-create accounting entries

---

## Acceptance Criteria

### AC1: Payment Recording
- Payment number (PAY-YYYY-NNNN)
- Payment date, amount
- Customer, payment method
- Bank account (if bank transfer)
- Reference number

### AC2: Invoice Allocation
- Select unpaid/partial invoices
- Allocate payment amount
- Support partial payment
- Support overpayment (unapplied cash)

### AC3: Payment Methods
- Cash, Bank Transfer, Check, Credit Card, Giro

### AC4: Journal Entry
- DR: Cash/Bank
- CR: Accounts Receivable
- Handle unapplied cash

### AC5: Payment Status
- Draft, Posted, Reconciled, Voided

---

## Database Schema

`sql
CREATE TABLE customer_payments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    payment_number VARCHAR(50) UNIQUE NOT NULL,
    payment_date DATE NOT NULL,
    customer_id BIGINT NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'check', 'credit_card', 'giro') NOT NULL,
    bank_account_id BIGINT NULL,
    reference_number VARCHAR(100),
    total_amount DECIMAL(15,2) NOT NULL,
    allocated_amount DECIMAL(15,2) DEFAULT 0,
    unapplied_amount DECIMAL(15,2) DEFAULT 0,
    status ENUM('draft', 'posted', 'reconciled', 'voided') DEFAULT 'draft',
    journal_entry_id BIGINT NULL,
    notes TEXT,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE customer_payment_allocations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    customer_payment_id BIGINT NOT NULL,
    sales_invoice_id BIGINT NOT NULL,
    allocated_amount DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
`

---

## Definition of Done

- [ ] Migrations, models, controller
- [ ] Payment number generation
- [ ] Invoice allocation
- [ ] Multiple payment methods
- [ ] Journal entry creation
- [ ] Unapplied cash handling
- [ ] Tests (80%+ coverage)
- [ ] Merged to main

---

## Notes

- Payment number: PAY-YYYY-NNNN
- Support partial payment
- Unapplied cash: Payment > allocated
- Cannot void if reconciled
