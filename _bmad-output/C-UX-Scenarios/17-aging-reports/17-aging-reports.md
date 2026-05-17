# 17: Sari's AR/AP Aging Reports

**Project:** AkuBook  
**Created:** 2026-05-12  
**Method:** Whiteport Design Studio (WDS)

---

## Transaction (Q1)

**What this scenario covers:**
Weekly: Generate AR/AP aging reports → identify overdue invoices → prioritize collections/payments → take action

---

## Business Goal (Q2)

**Goal:** 🚀 SECONDARY: Cash Flow Management  
**Objective:** AR/AP aging reports generated in < 5 minutes vs 1 hour manual Excel, actionable insights immediate

---

## User & Situation (Q3)

**Persona:** Sari (Finance Admin, PRIMARY)  
**Situation:** Weekly routine (every Monday). Sari needs to generate AR aging report to identify overdue customer invoices for collections team, and AP aging report to prioritize vendor payments.

---

## Driving Forces (Q4)

**Hope:** Aging reports generated one-click, overdue invoices flagged automatically, drill-down to invoice details, export to Excel for follow-up.

**Worry:** Manual Excel aging calculation tedious, errors in aging buckets, missed collections, vendor payment delays.

---

## Device & Starting Point (Q5 + Q6)

**Device:** Desktop  
**Entry:** Weekly routine — every Monday morning, Sari generates aging reports from Reports module.

---

## Best Outcome (Q7)

**User Success:**
AR/AP aging reports generated in < 5 minutes, overdue invoices identified, drill-down to details, exported to Excel. Collections team receives AR aging, Purchasing receives AP aging. Total time < 10 minutes.

**Business Success:**
Improved cash flow (faster collections), vendor relationships maintained (on-time payments), Finance Admin efficient.

---

## Shortest Path (Q8)

1. **Dashboard (Reports)** — Sari sees "Weekly Aging Due" notification, clicks "Generate Aging Reports"
2. **AR Aging Report** — Sari generates AR aging:
   - **Aging Buckets**: Current, 1-30 days, 31-60 days, 61-90 days, >90 days
   - **Basis**: Invoice Date or Due Date (selectable)
   - **Grouping**: By customer
3. **Review Overdue** — Sari reviews >30 days overdue (Rp 50M total), drills down to invoice details
4. **Export AR Aging** — Sari exports to Excel, sends to Collections team with priority list
5. **AP Aging Report** — Sari generates AP aging (same buckets)
6. **Review Due This Week** — Sari reviews invoices due this week (Rp 30M), prioritizes payments
7. **Export AP Aging** — Sari exports to Excel, sends to Purchasing for payment scheduling ✓

---

## Trigger Map Connections

**Persona:** Sari (Finance Admin, PRIMARY)

**Driving Forces Addressed:**
- ✅ **Want:** One-click aging reports, actionable insights
- ❌ **Fear:** Manual Excel calculation tedious, missed collections

**Business Goal:** 🚀 SECONDARY: Cash flow management → faster collections → vendor relationships maintained

---

## Scenario Steps

| Step | Folder | Purpose | Exit Action |
|------|--------|---------|-------------|
| 17.1 | `17.1-reports-dashboard/` | See aging notification | Click "Generate Aging" |
| 17.2 | `17.2-ar-aging-report/` | Generate AR aging | Click "Review Overdue" |
| 17.3 | `17.3-ar-overdue-detail/` | Drill-down to invoice details | Click "Export" |
| 17.4 | `17.4-ar-export/` | Export to Excel | Send to Collections |
| 17.5 | `17.5-ap-aging-report/` | Generate AP aging | Click "Review Due" |
| 17.6 | `17.6-ap-due-detail/` | Drill-down to invoice details | Click "Export" |
| 17.7 | `17.7-ap-export/` | Export to Excel | Send to Purchasing ✓ |

## Aging Calculation

**Aging Buckets (Standard):**
- **Current**: Not yet due (due date > today)
- **1-30 days**: 1-30 days overdue
- **31-60 days**: 31-60 days overdue
- **61-90 days**: 61-90 days overdue
- **>90 days**: More than 90 days overdue

**Aging Basis:**
- **Invoice Date**: Age calculated from invoice date
- **Due Date**: Age calculated from due date (more common)

**Example:**
```
Invoice Date: 2026-04-01
Due Date: 2026-05-01 (Net 30)
Today: 2026-05-15

Aging (Invoice Date basis): 44 days → 31-60 bucket
Aging (Due Date basis): 14 days → 1-30 bucket
```

## Report Features

**Grouping Options:**
- By Customer/Vendor
- By Salesperson (AR only)
- By Department
- By Branch

**Sorting Options:**
- By Amount (largest first)
- By Age (oldest first)
- By Customer/Vendor Name

**Drill-Down:**
- Click customer → see all invoices
- Click invoice → see invoice details
- Click payment → see payment history

**Export Formats:**
- Excel (with formulas)
- PDF (for printing)
- CSV (for import to other systems)

## Integration Points

**Upstream:**
- Sales Invoices (AR source)
- Purchase Invoices (AP source)
- Payment Receipts (AR reductions)
- Payment Vouchers (AP reductions)

**Downstream:**
- Collections Team (AR aging)
- Purchasing Team (AP aging)
- Cash Flow Forecast (payment scheduling)
- Dashboard (overdue KPIs)

## Action Items from Aging

**AR Aging Actions:**
- **Current**: Monitor, no action
- **1-30 days**: Friendly reminder email
- **31-60 days**: Phone call, payment plan offer
- **61-90 days**: Escalate to manager, hold future orders
- **>90 days**: Legal action, write-off consideration

**AP Aging Actions:**
- **Current**: Schedule payment per terms
- **1-30 days**: Prioritize payment (avoid late fees)
- **31-60 days**: Urgent payment (vendor relationship risk)
- **61-90 days**: Negotiate payment plan
- **>90 days**: Dispute resolution, legal risk

---

_Scenario 17: Sari's AR/AP Aging Reports_
