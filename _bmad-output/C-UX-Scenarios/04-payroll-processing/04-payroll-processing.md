# 04: Budi's Payroll Processing

**Project:** AkuBook  
**Created:** 2026-05-12  
**Method:** Whiteport Design Studio (WDS)

---

## Transaction (Q1)

**What this scenario covers:**
Month-end: Review attendance data → run payroll calculation → generate payslips for 150+ employees

---

## Business Goal (Q2)

**Goal:** 🚀 SECONDARY: Automated Payroll (< 4 jam vs 2 hari)  
**Objective:** HRD Manager processes payroll in < 4 hours instead of 2-day manual calculation

---

## User & Situation (Q3)

**Persona:** Budi (HRD Manager, SECONDARY)  
**Situation:** Month-end (last week of month). Budi needs to process payroll for 150+ employees across 5 cabang. Biasanya 2 hari (manual attendance compilation + Excel calculation). Target: < 4 jam.

---

## Driving Forces (Q4)

**Hope:** Attendance auto-synced from ZKTeco, lembur/cuti auto-calculated, payslips generated in hours, employees paid on time.

**Worry:** Attendance data missing, manual calculation errors, employee complaints, payroll errors damage reputation.

---

## Device & Starting Point (Q5 + Q6)

**Device:** Desktop  
**Entry:** Monthly routine — last week of month, Budi starts payroll process from HRM Dashboard.

---

## Best Outcome (Q7)

**User Success:**
Attendance verified (auto-synced), payroll calculated accurately, payslips generated and distributed. Total time < 4 hours. No employee complaints, reputation intact.

**Business Success:**
HRD freed up for strategic HR work (talent development, culture building). Payroll accuracy 100%, compliance confidence, employee satisfaction maintained.

---

## Shortest Path (Q8)

1. **Dashboard (HRM)** — Budi sees "Payroll Ready" notification (attendance auto-synced), clicks to start
2. **Attendance (List)** — Budi reviews attendance summary (150+ employees, 5 cabang), verifies lembur/cuti auto-calculated
3. **Payroll Processing** — Budi runs payroll calculation (one-click), system auto-calculates salary + lembur + cuti - tax - BPJS
4. **Payslip (View)** — Budi spot-checks payslips, verifies calculation breakdown, approves batch distribution ✓
5. **Journal Entry (Auto-Posted)** — Finance Admin (Sari) reviews auto-posted payroll journal entry, verifies DR: Salary Expense / CR: Salary Payable + Tax Payable, marks as reviewed ✓

---

## Trigger Map Connections

**Persona:** Budi (HRD Manager, SECONDARY)

**Driving Forces Addressed:**
- ✅ **Want:** Automated payroll (< 4 jam vs 2 hari)
- ❌ **Fear:** Payroll errors, employee complaints, reputation damage

**Business Goal:** 🚀 SECONDARY: Automated payroll → HRD freed up → strategic HR partner

---

## Scenario Steps

| Step | Folder | Purpose | Exit Action |
|------|--------|---------|-------------|
| 04.1 | `04.1-hrm-dashboard/` | See payroll ready notification | Click "Start Payroll" |
| 04.2 | `04.2-attendance-list/` | Review attendance summary | Click "Run Payroll" |
| 04.3 | `04.3-payroll-processing/` | Run payroll calculation | Click "Review Payslips" |
| 04.4 | `04.4-payslip-view/` | Spot-check payslips, approve distribution | Click "Distribute Payslips" ✓ |
| 04.5 | `04.5-journal-entry-review/` | Finance Admin reviews auto-posted payroll journal | Mark as "Reviewed" ✓ |

## Auto-Posting Triggers

**Payroll Run Completed:**
- DR: Salary Expense (per department/cost center)
- DR: PPh 21 Receivable (employee tax withholding)
- CR: Salary Payable (net pay to employees)
- CR: PPh 21 Payable (tax liability to government)
- CR: BPJS Payable (social security contributions)
- **Trigger**: When payroll status = "Approved"

**Example Entry:**
```
DR: Salary Expense - Sales Dept     Rp 50,000,000
DR: Salary Expense - Admin Dept     Rp 30,000,000
DR: PPh 21 Receivable                Rp  8,000,000
    CR: Salary Payable               Rp 72,000,000
    CR: PPh 21 Payable               Rp  8,000,000
    CR: BPJS Payable                 Rp  8,000,000
```

---

_Scenario 04: Budi's Payroll Processing_
