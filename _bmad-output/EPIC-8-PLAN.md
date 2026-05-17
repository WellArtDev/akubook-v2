# Epic 8: Customer & Sales Management - Implementation Plan

## Overview
**Goal**: Complete sales cycle from quotation to payment collection
**Stories**: 12-15 stories
**Status**: Ready for implementation (Epic 1-7 complete)

## Prerequisites ✅
- [x] User Management (Epic 2)
- [x] Organization Structure (Epic 3)
- [x] Chart of Accounts (Epic 4)
- [x] Journal Entry System (Epic 5)

## Story Breakdown

### 8.1: Customer Master Data
**Priority**: P0 (Foundation)
- Customer CRUD operations
- Customer categories
- Credit limit management
- Customer contacts
- Customer addresses (billing/shipping)

**Acceptance Criteria**:
- Create/Edit/Delete customers
- Unique customer code
- Credit limit validation
- Multiple contacts per customer
- Audit logging

### 8.2: Sales Quotation
**Priority**: P1
- Create quotation from scratch
- Add items with pricing
- Calculate totals (subtotal, tax, discount)
- Print quotation
- Convert to Sales Order

**Acceptance Criteria**:
- Quotation numbering (auto)
- Item selection with stock check
- Tax calculation (PPN 11%)
- Discount (per item & total)
- Quotation expiry date
- Status: Draft, Sent, Accepted, Rejected

### 8.3: Sales Order Creation
**Priority**: P0 (Core)
- Create SO from quotation or scratch
- Item selection with stock availability
- Pricing & discount
- Delivery terms
- Payment terms

**Acceptance Criteria**:
- SO numbering (auto)
- Stock reservation
- Credit limit check
- Multi-warehouse support
- Status: Draft, Confirmed, Delivered, Invoiced

### 8.4: Sales Order Approval
**Priority**: P1
- Approval workflow
- Approval rules (amount threshold)
- Multi-level approval
- Rejection with reason

**Acceptance Criteria**:
- Configurable approval rules
- Email notifications
- Approval history
- Separation of duties

### 8.5: Delivery Order
**Priority**: P0 (Core)
- Create DO from SO
- Partial delivery support
- Delivery tracking
- Stock deduction
- Auto journal entry

**Acceptance Criteria**:
- DO numbering (auto)
- Stock validation
- Batch/serial tracking
- Delivery status
- Journal: Dr. COGS, Cr. Inventory

### 8.6: Sales Invoice
**Priority**: P0 (Core)
- Create invoice from DO
- Multiple DO to one invoice
- Tax calculation
- Payment terms
- Auto journal entry

**Acceptance Criteria**:
- Invoice numbering (auto)
- Tax invoice (Faktur Pajak)
- Due date calculation
- Journal: Dr. AR, Cr. Sales, Cr. Tax Payable

### 8.7: Sales Return
**Priority**: P2
- Return from invoice
- Partial return support
- Return reason
- Stock adjustment
- Credit note

**Acceptance Criteria**:
- Return numbering (auto)
- Stock return to warehouse
- Credit note generation
- Journal: Dr. Sales Return, Cr. AR

### 8.8: Customer Payment
**Priority**: P0 (Core)
- Record payment
- Payment allocation to invoices
- Multiple payment methods
- Bank reconciliation link
- Auto journal entry

**Acceptance Criteria**:
- Payment numbering (auto)
- Partial payment support
- Payment allocation
- Journal: Dr. Cash/Bank, Cr. AR

### 8.9: Sales Reports
**Priority**: P1
- Sales by customer
- Sales by product
- Sales by period
- Outstanding AR
- Aging analysis

**Acceptance Criteria**:
- Filterable reports
- Export to Excel/PDF
- Drill-down capability
- Real-time data

### 8.10: Customer Statement
**Priority**: P2
- Generate customer statement
- Period selection
- Outstanding balance
- Payment history
- Email to customer

### 8.11: Sales Dashboard
**Priority**: P2
- Sales metrics
- Top customers
- Top products
- Sales trend chart
- AR aging chart

### 8.12: Sales Order Bulk Actions
**Priority**: P3
- Bulk confirm
- Bulk print
- Bulk export
- Bulk status update

## Technical Requirements

### Database Tables
- [x] customers (exists)
- [x] sales_orders (exists)
- [x] sales_order_lines (exists)
- [x] delivery_orders (exists)
- [x] delivery_order_lines (exists)
- [x] invoices (exists)
- [x] invoice_lines (exists)
- [x] sales_returns (exists)
- [x] sales_return_lines (exists)
- [ ] customer_payments
- [ ] customer_payment_allocations
- [ ] quotations
- [ ] quotation_lines

### Controllers
- [x] CustomerController (exists)
- [x] SalesOrderController (exists)
- [ ] QuotationController
- [ ] DeliveryOrderController
- [ ] InvoiceController
- [ ] SalesReturnController
- [ ] CustomerPaymentController

### Pages (React)
- [x] Customers/Index.jsx (exists)
- [x] SalesOrders/Index.jsx (exists)
- [ ] Quotations/Index.jsx
- [ ] Quotations/Create.jsx
- [ ] SalesOrders/Create.jsx
- [ ] SalesOrders/Show.jsx
- [ ] DeliveryOrders/Index.jsx
- [ ] Invoices/Index.jsx
- [ ] SalesReturns/Index.jsx
- [ ] CustomerPayments/Index.jsx

## Implementation Order

### Phase 1: Foundation (Stories 8.1, 8.3)
1. Complete Customer CRUD
2. Complete Sales Order CRUD
3. Test basic flow

### Phase 2: Core Flow (Stories 8.5, 8.6, 8.8)
1. Delivery Order
2. Sales Invoice
3. Customer Payment
4. Test complete cycle

### Phase 3: Extended (Stories 8.2, 8.4, 8.7)
1. Quotation
2. Approval workflow
3. Sales Return

### Phase 4: Reporting (Stories 8.9, 8.10, 8.11)
1. Sales reports
2. Customer statement
3. Dashboard

## Next Steps

1. **Review existing code**:
   - Check CustomerController implementation
   - Check SalesOrderController implementation
   - Verify database schema

2. **Create missing migrations**:
   - customer_payments table
   - quotations table

3. **Start with Story 8.1**:
   - Complete Customer CRUD UI
   - Add validation
   - Add tests

4. **Run bmad-create-story** for each story
5. **Run bmad-dev-story** to implement
6. **Run bmad-code-review** after each story

## Estimated Timeline
- Phase 1: 2-3 days
- Phase 2: 3-4 days
- Phase 3: 2-3 days
- Phase 4: 2-3 days
- **Total**: 9-13 days (with testing & review)
