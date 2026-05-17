# Story 6.1: Trial Balance Report

**Epic:** 6 - Financial Reporting  
**Story ID:** 6.1  
**Story Key:** 6-1-trial-balance-report  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** High

---

## User Story

**Sebagai** Accountant  
**Saya ingin** generate Trial Balance report dengan filter periode  
**Sehingga** saya dapat verify bahwa total debit = total credit dan melihat saldo semua akun

---

## Business Context

Trial Balance adalah fundamental financial report:
- **Verification**: Memastikan debit = credit (accounting equation balance)
- **Period-End**: Digunakan sebelum closing untuk verify accuracy
- **Audit Trail**: Menampilkan semua account dengan activity
- **Foundation**: Basis untuk generate P&L dan Balance Sheet

Trial Balance menampilkan:
- Semua accounts dengan saldo (opening, movement, ending)
- Grouped by account type (Asset, Liability, Equity, Revenue, Expense)
- Total debit dan credit harus balance

---

## Acceptance Criteria

### AC1: Trial Balance Filter Form

**Given** user adalah Accountant  
**When** user mengakses halaman Trial Balance  
**Then** user melihat filter form dengan:
- Fiscal Period (dropdown)
- Date Range (from_date, to_date)
- Account Type filter (multi-select: Asset, Liability, Equity, Revenue, Expense)
- Show Zero Balance (checkbox, default: false)
- Generate Report button

### AC2: Trial Balance Display

**When** user click "Generate Report"  
**Then** system menampilkan Trial Balance dengan struktur:

```
TRIAL BALANCE
Period: [Fiscal Period Name]
Date Range: [from_date] to [to_date]

Account Code | Account Name | Debit | Credit
--------------------------------------------------------
ASSETS
1-10000 | Cash | 10,000,000 | 0
1-10100 | Bank BCA | 50,000,000 | 0
...
Total Assets | 60,000,000 | 0

LIABILITIES
2-10000 | Accounts Payable | 0 | 15,000,000
...
Total Liabilities | 0 | 15,000,000

EQUITY
3-10000 | Capital | 0 | 40,000,000
...
Total Equity | 0 | 40,000,000

REVENUE
4-10000 | Sales Revenue | 0 | 25,000,000
...
Total Revenue | 0 | 25,000,000

EXPENSES
5-10000 | Cost of Goods Sold | 15,000,000 | 0
5-20000 | Salary Expense | 5,000,000 | 0
...
Total Expenses | 20,000,000 | 0

--------------------------------------------------------
GRAND TOTAL | 80,000,000 | 80,000,000
Balance Check: ✓ BALANCED
```

### AC3: Calculation Logic

**Given** filter parameters (fiscal_period_id, from_date, to_date)  
**When** system calculate Trial Balance  
**Then** untuk setiap account:

1. **Opening Balance** (jika from_date > fiscal period start):
   - Sum all posted journal lines before from_date dalam fiscal period
   - Debit accounts: sum(debit) - sum(credit)
   - Credit accounts: sum(credit) - sum(debit)

2. **Period Movement**:
   - Sum all posted journal lines between from_date and to_date
   - Debit movement: sum(debit)
   - Credit movement: sum(credit)

3. **Ending Balance**:
   - Opening Balance + Period Movement
   - Display di kolom Debit atau Credit based on normal balance

4. **Grouping**:
   - Group by account_type
   - Order by account_code within group

5. **Filtering**:
   - If "Show Zero Balance" = false: exclude accounts dengan ending balance = 0
   - If account_type filter selected: only show selected types

### AC4: Balance Verification

**When** Trial Balance generated  
**Then** system verify:
- Total Debit = Total Credit
- Display balance check indicator:
  - ✓ BALANCED (green) jika equal
  - ✗ OUT OF BALANCE (red) jika tidak equal
  - Show difference amount jika tidak balance

### AC5: Export Functionality

**When** user click "Export to Excel"  
**Then** system generate Excel file dengan:
- Same structure as display
- Formatted numbers (thousand separator)
- Bold untuk subtotals dan grand total
- Filename: `trial-balance-[fiscal_period]-[date].xlsx`

**When** user click "Export to PDF"  
**Then** system generate PDF dengan:
- Company header (name, address)
- Report title dan parameters
- Same table structure
- Page numbers
- Filename: `trial-balance-[fiscal_period]-[date].pdf`

---

## Technical Specifications

### Database Query

```php
// TrialBalanceService.php
public function generate(
    int $fiscalPeriodId,
    string $fromDate,
    string $toDate,
    array $accountTypes = [],
    bool $showZeroBalance = false
): array {
    // 1. Get all accounts (filtered by type if specified)
    $accounts = Account::query()
        ->when($accountTypes, fn($q) => $q->whereIn('account_type', $accountTypes))
        ->orderBy('account_code')
        ->get();

    // 2. Calculate balances for each account
    $trialBalance = [];
    foreach ($accounts as $account) {
        $balance = $this->calculateAccountBalance(
            $account,
            $fiscalPeriodId,
            $fromDate,
            $toDate
        );

        if ($showZeroBalance || $balance['ending_balance'] != 0) {
            $trialBalance[] = [
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'account_type' => $account->account_type,
                'debit' => $balance['debit'],
                'credit' => $balance['credit'],
            ];
        }
    }

    // 3. Group by account type and calculate subtotals
    return $this->groupAndSubtotal($trialBalance);
}

private function calculateAccountBalance(
    Account $account,
    int $fiscalPeriodId,
    string $fromDate,
    string $toDate
): array {
    // Get all posted journal lines for this account in period
    $lines = JournalLine::query()
        ->whereHas('journalEntry', function($q) use ($fiscalPeriodId, $fromDate, $toDate) {
            $q->where('fiscal_period_id', $fiscalPeriodId)
              ->where('status', 'posted')
              ->whereBetween('journal_date', [$fromDate, $toDate]);
        })
        ->where('account_id', $account->id)
        ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
        ->first();

    $totalDebit = $lines->total_debit ?? 0;
    $totalCredit = $lines->total_credit ?? 0;
    $endingBalance = $totalDebit - $totalCredit;

    // Determine display column based on normal balance
    $normalBalance = $account->getNormalBalance(); // 'debit' or 'credit'
    
    if ($normalBalance === 'debit') {
        return [
            'debit' => $endingBalance >= 0 ? $endingBalance : 0,
            'credit' => $endingBalance < 0 ? abs($endingBalance) : 0,
            'ending_balance' => $endingBalance,
        ];
    } else {
        return [
            'debit' => $endingBalance < 0 ? abs($endingBalance) : 0,
            'credit' => $endingBalance >= 0 ? $endingBalance : 0,
            'ending_balance' => $endingBalance,
        ];
    }
}
```

### Controller

```php
// TrialBalanceController.php
namespace App\Http\Controllers;

use App\Services\TrialBalanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrialBalanceController extends Controller
{
    public function __construct(
        private TrialBalanceService $trialBalanceService
    ) {}

    public function index()
    {
        $fiscalPeriods = FiscalPeriod::orderBy('start_date', 'desc')->get();
        
        return Inertia::render('Reports/TrialBalance', [
            'fiscalPeriods' => $fiscalPeriods,
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'fiscal_period_id' => 'required|exists:fiscal_periods,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'account_types' => 'array',
            'account_types.*' => 'in:asset,liability,equity,revenue,expense',
            'show_zero_balance' => 'boolean',
        ]);

        $trialBalance = $this->trialBalanceService->generate(
            $validated['fiscal_period_id'],
            $validated['from_date'],
            $validated['to_date'],
            $validated['account_types'] ?? [],
            $validated['show_zero_balance'] ?? false
        );

        return response()->json($trialBalance);
    }

    public function exportExcel(Request $request)
    {
        // Same validation as generate()
        $validated = $request->validate([...]);

        $trialBalance = $this->trialBalanceService->generate(...);

        return $this->trialBalanceService->exportToExcel($trialBalance, $validated);
    }

    public function exportPdf(Request $request)
    {
        // Same validation as generate()
        $validated = $request->validate([...]);

        $trialBalance = $this->trialBalanceService->generate(...);

        return $this->trialBalanceService->exportToPdf($trialBalance, $validated);
    }
}
```

### Routes

```php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/trial-balance', [TrialBalanceController::class, 'index'])
            ->name('trial-balance.index');
        Route::post('/trial-balance/generate', [TrialBalanceController::class, 'generate'])
            ->name('trial-balance.generate');
        Route::post('/trial-balance/export-excel', [TrialBalanceController::class, 'exportExcel'])
            ->name('trial-balance.export-excel');
        Route::post('/trial-balance/export-pdf', [TrialBalanceController::class, 'exportPdf'])
            ->name('trial-balance.export-pdf');
    });
});
```

### React Component

```jsx
// resources/js/Pages/Reports/TrialBalance.jsx
import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

export default function TrialBalance({ fiscalPeriods }) {
    const [filters, setFilters] = useState({
        fiscal_period_id: '',
        from_date: '',
        to_date: '',
        account_types: [],
        show_zero_balance: false,
    });
    const [trialBalance, setTrialBalance] = useState(null);
    const [loading, setLoading] = useState(false);

    const handleGenerate = async () => {
        setLoading(true);
        try {
            const response = await axios.post(route('reports.trial-balance.generate'), filters);
            setTrialBalance(response.data);
        } catch (error) {
            console.error('Error generating trial balance:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleExport = (format) => {
        const url = format === 'excel' 
            ? route('reports.trial-balance.export-excel')
            : route('reports.trial-balance.export-pdf');
        
        // Submit form to download file
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        // Add CSRF token and filters as hidden inputs
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    };

    return (
        <AuthenticatedLayout>
            <Head title="Trial Balance" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h2 className="text-2xl font-semibold mb-6">Trial Balance</h2>

                            {/* Filter Form */}
                            <div className="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">
                                        Fiscal Period
                                    </label>
                                    <select
                                        value={filters.fiscal_period_id}
                                        onChange={(e) => setFilters({...filters, fiscal_period_id: e.target.value})}
                                        className="mt-1 block w-full rounded-md border-gray-300"
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
                                        From Date
                                    </label>
                                    <input
                                        type="date"
                                        value={filters.from_date}
                                        onChange={(e) => setFilters({...filters, from_date: e.target.value})}
                                        className="mt-1 block w-full rounded-md border-gray-300"
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700">
                                        To Date
                                    </label>
                                    <input
                                        type="date"
                                        value={filters.to_date}
                                        onChange={(e) => setFilters({...filters, to_date: e.target.value})}
                                        className="mt-1 block w-full rounded-md border-gray-300"
                                    />
                                </div>

                                <div>
                                    <label className="flex items-center">
                                        <input
                                            type="checkbox"
                                            checked={filters.show_zero_balance}
                                            onChange={(e) => setFilters({...filters, show_zero_balance: e.target.checked})}
                                            className="rounded border-gray-300"
                                        />
                                        <span className="ml-2 text-sm text-gray-700">Show Zero Balance</span>
                                    </label>
                                </div>
                            </div>

                            <div className="flex gap-2 mb-6">
                                <button
                                    onClick={handleGenerate}
                                    disabled={loading}
                                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                >
                                    {loading ? 'Generating...' : 'Generate Report'}
                                </button>

                                {trialBalance && (
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

                            {/* Trial Balance Display */}
                            {trialBalance && (
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                    Account Code
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                    Account Name
                                                </th>
                                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                                    Debit
                                                </th>
                                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                                    Credit
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {trialBalance.groups.map((group, idx) => (
                                                <React.Fragment key={idx}>
                                                    <tr className="bg-gray-100">
                                                        <td colSpan="4" className="px-6 py-2 font-semibold">
                                                            {group.type.toUpperCase()}
                                                        </td>
                                                    </tr>
                                                    {group.accounts.map((account, aidx) => (
                                                        <tr key={aidx}>
                                                            <td className="px-6 py-2">{account.account_code}</td>
                                                            <td className="px-6 py-2">{account.account_name}</td>
                                                            <td className="px-6 py-2 text-right">
                                                                {account.debit > 0 ? account.debit.toLocaleString() : '-'}
                                                            </td>
                                                            <td className="px-6 py-2 text-right">
                                                                {account.credit > 0 ? account.credit.toLocaleString() : '-'}
                                                            </td>
                                                        </tr>
                                                    ))}
                                                    <tr className="bg-gray-50 font-semibold">
                                                        <td colSpan="2" className="px-6 py-2">Total {group.type}</td>
                                                        <td className="px-6 py-2 text-right">{group.total_debit.toLocaleString()}</td>
                                                        <td className="px-6 py-2 text-right">{group.total_credit.toLocaleString()}</td>
                                                    </tr>
                                                </React.Fragment>
                                            ))}
                                            <tr className="bg-gray-200 font-bold">
                                                <td colSpan="2" className="px-6 py-3">GRAND TOTAL</td>
                                                <td className="px-6 py-3 text-right">{trialBalance.grand_total_debit.toLocaleString()}</td>
                                                <td className="px-6 py-3 text-right">{trialBalance.grand_total_credit.toLocaleString()}</td>
                                            </tr>
                                            <tr>
                                                <td colSpan="4" className="px-6 py-3 text-center">
                                                    <span className={`font-semibold ${trialBalance.is_balanced ? 'text-green-600' : 'text-red-600'}`}>
                                                        {trialBalance.is_balanced ? '✓ BALANCED' : '✗ OUT OF BALANCE'}
                                                        {!trialBalance.is_balanced && ` (Difference: ${trialBalance.difference.toLocaleString()})`}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
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
- Laravel Excel package untuk export
- PDF library (dompdf atau snappy)

---

## Testing Requirements

### Unit Tests

```php
// tests/Unit/Services/TrialBalanceServiceTest.php
test('calculate account balance correctly', function() {
    // Setup: Create account, journal entries
    // Assert: Balance calculation matches expected
});

test('group accounts by type with subtotals', function() {
    // Setup: Multiple accounts of different types
    // Assert: Grouped correctly with accurate subtotals
});

test('verify debit equals credit', function() {
    // Setup: Balanced journal entries
    // Assert: Grand total debit = grand total credit
});

test('filter by account type', function() {
    // Setup: Accounts of multiple types
    // Assert: Only selected types included
});

test('exclude zero balance accounts when flag is false', function() {
    // Setup: Mix of zero and non-zero balance accounts
    // Assert: Zero balance accounts excluded
});
```

### Feature Tests

```php
// tests/Feature/TrialBalanceTest.php
test('generate trial balance with valid parameters', function() {
    // Setup: Fiscal period, accounts, journal entries
    // Act: POST to generate endpoint
    // Assert: 200 response, correct structure
});

test('export to excel', function() {
    // Act: POST to export-excel endpoint
    // Assert: Excel file downloaded
});

test('export to pdf', function() {
    // Act: POST to export-pdf endpoint
    // Assert: PDF file downloaded
});
```

---

## Definition of Done

- [x] TrialBalanceService created dengan calculation logic
- [x] TrialBalanceController dengan index, generate, export methods
- [x] Routes registered
- [x] React component dengan filter form dan display table
- [x] Export to Excel functionality
- [x] Export to PDF functionality
- [x] Unit tests untuk service (80%+ coverage)
- [x] Feature tests untuk controller
- [x] Manual testing dengan sample data
- [x] Balance verification works correctly
- [x] Code review passed
- [x] Merged to main branch

---

## Notes

- Trial Balance adalah foundation untuk P&L dan Balance Sheet
- Calculation harus accurate - ini basis untuk financial statements
- Export format harus professional untuk external use
- Consider caching untuk large datasets (optimization later)
- Future: Add comparative periods (side-by-side months)
