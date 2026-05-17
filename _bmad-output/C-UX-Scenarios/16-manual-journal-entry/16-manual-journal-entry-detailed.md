# Scenario 16: Manual Journal Entry

**User:** Finance Admin / Accountant  
**Priority:** HIGH (Accounting flexibility)  
**Frequency:** Weekly  
**Success Metric:** Entry posted in <5 minutes

---

## Scenario Goal

Finance Admin creates manual journal entries for adjustments, accruals, and other transactions not covered by automated posting.

---

## User Context

**Who:** Sari (Finance Admin) or Accountant creating manual entries

**When:** Month-end adjustments, corrections, accruals, reclassifications

**Why:** Record transactions not captured by automated flows, correct errors, make adjustments

**Current Pain (from Accurate):** Complex entry form, no validation, easy to make errors, no templates

---

## Sunshine Path (Happy Flow)

### Step 1: Create Manual Journal Entry

**Page:** Journal Entry Form

**User Action:**
- Opens Journal Entry module
- Clicks "New Manual Entry"

**System Shows:**
- Journal entry form:
  - Entry Date: 2026-05-13
  - Entry Type: Manual / Adjustment / Accrual / Reclassification
  - Reference: (optional)
  - Description: (empty)
  - Lines: (empty)

**User Input:**
- Selects entry type: "Accrual"
- Enters description: "Accrued rent expense for May 2026"
- Adds lines:
  - DR: Rent Expense (5-2100): Rp 50,000,000
  - CR: Accrued Expenses (2-3100): Rp 50,000,000
- Clicks "Save Draft"

**System Response:**
- Validates entry:
  - ✅ Debit = Credit (balanced)
  - ✅ Accounts exist
  - ✅ Amounts > 0
- Creates journal entry: JE-2026-05-001
- Status: "Draft"
- Shows "Entry Saved" status

**Next:** Review and post

---

### Step 2: Review Journal Entry

**Page:** Journal Entry Review

**User Action:**
- Reviews entry details

**System Shows:**
- Entry summary:
  - Entry Number: JE-2026-05-001
  - Entry Date: 2026-05-13
  - Entry Type: Accrual
  - Description: Accrued rent expense for May 2026
  - Lines:
    - DR: Rent Expense (5-2100): Rp 50,000,000
    - CR: Accrued Expenses (2-3100): Rp 50,000,000
  - Total Debit: Rp 50,000,000
  - Total Credit: Rp 50,000,000
  - Balance: ✓ Balanced

**User Input:**
- Reviews entry
- Clicks "Post Entry"

**System Response:**
- Posts entry to general ledger
- Updates account balances:
  - Rent Expense: +Rp 50,000,000
  - Accrued Expenses: +Rp 50,000,000
- Updates entry status: "Posted"
- Shows "Entry Posted" status

**Next:** Done (entry posted)

---

### Step 3: Use Journal Entry Template

**Page:** Journal Entry Templates

**User Action:**
- Clicks "Use Template"
- Selects template: "Monthly Rent Accrual"

**System Shows:**
- Template details:
  - Template Name: Monthly Rent Accrual
  - Description: Accrued rent expense
  - Lines:
    - DR: Rent Expense (5-2100): [AMOUNT]
    - CR: Accrued Expenses (2-3100): [AMOUNT]

**User Input:**
- Enters amount: Rp 50,000,000
- Clicks "Create Entry"

**System Response:**
- Creates journal entry from template
- Fills in accounts and amounts
- Status: "Draft"
- Shows "Entry Created from Template" status

**Next:** Review and post

---

### Step 4: Reverse Journal Entry

**Page:** Journal Entry Reversal

**User Action:**
- Opens posted entry: JE-2026-05-001
- Clicks "Reverse Entry"

**System Shows:**
- Reversal confirmation:
  - Original Entry: JE-2026-05-001
  - Reversal Date: 2026-06-01 (next month)
  - Reversal Entry: JE-2026-06-001 (auto-generated)
  - Lines (reversed):
    - DR: Accrued Expenses (2-3100): Rp 50,000,000
    - CR: Rent Expense (5-2100): Rp 50,000,000

**User Input:**
- Confirms reversal date
- Clicks "Create Reversal"

**System Response:**
- Creates reversal entry: JE-2026-06-001
- Links to original entry
- Status: "Draft" (will post on reversal date)
- Shows "Reversal Entry Created" status

**Next:** Done (reversal scheduled)

---

### Step 5: Bulk Journal Entry Import

**Page:** Journal Entry Import

**User Action:**
- Clicks "Import Entries"
- Downloads template (Excel)

**System Shows:**
- Import template with columns:
  - Entry Date, Entry Type, Description, Account Code, Debit, Credit

**User Input:**
- Fills template with 50 entries
- Uploads file (Excel/CSV)

**System Response:**
- Validates data:
  - ✅ 45 entries valid
  - ⚠️ 3 entries unbalanced
  - ❌ 2 entries invalid account codes
- Shows validation report

**User Input:**
- Fixes errors in template
- Re-uploads file

**System Response:**
- Imports 50 entries successfully
- Creates entries in "Draft" status
- Shows "Import Complete" status

**Next:** Review and post entries

---

### Step 6: Audit Trail

**Page:** Journal Entry Audit Trail

**User Action:**
- Opens posted entry: JE-2026-05-001
- Clicks "View Audit Trail"

**System Shows:**
- Audit trail:
  - Created by: Sari Wijaya (Finance Admin)
  - Created at: 2026-05-13 10:30
  - Posted by: Sari Wijaya (Finance Admin)
  - Posted at: 2026-05-13 10:35
  - Reviewed by: (none)
  - Reversed by: (none)
  - Changes: (none)

**User Input:**
- Reviews audit trail

**System Response:**
- Shows complete history

**Next:** Done (audit trail reviewed)

---

## Pages/Screens Needed

1. **Journal Entry Form** - Create manual entry
2. **Journal Entry Review** - Review and post
3. **Journal Entry Templates** - Use templates
4. **Journal Entry Reversal** - Reverse entries
5. **Journal Entry Import** - Bulk import
6. **Journal Entry Audit Trail** - View history

---

## Data Models Required

### Tables

**journal_entries**
- id, company_id, entry_number, entry_date, entry_type
- description, reference, status (draft/posted/reversed)
- total_debit, total_credit, is_balanced
- created_by, posted_by, posted_at, reversed_by, reversed_at
- reversal_entry_id, created_at, updated_at

**journal_entry_lines**
- id, journal_entry_id, account_id, debit, credit, description
- created_at, updated_at

**journal_entry_templates**
- id, company_id, template_name, description, entry_type
- template_lines (JSON), is_active, created_at, updated_at

**journal_entry_audit_logs**
- id, journal_entry_id, action (created/posted/reversed/edited)
- user_id, changes (JSON), created_at

---

## Acceptance Criteria

**Functional:**
- ✅ Create manual journal entries
- ✅ Validate debit = credit
- ✅ Post entries to GL
- ✅ Reverse entries
- ✅ Use templates
- ✅ Bulk import
- ✅ Audit trail

**Performance:**
- ✅ Entry creation in <2 minutes
- ✅ Posting in <5 seconds
- ✅ Bulk import 1000 entries in <2 minutes

**Security:**
- ✅ Only authorized users can create entries
- ✅ Approval workflow for large entries
- ✅ Cannot edit posted entries
- ✅ Audit trail for all activities

**UX:**
- ✅ Clear validation messages
- ✅ Auto-balance check
- ✅ Template library
- ✅ One-click reversal

---

## Design Notes

**Tone:**
- Professional, precise (accounting work)
- Clear validation messages
- Helpful guidance for first-time users

**UX Principles:**
- Validation before post (prevent errors)
- Templates (save time)
- Bulk import (efficiency)
- Audit trail (transparency)

**Mobile Consideration:**
- Journal entry desktop-only (complex input)

---

## Related Scenarios

- **03: Monthly Close** - Manual adjustments during close
- **11: Bank Reconciliation** - Adjustment entries
- All scenarios - Manual entries for corrections

---

## Accurate Feature Parity

**Accurate Manual Journal Entry includes:**
- Manual entry creation
- Posting to GL
- Reversal entries

**AkuBook Enhancement:**
- Template library (Accurate limited)
- Bulk import (Accurate doesn't have this)
- Auto-balance validation (Accurate manual)
- Audit trail (Accurate limited)

---

**Scenario Status:** ✅ Ready for Implementation  
**Next:** Design wireframes for 6 pages in this flow
