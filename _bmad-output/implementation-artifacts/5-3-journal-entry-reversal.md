# Story 5.3: Journal Entry Reversal

**Epic:** 5 - Journal Entry & Posting System  
**Story ID:** 5.3  
**Story Key:** 5-3-journal-entry-reversal  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** Medium

---

## User Story

**Sebagai** Accountant  
**Saya ingin** reverse posted journal entry  
**Sehingga** saya dapat membatalkan entry yang salah tanpa menghapus audit trail

---

## Business Context

Reversal adalah cara proper untuk membatalkan journal entry yang sudah posted:
- **Preserve Audit Trail**: Original entry tetap ada, tidak di-delete
- **Create Opposite Entry**: Reversal entry dengan debit/credit terbalik
- **Link Entries**: Original dan reversal ter-link via reversed_journal_id
- **Balance Restoration**: Account balances kembali ke state sebelum original entry

Use cases:
- Koreksi entry yang salah
- Pembatalan transaksi
- Adjustment yang perlu di-undo

---

## Acceptance Criteria

### AC1: Reverse Button pada Posted Entry

**Given** journal entry dengan status = 'posted'  
**When** user view entry di Show page  
**Then** user melihat "Reverse" button

**When** user click "Reverse" button  
**Then** confirmation dialog muncul: "Reverse journal entry {journal_number}? This will create an opposite entry to cancel the original."

### AC2: Create Reversal Entry

**When** user confirm reversal  
**Then** system create new journal entry dengan:
- journal_date = today (atau user-specified)
- reference_number = "Reversal of {original_journal_number}"
- description = "REVERSAL: {original_description}"
- entry_type = 'adjustment'
- status = 'posted' (langsung posted)
- Lines: sama dengan original tapi debit/credit terbalik
- reversed_journal_id = null
- posted_at = now()
- posted_by = current user

### AC3: Update Original Entry

**After** reversal entry created  
**Then** system update original entry:
- status = 'reversed'
- reversed_journal_id = reversal entry id

### AC4: Update Account Balances

**When** reversal entry created  
**Then** system update account balances (via postJournal):
- Apply reversal lines (opposite of original)
- Net effect: balances kembali ke state sebelum original entry

### AC5: Reversal Success

**After** reversal complete  
**Then**:
- Redirect ke reversal entry Show page
- Success message: "Journal entry {original_number} reversed. Reversal entry: {reversal_number}"
- Original entry show "Reversed" badge
- Original entry show link ke reversal entry
- Reversal entry show link ke original entry

### AC6: Cannot Reverse Twice

**Given** journal entry dengan status = 'reversed'  
**When** user view entry  
**Then** Reverse button tidak muncul

---

## Technical Requirements

### Backend

**Controller:** `JournalEntryController` (extend)
- `reverse($id)` - reverse posted entry

**Service:** `JournalService` (extend)
```php
public function reverseJournal($journalId, $reversalDate = null): JournalEntry
{
    return DB::transaction(function () use ($journalId, $reversalDate) {
        $original = JournalEntry::with('lines')->findOrFail($journalId);
        
        // Validate
        if ($original->status !== 'posted') {
            throw new \Exception('Only posted entries can be reversed');
        }
        
        if ($original->reversed_journal_id !== null) {
            throw new \Exception('Entry already reversed');
        }
        
        // Create reversal entry
        $reversal = JournalEntry::create([
            'journal_number' => $this->generateJournalNumber($reversalDate ?? now()),
            'journal_date' => $reversalDate ?? now(),
            'reference_number' => "Reversal of {$original->journal_number}",
            'description' => "REVERSAL: {$original->description}",
            'entry_type' => 'adjustment',
            'status' => 'posted',
            'total_debit' => $original->total_debit,
            'total_credit' => $original->total_credit,
            'fiscal_period_id' => $this->getFiscalPeriodId($reversalDate ?? now()),
            'branch_id' => $original->branch_id,
            'posted_at' => now(),
            'posted_by' => auth()->id(),
            'created_by' => auth()->id(),
        ]);
        
        // Create reversal lines (swap debit/credit)
        foreach ($original->lines as $line) {
            JournalEntryLine::create([
                'journal_entry_id' => $reversal->id,
                'account_id' => $line->account_id,
                'description' => $line->description,
                'debit_amount' => $line->credit_amount,  // swap
                'credit_amount' => $line->debit_amount,  // swap
                'line_number' => $line->line_number,
            ]);
        }
        
        // Update account balances (via postJournal logic)
        $reversal->load('lines.account');
        foreach ($reversal->lines as $line) {
            $this->updateAccountBalance($line);
        }
        
        // Update original entry
        $original->update([
            'status' => 'reversed',
            'reversed_journal_id' => $reversal->id,
        ]);
        
        return $reversal;
    });
}
```

### Frontend

**Update Show.jsx:**
- Add "Reverse" button jika status = 'posted'
- Add confirmation dialog dengan date picker (optional)
- Show "Reversed" badge jika status = 'reversed'
- Show link ke reversal entry jika reversed_journal_id exists
- Show link ke original entry jika entry adalah reversal (check reference_number)

### Routes

```php
Route::post('journal-entries/{id}/reverse', [JournalEntryController::class, 'reverse'])
    ->name('journal-entries.reverse')
    ->middleware('permission:reverse-journal-entries');
```

### Permissions

- `reverse-journal-entries` - Reverse journal entries

---

## Developer Context

### Reversal Logic

**Key Points:**
- Reversal adalah new entry, bukan update original
- Debit/credit di-swap untuk create opposite effect
- Both entries tetap di database (audit trail)
- Link via reversed_journal_id

**Example:**
```
Original Entry:
- Debit: Cash 1000
- Credit: Revenue 1000

Reversal Entry:
- Debit: Revenue 1000  (was credit)
- Credit: Cash 1000    (was debit)

Net Effect: Cash -1000, Revenue -1000 (back to zero)
```

### Reversal Date

- Default: today
- Optional: user bisa specify reversal_date
- Reversal date harus dalam open fiscal period
- Reversal date bisa berbeda dengan original date

### Integration Points

- Story 5.2: Use postJournal logic untuk update balances
- Future: Reversal bisa di-reverse lagi (create another reversal)

---

## Tasks & Subtasks

### Task 1: Extend JournalService
- [ ] Add reverseJournal($journalId, $reversalDate) method
- [ ] Implement validation logic
- [ ] Create reversal entry dengan swapped lines
- [ ] Update account balances
- [ ] Update original entry status
- [ ] Add DB::transaction wrapper

### Task 2: Extend JournalEntryController
- [ ] Add reverse($id) method
- [ ] Call JournalService::reverseJournal()
- [ ] Handle exceptions
- [ ] Redirect ke reversal entry

### Task 3: Update Frontend
- [ ] Update Show.jsx - add Reverse button
- [ ] Add confirmation dialog (optional date picker)
- [ ] Show Reversed badge
- [ ] Show links between original and reversal
- [ ] Hide Reverse button jika already reversed

### Task 4: Add Route & Permission
- [ ] Add reverse route
- [ ] Add reverse-journal-entries permission
- [ ] Assign to Accountant role

### Task 5: Testing
- [ ] Test reverse posted entry
- [ ] Test cannot reverse draft entry
- [ ] Test cannot reverse twice
- [ ] Test account balances restored
- [ ] Test link between original and reversal
- [ ] Test reversal date validation
- [ ] Test transaction rollback on error

---

## Definition of Done

- [ ] reverseJournal() method implemented
- [ ] Reversal entry created with swapped lines
- [ ] Original entry marked as reversed
- [ ] Account balances restored
- [ ] Reverse button added to UI
- [ ] Links between entries working
- [ ] Manual testing complete
- [ ] Code review approved

---

## Notes

**Priority:** Medium - Important untuk corrections

**Estimated Effort:** 3-4 hours
- Service logic: 1.5 hours
- Controller: 30 minutes
- Frontend updates: 1 hour
- Testing: 1 hour

**Dependencies:**
- Story 5.1 complete
- Story 5.2 complete

---

**Created by:** BMad Create Story Workflow  
**Last Updated:** 2026-05-14
