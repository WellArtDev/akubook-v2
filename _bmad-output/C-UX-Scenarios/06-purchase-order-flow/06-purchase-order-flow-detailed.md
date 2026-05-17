# Scenario 06: Purchase Order Flow

**User:** Purchasing Manager / Finance Admin  
**Priority:** HIGH (Core procurement)  
**Frequency:** Daily  
**Success Metric:** PO → GRN → Invoice auto-posted in <10 minutes

---

## Scenario Goal

Purchasing Manager creates Purchase Order, receives goods, and Finance Admin verifies auto-posted journal entries.

---

## User Context

**Who:** Purchasing Manager (Rina) creating PO, Warehouse Staff receiving goods, Finance Admin (Sari) verifying entries

**When:** Daily procurement operations

**Why:** Procure inventory, track supplier orders, maintain stock levels

**Current Pain (from Accurate):** Manual PO creation, no auto-posting, scattered documents, slow approval

---

## Sunshine Path (Happy Flow)

### Step 1: Create Purchase Order

**Page:** Purchase Order Form

**User Action (Purchasing Manager):**
- Clicks "New Purchase Order"
- Selects supplier

**System Shows:**
- PO form:
  - Supplier: PT Supplier Audio
  - PO Date: 2026-05-13
  - Expected Delivery: 2026-05-20
  - Payment Terms: Net 30
  - Item list (empty)

**User Input:**
- Adds items:
  - Speaker JBL EON615: 10 units @ Rp 5,000,000 = Rp 50,000,000
  - Mixer Yamaha MG16XU: 5 units @ Rp 8,000,000 = Rp 40,000,000
- Subtotal: Rp 90,000,000
- PPN 12%: Rp 10,800,000
- Total: Rp 100,800,000

**System Response:**
- Calculates totals automatically
- Generates PO number: PO-2026-05-001
- Shows "PO Created" status

**Next:** Submit for approval

---

### Step 2: Approve Purchase Order

**Page:** PO Approval

**User Action (Manager):**
- Receives notification: "PO-2026-05-001 pending approval"
- Reviews PO details

**System Shows:**
- PO summary:
  - Supplier: PT Supplier Audio
  - Total: Rp 100,800,000
  - Items: 2 items, 15 units
  - Expected Delivery: 2026-05-20

**User Input:**
- Clicks "Approve"

**System Response:**
- Updates PO status to "Approved"
- Sends PO to supplier (email/PDF)
- Notifies Purchasing Manager
- Creates expected receipt in warehouse

**Next:** Wait for goods delivery

---

### Step 3: Receive Goods (GRN)

**Page:** Goods Receipt Note (GRN)

**User Action (Warehouse Staff):**
- Goods arrive from supplier
- Clicks "Receive Goods"
- Selects PO: PO-2026-05-001

**System Shows:**
- Expected items from PO:
  - Speaker JBL EON615: 10 units
  - Mixer Yamaha MG16XU: 5 units

**User Input:**
- Confirms received quantities:
  - Speaker JBL EON615: 10 units ✓
  - Mixer Yamaha MG16XU: 5 units ✓
- Clicks "Confirm Receipt"

**System Response:**
- Creates GRN: GRN-2026-05-001
- Updates inventory:
  - Speaker JBL EON615: +10 units
  - Mixer Yamaha MG16XU: +5 units
- Auto-posts journal entry:
  - DR: Inventory (Rp 90,000,000)
  - DR: VAT In (Rp 10,800,000)
  - CR: Accounts Payable (Rp 100,800,000)
- Shows "Goods Received" status

**Next:** Finance Admin reviews entry

---

### Step 4: Finance Admin Reviews Journal Entry

**Page:** Journal Entry (Auto-Posted)

**User Action (Finance Admin - Sari):**
- Receives notification: "GRN journal entry posted"
- Opens journal entry detail

**System Shows:**
- Auto-posted GRN journal entry:
  - DR: Inventory - Speaker JBL EON615: Rp 50,000,000
  - DR: Inventory - Mixer Yamaha MG16XU: Rp 40,000,000
  - DR: VAT In (PPN Masukan): Rp 10,800,000
  - CR: Accounts Payable - PT Supplier Audio: Rp 100,800,000
- Audit trail: GRN-2026-05-001 by Warehouse Staff

**User Input:**
- Reviews entry details
- Verifies account mapping
- Marks as "Reviewed"

**System Response:**
- Updates entry status
- Sends confirmation to Purchasing Manager

**Next:** Wait for supplier invoice

---

### Step 5: Match Supplier Invoice

**Page:** Supplier Invoice Matching

**User Action (Finance Admin):**
- Receives supplier invoice (email/PDF)
- Clicks "Match Invoice"
- Selects GRN: GRN-2026-05-001

**System Shows:**
- 3-way match:
  - PO: Rp 100,800,000
  - GRN: Rp 100,800,000
  - Invoice: Rp 100,800,000
  - Match status: ✓ Matched

**User Input:**
- Confirms invoice match
- Uploads invoice PDF
- Clicks "Post Invoice"

**System Response:**
- Creates supplier invoice: INV-SUP-2026-05-001
- Links to PO and GRN
- Updates AP aging
- Shows "Invoice Posted" status

**Next:** Schedule payment

---

### Step 6: Schedule Payment

**Page:** Payment Schedule

**User Action (Finance Admin):**
- Reviews AP aging
- Selects invoice: INV-SUP-2026-05-001
- Clicks "Schedule Payment"

**System Shows:**
- Payment details:
  - Supplier: PT Supplier Audio
  - Invoice: INV-SUP-2026-05-001
  - Amount: Rp 100,800,000
  - Due Date: 2026-06-12 (Net 30)
  - Payment Method: Bank Transfer

**User Input:**
- Confirms payment schedule
- Clicks "Schedule"

**System Response:**
- Adds to payment queue
- Sends reminder 3 days before due date
- Shows "Payment Scheduled" status

**Next:** Done (PO flow complete)

---

## Pages/Screens Needed

1. **Purchase Order Form** - Create PO
2. **PO Approval** - Manager approval
3. **Goods Receipt Note (GRN)** - Receive goods
4. **Journal Entry (Auto-Posted)** - Finance Admin review
5. **Supplier Invoice Matching** - 3-way match
6. **Payment Schedule** - Schedule payment

---

## Data Models Required

### Tables

**purchase_orders**
- id, company_id, supplier_id, po_number, po_date
- expected_delivery_date, payment_terms, status (draft/approved/received/closed)
- subtotal, tax_amount, total, created_by, approved_by, approved_at
- created_at, updated_at

**purchase_order_items**
- id, purchase_order_id, product_id, quantity, unit_price, total
- created_at, updated_at

**goods_receipt_notes**
- id, company_id, purchase_order_id, grn_number, receipt_date
- received_by, status (draft/posted), journal_entry_id
- created_at, updated_at

**goods_receipt_items**
- id, grn_id, product_id, quantity_ordered, quantity_received
- created_at, updated_at

**supplier_invoices**
- id, company_id, supplier_id, grn_id, invoice_number, invoice_date
- due_date, amount, status (pending/matched/posted/paid)
- pdf_path, created_at, updated_at

**payment_schedules**
- id, company_id, supplier_invoice_id, due_date, amount
- status (scheduled/paid), payment_date, payment_method
- created_at, updated_at

---

## Auto-Posting Rules

**Goods Receipt (GRN):**
- DR: Inventory (per product)
- DR: VAT In (PPN Masukan)
- CR: Accounts Payable (supplier)
- **Trigger:** When GRN status = "Posted"

**Example Entry:**
```
DR: Inventory - Speaker JBL EON615      Rp 50,000,000
DR: Inventory - Mixer Yamaha MG16XU     Rp 40,000,000
DR: VAT In (PPN Masukan)                Rp 10,800,000
    CR: Accounts Payable - PT Supplier  Rp 100,800,000
```

---

## Acceptance Criteria

**Functional:**
- ✅ PO created with supplier and items
- ✅ PO approval workflow
- ✅ GRN created from PO
- ✅ Inventory updated on GRN
- ✅ Journal entry auto-posted on GRN
- ✅ 3-way match (PO/GRN/Invoice)
- ✅ Payment scheduled

**Performance:**
- ✅ PO creation completes in <2 minutes
- ✅ GRN posting completes in <1 minute
- ✅ Journal entry auto-posted in <10 seconds

**Security:**
- ✅ Only authorized users can create PO
- ✅ Approval workflow enforced
- ✅ Audit trail for all activities

**UX:**
- ✅ Auto-calculate totals
- ✅ One-click GRN from PO
- ✅ 3-way match validation
- ✅ Clear status indicators

---

## Design Notes

**Tone:**
- Efficient, reliable (daily operations)
- Clear status indicators (draft/approved/received)
- Helpful guidance for first-time users

**UX Principles:**
- Auto-calculate (minimize manual input)
- One-click actions (fast processing)
- 3-way match (prevent errors)
- Preview before commit (show impact)

**Mobile Consideration:**
- PO creation desktop-only (complex input)
- GRN mobile-friendly (warehouse staff)

---

## Related Scenarios

- **02: Sales Order Flow** - Similar flow for sales
- **07: Inventory Movement** - Inventory updated on GRN
- **14: Purchase Return** - Return goods to supplier

---

## Accurate Feature Parity

**Accurate Purchase Order includes:**
- PO creation
- GRN
- Supplier invoice matching

**AkuBook Enhancement:**
- Auto-posted journal entries (Accurate manual)
- 3-way match validation (Accurate manual)
- Payment scheduling (Accurate limited)

---

**Scenario Status:** ✅ Ready for Implementation  
**Next:** Design wireframes for 6 pages in this flow
