# 03: Sari's Monthly Close

**Project:** AkuBook  
**Created:** 2026-05-12  
**Method:** Whiteport Design Studio (WDS)

---

## Transaction (Q1)

**What this scenario covers:**
Month-end: Review auto-posted entries → reconcile accounts → generate financial reports → close fiscal period

---

## Business Goal (Q2)

**Goal:** ⭐ PRIMARY: Faster Monthly Close (< 8 hours vs 3 hari)  
**Objective:** Finance Admin completes monthly close in < 8 hours instead of 3-day marathon

---

## User & Situation (Q3)

**Persona:** Sari (Finance Admin, PRIMARY)  
**Situation:** Month-end (tanggal 1 bulan baru). Sari needs to close previous month's books. Biasanya butuh 3 hari untuk reconcile, generate reports, close period. Target: < 8 jam.

---

## Driving Forces (Q4)

**Hope:** All transactions auto-posted throughout month, reconciliation clean, reports generated in hours, go home on time.

**Worry:** Missing entries, reconciliation errors, stuck in 3-day marathon again, weekend ruined, burnout continues.

---

## Device & Starting Point (Q5 + Q6)

**Device:** Desktop  
**Entry:** Monthly routine — first working day of new month, Sari starts monthly close process from Accounting Dashboard.

---

## Best Outcome (Q7)

**User Success:**
Trial balance balanced, P&L + Balance Sheet generated and accurate, fiscal period closed. Total time < 8 hours. Sari goes home on time, work-life balance restored.

**Business Success:**
Timely financial reports (within 1 day of month-end), owner gets real-time insights, strategic decisions made faster. Finance Admin demonstrates value as strategic analyst, not data entry clerk.

---

## Shortest Path (Q8)

1. **Dashboard (Accounting)** — Sari sees "Month-End Close" prompt, clicks to start process
2. **Trial Balance** — Sari reviews trial balance, all accounts balanced (95%+ auto-posted), no discrepancies
3. **General Ledger** — Sari spot-checks GL entries for accuracy, audit trail complete
4. **P&L Statement** — Sari generates P&L, reviews revenue/expenses, exports for owner
5. **Balance Sheet** — Sari generates balance sheet, verifies assets/liabilities/equity
6. **Fiscal Period Management** — Sari closes previous period, locks entries, opens new period ✓

---

## Trigger Map Connections

**Persona:** Sari (Finance Admin, PRIMARY)

**Driving Forces Addressed:**
- ✅ **Want:** Faster monthly close (< 8 hours vs 3 hari)
- ❌ **Fear:** 3-day marathon, burnout, career stagnation

**Business Goal:** ⭐ PRIMARY: Faster monthly close → timely insights → strategic decisions

---

## Scenario Steps

| Step | Folder | Purpose | Exit Action |
|------|--------|---------|-------------|
| 03.1 | `03.1-accounting-dashboard/` | Start month-end close process | Click "Start Month-End Close" |
| 03.2 | `03.2-trial-balance/` | Review trial balance, verify balanced | Click "Generate Reports" |
| 03.3 | `03.3-general-ledger/` | Spot-check GL entries | Click "P&L Statement" |
| 03.4 | `03.4-pl-statement/` | Generate & review P&L | Click "Balance Sheet" |
| 03.5 | `03.5-balance-sheet/` | Generate & review balance sheet | Click "Close Period" |
| 03.6 | `03.6-fiscal-period-management/` | Close period, lock entries | Click "Close Period" ✓ |

---

_Scenario 03: Sari's Monthly Close_
