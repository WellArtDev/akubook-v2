# Scenario 11: Bank Reconciliation

**User:** Finance Admin  
**Priority:** HIGH (Monthly close dependency)  
**Frequency:** Monthly  
**Success Metric:** Reconciliation completed in <2 hours (vs 1 day manual)

---

## Scenario Goal

Finance Admin reconciles bank statements with book records using AI-powered matching and creates adjustment entries for discrepancies.

---

## User Context

**Who:** Sari (Finance Admin) reconciling bank accounts

**When:** Monthly (part of month-end close process)

**Why:** Ensure book balance matches bank balance, identify missing transactions, maintain accurate cash records

**Current Pain (from Accurate):** Manual matching, time-consuming, error-prone, takes full day

---

## Sunshine Path (Happy Flow)

### Step 1: Start Bank Reconciliation

**Page:** Bank Reconciliation Dashboard

**User Action:**
- Opens Bank Reconciliation module
- Selects bank account (BCA - Operating)
- Selects period (April 2026)

**System Shows:**
- Reconciliation summary:
  - Book balance: Rp 1,200,000,000
  - Bank statement balance: Rp 1,195,000,000
  - Difference: Rp 5,000,000
  - Unreconciled transactions: 25
- Previous reconciliation status

**User Input:**
- Clicks "Start Reconciliation"

**System Response:**
- Shows reconciliation workspace

**Next:** Import bank statement

---

### Step 2: Import Bank Statement

**Page:** Bank Statement Import

**User Action:**
- Clicks "Import Statement"
- Uploads bank statement (CSV/PDF)

**System Shows:**
- Import options:
  - File format: CSV/PDF/Excel
  - Date format: DD/MM/YYYY
  - Column mapping (auto-detected)

**User Input:**
- Confirms column mapping:
  - Date → Column A
  - Description → Column B
  - Debit → Column C
  - Credit → Column D
  - Balance → Column E
- Clicks "Import"

**System Response:**
- Parses bank statement
- Extracts 120 transactions
- Shows "Import Complete" status

**Next:** Auto-match transactions

---

### Step 3: Auto-Match Transactions

**Page:** Transaction Matching

**User Action:**
- Clicks "Auto-Match"

**System Shows:**
- Matching progress:
  - Analyzing 120 bank transactions...
  - Matching with 115 book transactions...
  - AI-powered matching in progress...

**System Response:**
- Auto-matches transactions:
  - ✅ 108 transactions matched (90%)
  - ⚠️ 7 transactions suggested matches (need review)
  - ❌ 5 transactions unmatched
- Shows matching results

**Next:** Review suggested matches

---

### Step 4: Review Suggested Matches

**Page:** Suggested Matches

**User Action:**
- Reviews suggested matches

**System Shows:**
- Suggested match list:
  - Bank: "Transfer from PT Toko Jaya" (Rp 50,000,000)
  - Book: "Payment from PT Toko Elektronik Jaya" (Rp 50,000,000)
  - Confidence: 95%
  - Reason: Amount match, customer name similar

**User Input:**
- Reviews match details
- Clicks "Confirm Match" or "Reject"

**System Response:**
- Updates match status
- Moves to next suggested match

**Next:** Handle unmatched transactions

---

### Step 5: Handle Unmatched Transactions

**Page:** Unmatched Transactions

**User Action:**
- Reviews unmatched transactions

**System Shows:**
- Unmatched bank transactions:
  - Bank charge: Rp 50,000 (not in books)
  - Interest income: Rp 100,000 (not in books)
  - Unknown transfer: Rp 5,000,000 (not in books)
- Unmatched book transactions:
  - Check #12345: Rp 2,000,000 (not cleared)
  - Deposit in transit: Rp 3,000,000 (not in bank yet)

**User Input:**
- For bank charge:
  - Clicks "Create Adjustment Entry"
  - Selects account: Bank Charges Expense
  - Clicks "Post Entry"
- For interest income:
  - Clicks "Create Adjustment Entry"
  - Selects account: Interest Income
  - Clicks "Post Entry"
- For unknown transfer:
  - Clicks "Investigate"
  - Adds note: "Follow up with bank"

**System Response:**
- Creates adjustment journal entries:
  - DR: Bank Charges Expense (Rp 50,000)
  - CR: Cash in Bank (Rp 50,000)
  - DR: Cash in Bank (Rp 100,000)
  - CR: Interest Income (Rp 100,000)
- Updates reconciliation status

**Next:** Finalize reconciliation

---

### Step 6: Finalize Reconciliation

**Page:** Reconciliation Summary

**User Action:**
- Reviews reconciliation summary

**System Shows:**
- Final reconciliation:
  - Book balance (beginning): Rp 1,200,000,000
  - Add: Interest income: Rp 100,000
  - Less: Bank charges: Rp 50,000
  - Book balance (adjusted): Rp 1,200,050,000
  - Bank statement balance: Rp 1,195,000,000
  - Outstanding checks: (Rp 2,000,000)
  - Deposits in transit: Rp 3,000,000
  - Adjusted bank balance: Rp 1,196,000,000
  - Remaining difference: Rp 4,050,000 (under investigation)

**User Input:**
- Reviews summary
- Adds notes for remaining difference
- Clicks "Finalize Reconciliation"

**System Response:**
- Locks reconciliation (no more edits)
- Generates reconciliation report (PDF)
- Updates monthly close checklist: ✅ Bank reconciliation complete
- Shows "Reconciliation Complete" status

**Next:** Done (reconciliation complete)

---

## Pages/Screens Needed

1. **Bank Reconciliation Dashboard** - Start reconciliation
2. **Bank Statement Import** - Import and parse statement
3. **Transaction Matching** - Auto-match and manual match
4. **Suggested Matches** - Review AI suggestions
5. **Unmatched Transactions** - Handle discrepancies
6. **Reconciliation Summary** - Finalize and report

---

## Data Models Required

### Tables

**bank_reconciliations**
- id, company_id, bank_account_id, period
- statement_date, book_balance, statement_balance
- adjusted_book_balance, adjusted_bank_balance, difference
- status (in_progress/completed), reconciled_by, reconciled_at
- created_at, updated_at

**bank_statement_lines**
- id, reconciliation_id, transaction_date, description
- debit, credit, balance, is_matched, matched_transaction_id
- created_at, updated_at

**bank_reconciliation_items**
- id, reconciliation_id, book_transaction_id, bank_statement_line_id
- match_type (auto/manual/suggested), confidence_score
- matched_by, matched_at, created_at, updated_at

**bank_reconciliation_adjustments**
- id, reconciliation_id, adjustment_type (bank_charge/interest/error)
- amount, description, journal_entry_id
- created_at, updated_at

---

## Auto-Posting Rules

**Bank Charge Adjustment:**
- DR: Bank Charges Expense
- CR: Cash in Bank
- **Trigger:** When adjustment entry created

**Interest Income Adjustment:**
- DR: Cash in Bank
- CR: Interest Income
- **Trigger:** When adjustment entry created

**Example Entry:**
```
DR: Bank Charges Expense                Rp 50,000
    CR: Cash in Bank - BCA              Rp 50,000

DR: Cash in Bank - BCA                  Rp 100,000
    CR: Interest Income                 Rp 100,000
```

---

## Acceptance Criteria

**Functional:**
- ✅ Import bank statements (CSV/PDF/Excel)
- ✅ AI-powered auto-matching (90%+ accuracy)
- ✅ Manual matching for remaining transactions
- ✅ Create adjustment entries
- ✅ Generate reconciliation report
- ✅ Lock reconciliation after finalization

**Performance:**
- ✅ Import 1000+ transactions in <2 minutes
- ✅ Auto-match completes in <1 minute
- ✅ Reconciliation completes in <2 hours

**Security:**
- ✅ Only authorized users can reconcile
- ✅ Audit trail for all matches and adjustments
- ✅ Cannot edit after finalization

**UX:**
- ✅ Clear matching status (matched/suggested/unmatched)
- ✅ One-click auto-match
- ✅ Confidence scores for suggested matches
- ✅ Easy adjustment entry creation

---

## Design Notes

**Tone:**
- Efficient, accurate (critical process)
- Clear status indicators (matched/unmatched)
- Helpful AI suggestions (confidence scores)

**UX Principles:**
- AI-powered matching (save time)
- Visual matching (side-by-side comparison)
- One-click actions (fast processing)
- Preview before commit (show impact)

**Mobile Consideration:**
- Reconciliation desktop-only (complex matching)

---

## Related Scenarios

- **03: Monthly Close** - Part of close process
- **16: Manual Journal Entry** - Adjustment entries

---

## Accurate Feature Parity

**Accurate Bank Reconciliation includes:**
- Manual matching
- Adjustment entries

**AkuBook Enhancement:**
- AI-powered auto-matching (Accurate doesn't have this)
- Bank statement import (Accurate manual)
- Confidence scores (Accurate doesn't have this)
- One-click reconciliation (Accurate multi-step)

---

**Scenario Status:** ✅ Ready for Implementation  
**Next:** Design wireframes for 6 pages in this flow
