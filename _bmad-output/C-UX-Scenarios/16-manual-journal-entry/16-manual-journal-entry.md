# 16: Sari's Manual Journal Entry

**Project:** AkuBook  
**Created:** 2026-05-12  
**Method:** Whiteport Design Studio (WDS)

---

## Transaction (Q1)

**What this scenario covers:**
Month-end: Create manual adjusting entries (accruals, prepayments, reclassifications) → post to GL → verify balance

---

## Business Goal (Q2)

**Goal:** ⭐ PRIMARY: 95%+ Auto-Posting (but manual entries still needed for adjustments)  
**Objective:** Manual journal entry process streamlined, validation automated, posting immediate

---

## User & Situation (Q3)

**Persona:** Sari (Finance Admin, PRIMARY)  
**Situation:** Month-end. Sari needs to record adjusting entries: prepaid insurance (Rp 10M), accrued salary (Rp 5M), reclassify expense (Rp 2M). Need to ensure debit = credit, post to correct accounts, and verify impact on financial statements.

---

## Driving Forces (Q4)

**Hope:** Manual entry form intuitive, debit/credit validation automatic, posting immediate, financial statements updated in real-time.

**Worry:** Manual entry errors (debit ≠ credit), wrong account selection, posting to closed period, financial statements incorrect.

---

## Device & Starting Point (Q5 + Q6)

**Device:** Desktop  
**Entry:** Monthly routine — month-end adjusting entries, triggered by trial balance review.

---

## Best Outcome (Q7)

**User Success:**
Manual journal entries created quickly (< 15 minutes for 3 entries), validation automatic, posted successfully, financial statements updated. No errors, no rework.

**Business Success:**
Accurate financial statements, accrual accounting maintained, audit-ready journal entries, Finance Admin efficient.

---

## Shortest Path (Q8)

1. **Dashboard (Accounting)** — Sari sees "Trial Balance Ready" notification, reviews P&L, identifies adjustments needed
2. **Journal Entry (Create)** — Sari clicks "Create Manual Journal Entry"
3. **Entry Form** — Sari enters:
   - **Date**: 2026-05-31 (last day of month)
   - **Description**: "Prepaid Insurance - May 2026"
   - **Line 1**: DR: Prepaid Insurance Rp 10,000,000
   - **Line 2**: CR: Insurance Expense Rp 10,000,000
4. **Validation** — System validates:
   - ✅ Debit = Credit
   - ✅ Accounts exist and active
   - ✅ Period not closed
   - ✅ No negative balances (if configured)
5. **Post Entry** — Sari clicks "Post", system immediately updates GL
6. **Verify Impact** — Sari refreshes Trial Balance, verifies Prepaid Insurance increased, Insurance Expense decreased ✓

---

## Trigger Map Connections

**Persona:** Sari (Finance Admin, PRIMARY)

**Driving Forces Addressed:**
- ✅ **Want:** Streamlined manual entry (when needed), validation automatic
- ❌ **Fear:** Manual entry errors, wrong account selection, posting errors

**Business Goal:** ⭐ PRIMARY: 95%+ auto-posting (but manual entries still needed for adjustments)

---

## Scenario Steps

| Step | Folder | Purpose | Exit Action |
|------|--------|---------|-------------|
| 16.1 | `16.1-accounting-dashboard/` | Review trial balance | Click "Create Journal Entry" |
| 16.2 | `16.2-journal-entry-create/` | Enter manual journal | Click "Validate" |
| 16.3 | `16.3-validation/` | System validates entry | Click "Post" |
| 16.4 | `16.4-post-confirmation/` | Confirm posting | Click "Post to GL" |
| 16.5 | `16.5-trial-balance-verify/` | Verify impact on TB | Confirm ✓ |

## Validation Rules

**Mandatory Validations:**
- ✅ **Debit = Credit**: Total debit must equal total credit
- ✅ **Account Exists**: All accounts must exist in COA
- ✅ **Account Active**: Cannot post to suspended accounts
- ✅ **Period Open**: Cannot post to closed periods
- ✅ **Date Valid**: Date must be within fiscal year

**Optional Validations (configurable):**
- ⚠️ **Negative Balance**: Warn if entry creates negative balance (e.g., negative cash)
- ⚠️ **Large Amount**: Warn if entry > Rp 100M (potential error)
- ⚠️ **Unusual Account**: Warn if posting to rarely-used account

## Common Adjusting Entries

**Prepaid Expenses:**
```
DR: Prepaid Insurance         Rp 10,000,000
    CR: Insurance Expense     Rp 10,000,000
```

**Accrued Expenses:**
```
DR: Salary Expense            Rp 5,000,000
    CR: Accrued Salary        Rp 5,000,000
```

**Reclassification:**
```
DR: Marketing Expense         Rp 2,000,000
    CR: Office Expense        Rp 2,000,000
```

**Depreciation:**
```
DR: Depreciation Expense      Rp 3,000,000
    CR: Accumulated Depreciation  Rp 3,000,000
```

## Integration Points

**Upstream:**
- Chart of Accounts (account list)
- Trial Balance (current balances)
- Period Management (open/closed periods)

**Downstream:**
- General Ledger (posted entries)
- Financial Statements (P&L, Balance Sheet)
- Audit Trail (journal history)

---

_Scenario 16: Sari's Manual Journal Entry_
