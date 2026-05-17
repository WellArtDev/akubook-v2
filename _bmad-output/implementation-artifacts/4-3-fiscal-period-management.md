# Story 4.3: Fiscal Period Management

**Epic:** 4 - Chart of Accounts & Fiscal Periods  
**Story ID:** 4.3  
**Story Key:** 4-3-fiscal-period-management  
**Status:** done  
**Created:** 2026-05-14  
**Completed:** 2026-05-14  
**Priority:** Medium

---

## User Story

**Sebagai** Finance Admin  
**Saya ingin** mengelola periode fiskal (buka/tutup periode)  
**Sehingga** saya dapat mengontrol periode mana yang boleh diposting transaksi dan memastikan data periode lalu tidak berubah

---

## Business Context

Fiscal Period Management adalah kontrol penting dalam sistem akuntansi:
- **Kontrol Posting**: Hanya periode yang open yang boleh menerima transaksi baru
- **Data Integrity**: Periode yang closed tidak bisa diubah, menjaga integritas laporan historis
- **Audit Trail**: Setiap pembukaan/penutupan periode ter-log untuk audit
- **Compliance**: Memenuhi requirement akuntansi untuk period closing

Typical workflow:
1. Admin membuat fiscal period untuk tahun berjalan (12 bulan)
2. Setiap bulan, transaksi diposting ke periode yang open
3. Di akhir bulan, admin review dan close periode
4. Periode closed tidak bisa menerima transaksi baru
5. Jika ada koreksi, admin bisa reopen periode (dengan approval/log)

---

## Acceptance Criteria

### AC1: Fiscal Period CRUD

**Given** user adalah Finance Admin  
**When** user mengakses halaman Fiscal Periods  
**Then** user dapat:
- Melihat list semua fiscal periods dengan status (Open/Closed)
- Create fiscal period baru dengan: name, period_type (monthly/quarterly/yearly), start_date, end_date, fiscal_year
- Edit fiscal period yang masih Open
- Tidak bisa delete fiscal period yang sudah ada transaksi

### AC2: Close Period

**Given** fiscal period dengan status Open  
**When** admin click "Close Period"  
**Then**:
- System validasi: tidak ada unposted journal entries di periode tersebut
- System update status = Closed, closed_at = now, closed_by = current user
- System log audit trail
- Period tidak bisa menerima transaksi baru
- Success message: "Period {name} closed successfully"

### AC3: Reopen Period

**Given** fiscal period dengan status Closed  
**When** admin click "Reopen Period"  
**Then**:
- System tampilkan confirmation dialog dengan warning
- System update status = Open, closed_at = null, closed_by = null
- System log audit trail dengan reason
- Period bisa menerima transaksi lagi
- Success message: "Period {name} reopened. Please document the reason."

### AC4: Posting Validation

**Given** user membuat/edit journal entry  
**When** user pilih journal_date  
**Then**:
- System cari fiscal period yang mencakup journal_date
- Jika period Closed → error: "Cannot post to closed period {name}"
- Jika period Open → allow posting
- Jika tidak ada period → error: "No fiscal period defined for this date"

### AC5: Period Overlap Validation

**Given** admin create/edit fiscal period  
**When** admin save dengan date range  
**Then**:
- System validasi: tidak ada overlap dengan period lain di fiscal_year yang sama
- Jika overlap → error: "Period overlaps with existing period {name}"
- Jika valid → save successfully

---

## Technical Requirements

### Database

Model `FiscalPeriod` sudah ada dengan fields:
- `name` (string) - e.g., "January 2026"
- `period_type` (enum: monthly, quarterly, yearly)
- `start_date` (date)
- `end_date` (date)
- `fiscal_year` (integer) - e.g., 2026
- `status` (enum: open, closed)
- `closed_at` (timestamp, nullable)
- `closed_by` (foreign key to users, nullable)
- `created_by`, `updated_by` (audit fields)
- `timestamps`, `softDeletes`

### Backend

**Controller:** `FiscalPeriodController`
- `index()` - list with filters (fiscal_year, status)
- `create()` - form
- `store()` - validate + create
- `edit($id)` - form
- `update($id)` - validate + update
- `destroy($id)` - soft delete (only if no transactions)
- `close($id)` - close period
- `reopen($id)` - reopen period

**Form Requests:**
- `StoreFiscalPeriodRequest` - validation rules
- `UpdateFiscalPeriodRequest` - validation rules

**Validation Rules:**
- `name`: required, string, max:100
- `period_type`: required, in:monthly,quarterly,yearly
- `start_date`: required, date
- `end_date`: required, date, after:start_date
- `fiscal_year`: required, integer, min:2000, max:2100
- Custom rule: no overlap with existing periods in same fiscal_year

**Service:** `FiscalPeriodService` (optional, for complex logic)
- `closePeriod($periodId)` - validate + close
- `reopenPeriod($periodId, $reason)` - reopen + log
- `validatePeriodForPosting($date)` - check if date in open period

### Frontend

**Pages:**
- `resources/js/Pages/FiscalPeriods/Index.jsx` - list with filters
- `resources/js/Pages/FiscalPeriods/Create.jsx` - create form
- `resources/js/Pages/FiscalPeriods/Edit.jsx` - edit form

**Components:**
- Period status badge (Open = green, Closed = red)
- Close/Reopen buttons dengan confirmation
- Date range picker
- Fiscal year selector

**UI Features:**
- Filter by fiscal_year, status
- Search by name
- Sort by start_date
- Pagination
- Bulk actions: Close multiple periods

### Routes

```php
Route::middleware(['auth', 'permission:manage-fiscal-periods'])->group(function () {
    Route::resource('fiscal-periods', FiscalPeriodController::class);
    Route::post('fiscal-periods/{id}/close', [FiscalPeriodController::class, 'close'])->name('fiscal-periods.close');
    Route::post('fiscal-periods/{id}/reopen', [FiscalPeriodController::class, 'reopen'])->name('fiscal-periods.reopen');
});
```

### Permissions

- `manage-fiscal-periods` - CRUD fiscal periods
- `close-fiscal-periods` - Close periods
- `reopen-fiscal-periods` - Reopen periods (restricted, typically only for admins)

---

## Developer Context

### Existing Infrastructure

**Model:** `FiscalPeriod` sudah ada di `app/Models/FiscalPeriod.php`

**Pattern dari Story 4.1 (Account CRUD):**
- Controller pattern: index, create, store, edit, update, destroy
- Form Request validation
- Indonesian UI labels
- Permission middleware
- Flash messages
- Debounced search
- Soft delete

**Integration Points:**
- `JournalEntry` model memiliki `fiscal_period_id` foreign key
- `JournalService::post()` harus validate fiscal period status

### Implementation Notes

1. **Close Period Validation:**
   - Check for unposted journal entries: `JournalEntry::where('fiscal_period_id', $id)->where('status', 'draft')->exists()`
   - If exists → return error
   - Else → update status, closed_at, closed_by

2. **Reopen Period:**
   - Show confirmation dialog dengan warning tentang impact
   - Log reason di audit trail
   - Consider: require approval dari supervisor?

3. **Posting Validation:**
   - Di `JournalService::post()`, tambahkan:
     ```php
     $period = FiscalPeriod::where('start_date', '<=', $journalDate)
         ->where('end_date', '>=', $journalDate)
         ->where('status', 'open')
         ->first();
     
     if (!$period) {
         throw new \Exception('Cannot post to closed or undefined period');
     }
     ```

4. **Period Overlap Validation:**
   - Custom validation rule atau di Form Request:
     ```php
     $overlap = FiscalPeriod::where('fiscal_year', $fiscalYear)
         ->where('id', '!=', $id) // exclude self on update
         ->where(function($q) use ($startDate, $endDate) {
             $q->whereBetween('start_date', [$startDate, $endDate])
               ->orWhereBetween('end_date', [$startDate, $endDate])
               ->orWhere(function($q2) use ($startDate, $endDate) {
                   $q2->where('start_date', '<=', $startDate)
                      ->where('end_date', '>=', $endDate);
               });
         })
         ->exists();
     ```

5. **UI Considerations:**
   - Disable edit/delete untuk closed periods
   - Show closed_at, closed_by info
   - Color-code periods: green (open), red (closed), gray (future)

---

## Tasks & Subtasks

### Task 1: Create Controller & Routes
- [ ] Create `FiscalPeriodController.php`
- [ ] Implement index() with filters (fiscal_year, status)
- [ ] Implement create() - return Inertia view
- [ ] Implement store() - validate + create
- [ ] Implement edit($id) - return Inertia view
- [ ] Implement update($id) - validate + update
- [ ] Implement destroy($id) - soft delete with transaction check
- [ ] Implement close($id) - close period logic
- [ ] Implement reopen($id) - reopen period logic
- [ ] Add routes to `routes/web.php`

### Task 2: Create Form Requests
- [ ] Create `StoreFiscalPeriodRequest.php`
- [ ] Add validation rules (name, period_type, dates, fiscal_year)
- [ ] Add custom overlap validation
- [ ] Create `UpdateFiscalPeriodRequest.php`
- [ ] Add same validation rules as Store

### Task 3: Create Frontend Pages
- [ ] Create `resources/js/Pages/FiscalPeriods/Index.jsx`
- [ ] Implement table with columns: name, period_type, start_date, end_date, fiscal_year, status
- [ ] Add filters: fiscal_year dropdown, status dropdown
- [ ] Add search by name (debounced)
- [ ] Add pagination
- [ ] Add action buttons: Edit, Close/Reopen, Delete
- [ ] Create `resources/js/Pages/FiscalPeriods/Create.jsx`
- [ ] Implement form with fields: name, period_type, start_date, end_date, fiscal_year
- [ ] Add date pickers
- [ ] Add validation error display
- [ ] Create `resources/js/Pages/FiscalPeriods/Edit.jsx`
- [ ] Same as Create but with existing data
- [ ] Disable fields if period is closed

### Task 4: Implement Close/Reopen Logic
- [ ] In controller close() method:
  - Validate no unposted journal entries
  - Update status, closed_at, closed_by
  - Log audit trail
  - Return success message
- [ ] In controller reopen() method:
  - Show confirmation dialog (frontend)
  - Update status, clear closed_at, closed_by
  - Log audit trail with reason
  - Return success message
- [ ] Add confirmation modals in frontend

### Task 5: Integrate with Journal Posting
- [ ] Update `JournalService::post()` to validate fiscal period
- [ ] Check if journal_date falls in open period
- [ ] Throw exception if period closed or undefined
- [ ] Update `JournalEntryController` to handle exception

### Task 6: Add Permissions
- [ ] Add permissions to `RolePermissionSeeder`:
  - `manage-fiscal-periods`
  - `close-fiscal-periods`
  - `reopen-fiscal-periods`
- [ ] Assign to appropriate roles (Finance Admin, Accountant)
- [ ] Add permission middleware to routes

### Task 7: Testing
- [ ] Test create fiscal period
- [ ] Test edit fiscal period
- [ ] Test delete fiscal period (with/without transactions)
- [ ] Test close period (with/without unposted entries)
- [ ] Test reopen period
- [ ] Test overlap validation
- [ ] Test posting to closed period (should fail)
- [ ] Test posting to open period (should succeed)
- [ ] Test UI filters and search

---

## Testing Requirements

### Manual Testing Checklist
- [ ] Create fiscal period untuk 2026 (12 bulan)
- [ ] Edit period yang open
- [ ] Try edit period yang closed (should be disabled)
- [ ] Close period tanpa unposted entries (should succeed)
- [ ] Try close period dengan unposted entries (should fail)
- [ ] Reopen closed period (should succeed dengan confirmation)
- [ ] Try create overlapping period (should fail)
- [ ] Post journal entry ke open period (should succeed)
- [ ] Try post journal entry ke closed period (should fail)
- [ ] Delete period tanpa transaksi (should succeed)
- [ ] Try delete period dengan transaksi (should fail)
- [ ] Test filters: fiscal_year, status
- [ ] Test search by name
- [ ] Test pagination

### Unit Tests (Optional)
```php
// tests/Unit/Services/FiscalPeriodServiceTest.php
test('can close period without unposted entries')
test('cannot close period with unposted entries')
test('can reopen closed period')
test('validates period overlap')
test('validates posting to closed period')
```

---

## Definition of Done

- [ ] FiscalPeriodController implemented dengan semua methods
- [ ] Form Requests created dengan validation rules
- [ ] Frontend pages created (Index, Create, Edit)
- [ ] Close/Reopen functionality working
- [ ] Posting validation integrated dengan JournalService
- [ ] Permissions added dan assigned
- [ ] Manual testing checklist complete
- [ ] No regressions di existing features
- [ ] Code review approved
- [ ] Sprint status updated ke 'done'

---

## Notes

**Priority:** Medium - Deferred dari Story 4.1, tapi penting untuk production use

**Estimated Effort:** 4-6 hours
- Controller + Routes: 1.5 hours
- Form Requests: 30 minutes
- Frontend pages: 2 hours
- Close/Reopen logic: 1 hour
- Integration + Testing: 1 hour

**Dependencies:**
- FiscalPeriod model sudah ada
- JournalEntry model sudah ada dengan fiscal_period_id
- Permission system sudah ada

**Future Enhancements:**
- Bulk close multiple periods
- Period closing checklist (e.g., "All invoices posted?", "Bank reconciled?")
- Approval workflow untuk reopen period
- Period closing report
- Auto-create periods untuk tahun baru

---

**Created by:** BMad Create Story Workflow  
**Last Updated:** 2026-05-14
