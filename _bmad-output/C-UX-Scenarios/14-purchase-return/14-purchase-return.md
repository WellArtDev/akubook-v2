# 14: Sari's Purchase Return & Debit Memo

**Project:** AkuBook  
**Created:** 2026-05-12  
**Method:** Whiteport Design Studio (WDS)

---

## Transaction (Q1)

**What this scenario covers:**
Return goods to vendor → create purchase return → generate debit memo → auto-post journal entry (reduce AP/Inventory)

---

## Business Goal (Q2)

**Goal:** ⭐ PRIMARY: 95%+ Auto-Posting  
**Objective:** Purchase return → debit memo → journal entry auto-posted, no manual AP adjustment

---

## User & Situation (Q3)

**Persona:** Sari (Finance Admin, PRIMARY) + Purchasing Team  
**Situation:** Received wrong items from vendor (ordered mixer, received speaker). Purchasing team creates return. Sari needs to verify debit memo auto-generated and AP adjusted correctly.

---

## Driving Forces (Q4)

**Hope:** Return → debit memo → AP adjusted automatically, vendor account updated, no manual entry.

**Worry:** Manual AP adjustment errors, vendor disputes, inventory not updated, payment errors.

---

## Device & Starting Point (Q5 + Q6)

**Device:** Desktop  
**Entry:** Ad-hoc — triggered by quality inspection failure or wrong item received.

---

## Best Outcome (Q7)

**User Success:**
Purchase return created, debit memo auto-generated, AP adjusted, inventory updated. Vendor sees debit applied to account. Total time < 10 minutes.

**Business Success:**
Accurate AP balance, inventory corrected, vendor relationship maintained, audit trail complete.

---

## Shortest Path (Q8)

1. **Dashboard (Purchasing)** — Purchasing team sees quality issue, clicks "Create Purchase Return"
2. **Purchase Return (Create)** — Purchasing team selects original PO/receiving, specifies return items/quantities, reason (damaged/wrong item)
3. **Debit Memo (Auto-Generated)** — System auto-generates debit memo linked to return
4. **Dashboard (Accounting)** — Sari sees "New Debit Memo" notification, clicks to review
5. **Journal Entry (Auto-Posted)** — Sari verifies auto-posted entries:
   - **Purchase Return**: DR: AP, CR: Inventory (reduce inventory value)
   - **VAT Adjustment**: DR: AP, CR: VAT In (reverse PPN Masukan if applicable)
6. **Vendor Account** — Sari verifies vendor AP balance reduced, debit memo applied ✓

---

## Trigger Map Connections

**Persona:** Sari (Finance Admin, PRIMARY)

**Driving Forces Addressed:**
- ✅ **Want:** Auto-posting (no manual AP adjustment)
- ❌ **Fear:** Manual entry errors, vendor disputes

**Business Goal:** ⭐ PRIMARY: 95%+ auto-posting → returns automated → Finance Admin freed up

---

## Scenario Steps

| Step | Folder | Purpose | Exit Action |
|------|--------|---------|-------------|
| 14.1 | `14.1-purchasing-dashboard/` | See quality issue | Click "Create Purchase Return" |
| 14.2 | `14.2-purchase-return-create/` | Create return document | Click "Generate Debit Memo" |
| 14.3 | `14.3-debit-memo-auto/` | Review auto-generated debit memo | Click "Approve" |
| 14.4 | `14.4-accounting-dashboard/` | See debit memo notification | Click "Review Journal" |
| 14.5 | `14.5-journal-entry-review/` | Verify auto-posted entries | Mark as "Reviewed" ✓ |

## Auto-Posting Triggers

**Purchase Return Created:**
- DR: Accounts Payable (reduce vendor balance)
- CR: Inventory (reduce stock value)
- **Trigger**: When Purchase Return status = "Approved"

**VAT Adjustment (if applicable):**
- DR: Accounts Payable
- CR: VAT In (PPN Masukan) - reverse reclaimable tax
- **Trigger**: When Purchase Return includes VAT

**Example Entry:**
```
Purchase Return (wrong item):
DR: Accounts Payable          Rp 11,100,000
    CR: Inventory              Rp 10,000,000
    CR: VAT In (PPN Masukan)   Rp  1,100,000
```

## Return Reasons & Handling

**Damaged Goods:**
- Return to vendor → shipping arranged
- Inventory reduced immediately
- Debit memo applied to vendor account

**Wrong Item:**
- Return to vendor → exchange or refund
- Inventory reduced immediately
- Debit memo applied to vendor account

**Quality Issue:**
- Return to vendor → quality claim
- Inventory reduced immediately
- Debit memo + quality claim documentation

## Integration Points

**Upstream:**
- Purchase Orders (original transaction)
- Receiving Documents (goods received)
- Vendor Master (AP balance)

**Downstream:**
- General Ledger (AP, Inventory, VAT In)
- Vendor Account (debit memo applied)
- Inventory (stock reduced)

---

_Scenario 14: Sari's Purchase Return & Debit Memo_
