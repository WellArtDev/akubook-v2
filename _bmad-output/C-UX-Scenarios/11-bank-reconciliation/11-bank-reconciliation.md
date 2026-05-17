# 11: Sari's Bank Reconciliation

**Project:** AkuBook  
**Created:** 2026-05-12  
**Method:** Whiteport Design Studio (WDS)

---

## Transaction (Q1)

**What this scenario covers:**
Month-end: Import bank statement → auto-match transactions → reconcile differences → close bank account

---

## Business Goal (Q2)

**Goal:** ⭐ PRIMARY: 95%+ Auto-Posting (Single Source of Truth)  
**Objective:** Bank transactions auto-matched with system entries, reconciliation < 2 hours vs 1 day manual

---

## User & Situation (Q3)

**Persona:** Sari (Finance Admin, PRIMARY)  
**Situation:** Month-end (last day of month). Sari needs to reconcile 3 bank accounts (BCA, Mandiri, BNI) with system records. Biasanya 1 hari (manual matching Excel vs bank statement). Target: < 2 jam.

---

## Driving Forces (Q4)

**Hope:** Bank statement imported automatically, transactions auto-matched 90%+, only review unmatched items, reconciliation complete in hours.

**Worry:** Manual matching tedious, missing transactions, reconciliation errors, month-end delayed, audit findings.

---

## Device & Starting Point (Q5 + Q6)

**Device:** Desktop  
**Entry:** Monthly routine — last day of month, Sari starts bank reconciliation from Cash & Bank module.

---

## Best Outcome (Q7)

**User Success:**
Bank statements imported (CSV/Excel), 90%+ transactions auto-matched, unmatched items reviewed and resolved, reconciliation complete < 2 hours. Bank balance matches system balance.

**Business Success:**
Accurate cash position, no missing transactions, audit-ready reconciliation report, month-end close on time.

---

## Shortest Path (Q8)

1. **Dashboard (Cash & Bank)** — Sari sees "Bank Reconciliation Due" notification, clicks to start
2. **Bank Account (List)** — Sari selects BCA account, clicks "Import Statement"
3. **Import Bank Statement** — Sari uploads CSV file (from BCA internet banking), system parses transactions
4. **Auto-Match Transactions** — System auto-matches 90%+ transactions (by amount, date, reference), shows matched/unmatched summary
5. **Review Unmatched** — Sari reviews 10% unmatched items:
   - Bank charges → create journal entry
   - Missing receipts → link to existing receipt
   - Timing differences → mark as "In Transit"
6. **Reconciliation Report** — Sari verifies bank balance = system balance, generates reconciliation report, marks as "Reconciled" ✓

---

## Trigger Map Connections

**Persona:** Sari (Finance Admin, PRIMARY)

**Driving Forces Addressed:**
- ✅ **Want:** Single source of truth (no Excel reconciliation)
- ❌ **Fear:** Manual matching tedious, reconciliation errors, audit findings

**Business Goal:** ⭐ PRIMARY: 95%+ auto-matching → reconciliation automated → Finance Admin freed up

---

## Scenario Steps

| Step | Folder | Purpose | Exit Action |
|------|--------|---------|-------------|
| 11.1 | `11.1-cash-bank-dashboard/` | See reconciliation notification | Click "Start Reconciliation" |
| 11.2 | `11.2-bank-account-list/` | Select bank account | Click "Import Statement" |
| 11.3 | `11.3-import-statement/` | Upload CSV/Excel file | Click "Parse Transactions" |
| 11.4 | `11.4-auto-match/` | Review auto-matched transactions | Click "Review Unmatched" |
| 11.5 | `11.5-review-unmatched/` | Resolve unmatched items | Click "Generate Report" |
| 11.6 | `11.6-reconciliation-report/` | Verify balance, mark reconciled | Mark as "Reconciled" ✓ |

## Auto-Matching Rules

**Match Criteria (in order):**
1. **Exact Match**: Amount + Date + Reference Number
2. **Amount + Date**: Within ±1 day
3. **Amount Only**: Within ±3 days (manual review required)

**Unmatched Handling:**
- **Bank Charges**: Auto-create journal entry (DR: Bank Charges, CR: Cash)
- **Interest Income**: Auto-create journal entry (DR: Cash, CR: Interest Income)
- **Missing Receipts**: Link to existing receipt manually
- **Timing Differences**: Mark as "In Transit" (will match next period)

## Integration Points

**Upstream:**
- Cash Receipts (from Sales)
- Cash Payments (from Purchases)
- Bank Transfers (inter-account)
- Manual Journal Entries

**Downstream:**
- General Ledger (reconciliation adjustments)
- Monthly Close (reconciliation required before close)
- Audit Trail (reconciliation history)

---

_Scenario 11: Sari's Bank Reconciliation_
