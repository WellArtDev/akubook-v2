# 02: Sari's Sales Order Flow

**Project:** AkuBook  
**Created:** 2026-05-12  
**Method:** Whiteport Design Studio (WDS)

---

## Transaction (Q1)

**What this scenario covers:**
Sales team creates Sales Order → generates Invoice → Finance Admin verifies auto-posted journal entry

---

## Business Goal (Q2)

**Goal:** ⭐ PRIMARY: 95%+ Auto-Posting (THE ENGINE)  
**Objective:** Eliminate Finance Admin manual entry — transactions auto-post to journal without intervention

---

## User & Situation (Q3)

**Persona:** Sari (Finance Admin, PRIMARY)  
**Situation:** Sari is Finance Admin di PT Distributor Sound System. Sales team just closed a deal and created a Sales Order. Sari needs to verify that the SO → Invoice → Journal Entry flow worked automatically without her manual intervention.

---

## Driving Forces (Q4)

**Hope:** SO → Invoice → Journal entry auto-posted correctly, no manual work needed, she can focus on analysis instead of data entry.

**Worry:** Auto-posting fails, she's back to manual entry hell, spending 60% of her time on data entry instead of strategic work.

---

## Device & Starting Point (Q5 + Q6)

**Device:** Desktop (office workstation)  
**Entry:** Daily workflow — Sari checks Accounting Dashboard every morning to verify yesterday's transactions auto-posted correctly.

---

## Best Outcome (Q7)

**User Success:**
Sari sees journal entry auto-generated from SO/Invoice, verifies debit/credit accounts correct, marks as reviewed. Total time: < 5 minutes. No manual entry needed.

**Business Success:**
95%+ auto-posting rate achieved. Finance Admin freed from manual entry, can focus on strategic analysis. Monthly close time reduced from 3 days to < 8 hours.

---

## Shortest Path (Q8)

1. **Dashboard (Accounting)** — Sari sees "5 new auto-posted entries" notification, clicks to review
2. **Journal Entry (List - Auto-Generated)** — Sari sees SO#12345 → DO#12345 → INV#12345 → JE#12345 chain, verifies entries:
   - **Delivery Order (DO)**: DR: COGS / CR: Inventory (auto-posted on delivery)
   - **Sales Invoice**: DR: AR / CR: Revenue, CR: VAT Out (auto-posted on invoice)
3. **Journal Entry (Detail)** — Sari reviews entry details, sees audit trail (SO → DO → Invoice → JE), marks as "Reviewed" ✓

---

## Trigger Map Connections

**Persona:** Sari (Finance Admin, PRIMARY)

**Driving Forces Addressed:**
- ✅ **Want:** Eliminate manual entry (95%+ auto-posting)
- ❌ **Fear:** Manual entry hell, error risk, career stagnation as data entry clerk

**Business Goal:** ⭐ PRIMARY: 95%+ auto-posting rate → Finance Admin freed up → strategic analyst transformation

---

## Scenario Steps

| Step | Folder | Purpose | Exit Action |
|------|--------|---------|-------------|
| 02.1 | `02.1-accounting-dashboard/` | See auto-posted entries notification | Click "Review Entries" |
| 02.2 | `02.2-journal-entry-list/` | Browse auto-generated entries (DO + Invoice) | Click entry to view details |
| 02.3 | `02.3-journal-entry-detail/` | Verify entry correctness, mark reviewed | Mark as "Reviewed" ✓ |

## Auto-Posting Triggers

**Delivery Order Created:**
- DR: Cost of Goods Sold (COGS)
- CR: Inventory
- **Trigger**: When DO status = "Delivered"

**Sales Invoice Created:**
- DR: Accounts Receivable (AR)
- CR: Sales Revenue
- CR: VAT Out (PPN Keluaran)
- **Trigger**: When Invoice status = "Posted"

---

_Scenario 02: Sari's Sales Order Flow_
