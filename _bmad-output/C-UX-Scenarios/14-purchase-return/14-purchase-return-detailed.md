# Scenario 14: Purchase Return

**User:** Purchasing Manager / Finance Admin  
**Priority:** MEDIUM (Supplier management)  
**Frequency:** Weekly  
**Success Metric:** Return processed in <10 minutes

---

## Scenario Goal

Purchasing Manager processes supplier returns with inventory adjustment and auto-posted journal entries.

---

## User Context

**Who:** Purchasing Manager (Rina) processing returns, Finance Admin (Sari) verifying entries

**When:** Returning defective or incorrect goods to supplier

**Why:** Maintain quality standards, adjust inventory, correct financial records

**Current Pain (from Accurate):** Manual return entry, no auto-posting, inventory errors, slow credit note

---

## Sunshine Path (Happy Flow)

### Step 1: Create Purchase Return

**Page:** Purchase Return Form

**User Action:**
- Clicks "New Purchase Return"
- Selects original GRN (Goods Receipt Note)

**System Shows:**
- Return form:
  - Original GRN: GRN-2026-04-025
  - Supplier: PT Supplier Audio
  - GRN Date: 2026-04-20
  - GRN Amount: Rp 100,800,000
  - Items:
    - Speaker JBL EON615: 10 units @ Rp 5,000,000
    - Mixer Yamaha MG16XU: 5 units @ Rp 8,000,000
  - Return Date: 2026-05-13
  - Return Reason: (empty)

**User Input:**
- Selects items to return:
  - Speaker JBL EON615: 2 units (defective)
- Enters return reason: "Defective - speaker not working"
- Clicks "Create Return"

**System Response:**
- Creates purchase return: PR-2026-05-001
- Calculates return amount:
  - Return value: Rp 10,000,000
  - PPN 12%: Rp 1,200,000
  - Total credit: Rp 11,200,000
- Status: "Pending Approval"

**Next:** Approve return

---

### Step 2: Approve Purchase Return

**Page:** Purchase Return Approval

**User Action (Manager):**
- Receives notification: "Purchase return PR-2026-05-001 pending approval"
- Reviews return details

**System Shows:**
- Return summary:
  - Supplier: PT Supplier Audio
  - Original GRN: GRN-2026-04-025
  - Return Items: Speaker JBL EON615 (2 units)
  - Return Reason: Defective
  - Credit Amount: Rp 11,200,000

**User Input:**
- Reviews return details
- Clicks "Approve"

**System Response:**
- Updates return status: "Approved"
- Notifies Purchasing Manager
- Notifies Warehouse to ship goods

**Next:** Ship returned goods

---

### Step 3: Ship Returned Goods

**Page:** Return Shipment

**User Action (Warehouse Staff):**
- Prepares goods for return to supplier
- Clicks "Ship Return"
- Selects return: PR-2026-05-001

**System Shows:**
- Return items to ship:
  - Speaker JBL EON615: 2 units

**User Input:**
- Confirms shipped quantities:
  - Speaker JBL EON615: 2 units ✓
- Enters tracking number (optional)
- Clicks "Confirm Shipment"

**System Response:**
- Updates return status: "Shipped"
- Updates inventory:
  - Speaker JBL EON615: -2 units
- Auto-posts journal entry:
  - DR: Accounts Payable (Rp 11,200,000)
  - CR: Inventory (Rp 10,000,000)
  - CR: VAT In (Rp 1,200,000)
- Sends return notification to supplier
- Shows "Return Shipped" status

**Next:** Receive supplier credit note

---

### Step 4: Receive Supplier Credit Note

**Page:** Supplier Credit Note

**User Action (Finance Admin):**
- Receives supplier credit note (email/PDF)
- Clicks "Match Credit Note"
- Selects return: PR-2026-05-001

**System Shows:**
- Credit note matching:
  - Return: PR-2026-05-001 (Rp 11,200,000)
  - Supplier Credit Note: CN-SUP-2026-05-001 (Rp 11,200,000)
  - Match status: ✓ Matched

**User Input:**
- Confirms credit note match
- Uploads credit note PDF
- Clicks "Post Credit Note"

**System Response:**
- Creates supplier credit note record
- Links to purchase return
- Updates AP aging (reduces payable)
- Shows "Credit Note Posted" status

**Next:** Apply credit to future purchases

---

### Step 5: Finance Admin Reviews Journal Entry

**Page:** Journal Entry (Auto-Posted)

**User Action (Finance Admin - Sari):**
- Receives notification: "Purchase return journal entry posted"
- Opens journal entry detail

**System Shows:**
- Auto-posted return journal entry:
  - DR: Accounts Payable - PT Supplier Audio: Rp 11,200,000
  - CR: Inventory - Speaker JBL EON615: Rp 10,000,000
  - CR: VAT In (PPN Masukan): Rp 1,200,000
- Audit trail: PR-2026-05-001 by Purchasing Manager

**User Input:**
- Reviews entry details
- Verifies account mapping
- Marks as "Reviewed"

**System Response:**
- Updates entry status
- Sends confirmation to Purchasing Manager

**Next:** Done (entry reviewed)

---

## Pages/Screens Needed

1. **Purchase Return Form** - Create return
2. **Purchase Return Approval** - Manager approval
3. **Return Shipment** - Warehouse ships goods
4. **Supplier Credit Note** - Match credit note
5. **Journal Entry (Auto-Posted)** - Finance Admin review

---

## Data Models Required

### Tables

**purchase_returns**
- id, company_id, grn_id, return_number, return_date
- supplier_id, return_reason, status (pending/approved/shipped/credited)
- subtotal, tax_amount, total, approved_by, approved_at
- shipped_by, shipped_at, tracking_number
- journal_entry_id, created_at, updated_at

**purchase_return_items**
- id, purchase_return_id, product_id, quantity, unit_price, total
- created_at, updated_at

**supplier_credit_notes**
- id, company_id, purchase_return_id, credit_note_number, issue_date
- supplier_id, amount, status (received/applied), pdf_path
- created_at, updated_at

---

## Auto-Posting Rules

**Purchase Return (Goods Shipped):**
- DR: Accounts Payable
- CR: Inventory
- CR: VAT In (PPN Masukan reversal)
- **Trigger:** When return goods shipped

**Example Entry:**
```
DR: Accounts Payable - PT Supplier      Rp 11,200,000
    CR: Inventory - Speaker JBL EON615  Rp 10,000,000
    CR: VAT In (PPN Masukan)            Rp  1,200,000
```

---

## Acceptance Criteria

**Functional:**
- ✅ Create purchase return from original GRN
- ✅ Approval workflow
- ✅ Ship returned goods
- ✅ Update inventory
- ✅ Match supplier credit note
- ✅ Auto-post journal entries

**Performance:**
- ✅ Return creation in <2 minutes
- ✅ Credit note matching in <5 minutes

**Security:**
- ✅ Only authorized users can create returns
- ✅ Approval workflow enforced
- ✅ Audit trail for all activities

**UX:**
- ✅ Select from original GRN items
- ✅ Clear return reason
- ✅ One-click credit note matching

---

## Design Notes

**Tone:**
- Professional, efficient (supplier management)
- Clear status indicators (pending/approved/shipped)
- Helpful guidance for return reasons

**UX Principles:**
- Link to original GRN (no re-entry)
- Approval workflow (prevent fraud)
- Auto-posting (eliminate manual entry)

**Mobile Consideration:**
- Return creation desktop-only (complex input)
- Warehouse shipment mobile-friendly

---

## Related Scenarios

- **06: Purchase Order Flow** - Original GRN
- **07: Inventory Movement** - Inventory adjustment
- **13: Sales Return** - Similar flow for sales

---

## Accurate Feature Parity

**Accurate Purchase Return includes:**
- Purchase return creation
- Credit note matching

**AkuBook Enhancement:**
- Auto-posted journal entries (Accurate manual)
- Approval workflow (Accurate limited)
- Supplier credit note tracking (Accurate manual)

---

**Scenario Status:** ✅ Ready for Implementation  
**Next:** Design wireframes for 5 pages in this flow
