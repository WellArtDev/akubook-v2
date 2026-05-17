# Scenario 17: Aging Reports (AR/AP)

**User:** Finance Admin / Credit Controller  
**Priority:** HIGH (Cash flow management)  
**Frequency:** Weekly  
**Success Metric:** Report generated in <30 seconds

---

## Scenario Goal

Finance Admin generates Accounts Receivable (AR) and Accounts Payable (AP) aging reports to monitor outstanding balances and manage cash flow.

---

## User Context

**Who:** Sari (Finance Admin) or Credit Controller monitoring collections and payments

**When:** Weekly review, before payment runs, before collections calls

**Why:** Monitor overdue invoices, prioritize collections, plan payments, manage cash flow

**Current Pain (from Accurate):** Static reports, no drill-down, manual follow-up, slow generation

---

## Sunshine Path (Happy Flow)

### Step 1: View AR Aging Dashboard

**Page:** AR Aging Dashboard

**User Action:**
- Opens AR Aging module
- Views aging summary

**System Shows:**
- AR aging summary (as of 2026-05-13):
  - Total AR: Rp 800,000,000
  - Current (0-30 days): Rp 480,000,000 (60%)
  - 31-60 days: Rp 160,000,000 (20%)
  - 61-90 days: Rp 80,000,000 (10%)
  - Over 90 days: Rp 80,000,000 (10%)
- Top 10 overdue customers
- Aging trend chart (last 6 months)
- Collection alerts (5 customers overdue >60 days)

**User Input:**
- Reviews dashboard
- Clicks "View Detailed Report"

**System Response:**
- Shows detailed AR aging report

**Next:** Review detailed report

---

### Step 2: Generate AR Aging Report

**Page:** AR Aging Report

**User Action:**
- Selects report date: 2026-05-13
- Selects aging buckets: 0-30, 31-60, 61-90, 90+

**System Shows:**
- AR aging report:
  - Customer list (100 customers)
  - Columns: Customer, Total, Current, 31-60, 61-90, 90+
  - Example rows:
    - PT Toko Elektronik Jaya: Rp 150M (Rp 100M, Rp 30M, Rp 20M, Rp 0)
    - PT Distributor Audio: Rp 80M (Rp 50M, Rp 20M, Rp 10M, Rp 0)
    - PT Retail Sound: Rp 60M (Rp 0, Rp 0, Rp 20M, Rp 40M) ⚠️
  - Totals: Rp 800M (Rp 480M, Rp 160M, Rp 80M, Rp 80M)

**User Input:**
- Filters by overdue only (>30 days)
- Sorts by overdue amount (descending)
- Clicks "Export to Excel"

**System Response:**
- Generates Excel report
- Downloads file: AR_Aging_2026-05-13.xlsx

**Next:** Follow up with overdue customers

---

### Step 3: Drill Down to Customer Detail

**Page:** Customer AR Detail

**User Action:**
- Clicks customer: "PT Retail Sound"

**System Shows:**
- Customer AR detail:
  - Customer: PT Retail Sound
  - Total AR: Rp 60,000,000
  - Overdue: Rp 60,000,000 (100%)
  - Invoice list:
    - INV-2026-01-015: Rp 20M (120 days overdue) ⚠️
    - INV-2026-02-025: Rp 20M (90 days overdue) ⚠️
    - INV-2026-03-035: Rp 20M (60 days overdue) ⚠️
  - Payment history (last 6 months)
  - Contact information
  - Credit limit: Rp 100M
  - Available credit: Rp 40M

**User Input:**
- Reviews invoice details
- Clicks "Send Collection Email"

**System Response:**
- Opens email template
- Pre-fills customer email and invoice details
- Shows "Email Ready to Send" status

**Next:** Send collection email

---

### Step 4: View AP Aging Dashboard

**Page:** AP Aging Dashboard

**User Action:**
- Opens AP Aging module
- Views aging summary

**System Shows:**
- AP aging summary (as of 2026-05-13):
  - Total AP: Rp 600,000,000
  - Current (0-30 days): Rp 480,000,000 (80%)
  - 31-60 days: Rp 60,000,000 (10%)
  - 61-90 days: Rp 30,000,000 (5%)
  - Over 90 days: Rp 30,000,000 (5%)
- Top 10 suppliers by balance
- Payment due this week: Rp 100,000,000
- Aging trend chart (last 6 months)

**User Input:**
- Reviews dashboard
- Clicks "View Detailed Report"

**System Response:**
- Shows detailed AP aging report

**Next:** Review detailed report

---

### Step 5: Generate AP Aging Report

**Page:** AP Aging Report

**User Action:**
- Selects report date: 2026-05-13
- Selects aging buckets: 0-30, 31-60, 61-90, 90+

**System Shows:**
- AP aging report:
  - Supplier list (50 suppliers)
  - Columns: Supplier, Total, Current, 31-60, 61-90, 90+
  - Example rows:
    - PT Supplier Audio: Rp 200M (Rp 180M, Rp 20M, Rp 0, Rp 0)
    - PT Distributor Parts: Rp 150M (Rp 150M, Rp 0, Rp 0, Rp 0)
    - PT Vendor Electronics: Rp 100M (Rp 80M, Rp 20M, Rp 0, Rp 0)
  - Totals: Rp 600M (Rp 480M, Rp 60M, Rp 30M, Rp 30M)

**User Input:**
- Filters by due this week
- Sorts by due date (ascending)
- Clicks "Export to Excel"

**System Response:**
- Generates Excel report
- Downloads file: AP_Aging_2026-05-13.xlsx

**Next:** Schedule payments

---

### Step 6: Schedule Payments from AP Aging

**Page:** Payment Scheduling

**User Action:**
- Selects invoices due this week
- Clicks "Schedule Payments"

**System Shows:**
- Payment batch:
  - Total invoices: 10
  - Total amount: Rp 100,000,000
  - Payment date: 2026-05-15
  - Payment method: Bank transfer

**User Input:**
- Reviews payment batch
- Clicks "Schedule Batch"

**System Response:**
- Creates payment batch: PB-2026-05-001
- Adds to payment queue
- Sends reminder 1 day before payment date
- Shows "Payments Scheduled" status

**Next:** Done (payments scheduled)

---

## Pages/Screens Needed

1. **AR Aging Dashboard** - AR summary and alerts
2. **AR Aging Report** - Detailed AR aging
3. **Customer AR Detail** - Drill down to customer
4. **AP Aging Dashboard** - AP summary and alerts
5. **AP Aging Report** - Detailed AP aging
6. **Payment Scheduling** - Schedule payments from AP

---

## Data Models Required

### Tables

**ar_aging_snapshots**
- id, company_id, snapshot_date
- total_ar, current, days_31_60, days_61_90, days_over_90
- created_at, updated_at

**ar_aging_details**
- id, snapshot_id, customer_id, invoice_id
- invoice_date, due_date, days_overdue, amount, aging_bucket
- created_at, updated_at

**ap_aging_snapshots**
- id, company_id, snapshot_date
- total_ap, current, days_31_60, days_61_90, days_over_90
- created_at, updated_at

**ap_aging_details**
- id, snapshot_id, supplier_id, invoice_id
- invoice_date, due_date, days_overdue, amount, aging_bucket
- created_at, updated_at

**collection_activities**
- id, company_id, customer_id, invoice_id, activity_type
- activity_date, notes, follow_up_date, created_by
- created_at, updated_at

---

## Acceptance Criteria

**Functional:**
- ✅ Generate AR aging report
- ✅ Generate AP aging report
- ✅ Drill down to customer/supplier detail
- ✅ Export to Excel/PDF
- ✅ Send collection emails
- ✅ Schedule payments from AP aging

**Performance:**
- ✅ Report generation in <30 seconds
- ✅ Dashboard loads in <2 seconds
- ✅ Export completes in <10 seconds

**Security:**
- ✅ Role-based access (AR/AP separation)
- ✅ Audit trail for collection activities

**UX:**
- ✅ Interactive charts (drill-down)
- ✅ Clear aging indicators (red/yellow/green)
- ✅ One-click export
- ✅ Collection email templates

---

## Design Notes

**Tone:**
- Professional, actionable (cash flow management)
- Clear aging indicators (overdue alerts)
- Helpful guidance for collections

**UX Principles:**
- Real-time data (no delays)
- Interactive drill-down (customer/supplier detail)
- One-click actions (export, email, schedule)
- Visual indicators (aging buckets)

**Mobile Consideration:**
- Dashboards mobile-optimized
- Reports desktop-optimized (complex tables)

---

## Related Scenarios

- **02: Sales Order Flow** - AR source
- **06: Purchase Order Flow** - AP source
- **03: Monthly Close** - Aging review before close

---

## Accurate Feature Parity

**Accurate Aging Reports include:**
- AR aging report
- AP aging report

**AkuBook Enhancement:**
- Interactive dashboards (Accurate static)
- Drill-down to detail (Accurate limited)
- Collection email templates (Accurate doesn't have this)
- Payment scheduling from AP (Accurate manual)

---

**Scenario Status:** ✅ Ready for Implementation  
**Next:** Design wireframes for 6 pages in this flow
