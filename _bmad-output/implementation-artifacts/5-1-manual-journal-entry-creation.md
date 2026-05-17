# Story 5.1: Manual Journal Entry Creation

**Epic:** 5 - Journal Entry & Posting System  
**Story ID:** 5.1  
**Story Key:** 5-1-manual-journal-entry-creation  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** High

---

## User Story

**Sebagai** Accountant  
**Saya ingin** membuat journal entry manual dengan multiple lines (debit/credit)  
**Sehingga** saya dapat mencatat transaksi akuntansi yang tidak ter-generate otomatis

---

## Business Context

Manual Journal Entry adalah core functionality sistem akuntansi:
- **Adjusting Entries**: Koreksi, penyesuaian akhir periode
- **Opening Balances**: Entry saldo awal
- **Non-Standard Transactions**: Transaksi yang tidak ter-cover oleh modul lain
- **Corrections**: Perbaikan entry yang salah

Journal Entry harus:
- Balance (total debit = total credit)
- Memiliki minimal 2 lines (1 debit, 1 credit)
- Ter-assign ke fiscal period yang valid
- Memiliki reference number untuk tracking

---

## Acceptance Criteria

### AC1: Create Journal Entry Form

**Given** user adalah Accountant  
**When** user mengakses halaman Create Journal Entry  
**Then** user melihat form dengan:
- Journal Date (date picker)
- Reference Number (text input, optional)
- Description (textarea)
- Dynamic lines table dengan kolom:
  - Account (dropdown dengan search)
  - Description (text)
  - Debit (number)
  - Credit (number)
  - Action (remove line button)
- Add Line button
- Total Debit dan Total Credit (auto-calculated)
- Balance indicator (green jika balance, red jika tidak)
- Save as Draft button
- Save & Post button (jika balance)

### AC2: Line Management

**Given** user sedang create/edit journal entry  
**When** user click "Add Line"  
**Then** new empty line ditambahkan ke table

**When** user click "Remove Line" pada line tertentu  
**Then** line tersebut dihapus dari table

**When** user input debit/credit  
**Then**:
- Total Debit dan Total Credit ter-update otomatis
- Balance indicator ter-update
- Save & Post button enabled/disabled based on balance

### AC3: Validation Rules

**When** user save journal entry  
**Then** system validate:
- Journal date required
- Minimal 2 lines
- Setiap line harus punya account_id
- Setiap line harus punya debit ATAU credit (tidak boleh keduanya)
- Total debit = total credit (untuk post, tidak untuk draft)
- Journal date harus dalam fiscal period yang open

### AC4: Save as Draft

**Given** journal entry belum balance  
**When** user click "Save as Draft"  
**Then**:
- System save dengan status = 'draft'
- System generate journal_number (format: JE-YYYYMM-XXXX)
- System assign fiscal_period_id based on journal_date
- System save semua lines
- Redirect ke Index dengan success message
- Entry bisa di-edit lagi

### AC5: Save & Post

**Given** journal entry sudah balance  
**When** user click "Save & Post"  
**Then**:
- System validate balance
- System save dengan status = 'posted'
- System generate journal_number
- System assign fiscal_period_id
- System update account balances (via JournalService)
- System set posted_at = now, posted_by = current user
- Redirect ke Index dengan success message
- Entry tidak bisa di-edit lagi

---

## Technical Requirements

### Database

**Models sudah ada:**
- `JournalEntry` - header
- `JournalEntryLine` - lines

**Fields:**
```php
// JournalEntry
- journal_number (string, unique)
- journal_date (date)
- reference_number (string, nullable)
- description (text)
- entry_type (enum: manual, sales, purchase, adjustment)
- status (enum: draft, posted, reversed)
- total_debit (decimal)
- total_credit (decimal)
- fiscal_period_id (foreign key)
- branch_id (foreign key)
- posted_at (timestamp, nullable)
- posted_by (foreign key, nullable)
- reversed_journal_id (foreign key, nullable)
- created_by, updated_by
- timestamps, softDeletes

// JournalEntryLine
- journal_entry_id (foreign key)
- account_id (foreign key)
- description (text)
- debit_amount (decimal)
- credit_amount (decimal)
- line_number (integer)
```

### Backend

**Controller:** `JournalEntryController`
- `index()` - list dengan filters
- `create()` - form dengan accounts list
- `store()` - validate + create (draft atau posted)
- `edit($id)` - form (hanya jika draft)
- `update($id)` - validate + update (hanya jika draft)
- `show($id)` - detail view
- `destroy($id)` - soft delete (hanya jika draft)

**Form Request:** `StoreJournalEntryRequest`
```php
rules:
- journal_date: required, date
- reference_number: nullable, string, max:100
- description: required, string
- lines: required, array, min:2
- lines.*.account_id: required, exists:accounts,id
- lines.*.description: nullable, string
- lines.*.debit_amount: required_without:lines.*.credit_amount, numeric, min:0
- lines.*.credit_amount: required_without:lines.*.debit_amount, numeric, min:0
- lines.*.debit_amount: different:lines.*.credit_amount (tidak boleh keduanya filled)

Custom validation:
- Total debit = total credit (jika action = 'post')
- Journal date dalam fiscal period yang open
```

**Service:** `JournalService`
```php
- createJournal($data, $action) // action: 'draft' atau 'post'
- postJournal($journalId) // untuk post draft nanti
- generateJournalNumber($date) // format: JE-YYYYMM-XXXX
- validateBalance($lines) // check debit = credit
- updateAccountBalances($journalId) // update account.balance
```

### Frontend

**Page:** `resources/js/Pages/JournalEntries/Create.jsx`

**Components:**
- Date picker untuk journal_date
- Text input untuk reference_number
- Textarea untuk description
- Dynamic table untuk lines:
  - Account dropdown dengan search (react-select atau similar)
  - Description input
  - Debit input (number, format currency)
  - Credit input (number, format currency)
  - Remove button
- Add Line button
- Summary section:
  - Total Debit (formatted)
  - Total Credit (formatted)
  - Balance indicator (badge: green "Balance" atau red "Out of Balance")
- Action buttons:
  - Cancel (link ke index)
  - Save as Draft (always enabled)
  - Save & Post (enabled jika balance)

**State Management:**
```javascript
const [lines, setLines] = useState([
    { account_id: '', description: '', debit_amount: 0, credit_amount: 0 }
]);

const totalDebit = lines.reduce((sum, line) => sum + parseFloat(line.debit_amount || 0), 0);
const totalCredit = lines.reduce((sum, line) => sum + parseFloat(line.credit_amount || 0), 0);
const isBalanced = totalDebit === totalCredit && totalDebit > 0;
```

### Routes

```php
Route::middleware(['auth', 'permission:manage-journal-entries'])->group(function () {
    Route::resource('journal-entries', JournalEntryController::class);
});
```

### Permissions

- `manage-journal-entries` - CRUD journal entries
- `post-journal-entries` - Post journal entries (untuk Story 5.2)

---

## Developer Context

### Existing Infrastructure

**Models:** JournalEntry dan JournalEntryLine sudah ada

**Pattern dari Story 4.3:**
- Controller CRUD pattern
- Form Request validation
- Indonesian UI labels
- Permission middleware
- Flash messages
- Inertia pages

### Implementation Notes

1. **Journal Number Generation:**
   ```php
   // Format: JE-YYYYMM-XXXX
   $date = Carbon::parse($journalDate);
   $prefix = 'JE-' . $date->format('Ym') . '-';
   $lastNumber = JournalEntry::where('journal_number', 'like', $prefix . '%')
       ->orderBy('journal_number', 'desc')
       ->value('journal_number');
   $nextNumber = $lastNumber ? intval(substr($lastNumber, -4)) + 1 : 1;
   $journalNumber = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
   ```

2. **Fiscal Period Assignment:**
   ```php
   $period = FiscalPeriod::where('start_date', '<=', $journalDate)
       ->where('end_date', '>=', $journalDate)
       ->where('status', 'open')
       ->first();
   
   if (!$period) {
       throw new \Exception('No open fiscal period for this date');
   }
   ```

3. **Line Number Assignment:**
   - Auto-assign line_number berdasarkan urutan (1, 2, 3, ...)

4. **Account Balance Update (Story 5.2):**
   - Untuk Story 5.1, hanya save entry
   - Balance update akan di Story 5.2 (Posting)

5. **Frontend Dynamic Lines:**
   - Use array state untuk lines
   - Add/remove lines dengan array manipulation
   - Real-time calculation untuk totals

---

## Tasks & Subtasks

### Task 1: Create Controller & Service
- [ ] Create `JournalEntryController.php`
- [ ] Implement index() dengan filters (status, date range)
- [ ] Implement create() - return accounts list
- [ ] Implement store() - handle draft/post action
- [ ] Implement edit($id) - only if draft
- [ ] Implement update($id) - only if draft
- [ ] Implement show($id) - detail view
- [ ] Implement destroy($id) - only if draft
- [ ] Create `JournalService.php`
- [ ] Implement createJournal($data, $action)
- [ ] Implement generateJournalNumber($date)
- [ ] Implement validateBalance($lines)

### Task 2: Create Form Request
- [ ] Create `StoreJournalEntryRequest.php`
- [ ] Add validation rules untuk header fields
- [ ] Add validation rules untuk lines array
- [ ] Add custom validation untuk balance (jika post)
- [ ] Add custom validation untuk fiscal period
- [ ] Create `UpdateJournalEntryRequest.php` (same rules)

### Task 3: Create Frontend Pages
- [ ] Create `resources/js/Pages/JournalEntries/Index.jsx`
- [ ] Implement table dengan columns: journal_number, date, description, total, status
- [ ] Add filters: status, date range
- [ ] Add action buttons: View, Edit (if draft), Delete (if draft)
- [ ] Create `resources/js/Pages/JournalEntries/Create.jsx`
- [ ] Implement form dengan dynamic lines
- [ ] Add account dropdown dengan search
- [ ] Implement add/remove line functionality
- [ ] Implement real-time balance calculation
- [ ] Add Save as Draft dan Save & Post buttons
- [ ] Create `resources/js/Pages/JournalEntries/Edit.jsx` (similar to Create)
- [ ] Create `resources/js/Pages/JournalEntries/Show.jsx` (read-only view)

### Task 4: Add Routes & Permissions
- [ ] Add routes to `routes/web.php`
- [ ] Add permissions to `RolePermissionSeeder`:
  - `manage-journal-entries`
- [ ] Assign to appropriate roles (Accountant, Finance Admin)

### Task 5: Testing
- [ ] Test create journal entry as draft
- [ ] Test create journal entry and post (balance)
- [ ] Test create with unbalanced lines (should fail if post)
- [ ] Test edit draft entry
- [ ] Test delete draft entry
- [ ] Test cannot edit/delete posted entry
- [ ] Test fiscal period validation
- [ ] Test journal number generation (unique, sequential)
- [ ] Test UI: add/remove lines
- [ ] Test UI: balance indicator

---

## Definition of Done

- [ ] JournalEntryController implemented
- [ ] JournalService implemented
- [ ] Form Requests created
- [ ] Frontend pages created (Index, Create, Edit, Show)
- [ ] Routes added
- [ ] Permissions added
- [ ] Manual testing checklist complete
- [ ] Can create draft journal entries
- [ ] Can create and post balanced journal entries
- [ ] Cannot post unbalanced entries
- [ ] Code review approved
- [ ] Sprint status updated

---

## Notes

**Priority:** High - Core accounting functionality

**Estimated Effort:** 6-8 hours
- Controller + Service: 2 hours
- Form Requests: 1 hour
- Frontend pages: 3-4 hours
- Testing: 1-2 hours

**Dependencies:**
- JournalEntry dan JournalEntryLine models exist
- FiscalPeriod model exists
- Account model exists

**Next Stories:**
- 5.2: Journal Entry Posting (update balances)
- 5.3: Journal Entry Reversal
- 5.4-5.5: Auto-generation from Sales/Purchase

---

**Created by:** BMad Create Story Workflow  
**Last Updated:** 2026-05-14
