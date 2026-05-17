# 4. Purchasing (Pembelian)

## Overview

Purchasing module in Accurate Online manages complete procurement cycle from purchase requisition to vendor payment. Supports multi-currency transactions, landed cost allocation, approval workflows, and vendor management for distributor operations.

**Priority**: HIGH (distributor core)  
**Complexity**: High - multi-step workflows, landed costs, multi-currency  
**Indonesian Compliance**: VAT handling, import duties, vendor tax reporting

---

## Core Procurement (6 Features)

### 1. Purchase Order (PO)
**Function**: Create and track purchase orders to vendors  
**Key Capabilities**:
- Multi-item PO with quantities, prices, terms
- Approval workflow (optional)
- PO status tracking (open, partial, closed)
- Convert from Purchase Requisition
- Multi-currency support
- Delivery schedule
- Terms and conditions
- Auto-email to vendor

**Workflow**: PR → PO → Goods Receipt → Purchase Invoice → Payment

### 2. Goods Receipt (Penerimaan Barang)
**Function**: Record received goods from vendors  
**Key Capabilities**:
- Receive against PO or direct receipt
- Multi-warehouse receiving
- Partial receipt support
- Quality inspection notes
- Damaged goods handling
- Auto-update inventory
- 3-way matching (PO-Receipt-Invoice)

**Validation**: Cannot receive more than PO quantity (unless override enabled)

### 3. Down Payment (Uang Muka Pembelian)
**Function**: Record advance payments to vendors  
**Key Capabilities**:
- Down payment recording
- Auto-deduction from final invoice
- Multi-currency support
- Partial utilization
- Refund handling

**Accounting**: DR: Down Payment Asset, CR: Cash/Bank

### 4. Purchase Invoice (Faktur Pembelian)
**Function**: Record vendor invoices and accounts payable  
**Key Capabilities**:
- Invoice against PO/Receipt or direct invoice
- Multi-currency with exchange rate
- VAT In (PPN Masukan) calculation
- Withholding tax (PPh 23) calculation
- Payment terms (net 30, 60, 90)
- 3-way matching validation
- Auto-post to AP and GL

**Validation**: 
- Receipt quantity ≥ Invoice quantity
- Price variance alert if invoice price ≠ PO price

### 5. Purchase Payment (Pembayaran Pembelian)
**Function**: Record payments to vendors  
**Key Capabilities**:
- Pay multiple invoices in one payment
- Multi-currency payment
- Payment methods (cash, bank transfer, check, giro)
- Partial payment support
- Payment discount handling
- Auto-clear AP
- Check/giro printing

**Accounting**: DR: AP, CR: Cash/Bank

### 6. Purchase Return (Retur Pembelian)
**Function**: Return goods to vendors  
**Key Capabilities**:
- Return against receipt or direct return
- Partial return support
- Return reasons (damaged, wrong item, excess)
- Debit memo generation
- Auto-update inventory
- Multi-warehouse return

**Accounting**: DR: AP (or Debit Memo), CR: Inventory

---

## Vendor Management (5 Features)

### 7. Debit Memo (Memo Debit)
**Function**: Record vendor credits for returns, discounts, allowances  
**Key Capabilities**:
- Auto-generate from purchase return
- Manual debit memo creation
- Apply to future invoices
- Multi-currency support
- Aging tracking

**Accounting**: DR: AP, CR: Debit Memo (asset account)

### 8. Vendor Pricing (Harga Pemasok)
**Function**: Maintain vendor price agreements  
**Key Capabilities**:
- Price per item per vendor
- Quantity breaks (tiered pricing)
- Effective date ranges
- Currency per vendor
- Price history tracking
- Auto-populate PO prices

**Use Case**: Bulk purchasing with negotiated rates

### 9. Vendor Claims (Klaim Pemasok)
**Function**: Track quality claims and disputes  
**Key Capabilities**:
- Claim registration
- Claim status tracking
- Resolution recording
- Claim amount tracking
- Vendor performance impact

### 10. Vendor Categories (Kategori Pemasok)
**Function**: Group vendors for reporting and analysis  
**Key Capabilities**:
- Custom category creation
- Multi-level hierarchy
- Category-based reporting
- Filter by category

### 11. Vendor Master (Data Pemasok)
**Function**: Maintain vendor database  
**Key Capabilities**:
- Vendor profile (name, address, contact, tax ID)
- Payment terms default
- Currency default
- Credit limit
- Vendor status (active/inactive)
- Custom fields
- Vendor performance metrics

---

## Advanced Features (6)

### 12. Payment Order (Perintah Pembayaran)
**Function**: Batch payment planning and approval  
**Key Capabilities**:
- Select multiple invoices for payment
- Approval workflow
- Payment scheduling
- Budget control
- Batch payment execution

**Use Case**: Weekly/monthly payment runs with approval

### 13. Vendor Transfer (Transfer Pemasok)
**Function**: Consolidate or merge vendor accounts  
**Key Capabilities**:
- Transfer AP balance to another vendor
- Merge duplicate vendors
- Maintain transaction history

### 14. Multi-Currency (Multi Mata Uang)
**Function**: Handle foreign currency purchases  
**Key Capabilities**:
- Multiple currencies per vendor
- Exchange rate per transaction
- Realized gain/loss on payment
- Unrealized gain/loss on month-end
- Fiscal vs commercial rate (for VAT)

**Accounting**: 
- Invoice: DR: Inventory/Expense @ transaction rate, CR: AP @ transaction rate
- Payment: DR: AP @ invoice rate, CR: Cash @ payment rate, DR/CR: Gain/Loss

### 15. Landed Cost (Biaya Tambahan)
**Function**: Allocate freight, insurance, customs to inventory cost  
**Key Capabilities**:
- Add landed costs to receipt
- Allocate by weight, volume, value, or quantity
- Multi-item allocation
- Auto-update inventory cost
- Separate GL posting

**Example**: 
- Goods: Rp 100M
- Freight: Rp 10M
- Customs: Rp 5M
- Total landed cost: Rp 115M (allocated to inventory)

### 16. Approval Workflows (Persetujuan)
**Function**: Multi-level approval for PO and payments  
**Key Capabilities**:
- Approval rules by amount threshold
- Multi-level approval chain
- Email notifications
- Approval history
- Override capability

### 17. Purchase Preferences (Preferensi Pembelian)
**Function**: System settings for purchasing module  
**Key Capabilities**:
- Default accounts (AP, expense, discount)
- Numbering formats
- Approval thresholds
- Landed cost allocation method
- VAT settings

---

## Key Workflows

### Workflow 1: Standard Purchase (PO → Receipt → Invoice → Payment)
1. Create Purchase Order
2. Receive Goods (update inventory)
3. Record Purchase Invoice (create AP)
4. Make Payment (clear AP)

### Workflow 2: Direct Purchase (No PO)
1. Receive Goods directly
2. Record Purchase Invoice
3. Make Payment

### Workflow 3: Down Payment Purchase
1. Record Down Payment
2. Create Purchase Order
3. Receive Goods
4. Record Purchase Invoice (auto-deduct down payment)
5. Make Final Payment

### Workflow 4: Purchase Return
1. Create Purchase Return (reduce inventory)
2. Generate Debit Memo (reduce AP)
3. Apply Debit Memo to future invoices

### Workflow 5: Import Purchase (with Landed Cost)
1. Create Purchase Order (foreign currency)
2. Receive Goods
3. Add Landed Costs (freight, customs, insurance)
4. Record Purchase Invoice
5. Make Payment (handle exchange rate)

---

## Integration Points

### Accounting Integration
- **Accounts Payable**: Auto-post invoices to AP
- **General Ledger**: Auto-post to expense/inventory accounts
- **Cash & Bank**: Payment transactions
- **Tax**: VAT In (PPN Masukan), Withholding Tax (PPh 23)

### Inventory Integration
- **Stock Updates**: Auto-update on receipt and return
- **COGS Calculation**: Landed cost included in inventory cost
- **Multi-Warehouse**: Receive to specific warehouse

### Other Modules
- **Sales**: Vendor as customer (for returns/exchanges)
- **Manufacturing**: Purchase for production materials
- **Fixed Assets**: Purchase of capital assets

---

## Distributor Requirements

### Bulk Purchasing
- Quantity breaks in vendor pricing
- Tiered pricing support
- Bulk receipt handling

### Landed Cost Management
- Freight allocation by weight/volume
- Customs duty allocation
- Insurance cost allocation

### Multi-Currency
- Foreign supplier support
- Exchange rate management
- Gain/loss tracking

### Vendor Management
- Vendor performance tracking
- Price history
- Payment terms negotiation

### Approval Controls
- PO approval by amount
- Payment approval workflow
- Budget control

---

## Best Practices

1. **PO Creation**: Always create PO for trackability (except petty cash purchases)
2. **3-Way Matching**: Enable validation (PO-Receipt-Invoice) to prevent overbilling
3. **Landed Cost**: Record all import costs for accurate inventory valuation
4. **Vendor Pricing**: Maintain price agreements for auto-population
5. **Payment Terms**: Negotiate and record terms for cash flow planning
6. **Multi-Currency**: Use consistent exchange rate source (BI rate)
7. **Approval Workflow**: Set thresholds to balance control and efficiency

---

## Priority for AkuBook MVP

### Phase 1 (Must Have - 6 features):
1. Purchase Order
2. Goods Receipt
3. Purchase Invoice
4. Purchase Payment
5. Vendor Master
6. Vendor Pricing

### Phase 2 (Should Have - 5 features):
7. Purchase Return
8. Debit Memo
9. Down Payment
10. Landed Cost
11. Multi-Currency

### Phase 3 (Nice to Have - 6 features):
12. Payment Order (batch payments)
13. Approval Workflows
14. Vendor Claims
15. Vendor Categories
16. Vendor Transfer
17. Purchase Preferences

---

## Technical Notes

### Data Model
- **Vendor Master**: Vendor details, terms, currency
- **Purchase Order**: Header (vendor, date, terms) + Lines (item, qty, price)
- **Goods Receipt**: Header (vendor, date, warehouse) + Lines (item, qty, PO reference)
- **Purchase Invoice**: Header (vendor, date, terms, AP account) + Lines (item, qty, price, tax)
- **Purchase Payment**: Header (vendor, date, payment method) + Lines (invoice, amount)

### Validation Rules
- Receipt quantity ≤ PO quantity (unless override)
- Invoice quantity ≤ Receipt quantity
- Payment amount ≤ Invoice outstanding
- Debit memo amount ≤ AP balance

### Accounting Entries

**Purchase Invoice**:
- DR: Inventory/Expense (item cost)
- DR: VAT In (PPN Masukan)
- CR: Accounts Payable

**Purchase Payment**:
- DR: Accounts Payable
- CR: Cash/Bank

**Purchase Return**:
- DR: Accounts Payable (or Debit Memo)
- CR: Inventory

**Landed Cost**:
- DR: Inventory (allocated cost)
- CR: Accounts Payable (freight/customs invoice)

---

## Common Pitfalls

1. **Missing Landed Cost**: Understates inventory cost and COGS
2. **Wrong Exchange Rate**: Use transaction date rate, not payment date
3. **No 3-Way Matching**: Risk of overbilling
4. **Duplicate Invoices**: Check invoice number before recording
5. **Wrong Warehouse**: Receive to correct location for accurate stock
6. **Partial Receipt**: Track outstanding PO quantities
7. **Payment Without Invoice**: Always record invoice first for AP tracking

---

**Source**: Accurate Online Help Documentation (https://help.accurate.id/product/pembelian/)  
**Last Updated**: May 2026  
**Compliance**: Indonesian VAT (PPN), Withholding Tax (PPh 23)
