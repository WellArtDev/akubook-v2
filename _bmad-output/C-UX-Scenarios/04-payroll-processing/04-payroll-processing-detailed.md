# Scenario 04: Payroll Processing

**User:** HRD Manager / Payroll Admin  
**Priority:** HIGH (Employee satisfaction)  
**Frequency:** Monthly  
**Success Metric:** Payroll processed in <4 hours (vs 2 days manual)

---

## Scenario Goal

HRD Manager processes monthly payroll for 150+ employees with automated attendance sync, calculation, and payslip generation.

---

## User Context

**Who:** Budi (HRD Manager) responsible for payroll processing

**When:** Last week of month (before payday)

**Why:** Pay employees accurately and on time, maintain employee satisfaction

**Current Pain (from Accurate):** Manual attendance compilation from ZKTeco, Excel calculations, error-prone, takes 2 days

---

## Sunshine Path (Happy Flow)

### Step 1: Attendance Sync

**Page:** HRM Dashboard

**User Action:**
- Opens HRM module
- Sees "Payroll Ready" notification

**System Shows:**
- Attendance sync status:
  - ✅ Attendance synced from ZKTeco (150 employees)
  - ✅ Overtime calculated (45 employees)
  - ✅ Leave deductions calculated (12 employees)
  - ⚠️ 3 employees missing attendance data

**User Input:**
- Reviews sync status
- Clicks "Review Attendance"

**System Response:**
- Shows attendance summary by branch
- Highlights missing data

**Next:** Fix missing data or proceed

---

### Step 2: Review Attendance

**Page:** Attendance List

**User Action:**
- Reviews attendance summary
- Filters by branch/department

**System Shows:**
- Attendance table (150 employees):
  - Employee name, branch, days worked, overtime hours, leave days
  - Auto-calculated totals
  - Missing data highlighted in red

**User Input:**
- Clicks employee with missing data
- Manually enters attendance (if needed)
- Confirms attendance data

**System Response:**
- Updates attendance records
- Recalculates totals
- Shows "Attendance Complete" status

**Next:** Proceed to payroll calculation

---

### Step 3: Run Payroll Calculation

**Page:** Payroll Processing

**User Action:**
- Clicks "Run Payroll" button

**System Shows:**
- Payroll calculation preview:
  - Total employees: 150
  - Total gross salary: Rp 500M
  - Total overtime: Rp 50M
  - Total deductions: Rp 80M (tax + BPJS)
  - Total net pay: Rp 470M

**User Input:**
- Reviews calculation summary
- Clicks "Confirm Calculation"

**System Response:**
- Runs payroll calculation for all employees:
  - Gross salary (base + allowances)
  - Overtime pay (hours × rate)
  - Leave deductions (days × daily rate)
  - Tax withholding (PPh 21)
  - BPJS contributions
  - Net pay
- Shows "Payroll Calculated" status

**Next:** Review payslips

---

### Step 4: Review Payslips

**Page:** Payslip View

**User Action:**
- Spot-checks payslips
- Filters by branch/department

**System Shows:**
- Payslip list (150 employees)
- Sample payslip detail:
  - Employee: Andi Wijaya
  - Base Salary: Rp 8,000,000
  - Allowances: Rp 2,000,000
  - Overtime: Rp 500,000
  - Gross: Rp 10,500,000
  - PPh 21: Rp 1,050,000
  - BPJS: Rp 450,000
  - Net Pay: Rp 9,000,000

**User Input:**
- Reviews payslip details
- Verifies calculation breakdown
- Clicks "Approve Payroll"

**System Response:**
- Locks payroll (no more edits)
- Generates payslip PDFs
- Shows "Payroll Approved" status

**Next:** Distribute payslips

---

### Step 5: Distribute Payslips

**Page:** Payslip Distribution

**User Action:**
- Clicks "Distribute Payslips" button

**System Shows:**
- Distribution options:
  - Email to employees (default)
  - Download all PDFs (bulk)
  - Print payslips (batch)

**User Input:**
- Selects "Email to employees"
- Confirms distribution

**System Response:**
- Sends payslip PDFs to employee emails
- Shows distribution status (150/150 sent)
- Creates notification for employees

**Next:** Review auto-posted journal entry

---

### Step 6: Finance Admin Reviews Journal Entry

**Page:** Journal Entry (Auto-Posted)

**User Action (Finance Admin - Sari):**
- Receives notification: "Payroll journal entry posted"
- Opens journal entry detail

**System Shows:**
- Auto-posted payroll journal entry:
  - DR: Salary Expense - Sales Dept: Rp 200M
  - DR: Salary Expense - Admin Dept: Rp 150M
  - DR: Salary Expense - Warehouse: Rp 150M
  - DR: PPh 21 Receivable: Rp 50M
  - CR: Salary Payable: Rp 470M
  - CR: PPh 21 Payable: Rp 50M
  - CR: BPJS Payable: Rp 30M
- Audit trail: Payroll Run #2026-04 by Budi

**User Input:**
- Reviews entry details
- Verifies account mapping
- Marks as "Reviewed"

**System Response:**
- Updates entry status
- Sends confirmation to HRD

**Next:** Done (payroll complete)

---

## Pages/Screens Needed

1. **HRM Dashboard** - Payroll status and notifications
2. **Attendance List** - Review and edit attendance data
3. **Payroll Processing** - Run calculation, preview totals
4. **Payslip View** - Review individual payslips
5. **Payslip Distribution** - Email/download/print options
6. **Journal Entry (Auto-Posted)** - Finance Admin review

---

## Data Models Required

### Tables

**payroll_runs**
- id, company_id, period (YYYY-MM), status (draft/calculated/approved/distributed)
- total_employees, total_gross, total_deductions, total_net
- calculated_by, calculated_at, approved_by, approved_at
- journal_entry_id, created_at, updated_at

**payroll_details**
- id, payroll_run_id, employee_id
- base_salary, allowances, overtime_pay, leave_deductions
- gross_salary, tax_withholding, bpjs_contribution, net_pay
- created_at, updated_at

**attendance_records**
- id, employee_id, date, check_in, check_out
- hours_worked, overtime_hours, status (present/absent/leave)
- synced_from_device, created_at, updated_at

**leave_requests**
- id, employee_id, leave_type, start_date, end_date, days
- status (pending/approved/rejected), approved_by, approved_at
- created_at, updated_at

**overtime_records**
- id, employee_id, date, hours, rate, amount
- approved_by, approved_at, created_at, updated_at

**payslips**
- id, payroll_detail_id, employee_id, period
- pdf_path, email_sent_at, downloaded_at
- created_at, updated_at

---

## Auto-Posting Rules

**Payroll Run Approved:**
- DR: Salary Expense (per department/cost center)
- DR: PPh 21 Receivable (employee tax withholding)
- CR: Salary Payable (net pay to employees)
- CR: PPh 21 Payable (tax liability to government)
- CR: BPJS Payable (social security contributions)
- **Trigger:** When payroll status = "Approved"

**Example Entry:**
```
DR: Salary Expense - Sales Dept     Rp 200,000,000
DR: Salary Expense - Admin Dept     Rp 150,000,000
DR: Salary Expense - Warehouse      Rp 150,000,000
DR: PPh 21 Receivable               Rp  50,000,000
    CR: Salary Payable              Rp 470,000,000
    CR: PPh 21 Payable              Rp  50,000,000
    CR: BPJS Payable                Rp  30,000,000
```

---

## Acceptance Criteria

**Functional:**
- ✅ Attendance syncs from ZKTeco automatically
- ✅ Overtime and leave auto-calculated
- ✅ Payroll calculation accurate (gross, deductions, net)
- ✅ Payslips generated as PDFs
- ✅ Payslips distributed via email
- ✅ Journal entry auto-posted to GL
- ✅ Audit trail complete

**Performance:**
- ✅ Payroll processing completes in <4 hours
- ✅ Attendance sync processes 150+ employees in <5 minutes
- ✅ Payslip generation completes in <2 minutes
- ✅ Email distribution completes in <5 minutes

**Security:**
- ✅ Only authorized users can run payroll
- ✅ Payroll locked after approval (no edits)
- ✅ Payslips encrypted (PDF password protected)
- ✅ Audit trail for all payroll activities

**UX:**
- ✅ Clear status indicators (sync/calculated/approved)
- ✅ Missing data highlighted
- ✅ One-click payroll run
- ✅ Bulk payslip distribution

---

## Design Notes

**Tone:**
- Efficient, reliable (critical process)
- Clear status indicators (green/yellow/red)
- Helpful guidance for first-time users
- "Payroll ready to distribute" confirmation

**UX Principles:**
- Automated sync (minimize manual input)
- One-click calculation (fast processing)
- Bulk operations (distribute all at once)
- Preview before commit (show totals)
- Celebrate completion (positive reinforcement)

**Mobile Consideration:**
- Payroll processing desktop-only (complex calculation)
- Mobile: View-only access to payslips

---

## Related Scenarios

- **05: Attendance Management** - Attendance data source
- **03: Monthly Close** - Payroll journal entry reviewed during close
- **10: Profile & Help** - Employee self-service payslip access

---

## Accurate Feature Parity

**Accurate Payroll includes:**
- Payroll calculation
- Tax withholding (PPh 21)
- BPJS contributions
- Payslip generation

**AkuBook Enhancement:**
- ZKTeco attendance sync (Accurate doesn't have this)
- One-click payroll run (Accurate more manual)
- Bulk email distribution (Accurate manual)
- Auto-posted journal entries (Accurate requires manual posting)

---

**Scenario Status:** ✅ Ready for Implementation  
**Next:** Design wireframes for 6 pages in this flow
