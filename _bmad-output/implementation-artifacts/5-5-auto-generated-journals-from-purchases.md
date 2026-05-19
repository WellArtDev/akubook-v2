# Story 5.5: Auto-Generated Journals from Purchases

**Epic:** 5 - Journal Entry & Posting System  
**Story ID:** 5.5  
**Story Key:** 5-5-auto-generated-journals-from-purchases  
**Status:** review  
**Created:** 2026-05-14  
**Priority:** Low (Deferred - Purchase module belum ada)

---

## User Story

**Sebagai** System  
**Saya ingin** auto-generate journal entry ketika purchase invoice di-post  
**Sehingga** transaksi pembelian otomatis ter-record di accounting

---

## Business Context

Purchase transactions harus ter-reflect di accounting:
- **Purchase Invoice Posted** → Generate journal entry:
  - Debit: Inventory atau Expense (tergantung item type)
  - Debit: Input Tax (PPN Masukan) - jika ada tax
  - Credit: Accounts Payable (Hutang Usaha)

Integration point antara Purchase module dan Accounting module.

---

## Acceptance Criteria

### AC1: Generate Journal on Purchase Invoice Post

**Given** purchase invoice dengan status = 'draft'  
**When** user post purchase invoice  
**Then** system auto-generate journal entry dengan:
- journal_date = invoice_date
- reference_number = invoice_number
- description = "Purchase Invoice: {supplier_name} - {invoice_number}"
- entry_type = 'purchase'
- status = 'posted' (langsung posted)
- Lines:
  - Debit: Persediaan = invoice_subtotal (jika inventory items)
  - Debit: PPN Masukan = invoice_tax (jika ada)
  - Credit: Hutang Usaha = invoice_total

### AC2: Link Invoice to Journal

**After** journal generated  
**Then**:
- PurchaseInvoice.journal_entry_id = generated journal id
- Journal entry reference_number = invoice_number

### AC3: Account Mapping

**System** use predefined account mapping:
- Persediaan: Account dengan code '1-1400' (dari CoA)
- PPN Masukan: Account dengan code '1-1500' (prepaid tax)
- Hutang Usaha: Account dengan code '2-1100'

---

## Technical Requirements

### Service

**JournalService** (extend):
```php
public function generateFromPurchaseInvoice($invoiceId): JournalEntry
{
    $invoice = PurchaseInvoice::with('supplier')->findOrFail($invoiceId);
    
    // Get accounts from mapping
    $inventoryAccount = Account::where('code', '1-1400')->first();
    $inputTaxAccount = Account::where('code', '1-1500')->first();
    $apAccount = Account::where('code', '2-1100')->first();
    
    // Create journal entry
    $journal = JournalEntry::create([
        'journal_number' => $this->generateJournalNumber($invoice->invoice_date),
        'journal_date' => $invoice->invoice_date,
        'reference_number' => $invoice->invoice_number,
        'description' => "Purchase Invoice: {$invoice->supplier->name} - {$invoice->invoice_number}",
        'entry_type' => 'purchase',
        'status' => 'posted',
        'total_debit' => $invoice->total,
        'total_credit' => $invoice->total,
        'fiscal_period_id' => $this->getFiscalPeriodId($invoice->invoice_date),
        'branch_id' => $invoice->branch_id,
        'posted_at' => now(),
        'posted_by' => auth()->id(),
        'created_by' => auth()->id(),
    ]);
    
    // Create lines
    // Debit: Inventory
    JournalEntryLine::create([
        'journal_entry_id' => $journal->id,
        'account_id' => $inventoryAccount->id,
        'description' => "Pembelian dari {$invoice->supplier->name}",
        'debit_amount' => $invoice->subtotal,
        'credit_amount' => 0,
        'line_number' => 1,
    ]);
    
    // Debit: Input Tax (if any)
    $lineNumber = 2;
    if ($invoice->tax > 0) {
        JournalEntryLine::create([
            'journal_entry_id' => $journal->id,
            'account_id' => $inputTaxAccount->id,
            'description' => "PPN Masukan",
            'debit_amount' => $invoice->tax,
            'credit_amount' => 0,
            'line_number' => $lineNumber++,
        ]);
    }
    
    // Credit: Accounts Payable
    JournalEntryLine::create([
        'journal_entry_id' => $journal->id,
        'account_id' => $apAccount->id,
        'description' => "Hutang kepada {$invoice->supplier->name}",
        'debit_amount' => 0,
        'credit_amount' => $invoice->total,
        'line_number' => $lineNumber,
    ]);
    
    // Update account balances
    $journal->load('lines.account');
    foreach ($journal->lines as $line) {
        $this->updateAccountBalance($line);
    }
    
    return $journal;
}
```

### Integration

**PurchaseInvoiceController** (future):
```php
public function post($id)
{
    $invoice = PurchaseInvoice::findOrFail($id);
    
    // Post invoice
    $invoice->update(['status' => 'posted']);
    
    // Generate journal entry
    $journal = app(JournalService::class)->generateFromPurchaseInvoice($id);
    
    // Link invoice to journal
    $invoice->update(['journal_entry_id' => $journal->id]);
    
    return redirect()->back()->with('success', 'Invoice posted and journal entry generated');
}
```

---

## Developer Context

### Account Mapping

Hardcoded untuk MVP, future: configurable via settings

**Mapping:**
- `1-1400` → Persediaan (Inventory)
- `1-1500` → PPN Masukan (Input Tax / Prepaid Tax)
- `2-1100` → Hutang Usaha (Accounts Payable)

### Deferred Implementation

Story ini **deferred** karena:
- Purchase module (Epic 9) belum implement
- PurchaseInvoice model belum ada
- Bisa implement nanti setelah Epic 9 complete

**Placeholder:**
- Buat method signature di JournalService
- Add TODO comment
- Implement nanti ketika Purchase module ready

---

## Tasks & Subtasks

### Task 1: Add Method Signature
- [x] Add generateFromPurchaseInvoice($invoiceId) to JournalService
- [x] Add TODO message: "Implement after Epic 9 (Purchase module)"
- [x] Throw BadMethodCallException until PurchaseInvoice model/context exists

### Task 2: Documentation
- [x] Document account mapping requirements
- [x] Document expected invoice structure
- [x] Add integration notes untuk Epic 9

### Task 3: Test Coverage
- [x] Add JournalService placeholder guard test

---

## Definition of Done

- [x] Method signature added
- [x] Documentation complete
- [x] Story marked as deferred placeholder implemented
- [x] Epic 9 integration point documented
- [x] Placeholder behavior tested

---

## Notes

**Priority:** Low - Deferred until Epic 9

**Estimated Effort:** 2-3 hours (when implemented)

**Dependencies:**
- Epic 9: Purchase Management (PurchaseInvoice model)
- Story 5.2: postJournal logic

**Implementation Timeline:**
- Create placeholder now
- Implement after Epic 9 complete

---

**Created by:** BMad Create Story Workflow  
**Last Updated:** 2026-05-17

---

## Dev Agent Record

### Completion Notes

- Added `JournalService::generateFromPurchaseInvoice(int $invoiceId): JournalEntry` placeholder.
- Placeholder throws `BadMethodCallException` with Epic 9 dependency message until `PurchaseInvoice` exists.
- Added unit test coverage for placeholder guard behavior.
- Validation done: `php artisan test tests/Unit/JournalServiceTest.php` passed.

### File List

- app/Services/JournalService.php
- tests/Unit/JournalServiceTest.php
- _bmad-output/implementation-artifacts/5-5-auto-generated-journals-from-purchases.md
- _bmad-output/implementation-artifacts/sprint-status.yaml

### Change Log

- 2026-05-17: Implemented deferred purchase journal placeholder and marked story ready for review.
