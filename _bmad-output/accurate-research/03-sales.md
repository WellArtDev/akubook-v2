# 3. Sales (Penjualan)

## Overview

Sales module in Accurate Online manages complete sales cycle from quotation to payment receipt. Supports multi-currency, tiered pricing, consignment, drop-ship, and e-commerce integration for distributor operations.

**Priority**: HIGH (distributor core)  
**Complexity**: High - multi-step workflows, pricing rules, multi-currency  
**Indonesian Compliance**: VAT (PPN), e-Faktur integration, customer tax reporting

---

## Core Sales Cycle (6 Features)

### 1. Sales Quotation (Penawaran Penjualan)
**Function**: Create price quotes for customers  
**Key Capabilities**:
- Multi-item quotation with quantities, prices, terms
- Validity period
- Convert to Sales Order
- Multi-currency support
- Terms and conditions
- Auto-email to customer
- Quote status tracking (pending, accepted, rejected)

**Workflow**: Quotation → Sales Order → Delivery → Invoice → Payment

### 2. Sales Order (Pesanan Penjualan)
**Function**: Record confirmed customer orders  
**Key Capabilities**:
- Multi-item order with quantities, prices, delivery schedule
- Order status tracking (open, partial, closed)
- Backorder handling
- Multi-warehouse allocation
- Approval workflow (optional)
- Auto-reserve inventory
- Convert from Quotation

**Validation**: Cannot exceed available stock (unless override enabled)

### 3. Delivery Order (Surat Jalan)
**Function**: Record goods shipped to customers  
**Key Capabilities**:
- Deliver against Sales Order or direct delivery
- Multi-warehouse shipping
- Partial delivery support
- Delivery notes and instructions
- Auto-update inventory
- Delivery status tracking
- Proof of delivery attachment

**Workflow**: Sales Order → Delivery Order → Sales Invoice

### 4. Sales Invoice (Faktur Penjualan)
**Function**: Record sales revenue and accounts receivable  
**Key Capabilities**:
- Invoice against Sales Order/Delivery or direct invoice
- Multi-currency with exchange rate
- VAT Out (PPN Keluaran) calculation
- e-Faktur integration (Smartlink Tax)
- Payment terms (net 30, 60, 90)
- Auto-post to AR and GL
- Recurring invoice support

**Validation**: 
- Delivery quantity ≥ Invoice quantity
- Price variance alert if invoice price ≠ Sales Order price

### 5. Payment Receipt (Penerimaan Pembayaran)
**Function**: Record customer payments  
**Key Capabilities**:
- Receive multiple invoices in one payment
- Multi-currency payment
- Payment methods (cash, bank transfer, check, giro, credit card)
- Partial payment support
- Payment discount handling
- Auto-clear AR
- Check/giro deposit

**Accounting**: DR: Cash/Bank, CR: AR

### 6. Sales Return (Retur Penjualan)
**Function**: Handle customer returns  
**Key Capabilities**:
- Return against invoice or direct return
- Partial return support
- Return reasons (damaged, wrong item, customer request)
- Credit memo generation
- Auto-update inventory
- Multi-warehouse return
- Refund or credit to account

**Accounting**: DR: Sales Return (contra-revenue), CR: AR (or Cash if refund)

---

## Pricing & Discounts (4 Features)

### 7. Customer Pricing (Harga Pelanggan)
**Function**: Maintain customer-specific pricing  
**Key Capabilities**:
- Price per item per customer
- Quantity breaks (tiered pricing)
- Effective date ranges
- Currency per customer
- Price history tracking
- Auto-populate Sales Order prices

**Use Case**: Distributor with different pricing for retailers vs wholesalers

### 8. Price Levels (Tingkat Harga)
**Function**: Define pricing tiers (retail, wholesale, VIP)  
**Key Capabilities**:
- Multiple price levels per item
- Assign price level to customer
- Auto-apply on Sales Order
- Markup/markdown from base price

**Example**: 
- Retail: Rp 100,000
- Wholesale: Rp 85,000 (15% discount)
- VIP: Rp 80,000 (20% discount)

### 9. Discount Rules (Aturan Diskon)
**Function**: Automated discount application  
**Key Capabilities**:
- Quantity-based discounts
- Amount-based discounts
- Promotional discounts
- Customer group discounts
- Item category discounts
- Time-limited promotions

### 10. Sales Commission (Komisi Penjualan)
**Function**: Calculate salesperson commissions  
**Key Capabilities**:
- Commission rate per salesperson
- Commission basis (revenue, profit, quantity)
- Multi-level commission (team + individual)
- Commission report
- Auto-calculate on invoice

---

## Customer Management (3 Features)

### 11. Customer Master (Data Pelanggan)
**Function**: Maintain customer database  
**Key Capabilities**:
- Customer profile (name, address, contact, tax ID/NPWP)
- Payment terms default
- Currency default
- Credit limit
- Customer status (active/inactive)
- Custom fields
- Customer performance metrics
- Salesperson assignment

### 12. Customer Categories (Kategori Pelanggan)
**Function**: Group customers for reporting and analysis  
**Key Capabilities**:
- Custom category creation
- Multi-level hierarchy
- Category-based reporting
- Filter by category
- Category-specific pricing

### 13. Credit Memo (Memo Kredit)
**Function**: Record customer credits for returns, discounts, allowances  
**Key Capabilities**:
- Auto-generate from sales return
- Manual credit memo creation
- Apply to future invoices
- Multi-currency support
- Aging tracking

**Accounting**: DR: Credit Memo (contra-asset), CR: AR

---

## Advanced Features (5 Features)

### 14. Multi-Currency (Multi Mata Uang)
**Function**: Handle foreign currency sales  
**Key Capabilities**:
- Multiple currencies per customer
- Exchange rate per transaction
- Realized gain/loss on payment
- Unrealized gain/loss on month-end
- Fiscal vs commercial rate (for VAT)

**Accounting**: 
- Invoice: DR: AR @ transaction rate, CR: Revenue @ transaction rate
- Payment: DR: Cash @ payment rate, CR: AR @ invoice rate, DR/CR: Gain/Loss

### 15. Consignment Sales (Penjualan Konsinyasi)
**Function**: Track goods held by customers for sale  
**Key Capabilities**:
- Consignment delivery (no revenue recognition)
- Consignment invoice (revenue on actual sale)
- Consignment return (unsold goods)
- Consignment report (goods at customer location)

**Accounting**: 
- Delivery: DR: Consignment Inventory, CR: Inventory
- Sale: DR: AR, CR: Revenue + DR: COGS, CR: Consignment Inventory

### 16. Drop Ship (Pengiriman Langsung)
**Function**: Ship directly from vendor to customer  
**Key Capabilities**:
- Sales Order without inventory movement
- Purchase Order to vendor
- Vendor ships to customer
- Invoice customer without receiving goods

**Workflow**: Customer Order → Vendor PO → Vendor ships to customer → Invoice customer

### 17. Invoice Exchange (Tukar Faktur)
**Function**: Exchange invoice for different items  
**Key Capabilities**:
- Return original items
- Issue new invoice for replacement items
- Price adjustment handling
- Auto-update inventory

### 18. E-Commerce Integration (Integrasi E-Commerce)
**Function**: Sync with online stores (Tokopedia, Shopee, Lazada)  
**Key Capabilities**:
- Auto-import orders
- Inventory sync
- Price sync
- Order fulfillment tracking
- Multi-marketplace support

---

## Key Workflows

### Workflow 1: Standard Sales (Order → Delivery → Invoice → Payment)
1. Create Sales Order (reserve inventory)
2. Create Delivery Order (ship goods, update inventory)
3. Create Sales Invoice (recognize revenue, create AR)
4. Receive Payment (clear AR)

### Workflow 2: Direct Sales (No Order)
1. Create Delivery Order directly
2. Create Sales Invoice
3. Receive Payment

### Workflow 3: Quotation to Order
1. Create Sales Quotation
2. Customer accepts → Convert to Sales Order
3. Follow standard workflow

### Workflow 4: Sales Return
1. Create Sales Return (increase inventory)
2. Generate Credit Memo (reduce AR)
3. Apply Credit Memo to future invoices or refund

### Workflow 5: Consignment Sales
1. Create Consignment Delivery (move to consignment inventory)
2. Customer sells goods → Create Consignment Invoice (recognize revenue)
3. Customer returns unsold → Create Consignment Return

---

## Integration Points

### Accounting Integration
- **Accounts Receivable**: Auto-post invoices to AR
- **General Ledger**: Auto-post to revenue accounts
- **Cash & Bank**: Payment transactions
- **Tax**: VAT Out (PPN Keluaran), e-Faktur integration

### Inventory Integration
- **Stock Updates**: Auto-update on delivery and return
- **COGS Calculation**: Auto-calculate cost of goods sold
- **Multi-Warehouse**: Ship from specific warehouse
- **Consignment Tracking**: Separate consignment inventory

### Other Modules
- **Purchasing**: Drop-ship orders
- **Manufacturing**: Make-to-order production
- **Dashboard**: Sales KPIs and metrics

---

## Distributor-Specific Features

### Bulk Orders
- Multi-item orders with large quantities
- Partial delivery support
- Backorder handling

### Tiered Pricing
- Quantity breaks (1-10: Rp 100, 11-50: Rp 95, 51+: Rp 90)
- Customer-specific pricing
- Price level assignment

### Customer Terms
- Net 30, 60, 90 payment terms
- Credit limit enforcement
- Aging analysis

### Multi-Currency
- Foreign customer support
- Exchange rate management
- Gain/loss tracking

### Consignment
- Goods at customer location
- Revenue recognition on actual sale
- Consignment report

### Drop-Ship
- Direct vendor-to-customer shipping
- No inventory movement
- Margin on pass-through

---

## Best Practices

1. **Sales Order**: Always create Sales Order for trackability (except cash sales)
2. **Delivery Order**: Record delivery before invoice for accurate inventory
3. **Pricing**: Maintain customer pricing for auto-population
4. **Payment Terms**: Record terms for aging analysis
5. **Multi-Currency**: Use consistent exchange rate source (BI rate)
6. **Credit Limit**: Set and enforce to manage risk
7. **E-Faktur**: Generate e-Faktur for all VAT transactions

---

## Priority for AkuBook MVP

### Phase 1 (Must Have - 5 features):
1. Sales Invoice
2. Payment Receipt
3. Customer Master
4. Sales Return
5. Customer Pricing

### Phase 2 (Should Have - 5 features):
6. Sales Order
7. Delivery Order
8. Down Payment
9. Customer Categories
10. Multi-Currency

### Phase 3 (Nice to Have - 5 features):
11. Sales Quotation
12. Invoice Exchange
13. Consignment Sales
14. Sales Commission
15. E-Commerce Integration

---

## Technical Notes

### Data Model
- **Customer Master**: Customer details, terms, currency
- **Sales Order**: Header (customer, date, terms) + Lines (item, qty, price)
- **Delivery Order**: Header (customer, date, warehouse) + Lines (item, qty, SO reference)
- **Sales Invoice**: Header (customer, date, terms, AR account) + Lines (item, qty, price, tax)
- **Payment Receipt**: Header (customer, date, payment method) + Lines (invoice, amount)

### Validation Rules
- Delivery quantity ≤ Sales Order quantity (unless override)
- Invoice quantity ≤ Delivery quantity
- Payment amount ≤ Invoice outstanding
- Credit memo amount ≤ AR balance

### Accounting Entries

**Sales Invoice**:
- DR: Accounts Receivable
- CR: Sales Revenue
- CR: VAT Out (PPN Keluaran)

**Payment Receipt**:
- DR: Cash/Bank
- CR: Accounts Receivable

**Sales Return**:
- DR: Sales Return (contra-revenue)
- CR: Accounts Receivable (or Cash if refund)

**COGS Entry** (on delivery):
- DR: Cost of Goods Sold
- CR: Inventory

---

## Common Pitfalls

1. **Invoice Before Delivery**: Recognizes revenue before goods shipped
2. **Wrong Exchange Rate**: Use transaction date rate, not payment date
3. **Missing e-Faktur**: Required for all VAT transactions
4. **Duplicate Invoices**: Check invoice number before recording
5. **Wrong Warehouse**: Ship from correct location for accurate stock
6. **Partial Delivery**: Track outstanding Sales Order quantities
7. **Payment Without Invoice**: Always record invoice first for AR tracking

---

**Source**: Accurate Online Help Documentation (https://help.accurate.id/product/penjualan/)  
**Last Updated**: May 2026  
**Compliance**: Indonesian VAT (PPN), e-Faktur integration
