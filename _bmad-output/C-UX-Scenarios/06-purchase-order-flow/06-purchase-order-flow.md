# 06: Sari's Purchase Order Flow

**Project:** AkuBook  
**Created:** 2026-05-12  
**Method:** Whiteport Design Studio (WDS)

---

## Transaction (Q1)

**What this scenario covers:**
Purchasing creates PO → Warehouse receives goods → Finance Admin verifies auto-posted journal entry (AP)

---

## Business Goal (Q2)

**Goal:** ⭐ PRIMARY: 95%+ Auto-Posting (Single Source of Truth)  
**Objective:** PO → Receiving → Journal Entry auto-posted, no manual AP entry

---

## User & Situation (Q3)

**Persona:** Sari (Finance Admin, PRIMARY) + Purchasing Manager  
**Situation:** Supplier delivery arrived at warehouse. Purchasing Manager created PO earlier. Warehouse receiving goods now. Sari needs to verify AP auto-posted correctly.

---

## Driving Forces (Q4)

**Hope:** PO → Receiving → Journal entry auto-posted, AP balance updated automatically, vendor payment scheduled without manual work.

**Worry:** Manual AP entry, reconciliation nightmare between PO and receiving, vendor payment delays, supplier relationship damaged.

---

## Device & Starting Point (Q5 + Q6)

**Device:** Desktop  
**Entry:** Daily workflow — Sari checks Accounting Dashboard for new AP entries after warehouse receiving notifications.

---

## Best Outcome (Q7)

**User Success:**
Sari sees journal entry auto-generated from PO/Receiving, AP balance updated, vendor payment scheduled. Total time < 5 minutes. No manual entry.

**Business Success:**
95%+ auto-posting achieved for AP. Finance Admin freed from manual entry. Vendor payments on time, supplier relationships maintained. Single source of truth (no Excel reconciliation).

---

## Shortest Path (Q8)

1. **Dashboard (Purchasing)** — Purchasing Manager creates PO for supplier, sends to warehouse
2. **Purchase Order (Create)** — PO created with items, quantities, prices
3. **Receiving (Create)** — Warehouse receives goods, creates receiving document linked to PO
4. **Dashboard (Accounting)** — Sari sees "New AP Entry" notification, clicks to review
5. **Journal Entry (List - Auto-Generated)** — Sari sees PO#67890 → RCV#67890 → INV#67890 → JE#67890 chain, verifies:
   - DR: Inventory (item cost)
   - DR: VAT In (PPN Masukan 11%)
   - CR: Accounts Payable (total amount)
   - Marks as "Reviewed" ✓

---

## Trigger Map Connections

**Persona:** Sari (Finance Admin, PRIMARY)

**Driving Forces Addressed:**
- ✅ **Want:** Single source of truth (no tool sprawl, no Excel reconciliation)
- ❌ **Fear:** Manual AP entry, reconciliation nightmare, vendor payment delays

**Business Goal:** ⭐ PRIMARY: 95%+ auto-posting → AP automated → Finance Admin freed up

---

## Scenario Steps

| Step | Folder | Purpose | Exit Action |
|------|--------|---------|-------------|
| 06.1 | `06.1-purchasing-dashboard/` | Purchasing creates PO | Click "Create PO" |
| 06.2 | `06.2-purchase-order-create/` | PO created, sent to warehouse | Click "Send to Warehouse" |
| 06.3 | `06.3-receiving-create/` | Warehouse receives goods | Click "Create Receiving" |
| 06.4 | `06.4-accounting-dashboard/` | Sari sees AP notification | Click "Review AP Entry" |
| 06.5 | `06.5-journal-entry-list/` | Sari verifies auto-posted AP entry | Mark as "Reviewed" ✓ |

## Auto-Posting Triggers

**Purchase Invoice Created (after Receiving):**
- DR: Inventory (or Expense for non-stock items)
- DR: VAT In (PPN Masukan) - reclaimable tax
- CR: Accounts Payable (AP)
- **Trigger**: When Purchase Invoice status = "Posted"

**Example Entry:**
```
DR: Inventory                    Rp 100,000,000
DR: VAT In (PPN Masukan 11%)     Rp  11,000,000
    CR: Accounts Payable         Rp 111,000,000
```

---

_Scenario 06: Sari's Purchase Order Flow_
