# Product Concept: AkuBook

**Date:** 2026-05-12

---

## Core Structural Idea

**"Modular Business OS dengan Industry-Aware Configuration"**

AkuBook bukan "accounting software dengan add-ons" — tapi **business operating system** yang semua module-nya udah ada, tinggal activate sesuai kebutuhan. Seperti smartphone: semua capability udah built-in, user tinggal pilih app mana yang mau dipakai.

---

## Implementation Principle

### 1. Industry-First Onboarding

**Setup Flow:**
- User creates company profile
- Selects industry type (Distributor, Retail, Manufacturing, Service, etc.)
- System auto-enables relevant modules
- Dashboard auto-configures untuk show metrics yang relevan

**Why Industry-First:**
- Eliminates decision paralysis ("which modules do I need?")
- Ensures best-practice configuration out of the box
- User can still customize after initial setup

### 2. Module-Centric Architecture

**Design Principles:**
- Every business function = independent module
- Modules are equal citizens (not "core + add-ons")
- Modules communicate via unified data layer
- Enable/disable without breaking dependencies

**Module Categories:**
- **Core Business:** Accounting, Inventory, Sales, Purchasing
- **Operations:** Warehouse, Manufacturing, Projects
- **People:** HRM, Attendance, Payroll
- **Customer:** CRM, POS, E-commerce
- **Reporting:** Financial Reports, Operational Reports, Custom Reports

**Module Independence:**
- Each module can function standalone
- Shared data (customers, products, transactions) via unified model
- Cross-module workflows optional but powerful

### 3. Role-Based Experience

**Personalization:**
- Login → see only modules you have access to
- Dashboard personalized per role
- Navigation menu filtered by permissions
- No clutter from irrelevant features

**Role Examples:**
- **Finance Admin:** Accounting, Reports, Dashboard (finance metrics only)
- **HRD:** HRM, Attendance, Payroll, Announcements
- **Sales:** Sales Orders, Customers, Inventory (view), Invoicing
- **Staff:** Attendance, Leave, Payslip, Announcements
- **Owner:** All modules, all dashboards

---

## Rationale

### Why This Structural Approach?

**Problem with Traditional ERP/Accounting Software:**
- **Accurate/Jurnal:** Accounting-first, add features incrementally
  - Always feels like "accounting software + extras"
  - Features feel bolted-on, not integrated
  - User must learn "accounting way" first
- **Modular ERP (Odoo, etc.):** Pay-per-module, complex configuration
  - Decision paralysis: "which modules do I need?"
  - Cost scales with features (discourages exploration)
  - Integration between modules often clunky

**AkuBook's Approach:**
- **Business-first, not accounting-first**
  - All functions are equal citizens
  - Feels like "complete business system"
  - User starts from their business context (industry), not software category
- **All modules included, user chooses what to use**
  - No decision paralysis (everything is available)
  - No cost penalty for enabling more features
  - Encourages exploration and adoption
- **Industry-aware configuration**
  - Best practices built-in per industry
  - Faster time-to-value
  - Less configuration burden

### What This Concept Enables

**Flexibility:**
- Company evolves (retail → distributor)? Just enable different modules
- No migration, no new software, no data loss
- Adapt to business changes without vendor lock-in

**Simplicity:**
- One login, one interface, one data model
- No context switching between tools
- Unified reporting across all business functions

**Scalability:**
- Start simple (few modules), grow complex (more modules)
- Add users without cost penalty
- Multi-company support (holding companies, accounting firms)

**Ownership:**
- One-time payment for all modules
- On-premise deployment option
- No vendor dependency for core operations

---

## Concrete Example

### Distributor Onboarding Journey

**Step 1: Company Setup**
```
Welcome to AkuBook!
Let's set up your company.

Company Name: [PT Distributor Sound System]
Industry Type: [Distributor ▼]
  - Distributor ✓
  - Retail
  - Manufacturing
  - Service
  - Other

Number of Employees: [100+]
Number of Warehouses: [3]
```

**Step 2: Auto-Configuration**
```
Based on your industry, we've enabled these modules:

✓ Accounting (Full GL, AP/AR, Bank Reconciliation)
✓ Multi-Warehouse Inventory
✓ Purchasing (PO, Supplier Management)
✓ Sales (SO, Customer Management, Invoicing)
✓ Surat Jalan (Delivery Notes)
✓ Attendance (Geo, Face Recognition, ZKTeco)
✓ HRM (Payroll, Leave, Overtime)

You can enable/disable modules anytime in Settings.
```

**Step 3: Dashboard (Owner View)**
```
Dashboard - PT Distributor Sound System

[Financial Health]
- Cash Flow: Rp 150M (↑ 12% vs last month)
- Outstanding AR: Rp 45M
- Outstanding AP: Rp 30M

[Operations]
- Pending POs: 12
- Pending SOs: 8
- Low Stock Items: 5 (across 3 warehouses)

[People]
- Attendance Today: 98/102 (96%)
- Pending Leave Requests: 3
- Overtime This Month: 120 hours

[Quick Actions]
→ Create Sales Order
→ Create Purchase Order
→ View Inventory
→ Generate Report
```

**Step 4: Role-Based Views**

**Finance Admin Login:**
```
Modules Visible:
- Accounting
- Reports
- Dashboard (finance metrics only)

Dashboard:
- Cash Flow
- AR/AP Aging
- Bank Balances
- Monthly P&L Summary
```

**Sales Staff Login:**
```
Modules Visible:
- Sales Orders
- Customers
- Inventory (view-only)
- Invoicing
- Commission Tracking

Dashboard:
- My Sales This Month
- Pending Orders
- Top Customers
- Commission Summary
```

**General Staff Login:**
```
Modules Visible:
- Attendance
- Leave Requests
- Payslip
- Announcements

Dashboard:
- Clock In/Out
- My Attendance This Month
- Leave Balance
- Latest Announcements
```

### Evolution Example: Adding Retail

**Scenario:** Distributor opens retail outlet

**Step 1: Enable POS Module**
```
Settings → Modules → Enable "Point of Sale"

POS module enabled!
New features available:
- Retail transactions
- Customer loyalty
- Promotions & discounts
- Retail inventory (separate from wholesale)
```

**Step 2: Dashboard Updates**
```
Dashboard now shows:
[Wholesale Operations]
- Pending POs: 12
- Pending SOs: 8

[Retail Operations]  ← NEW
- Today's Sales: Rp 5M
- Transactions: 45
- Top Products: [list]

[Combined Inventory]
- Wholesale Stock: [by warehouse]
- Retail Stock: [by outlet]
```

**No migration, no new software, no data loss.**

---

## Features That Stem From This Concept

### Industry Templates

**Pre-configured module sets per industry:**
- **Distributor:** Accounting, Multi-warehouse, Purchasing, Sales, Surat Jalan, Attendance, HRM
- **Retail:** Accounting, POS, Inventory, CRM, Loyalty, Attendance, HRM
- **Manufacturing:** Accounting, BOM, Production, Inventory, Purchasing, Sales, Attendance, HRM
- **Service:** Accounting, Projects, Time Tracking, Invoicing, CRM, Attendance, HRM

**Customizable:** User can modify template after initial setup

### Unified Data Model

**Shared entities across modules:**
- **Customers:** Used by Sales, CRM, POS, Accounting
- **Products:** Used by Inventory, Sales, Purchasing, Manufacturing, POS
- **Transactions:** Flow through Sales → Inventory → Accounting automatically
- **Employees:** Used by HRM, Attendance, Payroll, Accounting (expense claims)

**Benefits:**
- No duplicate data entry
- Consistent data across modules
- Cross-module reporting possible

### Cross-Module Workflows

**Example: Purchase Order → Receiving → Accounting**
```
1. Purchasing creates PO
   → Status: Pending

2. Warehouse receives goods
   → Inventory updated
   → PO status: Received

3. Finance receives invoice
   → AP created automatically
   → Links to PO and receiving

4. Payment made
   → AP closed
   → Bank balance updated
   → Cash flow report updated

All automatic, no manual data entry.
```

### Personalized Dashboards

**Dashboard adapts to:**
- User role (what they can see)
- Enabled modules (what's relevant)
- User preferences (what they care about)

**Example: Finance Admin Dashboard**
```
[Financial Metrics]
- Cash position
- AR/AP aging
- Monthly P&L
- Budget vs actual

[Alerts]
- Overdue invoices
- Low cash warning
- Unusual transactions

[Quick Actions]
- Record payment
- Generate report
- Reconcile bank
```

### Module Marketplace (Future)

**Extensibility:**
- Third-party modules (e.g., E-commerce integration, Advanced Analytics)
- Industry-specific modules (e.g., Pharmacy compliance, Restaurant management)
- Custom modules (built by partners or customers)

**Architecture supports:**
- Module installation without core system changes
- Module updates independent of core
- Module marketplace with ratings and reviews

---

## Design Implications

### Navigation

**Module-based navigation:**
```
[Sidebar]
📊 Dashboard
💰 Accounting
📦 Inventory
🛒 Sales
🛍️ Purchasing
👥 HRM
⏰ Attendance
📄 Reports
⚙️ Settings

(Only shows modules user has access to)
```

**Contextual navigation:**
- Within module: sub-navigation for module features
- Breadcrumbs show: Module → Feature → Detail
- Quick switcher: Jump between modules (Cmd+K)

### Information Architecture

**Hierarchy:**
```
Company
├── Modules (enabled/disabled)
│   ├── Accounting
│   │   ├── Chart of Accounts
│   │   ├── Transactions
│   │   ├── Bank Reconciliation
│   │   └── Reports
│   ├── Inventory
│   │   ├── Products
│   │   ├── Warehouses
│   │   ├── Stock Movements
│   │   └── Reports
│   └── ...
├── Users & Roles
│   ├── Users
│   ├── Roles
│   └── Permissions (per module)
└── Settings
    ├── Company Profile
    ├── Module Configuration
    └── System Settings
```

### User Experience

**First-time user:**
1. Industry selection → Auto-configuration
2. Guided tour of enabled modules
3. Quick-start tasks per role
4. Help resources contextual to module

**Daily user:**
1. Login → Personalized dashboard
2. See only relevant modules
3. Quick actions for common tasks
4. Notifications for pending items

**Admin user:**
1. Full system visibility
2. Module management
3. User/role management
4. System configuration

---

## Success Metrics

### Concept Validation

**Industry-aware configuration:**
- % of users who keep default module configuration
- Time to first productive use (by industry)
- Module adoption rate (which modules get enabled)

**Module-centric architecture:**
- Average modules enabled per company
- Module enable/disable frequency
- Cross-module workflow usage

**Role-based experience:**
- User satisfaction by role
- Feature discovery rate
- Support tickets by role (lower = better UX)

### Business Impact

**Flexibility:**
- % of companies that change industry configuration
- % of companies that enable additional modules over time
- Multi-company adoption rate

**Simplicity:**
- Time to complete common tasks (vs competitors)
- User onboarding time (by role)
- Support ticket volume (lower = simpler)

**Ownership:**
- Customer retention (no subscription churn)
- Feature adoption (no cost barrier)
- On-premise deployment rate
