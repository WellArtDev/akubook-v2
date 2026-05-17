---
stepsCompleted: ["step-01-validate-prerequisites", "step-02-design-epics", "step-03-create-stories"]
inputDocuments:
  - "_bmad-output/planning-artifacts/prd.md"
  - "_bmad-output/planning-artifacts/architecture.md"
  - "_bmad-output/A-Product-Brief/product-brief.md"
  - "_bmad-output/C-UX-Scenarios/00-ux-scenarios.md"
  - "_bmad-output/accurate-research/00-INDEX.md"
---

# AkuBook - Epic Breakdown

## Overview

This document provides the complete epic and story breakdown for AkuBook, decomposing the requirements from the PRD, UX Design, and Architecture requirements into implementable stories.

## Requirements Inventory

### Functional Requirements

**From Product Brief - MVP Scope:**

FR1: Company profile setup dengan multi-branch dan multi-warehouse support
FR2: User management dengan RBAC (role-based access control)
FR3: Chart of accounts setup dengan industry templates (distributor, retail, service)
FR4: Fiscal year management dan period configuration
FR5: Journal entries (manual dan auto-generated dari transactions)
FR6: Posting dan period close functionality
FR7: Financial reports (Trial Balance, General Ledger, P&L, Balance Sheet)
FR8: Complete audit trail untuk semua transactions
FR9: Sales Order → Invoice → Delivery → Payment flow
FR10: Purchase Order → Receiving → Payment flow
FR11: Auto-posting to journal untuk sales transactions (AR/AP, Revenue/COGS, Inventory)
FR12: Auto-posting to journal untuk purchase transactions
FR13: Customer dan supplier management
FR14: Item master data management
FR15: Multi-warehouse stock tracking
FR16: Stock movements dan warehouse transfers
FR17: Cost accounting (FIFO/Average methods)
FR18: Auto-posting inventory transactions to journal
FR19: Employee master data management
FR20: Attendance management (online: geo-location + face recognition)
FR21: Attendance management (offline: ZKTeco device integration)
FR22: Leave management dengan approval workflow
FR23: Payroll processing dengan auto-calculation
FR24: Payroll components (salary, allowances, deductions)
FR25: Auto-posting payroll to journal
FR26: e-Faktur generation dan XML export
FR27: PPN calculation dan reporting
FR28: Tax compliance (basic PPh)
FR29: WhatsApp notifications via Wablas integration
FR30: Accurate data migration tool (offline & online)

### NonFunctional Requirements

NFR1: Page load time <2 seconds untuk dashboard dan list views
NFR2: Report generation <5 seconds untuk standard reports (P&L, Balance Sheet)
NFR3: Real-time sync <1 second untuk inventory updates across modules
NFR4: Uptime 99.5%+ (acceptable untuk self-hosted model)
NFR5: Zero accounting errors (journal entries always balance)
NFR6: 100% audit trail coverage (every transaction logged)
NFR7: Database transaction integrity (no partial commits)
NFR8: Zero data loss during migration dari Accurate
NFR9: 95%+ transactions auto-post to journal (PRIMARY SUCCESS METRIC)
NFR10: ZKTeco attendance device integration 100% sync success rate
NFR11: WhatsApp notification delivery 95%+ success rate
NFR12: Accurate data migration 90%+ data accuracy
NFR13: RBAC enforcement dengan granular permissions
NFR14: Data sovereignty (self-hosted deployment option)
NFR15: Indonesian tax compliance (e-Faktur, PPN, PPh)

### Additional Requirements

**From Architecture Document:**

ARCH1: Laravel 13 (PHP 8.3+) backend framework
ARCH2: React 18 + Inertia.js frontend (SPA experience dengan server-side routing)
ARCH3: PostgreSQL 17 database (ACID compliance)
ARCH4: React Native mobile app (code sharing dengan web)
ARCH5: Monolithic modular architecture (bukan microservices)
ARCH6: Domain-driven design (DDD) untuk module boundaries
ARCH7: Event-driven communication untuk cross-module integration
ARCH8: Repository pattern untuk data access
ARCH9: Auto-Posting Engine (event-driven + template-based posting rules)
ARCH10: Multi-Tenant Manager (schema-per-tenant dengan PostgreSQL schemas)
ARCH11: Audit Trail Service (event sourcing untuk audit events)
ARCH12: Tax Calculation Engine (strategy pattern untuk tax rules)
ARCH13: Real-Time Sync Service (WebSocket dengan Laravel Echo)
ARCH14: Notification Service (queue-based async processing)
ARCH15: Database transactions untuk accounting operations (ACID guarantee)
ARCH16: CQRS untuk reporting (materialized views untuk denormalized reads)
ARCH17: Docker containers untuk deployment
ARCH18: Queue workers untuk async processing

### UX Design Requirements

**From UX Scenarios (17 scenarios, 95 pages):**

UX1: Company setup wizard dengan industry selection
UX2: Dashboard dengan real-time KPIs dan metrics
UX3: Sales Order flow dengan approval workflow
UX4: Purchase Order flow dengan approval workflow
UX5: Inventory movement dengan barcode scanning support
UX6: Monthly close checklist workflow
UX7: Payroll processing dengan attendance import
UX8: Mobile attendance (geo-location + face recognition)
UX9: Leave request dengan approval workflow
UX10: Bank reconciliation dengan auto-matching
UX11: e-Faktur generation workflow
UX12: Sales return dan credit memo
UX13: Purchase return dan debit memo
UX14: Stock opname (physical count)
UX15: Manual journal entry dengan validation
UX16: AR/AP aging reports
UX17: Master data management (customers, suppliers, products, accounts)

### FR Coverage Map

(Will be populated after epic design in step 2)

## Epic List

(Will be populated in step 2)

## Epic List

Epic 1: Foundation & Setup (Company, Users, RBAC, Chart of Accounts, Fiscal Year)
Epic 2: Accounting Core (Journal Entries, Posting, Reports, Audit Trail)
Epic 3: Sales Order Flow (SO → Invoice → Delivery → Payment, Auto-posting)
Epic 4: Inventory Management (Items, Multi-warehouse, Movements, Cost Accounting)
Epic 5: Monthly Close & Financial Reporting (Period close, Financial reports)
Epic 6: Payroll Processing (Payroll calculation, Auto-posting)
Epic 7: Master Data Management (Customers, Suppliers, Products, Accounts)
Epic 8: Business Intelligence & Reports (Dashboard, Analytics, Custom reports)
Epic 9: Purchase Order Flow (PO → Receiving → Payment, Auto-posting)
Epic 10: Attendance Management (Online: geo+face, Offline: ZKTeco integration)
Epic 11: HRM Core (Employee management, Leave management, Approval workflows)
Epic 12: Tax & e-Faktur (e-Faktur generation, PPN calculation, Tax reporting)
Epic 13: Integrations & Migration (WhatsApp notifications, Accurate migration)

## Epic 9: Purchase Order Flow

Enable purchasing team to create PO, receive goods, and process payments dengan auto-posting to journal.

**Goal:** Purchasing Manager dapat create PO, approve, receive goods, dan system auto-post ke journal (Inventory debit, AP credit).

**FRs Covered:** FR10, FR12, FR13

### Story 9.1: Create Purchase Order

As a Purchasing Manager,
I want to create purchase orders untuk suppliers,
So that I can formalize purchasing requests dan track orders.

**Acceptance Criteria:**

**Given** I am logged in sebagai Purchasing Manager
**When** I navigate to Purchase → Create PO
**Then** I see form dengan fields: Supplier, PO Date, Items (product, qty, unit price), Terms, Notes
**And** I can add multiple line items
**And** I can save as Draft atau Submit for Approval

**Given** I submit PO for approval
**When** PO is submitted
**Then** PO status changes to "Pending Approval"
**And** Approval notification sent to Manager (WhatsApp + in-app)

### Story 9.2: Approve Purchase Order

As a Manager,
I want to approve atau reject purchase orders,
So that I can control purchasing decisions.

**Acceptance Criteria:**

**Given** I receive PO approval notification
**When** I open PO detail
**Then** I see PO information dan approval actions (Approve/Reject)
**And** I can add approval notes

**Given** I approve PO
**When** I click Approve
**Then** PO status changes to "Approved"
**And** Purchasing Manager receives approval notification
**And** PO is ready untuk receiving

**Given** I reject PO
**When** I click Reject dan add reason
**Then** PO status changes to "Rejected"
**And** Purchasing Manager receives rejection notification dengan reason

### Story 9.3: Goods Receipt (Receiving)

As a Warehouse Staff,
I want to receive goods dari supplier,
So that inventory is updated dan payment can be processed.

**Acceptance Criteria:**

**Given** PO is approved
**When** I navigate to Purchase → Goods Receipt
**Then** I see list of approved POs ready untuk receiving

**Given** I select PO untuk receiving
**When** I enter received quantities (can be partial)
**Then** System validates received qty ≤ ordered qty
**And** I can add receiving notes (damage, shortage, etc.)

**Given** I complete goods receipt
**When** I click Complete Receipt
**Then** Inventory is updated (stock increased)
**And** System auto-generates journal entry:
  - Debit: Inventory (asset account)
  - Credit: Accounts Payable (liability account)
**And** Receipt document is generated (printable)
**And** Supplier receives receipt confirmation (WhatsApp)

### Story 9.4: Purchase Payment Processing

As a Finance Admin,
I want to process payment untuk approved POs,
So that supplier obligations are settled dan AP is cleared.

**Acceptance Criteria:**

**Given** Goods receipt is completed
**When** I navigate to Purchase → Payments
**Then** I see list of unpaid POs dengan amounts due

**Given** I select PO untuk payment
**When** I enter payment details (date, amount, payment method, bank account)
**Then** System validates payment amount ≤ outstanding AP
**And** I can process partial payments

**Given** I complete payment
**When** I click Process Payment
**Then** System auto-generates journal entry:
  - Debit: Accounts Payable (clear liability)
  - Credit: Cash/Bank (asset account)
**And** Payment receipt is generated
**And** Supplier receives payment confirmation (WhatsApp)
**And** PO status updates to "Paid" (or "Partially Paid")

---

## Epic 10: Attendance Management

Enable employees to clock in/out (online: geo+face, offline: ZKTeco) dan HRD monitor attendance.

**Goal:** Employees dapat record attendance via mobile (geo-location + face recognition) atau ZKTeco device, dan HRD dapat monitor real-time.

**FRs Covered:** FR20, FR21

### Story 10.1: Mobile Clock In/Out (Geo-location)

As an Employee,
I want to clock in/out via mobile app dengan geo-location verification,
So that my attendance is recorded accurately.

**Acceptance Criteria:**

**Given** I open mobile app dan logged in
**When** I tap "Clock In"
**Then** App requests location permission
**And** App captures current GPS coordinates
**And** System validates location within company geo-fence (configurable radius)

**Given** Location is valid
**When** Clock in is confirmed
**Then** Attendance record is created dengan timestamp dan location
**And** I see confirmation message "Clocked In at [time]"
**And** Dashboard shows "Clocked In" status

**Given** I want to clock out
**When** I tap "Clock Out"
**Then** System validates I am clocked in
**And** Captures GPS coordinates
**And** Creates clock out record dengan timestamp
**And** Calculates working hours (clock out - clock in)

### Story 10.2: Mobile Clock In/Out (Face Recognition)

As an Employee,
I want to clock in/out dengan face recognition verification,
So that attendance is secure dan prevents buddy punching.

**Acceptance Criteria:**

**Given** I tap "Clock In" di mobile app
**When** Face recognition is enabled
**Then** App opens camera untuk face capture
**And** System compares face dengan registered employee photo
**And** Validates match confidence ≥ 85%

**Given** Face match is successful
**When** Verification completes
**Then** Attendance record is created dengan face verification flag
**And** I see confirmation "Clocked In - Face Verified"

**Given** Face match fails
**When** Confidence < 85%
**Then** System shows error "Face verification failed"
**And** Attendance is NOT recorded
**And** Employee can retry atau contact HRD

### Story 10.3: ZKTeco Device Integration (Offline Attendance)

As an HRD Manager,
I want to sync attendance data dari ZKTeco devices,
So that employees without mobile can record attendance.

**Acceptance Criteria:**

**Given** ZKTeco device is configured dengan connection settings
**When** Scheduled sync job runs (every 15 minutes)
**Then** System connects to ZKTeco device via SDK/API
**And** Pulls attendance records since last sync
**And** Maps device user ID to employee ID

**Given** Attendance records are pulled
**When** Sync completes
**Then** Records are imported to attendance table
**And** Duplicate records are skipped (same employee, same timestamp)
**And** Sync log is created (success/failure, record count)
**And** HRD receives notification jika sync fails

### Story 10.4: Attendance Dashboard & Monitoring

As an HRD Manager,
I want to monitor real-time attendance,
So that I can track who is present/absent dan identify issues.

**Acceptance Criteria:**

**Given** I navigate to HRM → Attendance Dashboard
**When** Page loads
**Then** I see today's attendance summary:
  - Total employees
  - Present (clocked in)
  - Absent (not clocked in by cutoff time)
  - Late (clocked in after scheduled time)
  - On leave (approved leave)

**Given** I want to see attendance details
**When** I click on attendance category
**Then** I see list of employees dengan clock in/out times
**And** I can filter by department, date range
**And** I can export to Excel

**Given** Employee is late
**When** Clock in time > scheduled time + grace period
**Then** System flags as "Late"
**And** Late notification sent to Manager

---

## Epic 11: HRM Core

Enable HRD to manage employees, leave requests, dan approval workflows.

**Goal:** HRD dapat manage employee master data, employees dapat submit leave requests, dan managers dapat approve/reject.

**FRs Covered:** FR19, FR22

### Story 11.1: Employee Master Data Management

As an HRD Manager,
I want to manage employee master data,
So that employee information is centralized dan up-to-date.

**Acceptance Criteria:**

**Given** I navigate to HRM → Employees
**When** I click "Add Employee"
**Then** I see employee form dengan fields:
  - Personal: Name, NIK, Email, Phone, Address, Birth Date
  - Employment: Employee ID, Join Date, Department, Position, Employment Type
  - Payroll: Salary, Bank Account, Tax ID (NPWP)
  - Attendance: Scheduled work hours, Geo-fence location

**Given** I fill employee form
**When** I save employee
**Then** Employee record is created
**And** User account is created (if email provided)
**And** Welcome email sent to employee
**And** Employee appears in employee list

**Given** I want to edit employee
**When** I click Edit on employee row
**Then** Form is pre-filled dengan existing data
**And** I can update any field
**And** Changes are logged in audit trail

### Story 11.2: Leave Request Submission

As an Employee,
I want to submit leave requests,
So that my absence is formally approved dan tracked.

**Acceptance Criteria:**

**Given** I navigate to HRM → My Leave
**When** I click "Request Leave"
**Then** I see leave request form dengan fields:
  - Leave Type (Annual, Sick, Unpaid, etc.)
  - Start Date, End Date
  - Reason/Notes
  - Attachment (optional, e.g. sick certificate)

**Given** I select leave dates
**When** I submit request
**Then** System validates:
  - Leave balance sufficient (for annual leave)
  - No overlapping leave requests
  - Start date ≤ End date
**And** Leave request is created dengan status "Pending"
**And** Manager receives approval notification (WhatsApp + in-app)

### Story 11.3: Leave Request Approval

As a Manager,
I want to approve atau reject leave requests,
So that I can manage team availability.

**Acceptance Criteria:**

**Given** I receive leave approval notification
**When** I open leave request detail
**Then** I see employee info, leave dates, reason, current leave balance
**And** I see approval actions (Approve/Reject)

**Given** I approve leave
**When** I click Approve
**Then** Leave status changes to "Approved"
**And** Leave balance is deducted (for annual leave)
**And** Employee receives approval notification
**And** Leave is reflected in attendance calendar

**Given** I reject leave
**When** I click Reject dan add reason
**Then** Leave status changes to "Rejected"
**And** Employee receives rejection notification dengan reason
**And** Leave balance is NOT deducted

### Story 11.4: Leave Balance Management

As an HRD Manager,
I want to manage employee leave balances,
So that leave entitlements are accurate.

**Acceptance Criteria:**

**Given** New employee joins
**When** Employee record is created
**Then** Initial leave balance is set based on policy (e.g. 12 days annual leave)

**Given** Fiscal year ends
**When** Year-end process runs
**Then** Unused leave is carried forward (based on policy)
**And** New year allocation is added
**And** Leave balance is reset

**Given** I want to adjust leave balance manually
**When** I navigate to Employee → Leave Balance
**Then** I can add/deduct leave days dengan reason
**And** Adjustment is logged in audit trail

---

## Epic 12: Tax & e-Faktur

Enable finance team to generate e-Faktur, calculate PPN, dan export tax reports.

**Goal:** Finance Admin dapat generate e-Faktur untuk sales invoices, calculate PPN accurately, dan export XML untuk DJP submission.

**FRs Covered:** FR26, FR27, FR28

### Story 12.1: PPN Calculation on Sales Invoice

As a Finance Admin,
I want PPN calculated automatically on sales invoices,
So that tax compliance is accurate.

**Acceptance Criteria:**

**Given** Sales invoice is created
**When** Invoice line items are added
**Then** System checks if item is PPN-taxable (based on item master data)
**And** Calculates PPN = Subtotal × 11% (for taxable items)
**And** Displays PPN amount separately on invoice

**Given** Customer is PKP (Pengusaha Kena Pajak)
**When** Invoice is finalized
**Then** PPN is included in total amount
**And** Invoice is flagged as "Requires e-Faktur"

**Given** Customer is non-PKP
**When** Invoice is finalized
**Then** PPN is still calculated but not charged to customer
**And** Invoice is NOT flagged for e-Faktur

### Story 12.2: e-Faktur Generation

As a Finance Admin,
I want to generate e-Faktur untuk sales invoices,
So that I can submit to DJP (Direktorat Jenderal Pajak).

**Acceptance Criteria:**

**Given** Sales invoice is finalized dan flagged for e-Faktur
**When** I navigate to Tax → e-Faktur Generation
**Then** I see list of invoices requiring e-Faktur

**Given** I select invoices untuk e-Faktur generation
**When** I click "Generate e-Faktur"
**Then** System validates:
  - Customer NPWP is valid (15 digits)
  - Invoice date is valid
  - PPN calculation is correct
**And** e-Faktur number is generated (sequential, format: XXX-YY.ZZZZZZZZ)
**And** e-Faktur record is created dengan status "Generated"

**Given** e-Faktur is generated
**When** Generation completes
**Then** e-Faktur PDF is created (printable)
**And** e-Faktur is linked to sales invoice
**And** Invoice status updates to "e-Faktur Generated"

### Story 12.3: e-Faktur XML Export

As a Finance Admin,
I want to export e-Faktur to XML format,
So that I can upload to DJP e-Faktur application.

**Acceptance Criteria:**

**Given** e-Faktur is generated
**When** I navigate to Tax → e-Faktur Export
**Then** I see list of e-Faktur ready untuk export

**Given** I select e-Faktur untuk export
**When** I click "Export to XML"
**Then** System generates XML file following DJP format specification
**And** XML includes:
  - Faktur header (nomor, tanggal, NPWP penjual/pembeli)
  - Detail barang/jasa (nama, harga, PPN)
  - Total DPP, PPN, total faktur
**And** XML file is downloadable

**Given** XML export completes
**When** Download is ready
**Then** e-Faktur status updates to "Exported"
**And** Export log is created (timestamp, user, file name)

### Story 12.4: Tax Reporting (SPT Masa PPN)

As a Finance Admin,
I want to generate SPT Masa PPN report,
So that I can submit monthly tax report to DJP.

**Acceptance Criteria:**

**Given** I navigate to Tax → SPT Masa PPN
**When** I select reporting period (month/year)
**Then** System calculates:
  - Total PPN Keluaran (output tax dari sales)
  - Total PPN Masukan (input tax dari purchases)
  - PPN Kurang/Lebih Bayar (output - input)

**Given** Calculation completes
**When** Report is generated
**Then** I see SPT Masa PPN summary dengan breakdown
**And** I can drill down to individual invoices
**And** I can export to Excel/PDF
**And** Report includes e-Faktur numbers untuk traceability

---

## Epic 13: Integrations & Migration

Enable WhatsApp notifications dan Accurate data migration untuk smooth onboarding.

**Goal:** System dapat send WhatsApp notifications untuk approvals/alerts, dan users dapat migrate data dari Accurate (offline/online).

**FRs Covered:** FR29, FR30

### Story 13.1: WhatsApp Notification Integration

As a System Administrator,
I want to configure WhatsApp notification integration,
So that users receive timely alerts via WhatsApp.

**Acceptance Criteria:**

**Given** I navigate to Settings → Integrations → WhatsApp
**When** I enter Wablas API credentials (API key, device ID)
**Then** System validates credentials dengan test connection
**And** Connection status is displayed (Connected/Failed)

**Given** WhatsApp is configured
**When** Notification event occurs (PO approval, leave request, payment due)
**Then** System queues notification message
**And** Background job sends message via Wablas API
**And** Delivery status is tracked (Sent/Failed)

**Given** Notification fails to send
**When** Delivery fails (API error, invalid number)
**Then** System retries up to 3 times
**And** Failure is logged
**And** Admin receives alert jika retry exhausted

### Story 13.2: WhatsApp Notification Templates

As a System Administrator,
I want to customize WhatsApp notification templates,
So that messages are clear dan professional.

**Acceptance Criteria:**

**Given** I navigate to Settings → Notifications → Templates
**When** I select notification type (PO Approval, Leave Request, etc.)
**Then** I see template editor dengan variables ({{employee_name}}, {{amount}}, etc.)
**And** I can edit message text
**And** I can preview message dengan sample data

**Given** I save template
**When** Template is updated
**Then** New notifications use updated template
**And** Template change is logged in audit trail

### Story 13.3: Accurate Data Migration (Offline)

As a Finance Admin,
I want to import data dari Accurate offline database,
So that I can migrate to AkuBook without losing historical data.

**Acceptance Criteria:**

**Given** I navigate to Settings → Data Migration → Accurate Offline
**When** I upload Accurate database backup file (.bak atau .mdb)
**Then** System validates file format
**And** Displays migration options:
  - Chart of Accounts
  - Customers & Suppliers
  - Items/Products
  - Opening Balances
  - Historical Transactions (optional)

**Given** I select data to migrate
**When** I click "Start Migration"
**Then** System extracts data dari Accurate database
**And** Maps Accurate fields to AkuBook fields
**And** Validates data integrity (required fields, data types)
**And** Shows migration progress (% complete, records processed)

**Given** Migration completes
**When** All data is imported
**Then** Migration summary is displayed:
  - Records imported (success count)
  - Records skipped (with reasons)
  - Data accuracy: 90%+ target
**And** Migration log is saved
**And** I can review imported data before finalizing

### Story 13.4: Accurate Data Migration (Online API)

As a Finance Admin,
I want to import data dari Accurate Online via API,
So that migration is seamless dan up-to-date.

**Acceptance Criteria:**

**Given** I navigate to Settings → Data Migration → Accurate Online
**When** I enter Accurate Online credentials (session ID atau API token)
**Then** System authenticates dengan Accurate Online API
**And** Retrieves available databases

**Given** I select database to migrate
**When** I choose data types to import
**Then** System fetches data via Accurate Online API
**And** Maps API response to AkuBook data structure
**And** Validates data integrity

**Given** Migration completes
**When** Data import finishes
**Then** Migration summary is displayed
**And** Data accuracy is validated (90%+ target)
**And** I can review dan finalize migration
**And** Original Accurate data remains unchanged (read-only migration)
