# Story 8.10: Customer Statement

**Epic:** 8 - Customer & Sales Management  
**Story ID:** 8.10  
**Story Key:** 8-10-customer-statement  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** P2 (Nice to Have)

---

## User Story

**Sebagai** Finance Staff  
**Saya ingin** generate customer statements  
**Sehingga** customer dapat see transaction history dan outstanding balance

---

## Business Context

Customer Statement adalah summary transaksi customer:
- **Transaction History**: Invoices, payments, returns
- **Outstanding Balance**: Current AR balance
- **Aging**: Breakdown by age
- **Send to Customer**: Email PDF statement

---

## Acceptance Criteria

### AC1: Statement Generation
- Select customer & date range
- Show opening balance
- List all transactions (invoices, payments, credit notes)
- Show closing balance
- Show aging breakdown

### AC2: Statement Format
- Company header
- Customer details
- Transaction table (date, type, reference, debit, credit, balance)
- Summary section (total invoices, payments, balance)
- Aging table

### AC3: Send Statement
- Generate PDF
- Send via email
- Log sent statements

---

## Database Schema

`sql
CREATE TABLE customer_statement_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT NOT NULL,
    statement_date DATE NOT NULL,
    date_from DATE NOT NULL,
    date_to DATE NOT NULL,
    opening_balance DECIMAL(15,2) NOT NULL,
    closing_balance DECIMAL(15,2) NOT NULL,
    sent_to VARCHAR(255),
    sent_at TIMESTAMP NULL,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
`

---

## Definition of Done

- [ ] CustomerStatementService created
- [ ] Statement generation logic
- [ ] PDF template
- [ ] Email sending
- [ ] Statement log
- [ ] Tests (80%+ coverage)
- [ ] Merged to main

---

## Notes

- PDF via Laravel DomPDF
- Email via Laravel Mail
- Statement period: Usually monthly
