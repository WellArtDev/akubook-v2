# Story 8.6: Sales Invoice

**Epic:** 8 - Customer & Sales Management  
**Story ID:** 8.6  
**Story Key:** 8-6-sales-invoice  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** P0 (Foundation)

---

## User Story

**Sebagai** Finance Staff  
**Saya ingin** create dan manage sales invoices  
**Sehingga** saya dapat bill customers dan track accounts receivable

---

## Business Context

Sales Invoice adalah dokumen tagihan ke customer:
- **Revenue Recognition**: Record revenue saat invoice
- **Accounts Receivable**: Track customer debt
- **Tax Compliance**: Generate tax invoice (Faktur Pajak)
- **Payment Tracking**: Link invoice to payments
- **Journal Entry**: Auto-create accounting entries

Invoice flow:
1. Create invoice dari delivered items
2. Generate invoice number & tax invoice number
3. Create journal entry (DR: AR, CR: Sales Revenue, CR: Tax Payable)
4. Send invoice ke customer
5. Track payment status

---

## Acceptance Criteria

### AC1: Create Invoice from Delivery Order
- Select delivered DO
- Show delivered items
- Allow partial invoicing
- Generate invoice number (INV-YYYY-NNNN)
- Generate tax invoice number (if PKP customer)

### AC2: Invoice Header
- Invoice number, date, due date
- Customer, billing address
- Payment terms, PO reference
- Tax invoice number (for PKP)
- Notes

### AC3: Invoice Line Items
- Product, description, quantity
- Unit price, discount, tax
- Line total
- Cannot exceed delivered quantity

### AC4: Invoice Calculations
- Subtotal, discount, tax (PPN 11%)
- Grand total
- Amount due (grand total - payments)

### AC5: Invoice Status
- Draft, Sent, Partially Paid, Paid, Overdue, Cancelled

### AC6: Journal Entry Creation
- Auto-create saat invoice sent
- DR: Accounts Receivable
- CR: Sales Revenue
- CR: Tax Payable (PPN)

### AC7: Payment Tracking
- Link payments to invoice
- Update amount paid
- Update status (paid/partial/overdue)
- Calculate outstanding balance

### AC8: Tax Invoice (Faktur Pajak)
- Generate for PKP customers
- Tax invoice number format
- Include NPWP
- Tax calculation details

---

## Technical Specifications

### Database Schema

`sql
CREATE TABLE sales_invoices (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NOT NULL,
    sales_order_id BIGINT NOT NULL,
    customer_id BIGINT NOT NULL,
    billing_address_id BIGINT NOT NULL,
    tax_invoice_number VARCHAR(50) UNIQUE,
    payment_terms VARCHAR(50),
    reference VARCHAR(100),
    notes TEXT,
    status ENUM('draft', 'sent', 'partially_paid', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    tax_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    grand_total DECIMAL(15,2) NOT NULL DEFAULT 0,
    amount_paid DECIMAL(15,2) DEFAULT 0,
    amount_due DECIMAL(15,2) NOT NULL DEFAULT 0,
    journal_entry_id BIGINT NULL,
    sent_at TIMESTAMP NULL,
    cancelled_by BIGINT NULL,
    cancelled_at TIMESTAMP NULL,
    cancellation_reason TEXT NULL,
    created_by BIGINT NOT NULL,
    updated_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (sales_order_id) REFERENCES sales_orders(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (billing_address_id) REFERENCES customer_addresses(id),
    FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(id),
    FOREIGN KEY (cancelled_by) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_customer (customer_id),
    INDEX idx_status (status),
    INDEX idx_invoice_date (invoice_date),
    INDEX idx_due_date (due_date)
);

CREATE TABLE sales_invoice_lines (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sales_invoice_id BIGINT NOT NULL,
    delivery_order_line_id BIGINT NOT NULL,
    line_number INT NOT NULL,
    product_id BIGINT NOT NULL,
    description TEXT,
    quantity DECIMAL(15,3) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    line_total DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sales_invoice_id) REFERENCES sales_invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (delivery_order_line_id) REFERENCES delivery_order_lines(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);
`

### Key Methods

`php
// SalesInvoice Model
public static function generateInvoiceNumber();
public static function generateTaxInvoiceNumber();
public function calculateTotals();
public function send();
public function createJournalEntry();
public function recordPayment(\\, \\);
public function updateStatus();
public function cancel(\\, \\);
`

---

## Definition of Done

- [x] Migrations created
- [x] Models & relationships
- [x] Controller & routes
- [x] Form requests & validation
- [x] React components
- [x] Invoice number generation
- [x] Tax invoice generation
- [x] Journal entry creation
- [x] Payment tracking
- [x] Status workflow
- [x] Tests (80%+ coverage)
- [ ] Code review
- [ ] Merged to main

---

## Implementation Notes

**Date:** 2026-05-15

### Completed Features
1. **Database Schema**
   - Created `sales_invoices` table with all required fields
   - Created `sales_invoice_lines` table
   - Added `Hutang Pajak (PPN)` account (2-1200) to COA
   - Adjusted schema to work with existing tables (simplified billing_address, made product_id nullable)

2. **Models**
   - `SalesInvoice` model with full relationships and business logic
   - `SalesInvoiceLine` model with calculation methods
   - Invoice number generation: `INV-YYYY-NNNN`
   - Tax invoice number generation: `FP-YYYY-NNNN`
   - Journal number generation: `JV-YYYYMM-NNNN`

3. **Controller & Routes**
   - Full CRUD operations
   - Send invoice action
   - Cancel invoice action
   - Record payment action
   - Proper validation and error handling

4. **React Components**
   - Index page (list invoices)
   - Show page (invoice detail)
   - Create page (manual invoice creation)

5. **Business Logic**
   - Automatic total calculations (subtotal, tax 11%, grand total)
   - Payment tracking and status updates
   - Status workflow: draft → sent → partially_paid/paid/overdue
   - Cannot cancel if has payments

6. **Journal Entry Integration** ✅
   - Auto-create journal entry saat invoice sent
   - Journal lines:
     - DR: Piutang Usaha (1-1200) = Grand Total
     - CR: Pendapatan Usaha (4-1000) = Subtotal
     - CR: Hutang Pajak PPN (2-1200) = Tax Amount
   - Journal reversal saat invoice cancelled
   - Fiscal period validation
   - All journal tests passing (3/3)

### Pending Items
- **Delivery Order Integration**: Currently using sales_order_line_id (DO not yet implemented)
- **Product Master Integration**: Using product_name field (Product master not yet implemented)

### Files Created/Modified
- `database/migrations/2026_05_15_045307_create_sales_invoices_table.php`
- `database/migrations/2026_05_15_045318_create_sales_invoice_lines_table.php`
- `database/migrations/2026_05_15_052443_add_tax_payable_account.php` ✅
- `app/Models/SalesInvoice.php` (with journal entry methods) ✅
- `app/Models/SalesInvoiceLine.php`
- `app/Http/Controllers/SalesInvoiceController.php`
- `routes/web.php` (added invoice routes)
- `resources/js/Pages/SalesInvoices/Index.jsx`
- `resources/js/Pages/SalesInvoices/Show.jsx`
- `resources/js/Pages/SalesInvoices/Create.jsx`
- `database/factories/SalesInvoiceFactory.php`
- `tests/Feature/SalesInvoiceTest.php`
- `tests/Feature/SalesInvoiceJournalTest.php` ✅

### Status
**Status:** review

---

## Notes

- Invoice number: INV-YYYY-NNNN
- Tax invoice: FP-YYYY-NNNN (for PKP customers)
- Journal number: JV-YYYYMM-NNNN
- Due date: Invoice date + payment terms
- Overdue: Auto-update via daily job
- Journal entry: Created saat status → Sent (auto_sales type)
- Journal reversal: Created saat invoice cancelled (manual type)
- Cannot cancel if has payments
