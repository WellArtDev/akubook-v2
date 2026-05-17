# 13: Sari's Sales Return & Credit Memo

**Project:** AkuBook  
**Created:** 2026-05-12  
**Method:** Whiteport Design Studio (WDS)

---

## Transaction (Q1)

**What this scenario covers:**
Customer returns goods → create sales return → generate credit memo → auto-post journal entry (reverse AR/Revenue)

---

## Business Goal (Q2)

**Goal:** ⭐ PRIMARY: 95%+ Auto-Posting  
**Objective:** Sales return → credit memo → journal entry auto-posted, no manual AR adjustment

---

## User & Situation (Q3)

**Persona:** Sari (Finance Admin, PRIMARY) + Sales Team  
**Situation:** Customer returns damaged goods (speaker rusak). Sales team creates return document. Sari needs to verify credit memo auto-generated and AR adjusted correctly.

---

## Driving Forces (Q4)

**Hope:** Return → credit memo → AR adjusted automatically, customer account updated, no manual entry.

**Worry:** Manual AR adjustment errors, customer disputes, inventory not updated, COGS not reversed.

---

## Device & Starting Point (Q5 + Q6)

**Device:** Desktop  
**Entry:** Ad-hoc — triggered by customer return request, Sales team initiates return process.

---

## Best Outcome (Q7)

**User Success:**
Sales return created, credit memo auto-generated, AR adjusted, inventory updated. Customer sees credit applied to account. Total time < 10 minutes.

**Business Success:**
Accurate AR balance, inventory updated, COGS reversed, customer satisfaction maintained, audit trail complete.

---

## Shortest Path (Q8)

1. **Dashboard (Sales)** — Sales team sees customer return request, clicks "Create Sales Return"
2. **Sales Return (Create)** — Sales team selects original invoice, specifies return items/quantities, reason (damaged/wrong item)
3. **Credit Memo (Auto-Generated)** — System auto-generates credit memo linked to return
4. **Dashboard (Accounting)** — Sari sees "New Credit Memo" notification, clicks to review
5. **Journal Entry (Auto-Posted)** — Sari verifies auto-posted entries:
   - **Sales Return**: DR: Sales Return (contra-revenue), CR: AR
   - **Inventory Return**: DR: Inventory, CR: COGS (reverse original COGS)
6. **Customer Account** — Sari verifies customer AR balance reduced, credit memo applied ✓

---

## Trigger Map Connections

**Persona:** Sari (Finance Admin, PRIMARY)

**Driving Forces Addressed:**
- ✅ **Want:** Auto-posting (no manual AR adjustment)
- ❌ **Fear:** Manual entry errors, customer disputes

**Business Goal:** ⭐ PRIMARY: 95%+ auto-posting → returns automated → Finance Admin freed up

---

## Scenario Steps

| Step | Folder | Purpose | Exit Action |
|------|--------|---------|-------------|
| 13.1 | `13.1-sales-dashboard/` | See return request | Click "Create Sales Return" |
| 13.2 | `13.2-sales-return-create/` | Create return document | Click "Generate Credit Memo" |
| 13.3 | `13.3-credit-memo-auto/` | Review auto-generated credit memo | Click "Approve" |
| 13.4 | `13.4-accounting-dashboard/` | See credit memo notification | Click "Review Journal" |
| 13.5 | `13.5-journal-entry-review/` | Verify auto-posted entries | Mark as "Reviewed" ✓ |

## Auto-Posting Triggers

**Sales Return Created:**
- DR: Sales Return (contra-revenue account)
- CR: Accounts Receivable (reduce customer balance)
- **Trigger**: When Sales Return status = "Approved"

**Inventory Return (if goods returned to warehouse):**
- DR: Inventory (restore stock)
- CR: Cost of Goods Sold (reverse original COGS)
- **Trigger**: When Sales Return status = "Received in Warehouse"

**Example Entry:**
```
Sales Return (damaged goods):
DR: Sales Return              Rp 10,000,000
    CR: Accounts Receivable   Rp 10,000,000

Inventory Return:
DR: Inventory                 Rp  7,000,000
    CR: COGS                  Rp  7,000,000
```

## Return Reasons & Handling

**Damaged Goods:**
- Return to warehouse → damaged goods location
- Inventory restored at reduced value
- COGS partially reversed

**Wrong Item:**
- Return to warehouse → regular stock
- Inventory fully restored
- COGS fully reversed

**Customer Request (no defect):**
- Return to warehouse → regular stock
- Inventory fully restored
- COGS fully reversed
- May charge restocking fee

## Integration Points

**Upstream:**
- Sales Invoices (original transaction)
- Customer Master (AR balance)
- Inventory (stock restoration)

**Downstream:**
- General Ledger (Sales Return, AR, COGS)
- Customer Account (credit memo applied)
- Inventory (stock updated)

---

_Scenario 13: Sari's Sales Return & Credit Memo_
