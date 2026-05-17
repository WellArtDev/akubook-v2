# 15: Warehouse Staff's Stock Opname (Physical Count)

**Project:** AkuBook  
**Created:** 2026-05-12  
**Method:** Whiteport Design Studio (WDS)

---

## Transaction (Q1)

**What this scenario covers:**
Month-end: Conduct physical inventory count → compare with system → adjust discrepancies → auto-post adjustment journal

---

## Business Goal (Q2)

**Goal:** 🚀 SECONDARY: Real-Time Inventory Accuracy  
**Objective:** Stock opname process < 4 hours vs 2 days manual, discrepancies auto-adjusted

---

## User & Situation (Q3)

**Persona:** Warehouse Staff (SECONDARY) + Sari (Finance Admin)  
**Situation:** Month-end (last day). Warehouse staff conducts physical count of 500+ SKUs across 3 warehouses. Need to compare with system, identify discrepancies, and adjust.

---

## Driving Forces (Q4)

**Hope:** Mobile app for counting (barcode scan), system comparison automatic, discrepancies flagged, adjustments auto-posted.

**Worry:** Manual counting tedious, Excel reconciliation nightmare, discrepancies not resolved, inventory valuation wrong.

---

## Device & Starting Point (Q5 + Q6)

**Device:** Mobile (warehouse staff) + Desktop (Finance Admin)  
**Entry:** Monthly routine — last day of month, triggered by month-end close requirement.

---

## Best Outcome (Q7)

**User Success:**
Physical count completed in < 4 hours (mobile app + barcode), discrepancies identified automatically, adjustments approved and posted. Inventory accurate for month-end close.

**Business Success:**
Accurate inventory valuation, no surprises at year-end audit, COGS calculation correct, Finance Admin confident in inventory balance.

---

## Shortest Path (Q8)

1. **Dashboard (Inventory)** — Warehouse Manager sees "Stock Opname Due" notification, clicks "Start Count"
2. **Stock Opname (Create)** — Manager creates opname session, assigns warehouses/staff, generates count sheets
3. **Mobile App (Count)** — Warehouse staff scans barcodes, enters quantities, system records counts in real-time
4. **Discrepancy Report** — System compares physical count vs system balance, flags discrepancies (over/short)
5. **Review Discrepancies** — Warehouse Manager reviews discrepancies, investigates causes (theft/damage/error)
6. **Adjustment Approval** — Finance Admin (Sari) reviews adjustment proposal, approves
7. **Journal Entry (Auto-Posted)** — System auto-posts adjustment:
   - **Shortage**: DR: Inventory Adjustment (expense), CR: Inventory
   - **Overage**: DR: Inventory, CR: Inventory Adjustment (income)
8. **Opname Report** — Sari generates opname report for audit trail, marks as "Completed" ✓

---

## Trigger Map Connections

**Persona:** Warehouse Staff (SECONDARY) + Sari (Finance Admin, PRIMARY)

**Driving Forces Addressed:**
- ✅ **Want:** Digital inventory count (mobile app), auto-adjustment
- ❌ **Fear:** Manual counting tedious, Excel reconciliation nightmare

**Business Goal:** 🚀 SECONDARY: Real-time inventory accuracy → auto-adjustment → Finance Admin confidence

---

## Scenario Steps

| Step | Folder | Purpose | Exit Action |
|------|--------|---------|-------------|
| 15.1 | `15.1-inventory-dashboard/` | See opname notification | Click "Start Stock Opname" |
| 15.2 | `15.2-opname-create/` | Create opname session | Click "Generate Count Sheets" |
| 15.3 | `15.3-mobile-count/` | Warehouse staff counts (mobile) | Submit counts |
| 15.4 | `15.4-discrepancy-report/` | Review discrepancies | Click "Investigate" |
| 15.5 | `15.5-adjustment-approval/` | Finance Admin approves adjustment | Click "Approve & Post" |
| 15.6 | `15.6-journal-entry-auto/` | Verify auto-posted adjustment | Mark as "Reviewed" ✓ |

## Auto-Posting Triggers

**Stock Adjustment (Shortage):**
- DR: Inventory Adjustment Expense (or specific reason: Theft, Damage, Shrinkage)
- CR: Inventory
- **Trigger**: When Stock Opname status = "Approved"

**Stock Adjustment (Overage):**
- DR: Inventory
- CR: Inventory Adjustment Income
- **Trigger**: When Stock Opname status = "Approved"

**Example Entry:**
```
Shortage (10 units @ Rp 500,000):
DR: Inventory Shrinkage Expense  Rp 5,000,000
    CR: Inventory                Rp 5,000,000

Overage (5 units @ Rp 500,000):
DR: Inventory                    Rp 2,500,000
    CR: Inventory Adjustment     Rp 2,500,000
```

## Discrepancy Investigation

**Common Causes:**
- **Theft**: Security review, police report if significant
- **Damage**: Damaged goods not recorded, move to damaged location
- **Data Entry Error**: Receiving/delivery not recorded properly
- **Counting Error**: Recount required
- **Shrinkage**: Normal loss (evaporation, breakage)

**Approval Threshold:**
- < Rp 1M: Warehouse Manager approval
- ≥ Rp 1M: Finance Admin + Owner approval

## Integration Points

**Upstream:**
- Inventory Master (SKU list, current balances)
- Warehouse Locations (bin locations)
- Mobile App (barcode scanning)

**Downstream:**
- General Ledger (Inventory Adjustment accounts)
- Inventory Valuation (COGS calculation)
- Audit Trail (opname history)

---

_Scenario 15: Warehouse Staff's Stock Opname_
