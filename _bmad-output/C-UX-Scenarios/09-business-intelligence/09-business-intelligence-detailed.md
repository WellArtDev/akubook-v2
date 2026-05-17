# Scenario 09: Business Intelligence & Reporting

**User:** Owner / Manager / Finance Admin  
**Priority:** HIGH (Decision making)  
**Frequency:** Daily/Weekly/Monthly  
**Success Metric:** Reports generated in <30 seconds

---

## Scenario Goal

Owner and managers access real-time dashboards and generate reports for business insights and decision making.

---

## User Context

**Who:** Owner (Pak Budi) reviewing business performance, Managers analyzing departmental metrics

**When:** Daily morning review, weekly meetings, monthly board meetings

**Why:** Monitor business health, identify trends, make data-driven decisions

**Current Pain (from Accurate):** Static reports, no real-time dashboards, manual Excel exports, slow report generation

---

## Sunshine Path (Happy Flow)

### Step 1: Executive Dashboard

**Page:** Executive Dashboard

**User Action (Owner):**
- Logs in to AkuBook
- Views executive dashboard

**System Shows:**
- Key metrics (current month):
  - Revenue: Rp 2.5B (↑ 15% vs last month)
  - Gross Profit: Rp 700M (28% margin)
  - Net Profit: Rp 300M (12% margin)
  - Cash Balance: Rp 1.2B
  - AR Aging: Rp 800M (60% current, 40% overdue)
  - AP Aging: Rp 600M (80% current, 20% overdue)
- Charts:
  - Revenue trend (last 12 months)
  - Top 10 customers by revenue
  - Top 10 products by sales
  - Expense breakdown by category

**User Input:**
- Reviews dashboard
- Clicks "Revenue Trend" chart

**System Response:**
- Drills down to revenue detail
- Shows monthly breakdown

**Next:** View detailed reports

---

### Step 2: Sales Report

**Page:** Sales Report

**User Action:**
- Clicks "Sales Report" from menu
- Selects date range (April 2026)

**System Shows:**
- Sales summary:
  - Total Sales: Rp 2.5B
  - Total Orders: 120
  - Average Order Value: Rp 20.8M
  - Top Customer: PT Toko Elektronik Jaya (Rp 500M)
  - Top Product: Speaker JBL EON615 (Rp 300M)
- Sales by:
  - Customer
  - Product
  - Salesperson
  - Branch
  - Category

**User Input:**
- Filters by salesperson: "Andi"
- Clicks "Export to Excel"

**System Response:**
- Generates Excel report
- Downloads file: Sales_Report_April_2026.xlsx

**Next:** Done (report exported)

---

### Step 3: Profit & Loss Report

**Page:** P&L Report

**User Action:**
- Clicks "P&L Report" from menu
- Selects period (April 2026)

**System Shows:**
- Income Statement:
  - **Revenue**
    - Sales Revenue: Rp 2,500,000,000
    - Other Income: Rp 50,000,000
    - **Total Revenue: Rp 2,550,000,000**
  - **Cost of Goods Sold**
    - COGS: Rp 1,800,000,000
    - **Gross Profit: Rp 750,000,000 (29.4%)**
  - **Operating Expenses**
    - Salary Expense: Rp 200,000,000
    - Rent Expense: Rp 50,000,000
    - Utilities: Rp 30,000,000
    - Marketing: Rp 70,000,000
    - Other Expenses: Rp 50,000,000
    - **Total Operating Expenses: Rp 400,000,000**
  - **Operating Income: Rp 350,000,000 (13.7%)**
  - **Other Expenses**
    - Interest Expense: Rp 20,000,000
    - Depreciation: Rp 30,000,000
    - **Total Other Expenses: Rp 50,000,000**
  - **Net Income: Rp 300,000,000 (11.8%)**

**User Input:**
- Compares with previous month
- Clicks "Export to PDF"

**System Response:**
- Generates PDF report
- Downloads file: PL_Report_April_2026.pdf

**Next:** Done (report exported)

---

### Step 4: Balance Sheet Report

**Page:** Balance Sheet Report

**User Action:**
- Clicks "Balance Sheet" from menu
- Selects date (April 30, 2026)

**System Shows:**
- Balance Sheet:
  - **Assets**
    - Current Assets:
      - Cash and Bank: Rp 1,200,000,000
      - Accounts Receivable: Rp 800,000,000
      - Inventory: Rp 1,500,000,000
      - **Total Current Assets: Rp 3,500,000,000**
    - Fixed Assets:
      - Property, Plant & Equipment: Rp 2,000,000,000
      - Less: Accumulated Depreciation: (Rp 300,000,000)
      - **Net Fixed Assets: Rp 1,700,000,000**
    - **Total Assets: Rp 5,200,000,000**
  - **Liabilities**
    - Current Liabilities:
      - Accounts Payable: Rp 600,000,000
      - Accrued Expenses: Rp 100,000,000
      - Short-term Debt: Rp 200,000,000
      - **Total Current Liabilities: Rp 900,000,000**
    - Long-term Liabilities:
      - Long-term Debt: Rp 1,200,000,000
      - **Total Liabilities: Rp 2,100,000,000**
  - **Equity**
    - Share Capital: Rp 1,000,000,000
    - Retained Earnings: Rp 2,100,000,000
    - **Total Equity: Rp 3,100,000,000**
  - **Total Liabilities & Equity: Rp 5,200,000,000**

**User Input:**
- Reviews balance sheet
- Clicks "Export to PDF"

**System Response:**
- Generates PDF report
- Downloads file: Balance_Sheet_April_2026.pdf

**Next:** Done (report exported)

---

### Step 5: Cash Flow Report

**Page:** Cash Flow Report

**User Action:**
- Clicks "Cash Flow" from menu
- Selects period (April 2026)

**System Shows:**
- Cash Flow Statement:
  - **Operating Activities**
    - Net Income: Rp 300,000,000
    - Adjustments:
      - Depreciation: Rp 30,000,000
      - Changes in AR: (Rp 100,000,000)
      - Changes in Inventory: (Rp 50,000,000)
      - Changes in AP: Rp 70,000,000
    - **Net Cash from Operating: Rp 250,000,000**
  - **Investing Activities**
    - Purchase of Equipment: (Rp 50,000,000)
    - **Net Cash from Investing: (Rp 50,000,000)**
  - **Financing Activities**
    - Loan Repayment: (Rp 100,000,000)
    - **Net Cash from Financing: (Rp 100,000,000)**
  - **Net Change in Cash: Rp 100,000,000**
  - **Cash at Beginning: Rp 1,100,000,000**
  - **Cash at End: Rp 1,200,000,000**

**User Input:**
- Reviews cash flow
- Clicks "Export to Excel"

**System Response:**
- Generates Excel report
- Downloads file: Cash_Flow_April_2026.xlsx

**Next:** Done (report exported)

---

### Step 6: Custom Report Builder

**Page:** Custom Report Builder

**User Action:**
- Clicks "Custom Report" from menu
- Selects report type: "Sales by Customer"

**System Shows:**
- Report builder:
  - Data source: Sales Orders
  - Columns: Customer, Total Sales, Order Count
  - Filters: Date range, Branch, Salesperson
  - Grouping: By Customer
  - Sorting: By Total Sales (descending)

**User Input:**
- Configures report:
  - Date range: April 2026
  - Branch: All
  - Salesperson: All
- Clicks "Generate Report"

**System Response:**
- Generates custom report
- Shows results in table
- Provides export options (Excel/PDF/CSV)

**Next:** Export or save report template

---

## Pages/Screens Needed

1. **Executive Dashboard** - Key metrics and charts
2. **Sales Report** - Sales analysis by various dimensions
3. **P&L Report** - Income statement
4. **Balance Sheet Report** - Financial position
5. **Cash Flow Report** - Cash flow statement
6. **Custom Report Builder** - Build custom reports

---

## Data Models Required

### Tables

**report_templates**
- id, company_id, report_name, report_type
- configuration (JSON), created_by, is_public
- created_at, updated_at

**report_schedules**
- id, company_id, report_template_id, frequency
- recipients (JSON), next_run_at, last_run_at
- created_at, updated_at

**dashboard_widgets**
- id, company_id, user_id, widget_type
- configuration (JSON), position, size
- created_at, updated_at

---

## Acceptance Criteria

**Functional:**
- ✅ Real-time executive dashboard
- ✅ Standard financial reports (P&L, Balance Sheet, Cash Flow)
- ✅ Sales and operational reports
- ✅ Custom report builder
- ✅ Export to Excel/PDF/CSV
- ✅ Scheduled reports (email delivery)

**Performance:**
- ✅ Dashboard loads in <2 seconds
- ✅ Reports generate in <30 seconds
- ✅ Export completes in <10 seconds

**Security:**
- ✅ Role-based report access
- ✅ Data filtering by user permissions
- ✅ Audit trail for report access

**UX:**
- ✅ Interactive charts (drill-down)
- ✅ Date range selector
- ✅ One-click export
- ✅ Mobile-friendly dashboards

---

## Design Notes

**Tone:**
- Professional, insightful (executive audience)
- Clear visualizations (charts, graphs)
- Actionable insights (trends, alerts)

**UX Principles:**
- Real-time data (no delays)
- Interactive charts (drill-down)
- One-click export (fast access)
- Mobile-friendly (on-the-go access)

**Mobile Consideration:**
- Dashboards mobile-optimized
- Reports desktop-optimized (complex tables)

---

## Related Scenarios

- **03: Monthly Close** - Financial statements generated during close
- **02: Sales Order Flow** - Sales data source
- **06: Purchase Order Flow** - Purchase data source

---

## Accurate Feature Parity

**Accurate Reporting includes:**
- Standard financial reports
- Sales reports
- Inventory reports

**AkuBook Enhancement:**
- Real-time dashboards (Accurate static)
- Interactive charts (Accurate limited)
- Custom report builder (Accurate doesn't have this)
- Scheduled reports (Accurate manual)

---

**Scenario Status:** ✅ Ready for Implementation  
**Next:** Design wireframes for 6 pages in this flow
