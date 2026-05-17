# Scenario 03: Monthly Close Process

**User:** Finance Admin / Controller  
**Priority:** HIGH (Core accounting)  
**Frequency:** Monthly  
**Success Metric:** Close completed in <8 hours (vs 3 days manual)

---

## Scenario Goal

Finance Admin executes month-end close process with automated reconciliation, adjustment entries, and financial statement generation.

---

## User Context

**Who:** Sari (Finance Admin) or Controller responsible for monthly financial close

**When:** Last day of month or first 3 days of next month

**Why:** Generate accurate financial statements, reconcile accounts, prepare for management review

**Current Pain (from Accurate):** Manual reconciliation, scattered adjustment entries, unclear close checklist, takes 3+ days

---

## Sunshine Path (Happy Flow)

### Step 1: Pre-Close Checklist

**Page:** Monthly Close Dashboard

**User Action:**
- Opens Monthly Close module
- Sees current month status

**System Shows:**
- Close checklist with status indicators:
  - ✅ All invoices posted (120/120)
  - ✅ All payments recorded (95/95)
  - ⚠️ Bank reconciliation pending (2/3 accounts)
  - ✅ Inventory counted (3/3 warehouses)
  - ⚠️ Depreciation not run
  - ❌ Accruals not recorded

**User Input:**
- Reviews checklist
- Clicks items to complete pending tasks

**System Response:**
- Shows completion percentage (67%)
- Highlights blocking items in red

**Next:** Complete pending items or proceed with warnings

---

### Step 2: Bank Reconciliation

**Page:** Bank Reconciliation

**User Action:**
- Clicks "Bank Reconciliation" from checklist
- Selects bank account (BCA - Operating)

**System Shows:**
- Bank statement import option
- Unreconciled transactions list
- Suggested matches (AI-powered)

**User Input:**
- Uploads bank statement (CSV/PDF)
- Reviews suggested matches
- Confirms matches or creates adjustment entries

**System Response:**
- Auto-matches 90% of transactions
- Creates reconciliation report
- Updates checklist: ✅ Bank reconciliation complete

**Next:** Return to checklist

---

### Step 3: Run Depreciation

**Page:** Fixed Assets Depreciation

**User Action:**
- Clicks "Run Depreciation" from checklist

**System Shows:**
- List of depreciable assets
- Depreciation method and rates
- Preview of journal entries to be created

**User Input:**
- Reviews depreciation schedule
- Clicks "Run Depreciation"

**System Response:**
- Creates depreciation journal entries:
  - DR: Depreciation Expense
  - CR: Accumulated Depreciation
- Posts entries automatically
- Updates checklist: ✅ Depreciation complete

**Next:** Return to checklist

---

### Step 4: Record Accruals

**Page:** Accrual Entries

**User Action:**
- Clicks "Record Accruals" from checklist

**System Shows:**
- Recurring accrual templates (rent, utilities, salaries)
- Manual accrual entry form

**User Input:**
- Reviews recurring accruals
- Confirms amounts or adjusts
- Adds manual accruals if needed

**System Response:**
- Creates accrual journal entries:
  - DR: Expense Account
  - CR: Accrued Liabilities
- Posts entries automatically
- Updates checklist: ✅ Accruals complete

**Next:** Return to checklist

---

### Step 5: Review Trial Balance

**Page:** Trial Balance Report

**User Action:**
- Clicks "Review Trial Balance" from checklist

**System Shows:**
- Trial balance with all accounts
- Debit/Credit totals (must balance)
- Comparison with previous month
- Variance analysis

**User Input:**
- Reviews balances
- Investigates unusual variances
- Creates adjustment entries if needed

**System Response:**
- Shows balanced trial balance
- Highlights accounts with >20% variance
- Updates checklist: ✅ Trial balance reviewed

**Next:** Proceed to close

---

### Step 6: Execute Month Close

**Page:** Close Confirmation

**User Action:**
- Clicks "Close Month" button

**System Shows:**
- Final confirmation dialog:
  - "Close April 2026?"
  - "This will lock all transactions for April"
  - "Financial statements will be generated"
  - Checklist summary (100% complete)

**User Input:**
- Reviews summary
- Clicks "Confirm Close"

**System Response:**
- Locks period (no more edits to April transactions)
- Generates financial statements:
  - Income Statement
  - Balance Sheet
  - Cash Flow Statement
- Creates closing entries (transfer P&L to Retained Earnings)
- Sends notification to management
- Shows success message: "April 2026 closed successfully"

**Next:** View financial statements

---

### Step 7: Review Financial Statements

**Page:** Financial Statements

**User Action:**
- Views generated statements

**System Shows:**
- Income Statement (April 2026)
  - Revenue: Rp 2.5B
  - COGS: Rp 1.8B
  - Gross Profit: Rp 700M (28%)
  - Operating Expenses: Rp 400M
  - Net Income: Rp 300M (12%)
- Balance Sheet (as of April 30, 2026)
  - Assets: Rp 5.2B
  - Liabilities: Rp 2.1B
  - Equity: Rp 3.1B
- Cash Flow Statement
  - Operating: +Rp 250M
  - Investing: -Rp 50M
  - Financing: -Rp 100M
  - Net Change: +Rp 100M

**User Input:**
- Reviews statements
- Exports to PDF/Excel
- Shares with management

**System Response:**
- Generates PDF reports
- Sends email to management with attachments
- Archives statements in document library

**Next:** Done (month closed)

---

## Pages/Screens Needed

1. **Monthly Close Dashboard** - Checklist and progress tracker
2. **Bank Reconciliation** - Import, match, reconcile
3. **Fixed Assets Depreciation** - Run depreciation, preview entries
4. **Accrual Entries** - Recurring and manual accruals
5. **Trial Balance Report** - Review balances, variance analysis
6. **Close Confirmation** - Final review and lock period
7. **Financial Statements** - Income Statement, Balance Sheet, Cash Flow

---

## Data Models Required

### Tables

**monthly_closes**
- id, company_id, period (YYYY-MM), status (open/in_progress/closed)
- closed_by, closed_at, checklist_data (JSON)
- created_at, updated_at

**bank_reconciliations**
- id, company_id, bank_account_id, period, statement_date
- statement_balance, book_balance, difference
- reconciled_by, reconciled_at, status
- created_at, updated_at

**bank_reconciliation_items**
- id, reconciliation_id, transaction_id, statement_line_id
- matched (boolean), adjustment_entry_id
- created_at, updated_at

**depreciation_schedules**
- id, fixed_asset_id, period, depreciation_amount
- accumulated_depreciation, book_value, journal_entry_id
- created_at, updated_at

**accrual_templates**
- id, company_id, name, account_id, amount, frequency
- is_active, created_at, updated_at

**accrual_entries**
- id, company_id, template_id, period, amount
- journal_entry_id, created_at, updated_at

**financial_statements**
- id, company_id, period, statement_type (income/balance/cashflow)
- data (JSON), generated_by, generated_at
- created_at, updated_at

---

## Auto-Posting Rules

**Depreciation Entry:**
- DR: Depreciation Expense (5xxx)
- CR: Accumulated Depreciation (1xxx)
- **Trigger:** When "Run Depreciation" executed

**Accrual Entry:**
- DR: Expense Account (5xxx/6xxx)
- CR: Accrued Liabilities (2xxx)
- **Trigger:** When accrual confirmed

**Closing Entry:**
- DR: Revenue Accounts (4xxx)
- DR: Other Income (7xxx)
- CR: Income Summary
- DR: Income Summary
- CR: Expense Accounts (5xxx/6xxx)
- DR: Income Summary
- CR: Retained Earnings (3xxx)
- **Trigger:** When month closed

---

## Acceptance Criteria

**Functional:**
- ✅ Checklist shows all required close tasks
- ✅ Bank reconciliation auto-matches 90%+ transactions
- ✅ Depreciation runs for all active assets
- ✅ Accruals created from templates
- ✅ Trial balance balances (DR = CR)
- ✅ Period locks after close (no edits)
- ✅ Financial statements generated automatically
- ✅ Closing entries posted to GL

**Performance:**
- ✅ Close process completes in <8 hours
- ✅ Bank reconciliation processes 1000+ transactions in <2 minutes
- ✅ Financial statements generate in <30 seconds

**Security:**
- ✅ Only authorized users can close periods
- ✅ Audit trail for all close activities
- ✅ Cannot reopen closed period without approval

**UX:**
- ✅ Clear progress indicator (% complete)
- ✅ Blocking items highlighted
- ✅ One-click actions where possible
- ✅ Confirmation before irreversible actions

---

## Design Notes

**Tone:**
- Professional, confident (critical process)
- Clear status indicators (green/yellow/red)
- Helpful guidance for first-time users
- "You're almost done" encouragement

**UX Principles:**
- Checklist-driven (clear path to completion)
- Auto-save progress (can pause and resume)
- Smart defaults (minimize manual input)
- Preview before commit (show impact)
- Celebrate completion (positive reinforcement)

**Mobile Consideration:**
- Close process desktop-only (complex review)
- Mobile: View-only access to statements

---

## Related Scenarios

- **02: Sales Order Flow** - Transactions must be posted before close
- **11: Bank Reconciliation** - Part of close process
- **16: Manual Journal Entry** - Adjustment entries during close
- **17: Aging Reports** - Review before close

---

## Accurate Feature Parity

**Accurate Monthly Close includes:**
- Period locking
- Bank reconciliation
- Depreciation calculation
- Financial statement generation

**AkuBook Enhancement:**
- Checklist-driven close (Accurate doesn't have this)
- AI-powered bank reconciliation matching
- One-click depreciation run
- Automated closing entries
- Real-time progress tracking

---

**Scenario Status:** ✅ Ready for Implementation  
**Next:** Design wireframes for 7 pages in this flow
