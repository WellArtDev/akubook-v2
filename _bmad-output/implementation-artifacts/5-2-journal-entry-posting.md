# Story 5.2: Journal Entry Posting

**Epic:** 5 - Journal Entry & Posting System  
**Story ID:** 5.2  
**Story Key:** 5-2-journal-entry-posting  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** High

---

## User Story

**Sebagai** Accountant  
**Saya ingin** post journal entry yang sudah balance  
**Sehingga** account balances ter-update dan entry menjadi permanent (tidak bisa di-edit)

---

## Business Context

Posting adalah proses finalisasi journal entry:
- **Update Account Balances**: Debit/credit ter-apply ke account.balance
- **Lock Entry**: Status berubah ke 'posted', tidak bisa di-edit/delete
- **Audit Trail**: posted_at dan posted_by ter-record
- **Fiscal Period Validation**: Hanya bisa post ke period yang open

Workflow:
1. User create journal entry as draft (Story 5.1)
2. User review dan validate balance
3. User click "Post" button
4. System validate fiscal period status
5. System update account balances
6. System lock entry

---

## Acceptance Criteria

### AC1: Post Button pada Draft Entry

**Given** journal entry dengan status = 'draft' dan balanced  
**When** user view entry di Index atau Show page  
**Then** user melihat "Post" button

**When** user click "Post" button  
**Then** confirmation dialog muncul: "Post journal entry {journal_number}? This action cannot be undone."

### AC2: Post Validation

**When** user confirm post  
**Then** system validate:
- Entry status = 'draft'
- Total debit = total credit
- Fiscal period untuk journal_date masih 'open'
- Semua accounts masih active

**If validation fails:**
- Show error message
- Entry tetap draft

### AC3: Account Balance Update

**When** post validation success  
**Then** system update account balances:
- For each line dengan debit_amount > 0:
  - Account.balance += debit_amount (jika account.type = asset/expense)
  - Account.balance -= debit_amount (jika account.type = liability/equity/revenue)
- For each line dengan credit_amount > 0:
  - Account.balance -= credit_amount (jika account.type = asset/expense)
  - Account.balance += credit_amount (jika account.type = liability/equity/revenue)

### AC4: Entry Status Update

**After** balance update  
**Then** system update journal entry:
- status = 'posted'
- posted_at = now()
- posted_by = current user id
- Save changes

### AC5: Post Success

**After** entry posted  
**Then**:
- Redirect ke Index atau Show page
- Success message: "Journal entry {journal_number} posted successfully"
- Entry tidak bisa di-edit atau di-delete
- Post button tidak muncul lagi

### AC6: Cannot Post to Closed Period

**Given** journal entry dengan journal_date di closed fiscal period  
**When** user try to post  
**Then**:
- Error message: "Cannot post to closed fiscal period {period_name}"
- Entry tetap draft

---

## Technical Requirements

### Backend

**Controller:** `JournalEntryController` (extend dari Story 5.1)
- `post($id)` - post draft entry

**Service:** `JournalService` (extend dari Story 5.1)
```php
public function postJournal($journalId): void
{
    DB::transaction(function () use ($journalId) {
        $journal = JournalEntry::with('lines.account')->findOrFail($journalId);
        
        // Validate
        if ($journal->status !== 'draft') {
            throw new \Exception('Only draft entries can be posted');
        }
        
        if ($journal->total_debit != $journal->total_credit) {
            throw new \Exception('Entry is not balanced');
        }
        
        // Check fiscal period
        $period = FiscalPeriod::find($journal->fiscal_period_id);
        if ($period->status !== 'open') {
            throw new \Exception("Cannot post to closed period {$period->name}");
        }
        
        // Update account balances
        foreach ($journal->lines as $line) {
            $this->updateAccountBalance($line);
        }
        
        // Update journal status
        $journal->update([
            'status' => 'posted',
            'posted_at' => now(),
            'posted_by' => auth()->id(),
        ]);
    });
}

private function updateAccountBalance($line): void
{
    $account = $line->account;
    $debit = $line->debit_amount;
    $credit = $line->credit_amount;
    
    // Normal balance logic
    $isDebitAccount = in_array($account->type, ['asset', 'expense']);
    
    if ($debit > 0) {
        $account->balance += $isDebitAccount ? $debit : -$debit;
    }
    
    if ($credit > 0) {
        $account->balance += $isDebitAccount ? -$credit : $credit;
    }
    
    $account->save();
}
```

### Frontend

**Update Index.jsx:**
- Add "Post" button untuk draft entries yang balanced
- Add confirmation dialog

**Update Show.jsx:**
- Add "Post" button jika status = draft
- Show posted_at dan posted_by jika status = posted
- Disable edit/delete buttons jika status = posted

### Routes

```php
Route::post('journal-entries/{id}/post', [JournalEntryController::class, 'post'])
    ->name('journal-entries.post')
    ->middleware('permission:post-journal-entries');
```

### Permissions

- `post-journal-entries` - Post journal entries

---

## Developer Context

### Account Balance Logic

**Normal Balance:**
- **Debit accounts** (Asset, Expense): increase with debit, decrease with credit
- **Credit accounts** (Liability, Equity, Revenue): increase with credit, decrease with debit

**Implementation:**
```php
// Debit entry
if (in_array($account->type, ['asset', 'expense'])) {
    $account->balance += $debit;  // increase
} else {
    $account->balance -= $debit;  // decrease
}

// Credit entry
if (in_array($account->type, ['asset', 'expense'])) {
    $account->balance -= $credit;  // decrease
} else {
    $account->balance += $credit;  // increase
}
```

### Transaction Safety

- Wrap semua update dalam DB::transaction()
- Jika ada error, rollback semua changes
- Lock journal entry untuk prevent concurrent posting

### Integration Points

- Story 5.1: Extend controller dan service
- Story 5.3: Reversal akan create opposite entry
- Story 5.4-5.5: Auto-generated entries akan langsung posted

---

## Tasks & Subtasks

### Task 1: Extend JournalService
- [ ] Add postJournal($journalId) method
- [ ] Implement validation logic
- [ ] Implement updateAccountBalance($line) method
- [ ] Add DB::transaction wrapper
- [ ] Add error handling

### Task 2: Extend JournalEntryController
- [ ] Add post($id) method
- [ ] Call JournalService::postJournal()
- [ ] Handle exceptions
- [ ] Return success/error response

### Task 3: Update Frontend
- [ ] Update Index.jsx - add Post button untuk draft entries
- [ ] Add confirmation dialog
- [ ] Update Show.jsx - add Post button
- [ ] Show posted_at dan posted_by info
- [ ] Disable edit/delete untuk posted entries

### Task 4: Add Route & Permission
- [ ] Add post route to web.php
- [ ] Add post-journal-entries permission
- [ ] Assign to Accountant role

### Task 5: Testing
- [ ] Test post balanced draft entry
- [ ] Test cannot post unbalanced entry
- [ ] Test cannot post to closed period
- [ ] Test account balances updated correctly
- [ ] Test cannot edit/delete posted entry
- [ ] Test concurrent posting prevention
- [ ] Test transaction rollback on error

---

## Definition of Done

- [ ] postJournal() method implemented
- [ ] Account balance update logic correct
- [ ] Post button added to UI
- [ ] Confirmation dialog working
- [ ] Posted entries locked (no edit/delete)
- [ ] Fiscal period validation working
- [ ] Manual testing complete
- [ ] Code review approved

---

## Notes

**Priority:** High - Required untuk finalize entries

**Estimated Effort:** 3-4 hours
- Service logic: 1.5 hours
- Controller: 30 minutes
- Frontend updates: 1 hour
- Testing: 1 hour

**Dependencies:**
- Story 5.1 complete

---

**Created by:** BMad Create Story Workflow  
**Last Updated:** 2026-05-14
