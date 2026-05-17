# Story 5.4: Auto-Generated Journals from Sales

**Epic:** 5 - Journal Entry & Posting System  
**Story ID:** 5.4  
**Story Key:** 5-4-auto-generated-journals-from-sales  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** Low (Deferred - Sales module belum ada)

---

## User Story

**Sebagai** System  
**Saya ingin** auto-generate journal entry ketika sales invoice di-post  
**Sehingga** transaksi penjualan otomatis ter-record di accounting

---

## Business Context

Sales transactions harus ter-reflect di accounting:
- **Sales Invoice Posted** → Generate journal entry:
  - Debit: Accounts Receivable (Piutang Usaha)
  - Credit: Sales Revenue (Pendapatan Penjualan)
  - Credit: Sales Tax Payable (Hutang PPN) - jika ada tax

Integration point antara Sales module dan Accounting module.

---

## Acceptance Criteria

### AC1: Generate Journal on Sales Invoice Post

**Given** sales invoice dengan status = 'draft'  
**When** user post sales invoice  
**Then** system auto-generate journal entry dengan:
- journal_date = invoice_date
- reference_number = invoice_number
- description = "Sales Invoice: {customer_name} - {invoice_number}"
- entry_type = 'sales'
- status = 'posted' (langsung posted)
- Lines:
  - Debit: Piutang Usaha = invoice_total
  - Credit: Pendapatan Penjualan = invoice_subtotal
  - Credit: Hutang PPN = invoice_tax (jika ada)

### AC2: Link Invoice to Journal

**After** journal generated  
**Then**:
- SalesInvoice.journal_entry_id = generated journal id
- Journal entry reference_number = invoice_number

### AC3: Account Mapping

**System** use predefined account mapping:
- Piutang Usaha: Account dengan code '1-1300' (dari CoA)
- Pendapatan Penjualan: Account dengan code '4-1000'
- Hutang PPN: Account dengan code '2-1200'

---

## Technical Requirements

### Service

**JournalService** (extend):
```php
public function generateFromSalesInvoice($invoiceId): JournalEntry
{
    $invoice = SalesInvoice::with('customer')->findOrFail($invoiceId);
    
    // Get accounts from mapping
    $arAccount = Account::where('code', '1-1300')->first();
    $revenueAccount = Account::where('code', '4-1000')->first();
    $taxAccount = Account::where('code', '2-1200')->first();
    
    // Create journal entry
    $journal = JournalEntry::create([
        'journal_number' => $this->generateJournalNumber($invoice->invoice_date),
        'journal_date' => $invoice->invoice_date,
        'reference_number' => $invoice->invoice_number,
        'description' => "Sales Invoice: {$invoice->customer->name} - {$invoice->invoice_number}",
        'entry_type' => 'sales',
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
    // Debit: Accounts Receivable
    JournalEntryLine::create([
        'journal_entry_id' => $journal->id,
        'account_id' => $arAccount->id,
        'description' => "Piutang dari {$invoice->customer->name}",
        'debit_amount' => $invoice->total,
        'credit_amount' => 0,
        'line_number' => 1,
    ]);
    
    // Credit: Sales Revenue
    JournalEntryLine::create([
        'journal_entry_id' => $journal->id,
        'account_id' => $revenueAccount->id,
        'description' => "Penjualan kepada {$invoice->customer->name}",
        'debit_amount' => 0,
        'credit_amount' => $invoice->subtotal,
        'line_number' => 2,
    ]);
    
    // Credit: Tax (if any)
    if ($invoice->tax > 0) {
        JournalEntryLine::create([
            'journal_entry_id' => $journal->id,
            'account_id' => $taxAccount->id,
            'description' => "PPN Penjualan",
            'debit_amount' => 0,
            'credit_amount' => $invoice->tax,
            'line_number' => 3,
        ]);
    }
    
    // Update account balances
    $journal->load('lines.account');
    foreach ($journal->lines as $line) {
        $this->updateAccountBalance($line);
    }
    
    return $journal;
}
```

### Integration

**SalesInvoiceController** (future):
```php
public function post($id)
{
    $invoice = SalesInvoice::findOrFail($id);
    
    // Post invoice
    $invoice->update(['status' => 'posted']);
    
    // Generate journal entry
    $journal = app(JournalService::class)->generateFromSalesInvoice($id);
    
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
- `1-1300` → Piutang Usaha (Accounts Receivable)
- `4-1000` → Pendapatan Penjualan (Sales Revenue)
- `2-1200` → Hutang Pajak (Tax Payable)

### Deferred Implementation

Story ini **deferred** karena:
- Sales module (Epic 8) belum implement
- SalesInvoice model belum ada
- Bisa implement nanti setelah Epic 8 complete

**Placeholder:**
- Buat method signature di JournalService
- Add TODO comment
- Implement nanti ketika Sales module ready

---

## Tasks & Subtasks

### Task 1: Add Method Signature
- [ ] Add generateFromSalesInvoice($invoiceId) to JournalService
- [ ] Add TODO comment: "Implement after Epic 8 (Sales module)"
- [ ] Return null atau throw NotImplementedException

### Task 2: Documentation
- [ ] Document account mapping requirements
- [ ] Document expected invoice structure
- [ ] Add integration notes untuk Epic 8

---

## Definition of Done

- [ ] Method signature added
- [ ] Documentation complete
- [ ] Story marked as deferred
- [ ] Epic 8 team aware of integration point

---

## Notes

**Priority:** Low - Deferred until Epic 8

**Estimated Effort:** 2-3 hours (when implemented)

**Dependencies:**
- Epic 8: Sales Management (SalesInvoice model)
- Story 5.2: postJournal logic

**Implementation Timeline:**
- Create placeholder now
- Implement after Epic 8 complete

---

**Created by:** BMad Create Story Workflow  
**Last Updated:** 2026-05-14
