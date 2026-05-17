# Scenario 13: Sales Return

**User:** Sales Admin / Finance Admin  
**Priority:** MEDIUM (Customer service)  
**Frequency:** Weekly  
**Success Metric:** Return processed in <10 minutes

---

## Scenario Goal

Sales Admin processes customer returns with inventory adjustment and auto-posted journal entries.

---

## User Context

**Who:** Sales Admin (Rina) processing returns, Finance Admin (Sari) verifying entries

**When:** Customer returns defective or unwanted goods

**Why:** Maintain customer satisfaction, adjust inventory, correct financial records

**Current Pain (from Accurate):** Manual return entry, no auto-posting, inventory errors, slow refund

---

## Sunshine Path (Happy Flow)

### Step 1: Create Sales Return

**Page:** Sales Return Form

**User Action:**
- Clicks "New Sales Return"
- Selects original invoice

**System Shows:**
- Return form:
  - Original Invoice: INV-2026-04-050
  - Customer: PT Toko Elektronik Jaya
  - Invoice Date: 2026-04-15
  - Invoice Amount: Rp 65,000,000
  - Items:
    - Speaker JBL EON615: 10 units @ Rp 6,500,000
  - Return Date: 2026-05-13
  - Return Reason: (empty)

**User Input:**
- Selects items to return:
  - Speaker JBL EON615: 2 units (defective)
- Enters return reason: "Defective - speaker not working"
- Clicks "Create Return"

**System Response:**
- Creates sales return: SR-2026-05-001
- Calculates return amount:
  - Return value: Rp 13,000,000
  - PPN 12%: Rp 1,560,000
  - Total refund: Rp 14,560,000
- Status: "Pending Approval"

**Next:** Approve return

---

### Step 2: Approve Sales Return

**Page:** Sales Return Approval

**User Action (Manager):**
- Receives notification: "Sales return SR-2026-05-001 pending approval"
- Reviews return details

**System Shows:**
- Return summary:
  - Customer: PT Toko Elektronik Jaya
  - Original Invoice: INV-2026-04-050
  - Return Items: Speaker JBL EON615 (2 units)
  - Return Reason: Defective
  - Refund Amount: Rp 14,560,000

**User Input:**
- Reviews return details
- Clicks "Approve"

**System Response:**
- Updates return status: "Approved"
- Notifies Sales Admin
- Notifies Warehouse to receive goods

**Next:** Receive returned goods

---

### Step 3: Receive Returned Goods

**Page:** Return Receipt

**User Action (Warehouse Staff):**
- Receives returned goods from customer
- Clicks "Receive Return"
- Selects return: SR-2026-05-001

**System Shows:**
- Expected return items:
  - Speaker JBL EON615: 2 units

**User Input:**
- Confirms received quantities:
  - Speaker JBL EON615: 2 units ✓
- Inspects goods:
  - Condition: Defective
  - Action: Scrap (not resalable)
- Clicks "Confirm Receipt"

**System Response:**
- Updates return status: "Received"
- Updates inventory:
  - Speaker JBL EON615: +2 units (defective stock)
- Auto-posts journal entry:
  - DR: Sales Returns (Rp 13,000,000)
  - DR: VAT Out (Rp 1,560,000)
  - CR: Accounts Receivable (Rp 14,560,000)
- Shows "Return Received" status

**Next:** Process refund

---

### Step 4: Process Refund

**Page:** Refund Processing

**User Action (Finance Admin):**
- Receives notification: "Return SR-2026-05-001 ready for refund"
- Reviews refund details

**System Shows:**
- Refund details:
  - Customer: PT Toko Elektronik Jaya
  - Return: SR-2026-05-001
  - Refund Amount: Rp 14,560,000
  - Refund Method: Bank transfer / Credit note

**User Input:**
- Selects refund method: "Credit note"
- Clicks "Process Refund"

**System Response:**
- Creates credit note: CN-2026-05-001
- Links to sales return
- Auto-posts journal entry:
  - DR: Accounts Receivable (Rp 14,560,000)
  - CR: Cash in Bank (Rp 14,560,000) [if bank transfer]
  - OR
  - No entry [if credit note - already posted on return receipt]
- Sends credit note to customer (email/PDF)
- Shows "Refund Processed" status

**Next:** Done (return complete)

---

### Step 5: Finance Admin Reviews Journal Entry

**Page:** Journal Entry (Auto-Posted)

**User Action (Finance Admin - Sari):**
- Receives notification: "Sales return journal entry posted"
- Opens journal entry detail

**System Shows:**
- Auto-posted return journal entry:
  - DR: Sales Returns: Rp 13,000,000
  - DR: VAT Out (PPN Keluaran): Rp 1,560,000
  - CR: Accounts Receivable - PT Toko Jaya: Rp 14,560,000
- Audit trail: SR-2026-05-001 by Sales Admin

**User Input:**
- Reviews entry details
- Verifies account mapping
- Marks as "Reviewed"

**System Response:**
- Updates entry status
- Sends confirmation to Sales Admin

**Next:** Done (entry reviewed)

---

## Pages/Screens Needed

1. **Sales Return Form** - Create return
2. **Sales Return Approval** - Manager approval
3. **Return Receipt** - Warehouse receives goods
4. **Refund Processing** - Process refund
5. **Journal Entry (Auto-Posted)** - Finance Admin review

---

## Data Models Required

### Tables

**sales_returns**
- id, company_id, sales_invoice_id, return_number, return_date
- customer_id, return_reason, status (pending/approved/received/refunded)
- subtotal, tax_amount, total, approved_by, approved_at
- received_by, received_at, refunded_by, refunded_at
- journal_entry_id, created_at, updated_at

**sales_return_items**
- id, sales_return_id, product_id, quantity, unit_price, total
- condition (good/defective/damaged), action (restock/scrap)
- created_at, updated_at

**credit_notes**
- id, company_id, sales_return_id, credit_note_number, issue_date
- customer_id, amount, status (issued/applied), pdf_path
- created_at, updated_at

---

## Auto-Posting Rules

**Sales Return (Goods Received):**
- DR: Sales Returns (contra-revenue)
- DR: VAT Out (PPN Keluaran reversal)
- CR: Accounts Receivable
- **Trigger:** When return goods received

**Refund Payment (if bank transfer):**
- DR: Accounts Receivable
- CR: Cash in Bank
- **Trigger:** When refund processed via bank transfer

**Example Entry:**
```
DR: Sales Returns                       Rp 13,000,000
DR: VAT Out (PPN Keluaran)              Rp  1,560,000
    CR: Accounts Receivable - PT Toko   Rp 14,560,000

[If bank transfer refund]
DR: Accounts Receivable - PT Toko       Rp 14,560,000
    CR: Cash in Bank - BCA              Rp 14,560,000
```

---

## Acceptance Criteria

**Functional:**
- ✅ Create sales return from original invoice
- ✅ Approval workflow
- ✅ Receive returned goods
- ✅ Update inventory
- ✅ Process refund (bank transfer or credit note)
- ✅ Auto-post journal entries

**Performance:**
- ✅ Return creation in <2 minutes
- ✅ Refund processing in <5 minutes

**Security:**
- ✅ Only authorized users can create returns
- ✅ Approval workflow enforced
- ✅ Audit trail for all activities

**UX:**
- ✅ Select from original invoice items
- ✅ Clear return reason
- ✅ One-click refund processing

---

## Design Notes

**Tone:**
- Supportive, efficient (customer service)
- Clear status indicators (pending/approved/received)
- Helpful guidance for return reasons

**UX Principles:**
- Link to original invoice (no re-entry)
- Approval workflow (prevent fraud)
- Auto-posting (eliminate manual entry)

**Mobile Consideration:**
- Return creation desktop-only (complex input)
- Warehouse receipt mobile-friendly

---

## Related Scenarios

- **02: Sales Order Flow** - Original sales invoice
- **07: Inventory Movement** - Inventory adjustment
- **14: Purchase Return** - Similar flow for purchases

---

## Accurate Feature Parity

**Accurate Sales Return includes:**
- Sales return creation
- Refund processing

**AkuBook Enhancement:**
- Auto-posted journal entries (Accurate manual)
- Approval workflow (Accurate limited)
- Credit note generation (Accurate manual)

---

**Scenario Status:** ✅ Ready for Implementation  
**Next:** Design wireframes for 5 pages in this flow
