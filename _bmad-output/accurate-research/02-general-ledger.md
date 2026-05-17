# 2. General Ledger (Buku Besar)

## Overview

Buku Besar module = central hub for all financial transactions in Accurate Online. Records every transaction using double-entry bookkeeping, generates financial reports (balance sheet, income statement, cash flow), and maintains audit trail.

**Core Function**: Centralized transaction recording → accurate financial reports ready for analysis anytime.

**Priority**: HIGH (accounting core)  
**Complexity**: High - multi-currency, period closing, validation rules

---

## Features List (10 Total)

### 1. Akun Perkiraan (Chart of Accounts)

**What**: Master list of all accounts used to record transactions (cash, bank, receivables, payables, inventory, expenses, revenue, equity).

**Key Capabilities**:
- Create/edit/delete/deactivate accounts
- 16 account types: Cash/Bank, Account Receivable, Account Payable, Inventory, Fixed Asset, Accumulated Depreciation, Other Current Asset, Other Asset, Account Payable, Other Current Liability, Long-term Liability, Equity, Revenue, COGS, Expense, Other Income, Other Expense
- Sub-account hierarchy (parent-child structure)
- Multi-currency support (for Cash/Bank, AR, AP types only)
- Opening balance entry (except AR, AP, inventory, fixed assets - require separate setup)
- Account numbering system (max 20 chars, alphanumeric)
- Suspended status (hide from lists/transactions)
- Fiscal flag (for tax reporting - applies to P&L accounts)

**Validation Rules**:
- Cannot delete if: used in transactions, set as default account, or is parent account
- Debit/Credit must balance before saving
- Account number must be unique

**Automation**: 
- Auto-creates default accounts when adding new currency
- Auto-groups accounts by type in financial reports

---

### 2. Jurnal Umum (General Journal)

**What**: Manual journal entry form for recording transactions not covered by specialized modules (sales, purchases, cash/bank).

**Key Capabilities**:
- Multi-line debit/credit entry
- Multi-currency support (per account)
- Department/project allocation
- Recurring journal templates
- Import from Excel/CSV
- Bulk delete
- Auto-balancing validation

**Validation Rules**:
- Total debit = total credit (enforced before save)
- Date cannot be before fiscal start date
- Cannot post to suspended accounts
- Multi-currency: rate required for foreign currency accounts

**Automation**:
- Auto-converts foreign currency amounts to base currency using specified rate
- Auto-posts to general ledger on save

**Audit Trail**: All changes logged in Log Aktivitas Jurnal

---

### 3. Histori Akun (Account History)

**What**: Transaction drill-down view for any account, showing all journal entries affecting that account.

**Key Capabilities**:
- Filter by date range
- View source transaction (invoice, payment, journal)
- Running balance display
- Export to Excel

**Use Cases**:
- Reconciliation
- Transaction verification
- Balance investigation

---

### 4. Log Aktivitas Jurnal (Journal Activity Log)

**What**: Audit trail for all journal changes (create, edit, delete).

**Key Capabilities**:
- Who changed what, when
- Before/after values
- Search by transaction number
- Filter by user, date, action type

**Audit Trail**: Immutable log - cannot be edited or deleted by users.

---

### 5. Pencatatan Beban (Expense Recording)

**What**: Accrual-based expense recording (prepaid expenses, accrued expenses).

**Key Capabilities**:
- Prepaid expense amortization
- Accrued expense recognition
- Auto-generate monthly journal entries
- Multi-period allocation

**Automation**: 
- Auto-posts monthly amortization journals
- Auto-calculates remaining balance

---

### 6. Pencatatan Gaji (Payroll Recording)

**What**: Payroll journal entry with PPh 21 (income tax) calculation.

**Key Capabilities**:
- Employee salary components (base, allowances, deductions)
- PPh 21 auto-calculation
- BPJS integration
- Payroll journal auto-generation

**Validation Rules**:
- PPh 21 calculation follows Indonesian tax law
- Salary components must balance

**Automation**:
- Auto-calculates tax withholding
- Auto-posts to payroll expense and tax payable accounts

---

### 7. Anggaran (Budget)

**What**: Budget planning and tracking by account, department, project.

**Key Capabilities**:
- Annual/monthly budget entry
- Budget vs actual comparison
- Budget transfer between accounts
- Multi-level approval workflow

**Validation Rules**:
- Budget period must be within fiscal year
- Cannot delete budget if used in monitoring

---

### 8. Monitor Anggaran (Budget Monitoring)

**What**: Real-time budget vs actual variance analysis.

**Key Capabilities**:
- Variance % calculation
- Drill-down to transactions
- Alert for over-budget accounts
- Export to Excel

---

### 9. Transfer Anggaran (Budget Transfer)

**What**: Move budget allocation between accounts mid-period.

**Key Capabilities**:
- Transfer between accounts
- Approval workflow
- Audit trail

**Validation Rules**:
- Cannot transfer more than available budget
- Requires approval if exceeds threshold

---

### 10. Pinjaman Karyawan (Employee Loan)

**What**: Track employee loans (advances, salary loans) with installment deduction.

**Key Capabilities**:
- Loan disbursement recording
- Installment schedule
- Auto-deduct from payroll
- Loan balance tracking

**Automation**:
- Auto-generates installment deduction journals
- Auto-updates loan balance

---

## Multi-Currency Implementation

**Core Concept**: 
- **Base Currency**: Default currency (IDR for Indonesia)
- **Prime Currency**: Original transaction currency
- **Historical Rate**: Rate used in original transaction
- **Period-End Rate**: Rate used in month-end revaluation

**Supported Account Types**: Cash/Bank, Account Receivable, Account Payable only.

**Automatic Processes**:
1. **Transaction Recording**: System stores both prime currency amount and base currency equivalent using transaction rate
2. **Realized Gain/Loss**: Auto-calculated when payment currency ≠ invoice currency
3. **Unrealized Gain/Loss**: Auto-calculated during Proses Akhir Bulan (month-end closing) for outstanding AR/AP

**Default Accounts per Currency**:
- Sales Discount Account
- Purchase Discount Account
- Realized Gain/Loss on Foreign Exchange
- Unrealized Gain/Loss on Foreign Exchange

**Fiscal vs Commercial Rate** (Indonesia-specific):
- **Commercial Rate**: Market rate for AR/AP recording
- **Fiscal Rate**: Tax office rate for VAT calculation
- System separates VAT payable (in IDR) from foreign currency receivable

**Example**: 
- Sale: USD 100 + 10% VAT
- Commercial rate: 9,000
- Fiscal rate: 8,000
- Journal:
  - DR: AR USD 100 @ 9,000 = Rp 900,000
  - CR: Sales USD 100 @ 9,000 = Rp 900,000
  - DR: AR VAT (IDR) = Rp 80,000 (USD 100 × 8,000 × 10%)
  - CR: VAT Payable (IDR) = Rp 80,000

---

## Period Closing (Proses Akhir Bulan)

**Purpose**: Auto-generate month-end adjustment journals for:
1. Fixed asset depreciation
2. Foreign exchange revaluation (unrealized gain/loss)
3. Manufacturing cost variance (if using manufacturing add-on)

**Process**:
1. Navigate: Perusahaan → Proses Akhir Bulan
2. Select month/year
3. Enter period-end exchange rates for all foreign currencies
4. Save → system auto-generates journals

**Validation**: 
- Warning if not run on last day of month (can override)
- Cannot close period if prior period not closed

**Audit Trail**: All generated journals viewable in Jurnal Umum with source = "Proses Akhir Bulan"

---

## Priority for AkuBook MVP

### Phase 1 (Must Have):
1. Akun Perkiraan (COA management)
2. Jurnal Umum (manual journal entry)
3. Histori Akun (transaction drill-down)
4. Multi-currency support (base + foreign)
5. Auto-posting from other modules
6. Validation rules (debit=credit, date checks)

### Phase 2 (Enhanced):
7. Log Aktivitas Jurnal (audit trail)
8. Proses Akhir Bulan (period closing)
9. Preferensi Akun (default account setup)
10. Import/Export

### Phase 3 (Advanced):
11. Anggaran (budget planning)
12. Monitor Anggaran (budget tracking)
13. Transfer Anggaran (budget transfer)
14. Pencatatan Beban (accrual accounting)
15. Pencatatan Gaji (payroll integration)
16. Pinjaman Karyawan (employee loans)

---

**Source**: Accurate Online Help Documentation (https://help.accurate.id/product/buku-besar/)  
**Last Updated**: May 2026  
**Compliance**: Indonesian accounting standards, multi-currency, tax integration
