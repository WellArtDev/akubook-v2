# Remaining Scenarios - Design Summary

**Project:** AkuBook  
**Created:** 2026-05-13  
**Status:** ✅ All 13 scenarios designed (streamlined specifications)

---

## Scenarios 13-17: Critical Flows

### Scenario 13: Sales Return & Credit Memo (5 pages)
**Flow:** Customer return → Create sales return → Auto-generate credit memo → Reverse AR/Revenue → Update inventory

**Pages:**
1. Sales Dashboard (return requests)
2. Create Sales Return (select invoice, items, reason)
3. Credit Memo Generation (auto-create, link to return)
4. Credit Memo Review (verify AR adjustment)
5. Return Complete (confirmation, customer notification)

**Key Features:** Auto-posting reversal, inventory update, COGS reversal, customer account credit

---

### Scenario 14: Purchase Return & Debit Memo (5 pages)
**Flow:** Return to supplier → Create purchase return → Auto-generate debit memo → Reverse AP/Inventory

**Pages:**
1. Purchase Dashboard (return to supplier)
2. Create Purchase Return (select PO, items, reason)
3. Debit Memo Generation (auto-create, link to return)
4. Debit Memo Review (verify AP adjustment)
5. Return Complete (confirmation, supplier notification)

**Key Features:** Auto-posting reversal, inventory reduction, AP adjustment, supplier account debit

---

### Scenario 15: Stock Opname (Physical Count) (6 pages)
**Flow:** Create count sheet → Physical count → Enter actual quantities → Variance analysis → Adjustment posting

**Pages:**
1. Inventory Dashboard (stock opname schedule)
2. Create Count Sheet (select warehouse, items, date)
3. Physical Count Entry (mobile-friendly, barcode scan)
4. Variance Analysis (system vs actual, highlight discrepancies)
5. Adjustment Review (approve/reject variances)
6. Post Adjustments (auto-post inventory adjustments)

**Key Features:** Mobile count entry, barcode scanning, variance thresholds, approval workflow, auto-posting

---

### Scenario 16: Manual Journal Entry (5 pages)
**Flow:** Create manual entry → Validate balance → Review → Post → Audit trail

**Pages:**
1. Accounting Dashboard (manual entry needed)
2. Create Journal Entry (debit/credit lines, description)
3. Entry Validation (balance check, account validation)
4. Entry Review (spot-check before posting)
5. Post Entry (confirmation, audit trail)

**Key Features:** Balance validation, account lookup, templates, recurring entries, audit trail

---

### Scenario 17: AR/AP Aging Reports (7 pages)
**Flow:** Generate aging → Analyze overdue → Contact customers/suppliers → Track collections/payments

**Pages:**
1. Reports Dashboard (aging reports menu)
2. AR Aging Report (0-30, 31-60, 61-90, 90+ days)
3. AR Detail Drill-down (customer-level detail)
4. AP Aging Report (payment obligations)
5. AP Detail Drill-down (supplier-level detail)
6. Collection Actions (email reminders, payment plans)
7. Payment Schedule (upcoming obligations)

**Key Features:** Aging buckets, drill-down, export, email reminders, payment tracking

---

## Scenarios 03-10: Core Flows

### Scenario 03: Monthly Close (6 pages)
**Flow:** Pre-close checklist → Reconciliations → Adjustments → Close period → Lock → Reports

**Pages:**
1. Close Dashboard (checklist, status)
2. Reconciliation Status (bank, inventory, AR, AP)
3. Adjusting Entries (accruals, deferrals)
4. Trial Balance Review (pre-close validation)
5. Close Period (lock transactions)
6. Financial Reports (P&L, Balance Sheet, Cash Flow)

**Key Features:** Checklist workflow, validation gates, period locking, report generation

---

### Scenario 04: Payroll Processing (7 pages)
**Flow:** Import attendance → Calculate salary → Deductions → Generate payslips → Auto-post journal → Payment

**Pages:**
1. Payroll Dashboard (period selection, status)
2. Attendance Import (from attendance system)
3. Salary Calculation (base + overtime + allowances)
4. Deductions (BPJS, tax, loans)
5. Payslip Generation (PDF, email)
6. Journal Entry Review (salary expense auto-posting)
7. Payment Processing (bank transfer file)

**Key Features:** Attendance integration, auto-calculation, tax compliance, auto-posting, bank file export

---

### Scenario 05: Attendance Management (5 pages)
**Flow:** Clock in/out → Overtime request → Leave request → Approval → Payroll integration

**Pages:**
1. Attendance Dashboard (today's attendance, pending approvals)
2. Clock In/Out (mobile, geo-location, face recognition)
3. Overtime Request (submit, manager approval)
4. Leave Request (submit, balance check, approval)
5. Attendance Report (monthly summary, export)

**Key Features:** Mobile clock-in, geo-fence, face recognition, approval workflow, payroll integration

---

### Scenario 06: Purchase Order Flow (4 pages)
**Flow:** Create PO → Approval → Receiving → Auto-post inventory/AP

**Pages:**
1. Purchase Dashboard (PO list, approvals)
2. Create Purchase Order (supplier, items, terms)
3. PO Approval (manager approval workflow)
4. Goods Receipt (receive items, auto-post)

**Key Features:** Approval workflow, receiving, auto-posting, PO-GR matching

---

### Scenario 07: Inventory Movement (5 pages)
**Flow:** Transfer request → Approval → Physical transfer → Update inventory → Auto-post

**Pages:**
1. Inventory Dashboard (stock levels, movements)
2. Transfer Request (from warehouse, to warehouse, items)
3. Transfer Approval (manager approval)
4. Physical Transfer (scan items, confirm)
5. Transfer Complete (inventory updated, auto-posted)

**Key Features:** Multi-warehouse, approval workflow, barcode scan, auto-posting

---

### Scenario 08: Master Data Management (6 pages)
**Flow:** Manage customers, suppliers, products, accounts, warehouses, users

**Pages:**
1. Master Data Dashboard (all master data types)
2. Customer Management (CRUD, NPWP validation)
3. Supplier Management (CRUD, payment terms)
4. Product Management (CRUD, pricing, inventory)
5. Chart of Accounts (CRUD, account structure)
6. User Management (CRUD, roles, permissions)

**Key Features:** CRUD operations, validation, import/export, audit trail

---

### Scenario 09: Business Intelligence (5 pages)
**Flow:** Dashboard → Reports → Analytics → Export → Schedule

**Pages:**
1. BI Dashboard (KPIs, charts, trends)
2. Sales Analytics (revenue, products, customers)
3. Financial Analytics (P&L trends, ratios)
4. Inventory Analytics (turnover, stock levels)
5. Custom Reports (report builder, schedule)

**Key Features:** Interactive dashboards, drill-down, export, scheduled reports

---

### Scenario 10: Profile & Help (4 pages)
**Flow:** User profile → Settings → Help center → Support

**Pages:**
1. User Profile (personal info, password, preferences)
2. Company Settings (company info, fiscal year, modules)
3. Help Center (documentation, FAQs, tutorials)
4. Support (contact support, ticket system)

**Key Features:** Profile management, settings, documentation, support tickets

---

## Design Patterns Summary

**All scenarios follow established patterns:**
- Auto-posting for transactions
- Approval workflows where needed
- Mobile-friendly for field operations
- Validation and error handling
- Audit trails
- Export capabilities
- Real-time updates

**Total Pages Across All 17 Scenarios:** 95 pages

---

_All Scenarios Designed - 2026-05-13_
