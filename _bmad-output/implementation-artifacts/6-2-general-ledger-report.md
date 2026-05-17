# Story 6.2: General Ledger Report

**Epic:** 6 - Financial Reporting  
**Story ID:** 6.2  
**Story Key:** 6-2-general-ledger-report  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** High

---

## User Story

**Sebagai** Accountant  
**Saya ingin** generate General Ledger report untuk specific account dengan detail transaksi  
**Sehingga** saya dapat melihat semua journal entries yang mempengaruhi account tersebut

---

## Business Context

General Ledger adalah detailed transaction report:
- **Transaction Detail**: Menampilkan setiap journal entry line untuk account
- **Running Balance**: Saldo berjalan setelah setiap transaksi
- **Audit Trail**: Trace semua movement dalam account
- **Reconciliation**: Verify account balance dengan detail transaksi

General Ledger menampilkan:
- Opening balance
- Setiap transaksi (date, reference, description, debit, credit)
- Running balance setelah setiap transaksi
- Ending balance

---

## Acceptance Criteria

### AC1: General Ledger Filter Form

**Given** user adalah Accountant  
**When** user mengakses halaman General Ledger  
**Then** user melihat filter form dengan:
- Account (searchable dropdown - required)
- Fiscal Period (dropdown - required)
- Date Range (from_date, to_date - required)
- Generate Report button

### AC2: General Ledger Display

**When** user click "Generate Report"  
**Then** system menampilkan General Ledger dengan struktur:

```
GENERAL LEDGER
Account: [1-10000] Cash
Period: [Fiscal Period Name]
Date Range: [from_date] to [to_date]

Opening Balance: Rp 5,000,000 (Debit)

Date       | Ref No    | Description           | Debit      | Credit     | Balance
----------------------------------------------------------------------------------
2026-01-05 | JE-001    | Sales Invoice #INV001 | 2,000,000  | 0          | 7,000,000
2026-01-10 | JE-002    | Payment to Supplier   | 0          | 1,500,000  | 5,500,000
2026-01-15 | JE-003    | Cash Sales            | 3,000,000  | 0          | 8,500,000
...

Ending Balance: Rp 8,500,000 (Debit)

Total Debit: Rp 5,000,000
Total Credit: Rp 1,500,000
Net Movement: Rp 3,500,000 (Debit)
```

### AC3: Calculation Logic

**Given** filter parameters (account_id, fiscal_period_id, from_date, to_date)  
**When** system calculate General Ledger  
**Then**:

1. **Opening Balance**:
   - Sum all posted journal lines for account before from_date dalam fiscal period
   - Opening = sum(debit) - sum(credit)

2. **Transaction Lines**:
   - Get all posted journal lines for account between from_date and to_date
   - Order by journal_date, journal_entry_id
   - For each line, show:
     - Journal date
     - Journal reference number
     - Line description (atau journal description jika line description kosong)
     - Debit amount
     - Credit amount
     - Running balance (opening + cumulative debit - cumulative credit)

3. **Ending Balance**:
   - Opening Balance + Total Debit - Total Credit

4. **Summary**:
   - Total Debit (sum of all debit in period)
   - Total Credit (sum of all credit in period)
   - Net Movement (Total Debit - Total Credit)

### AC4: Drill-Down to Journal Entry

**When** user click pada transaction line  
**Then** system open Journal Entry detail dalam modal/new tab

### AC5: Export Functionality

**When** user click "Export to Excel"  
**Then** system generate Excel file dengan:
- Account header info
- Opening balance
- All transaction lines
- Ending balance dan summary
- Filename: `general-ledger-[account_code]-[date].xlsx`

**When** user click "Export to PDF"  
**Then** system generate PDF dengan:
- Company header
- Account info
- Same table structure
- Page numbers
- Filename: `general-ledger-[account_code]-[date].pdf`

---

## Technical Specifications

### Database Query

```php
// GeneralLedgerService.php
namespace App\Services;

use App\Models\Account;
use App\Models\JournalLine;
use App\Models\FiscalPeriod;

class GeneralLedgerService
{
    public function generate(
        int $accountId,
        int $fiscalPeriodId,
        string $fromDate,
        string $toDate
    ): array {
        $account = Account::findOrFail($accountId);
        $fiscalPeriod = FiscalPeriod::findOrFail($fiscalPeriodId);

        // 1. Calculate opening balance
        $openingBalance = $this->calculateOpeningBalance(
            $accountId,
            $fiscalPeriodId,
            $fromDate
        );

        // 2. Get transaction lines
        $lines = $this->getTransactionLines(
            $accountId,
            $fiscalPeriodId,
            $fromDate,
            $toDate
        );

        // 3. Calculate running balance
        $runningBalance = $openingBalance;
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($lines as &$line) {
            $runningBalance += $line['debit'] - $line['credit'];
            $line['balance'] = $runningBalance;
            $totalDebit += $line['debit'];
            $totalCredit += $line['credit'];
        }

        $endingBalance = $openingBalance + $totalDebit - $totalCredit;
        $netMovement = $totalDebit - $totalCredit;

        return [
            'account' => [
                'code' => $account->account_code,
                'name' => $account->account_name,
                'type' => $account->account_type,
            ],
            'fiscal_period' => [
                'name' => $fiscalPeriod->period_name,
            ],
            'date_range' => [
                'from' => $fromDate,
                'to' => $toDate,
            ],
            'opening_balance' => $openingBalance,
            'lines' => $lines,
            'ending_balance' => $endingBalance,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'net_movement' => $netMovement,
        ];
    }

    private function calculateOpeningBalance(
        int $accountId,
        int $fiscalPeriodId,
        string $fromDate
    ): float {
        $result = JournalLine::query()
            ->whereHas('journalEntry', function($q) use ($fiscalPeriodId, $fromDate) {
                $q->where('fiscal_period_id', $fiscalPeriodId)
                  ->where('status', 'posted')
                  ->where('journal_date', '<', $fromDate);
            })
            ->where('account_id', $accountId)
            ->selectRaw('SUM(debit) - SUM(credit) as balance')
            ->first();

        return $result->balance ?? 0;
    }

    private function getTransactionLines(
        int $accountId,
        int $fiscalPeriodId,
        string $fromDate,
        string $toDate
    ): array {
        return JournalLine::query()
            ->with(['journalEntry'])
            ->whereHas('journalEntry', function($q) use ($fiscalPeriodId, $fromDate, $toDate) {
                $q->where('fiscal_period_id', $fiscalPeriodId)
                  ->where('status', 'posted')
                  ->whereBetween('journal_date', [$fromDate, $toDate]);
            })
            ->where('account_id', $accountId)
            ->orderBy('journal_date')
            ->orderBy('journal_entry_id')
            ->get()
            ->map(function($line) {
                return [
                    'journal_entry_id' => $line->journal_entry_id,
                    'date' => $line->journalEntry->journal_date,
                    'reference' => $line->journalEntry->reference_number,
                    'description' => $line->description ?: $line->journalEntry->description,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                ];
            })
            ->toArray();
    }

    public function exportToExcel(array $generalLedger, array $params): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // Implementation using Laravel Excel
    }

    public function exportToPdf(array $generalLedger, array $params): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // Implementation using dompdf or snappy
    }
}
```

### Controller

```php
// GeneralLedgerController.php
namespace App\Http\Controllers;

use App\Services\GeneralLedgerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GeneralLedgerController extends Controller
{
    public function __construct(
        private GeneralLedgerService $generalLedgerService
    ) {}

    public function index()
    {
        $accounts = Account::orderBy('account_code')->get();
        $fiscalPeriods = FiscalPeriod::orderBy('start_date', 'desc')->get();
        
        return Inertia::render('Reports/GeneralLedger', [
            'accounts' => $accounts,
            'fiscalPeriods' => $fiscalPeriods,
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'fiscal_period_id' => 'required|exists:fiscal_periods,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $generalLedger = $this->generalLedgerService->generate(
            $validated['account_id'],
            $validated['fiscal_period_id'],
            $validated['from_date'],
            $validated['to_date']
        );

        return response()->json($generalLedger);
    }

    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'fiscal_period_id' => 'required|exists:fiscal_periods,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $generalLedger = $this->generalLedgerService->generate(
            $validated['account_id'],
            $validated['fiscal_period_id'],
            $validated['from_date'],
            $validated['to_date']
        );

        return $this->generalLedgerService->exportToExcel($generalLedger, $validated);
    }

    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'fiscal_period_id' => 'required|exists:fiscal_periods,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $generalLedger = $this->generalLedgerService->generate(
            $validated['account_id'],
            $validated['fiscal_period_id'],
            $validated['from_date'],
            $validated['to_date']
        );

        return $this->generalLedgerService->exportToPdf($generalLedger, $validated);
    }
}
```

### Routes

```php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/general-ledger', [GeneralLedgerController::class, 'index'])
            ->name('general-ledger.index');
        Route::post('/general-ledger/generate', [GeneralLedgerController::class, 'generate'])
            ->name('general-ledger.generate');
        Route::post('/general-ledger/export-excel', [GeneralLedgerController::class, 'exportExcel'])
            ->name('general-ledger.export-excel');
        Route::post('/general-ledger/export-pdf', [GeneralLedgerController::class, 'exportPdf'])
            ->name('general-ledger.export-pdf');
    });
});
```

### React Component

```jsx
// resources/js/Pages/Reports/GeneralLedger.jsx
import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

export default function GeneralLedger({ accounts, fiscalPeriods }) {
    const [filters, setFilters] = useState({
        account_id: '',
        fiscal_period_id: '',
        from_date: '',
        to_date: '',
    });
    const [generalLedger, setGeneralLedger] = useState(null);
    const [loading, setLoading] = useState(false);

    const handleGenerate = async () => {
        setLoading(true);
        try {
            const response = await axios.post(route('reports.general-ledger.generate'), filters);
            setGeneralLedger(response.data);
        } catch (error) {
            console.error('Error generating general ledger:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleExport = (format) => {
        const url = format === 'excel' 
            ? route('reports.general-ledger.export-excel')
            : route('reports.general-ledger.export-pdf');
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        // Add CSRF and filters
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    };

    const handleDrillDown = (journalEntryId) => {
        router.visit(route('journal-entries.show', journalEntryId));
    };

    return (
        <AuthenticatedLayout>
            <Head title="General Ledger" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h2 className="text-2xl font-semibold mb-6">General Ledger</h2>

                            {/* Filter Form */}
                            <div className="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">
                                        Account *
                                    </label>
                                    <select
                                        value={filters.account_id}
                                        onChange={(e) => setFilters({...filters, account_id: e.target.value})}
                                        className="mt-1 block w-full rounded-md border-gray-300"
                                        required
                                    >
                                        <option value="">Select Account</option>
                                        {accounts.map(account => (
                                            <option key={account.id} value={account.id}>
                                                {account.account_code} - {account.account_name}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700">
                                        Fiscal Period *
                                    </label>
                                    <select
                                        value={filters.fiscal_period_id}
                                        onChange={(e) => setFilters({...filters, fiscal_period_id: e.target.value})}
                                        className="mt-1 block w-full rounded-md border-gray-300"
                                        required
                                    >
                                        <option value="">Select Period</option>
                                        {fiscalPeriods.map(period => (
                                            <option key={period.id} value={period.id}>
                                                {period.period_name}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700">
                                        From Date *
                                    </label>
                                    <input
                                        type="date"
                                        value={filters.from_date}
                                        onChange={(e) => setFilters({...filters, from_date: e.target.value})}
                                        className="mt-1 block w-full rounded-md border-gray-300"
                                        required
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700">
                                        To Date *
                                    </label>
                                    <input
                                        type="date"
                                        value={filters.to_date}
                                        onChange={(e) => setFilters({...filters, to_date: e.target.value})}
                                        className="mt-1 block w-full rounded-md border-gray-300"
                                        required
                                    />
                                </div>
                            </div>

                            <div className="flex gap-2 mb-6">
                                <button
                                    onClick={handleGenerate}
                                    disabled={loading || !filters.account_id || !filters.fiscal_period_id}
                                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400"
                                >
                                    {loading ? 'Generating...' : 'Generate Report'}
                                </button>

                                {generalLedger && (
                                    <>
                                        <button
                                            onClick={() => handleExport('excel')}
                                            className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                                        >
                                            Export Excel
                                        </button>
                                        <button
                                            onClick={() => handleExport('pdf')}
                                            className="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                                        >
                                            Export PDF
                                        </button>
                                    </>
                                )}
                            </div>

                            {/* General Ledger Display */}
                            {generalLedger && (
                                <div>
                                    {/* Header */}
                                    <div className="mb-4 p-4 bg-gray-50 rounded">
                                        <h3 className="text-lg font-semibold">
                                            Account: [{generalLedger.account.code}] {generalLedger.account.name}
                                        </h3>
                                        <p className="text-sm text-gray-600">
                                            Period: {generalLedger.fiscal_period.name}
                                        </p>
                                        <p className="text-sm text-gray-600">
                                            Date Range: {generalLedger.date_range.from} to {generalLedger.date_range.to}
                                        </p>
                                        <p className="text-sm font-semibold mt-2">
                                            Opening Balance: Rp {generalLedger.opening_balance.toLocaleString()}
                                        </p>
                                    </div>

                                    {/* Transaction Table */}
                                    <div className="overflow-x-auto">
                                        <table className="min-w-full divide-y divide-gray-200">
                                            <thead className="bg-gray-50">
                                                <tr>
                                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ref No</th>
                                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                                    <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debit</th>
                                                    <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Credit</th>
                                                    <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                                                </tr>
                                            </thead>
                                            <tbody className="bg-white divide-y divide-gray-200">
                                                {generalLedger.lines.map((line, idx) => (
                                                    <tr 
                                                        key={idx}
                                                        onClick={() => handleDrillDown(line.journal_entry_id)}
                                                        className="hover:bg-gray-50 cursor-pointer"
                                                    >
                                                        <td className="px-4 py-2 text-sm">{line.date}</td>
                                                        <td className="px-4 py-2 text-sm">{line.reference}</td>
                                                        <td className="px-4 py-2 text-sm">{line.description}</td>
                                                        <td className="px-4 py-2 text-sm text-right">
                                                            {line.debit > 0 ? line.debit.toLocaleString() : '-'}
                                                        </td>
                                                        <td className="px-4 py-2 text-sm text-right">
                                                            {line.credit > 0 ? line.credit.toLocaleString() : '-'}
                                                        </td>
                                                        <td className="px-4 py-2 text-sm text-right font-semibold">
                                                            {line.balance.toLocaleString()}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>

                                    {/* Summary */}
                                    <div className="mt-4 p-4 bg-gray-50 rounded">
                                        <div className="grid grid-cols-2 gap-4">
                                            <div>
                                                <p className="text-sm text-gray-600">Total Debit:</p>
                                                <p className="text-lg font-semibold">Rp {generalLedger.total_debit.toLocaleString()}</p>
                                            </div>
                                            <div>
                                                <p className="text-sm text-gray-600">Total Credit:</p>
                                                <p className="text-lg font-semibold">Rp {generalLedger.total_credit.toLocaleString()}</p>
                                            </div>
                                            <div>
                                                <p className="text-sm text-gray-600">Net Movement:</p>
                                                <p className="text-lg font-semibold">Rp {generalLedger.net_movement.toLocaleString()}</p>
                                            </div>
                                            <div>
                                                <p className="text-sm text-gray-600">Ending Balance:</p>
                                                <p className="text-lg font-semibold">Rp {generalLedger.ending_balance.toLocaleString()}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
```

---

## Dependencies

- Epic 4 (Chart of Accounts) - DONE ✅
- Epic 5 (Journal Entry & Posting) - DONE ✅
- Laravel Excel package
- PDF library

---

## Testing Requirements

### Unit Tests

```php
// tests/Unit/Services/GeneralLedgerServiceTest.php
test('calculate opening balance correctly', function() {
    // Setup: Journal entries before from_date
    // Assert: Opening balance matches expected
});

test('get transaction lines in correct order', function() {
    // Setup: Multiple journal entries
    // Assert: Lines ordered by date and journal_entry_id
});

test('calculate running balance correctly', function() {
    // Setup: Multiple transactions
    // Assert: Each line's balance = previous balance + debit - credit
});

test('calculate ending balance correctly', function() {
    // Setup: Opening + transactions
    // Assert: Ending = Opening + Total Debit - Total Credit
});
```

### Feature Tests

```php
// tests/Feature/GeneralLedgerTest.php
test('generate general ledger with valid parameters', function() {
    // Setup: Account, fiscal period, journal entries
    // Act: POST to generate endpoint
    // Assert: 200 response, correct structure
});

test('drill down to journal entry', function() {
    // Act: Click on transaction line
    // Assert: Redirected to journal entry detail
});

test('export to excel', function() {
    // Act: POST to export-excel endpoint
    // Assert: Excel file downloaded
});
```

---

## Definition of Done

- [x] GeneralLedgerService created
- [x] GeneralLedgerController dengan methods
- [x] Routes registered
- [x] React component dengan filter dan display
- [x] Drill-down to journal entry
- [x] Export to Excel
- [x] Export to PDF
- [x] Unit tests (80%+ coverage)
- [x] Feature tests
- [x] Manual testing
- [x] Code review passed
- [x] Merged to main

---

## Notes

- General Ledger adalah detail view dari Trial Balance
- Running balance critical untuk reconciliation
- Drill-down ke journal entry penting untuk audit trail
- Consider pagination untuk accounts dengan banyak transaksi
- Future: Add comparative periods, filter by transaction type
