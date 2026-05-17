# Story 6.4: Balance Sheet

**Epic:** 6 - Financial Reporting  
**Story ID:** 6.4  
**Story Key:** 6-4-balance-sheet  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** High

---

## User Story

**Sebagai** Finance Manager  
**Saya ingin** generate Balance Sheet untuk tanggal tertentu  
**Sehingga** saya dapat melihat financial position perusahaan (assets, liabilities, equity)

---

## Business Context

Balance Sheet adalah fundamental financial statement:
- **Financial Position**: Snapshot of company's financial health pada tanggal tertentu
- **Accounting Equation**: Assets = Liabilities + Equity (harus balance)
- **Liquidity Analysis**: Current assets vs current liabilities
- **Solvency Analysis**: Total assets vs total liabilities

Balance Sheet Structure:
```
ASSETS
  Current Assets
    - Cash
    - Accounts Receivable
    - Inventory
  = Total Current Assets

  Non-Current Assets
    - Fixed Assets
    - Accumulated Depreciation
    - Intangible Assets
  = Total Non-Current Assets

= TOTAL ASSETS

LIABILITIES
  Current Liabilities
    - Accounts Payable
    - Short-term Debt
  = Total Current Liabilities

  Non-Current Liabilities
    - Long-term Debt
  = Total Non-Current Liabilities

= TOTAL LIABILITIES

EQUITY
  - Capital
  - Retained Earnings
  - Current Year Profit/Loss
= TOTAL EQUITY

TOTAL LIABILITIES + EQUITY
```

---

## Acceptance Criteria

### AC1: Balance Sheet Filter Form

**Given** user adalah Finance Manager  
**When** user mengakses halaman Balance Sheet  
**Then** user melihat filter form dengan:
- As of Date (date picker - required)
- Fiscal Period (dropdown - required)
- Comparison Date (optional - untuk comparative balance sheet)
- Detail Level (Summary / Detailed)
- Generate Report button

### AC2: Balance Sheet Display Structure

**When** user click "Generate Report"  
**Then** system menampilkan Balance Sheet dengan struktur:

```
BALANCE SHEET
As of: [as_of_date]

ASSETS
CURRENT ASSETS
  Cash                                    10,000,000
  Bank BCA                                50,000,000
  Accounts Receivable                     20,000,000
  Inventory                               15,000,000
                                        -------------
TOTAL CURRENT ASSETS                      95,000,000

NON-CURRENT ASSETS
  Fixed Assets                            80,000,000
  Accumulated Depreciation               (20,000,000)
  Intangible Assets                        5,000,000
                                        -------------
TOTAL NON-CURRENT ASSETS                  65,000,000

TOTAL ASSETS                             160,000,000

LIABILITIES
CURRENT LIABILITIES
  Accounts Payable                        15,000,000
  Short-term Debt                         10,000,000
  Accrued Expenses                         5,000,000
                                        -------------
TOTAL CURRENT LIABILITIES                 30,000,000

NON-CURRENT LIABILITIES
  Long-term Debt                          40,000,000
                                        -------------
TOTAL NON-CURRENT LIABILITIES             40,000,000

TOTAL LIABILITIES                         70,000,000

EQUITY
  Capital                                 40,000,000
  Retained Earnings                       45,000,000
  Current Year Profit/Loss                 5,000,000
                                        -------------
TOTAL EQUITY                              90,000,000

TOTAL LIABILITIES + EQUITY               160,000,000

Balance Check: ✓ BALANCED
Current Ratio: 3.17
Debt to Equity Ratio: 0.78
```

### AC3: Calculation Logic

**Given** filter parameters (as_of_date, fiscal_period_id)  
**When** system calculate Balance Sheet  
**Then**:

1. **Asset Accounts** (account_type = 'asset'):
   - Sum all posted journal lines up to as_of_date
   - Debit balance = sum(debit) - sum(credit)
   - Group by subcategory (Current / Non-Current)
   - Calculate subtotals

2. **Liability Accounts** (account_type = 'liability'):
   - Sum all posted journal lines up to as_of_date
   - Credit balance = sum(credit) - sum(debit)
   - Group by subcategory (Current / Non-Current)
   - Calculate subtotals

3. **Equity Accounts** (account_type = 'equity'):
   - Sum all posted journal lines up to as_of_date
   - Credit balance = sum(credit) - sum(debit)
   - Include:
     - Capital accounts
     - Retained Earnings
     - Current Year Profit/Loss (from P&L)

4. **Current Year Profit/Loss**:
   - Calculate P&L from fiscal period start to as_of_date
   - Add to Equity section

5. **Balance Verification**:
   - Total Assets = Total Liabilities + Total Equity
   - Display balance check indicator

6. **Financial Ratios**:
   - Current Ratio = Current Assets / Current Liabilities
   - Debt to Equity Ratio = Total Liabilities / Total Equity
   - Working Capital = Current Assets - Current Liabilities

### AC4: Detailed vs Summary View

**When** user select "Detail Level = Detailed"  
**Then** show all individual accounts dengan balances

**When** user select "Detail Level = Summary"  
**Then** show only category subtotals

### AC5: Comparative Balance Sheet

**When** user select Comparison Date  
**Then** display side-by-side comparison:

```
                                Current Date    Previous Date    Change      Change %
ASSETS
CURRENT ASSETS
  Cash                            10,000,000        8,000,000    2,000,000      25.0%
  ...
TOTAL CURRENT ASSETS              95,000,000       85,000,000   10,000,000      11.8%
...
TOTAL ASSETS                     160,000,000      150,000,000   10,000,000       6.7%
```

### AC6: Export Functionality

**When** user click "Export to Excel"  
**Then** system generate Excel file dengan:
- Company header
- Balance Sheet structure dengan formatting
- Subtotals bold
- Negative amounts in parentheses
- Financial ratios
- Filename: `balance-sheet-[as_of_date].xlsx`

**When** user click "Export to PDF"  
**Then** system generate PDF dengan professional layout

---

## Technical Specifications

### Database Query

```php
// BalanceSheetService.php
namespace App\Services;

use App\Models\Account;
use App\Models\JournalLine;
use App\Models\FiscalPeriod;
use App\Services\ProfitLossService;

class BalanceSheetService
{
    public function __construct(
        private ProfitLossService $profitLossService
    ) {}

    public function generate(
        string $asOfDate,
        int $fiscalPeriodId,
        ?string $comparisonDate = null,
        string $detailLevel = 'summary'
    ): array {
        $currentDate = $this->calculateBalanceSheet($asOfDate, $fiscalPeriodId);
        
        $comparisonDate = null;
        if ($comparisonDate) {
            $comparisonDate = $this->calculateBalanceSheet($comparisonDate, $fiscalPeriodId);
        }

        return [
            'current_date' => $currentDate,
            'comparison_date' => $comparisonDate,
            'detail_level' => $detailLevel,
        ];
    }

    private function calculateBalanceSheet(string $asOfDate, int $fiscalPeriodId): array
    {
        // 1. Get Assets
        $assets = $this->getAssets($asOfDate, $fiscalPeriodId);
        $currentAssets = array_filter($assets, fn($a) => $a['subcategory'] === 'Current');
        $nonCurrentAssets = array_filter($assets, fn($a) => $a['subcategory'] === 'Non-Current');
        
        $totalCurrentAssets = array_sum(array_column($currentAssets, 'balance'));
        $totalNonCurrentAssets = array_sum(array_column($nonCurrentAssets, 'balance'));
        $totalAssets = $totalCurrentAssets + $totalNonCurrentAssets;

        // 2. Get Liabilities
        $liabilities = $this->getLiabilities($asOfDate, $fiscalPeriodId);
        $currentLiabilities = array_filter($liabilities, fn($l) => $l['subcategory'] === 'Current');
        $nonCurrentLiabilities = array_filter($liabilities, fn($l) => $l['subcategory'] === 'Non-Current');
        
        $totalCurrentLiabilities = array_sum(array_column($currentLiabilities, 'balance'));
        $totalNonCurrentLiabilities = array_sum(array_column($nonCurrentLiabilities, 'balance'));
        $totalLiabilities = $totalCurrentLiabilities + $totalNonCurrentLiabilities;

        // 3. Get Equity
        $equity = $this->getEquity($asOfDate, $fiscalPeriodId);
        
        // 4. Calculate Current Year Profit/Loss
        $fiscalPeriod = FiscalPeriod::findOrFail($fiscalPeriodId);
        $profitLoss = $this->profitLossService->generate(
            $fiscalPeriodId,
            $fiscalPeriod->start_date,
            $asOfDate
        );
        $currentYearProfitLoss = $profitLoss['current_period']['net_profit_after_tax'];
        
        $totalEquity = array_sum(array_column($equity, 'balance')) + $currentYearProfitLoss;

        // 5. Calculate Financial Ratios
        $currentRatio = $totalCurrentLiabilities > 0 
            ? $totalCurrentAssets / $totalCurrentLiabilities 
            : 0;
        
        $debtToEquityRatio = $totalEquity > 0 
            ? $totalLiabilities / $totalEquity 
            : 0;
        
        $workingCapital = $totalCurrentAssets - $totalCurrentLiabilities;

        // 6. Balance Check
        $totalLiabilitiesEquity = $totalLiabilities + $totalEquity;
        $isBalanced = abs($totalAssets - $totalLiabilitiesEquity) < 0.01; // Allow for rounding

        return [
            'as_of_date' => $asOfDate,
            'assets' => [
                'current' => array_values($currentAssets),
                'non_current' => array_values($nonCurrentAssets),
                'total_current' => $totalCurrentAssets,
                'total_non_current' => $totalNonCurrentAssets,
                'total' => $totalAssets,
            ],
            'liabilities' => [
                'current' => array_values($currentLiabilities),
                'non_current' => array_values($nonCurrentLiabilities),
                'total_current' => $totalCurrentLiabilities,
                'total_non_current' => $totalNonCurrentLiabilities,
                'total' => $totalLiabilities,
            ],
            'equity' => [
                'accounts' => $equity,
                'current_year_profit_loss' => $currentYearProfitLoss,
                'total' => $totalEquity,
            ],
            'total_liabilities_equity' => $totalLiabilitiesEquity,
            'is_balanced' => $isBalanced,
            'difference' => $totalAssets - $totalLiabilitiesEquity,
            'ratios' => [
                'current_ratio' => $currentRatio,
                'debt_to_equity_ratio' => $debtToEquityRatio,
                'working_capital' => $workingCapital,
            ],
        ];
    }

    private function getAssets(string $asOfDate, int $fiscalPeriodId): array
    {
        return Account::where('account_type', 'asset')
            ->with(['journalLines' => function($q) use ($asOfDate, $fiscalPeriodId) {
                $q->whereHas('journalEntry', function($jq) use ($asOfDate, $fiscalPeriodId) {
                    $jq->where('fiscal_period_id', $fiscalPeriodId)
                       ->where('status', 'posted')
                       ->where('journal_date', '<=', $asOfDate);
                });
            }])
            ->get()
            ->map(function($account) {
                $balance = $account->journalLines->sum('debit') - $account->journalLines->sum('credit');
                return [
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'subcategory' => $account->subcategory ?? 'Current',
                    'balance' => $balance,
                ];
            })
            ->filter(fn($item) => $item['balance'] != 0)
            ->values()
            ->toArray();
    }

    private function getLiabilities(string $asOfDate, int $fiscalPeriodId): array
    {
        return Account::where('account_type', 'liability')
            ->with(['journalLines' => function($q) use ($asOfDate, $fiscalPeriodId) {
                $q->whereHas('journalEntry', function($jq) use ($asOfDate, $fiscalPeriodId) {
                    $jq->where('fiscal_period_id', $fiscalPeriodId)
                       ->where('status', 'posted')
                       ->where('journal_date', '<=', $asOfDate);
                });
            }])
            ->get()
            ->map(function($account) {
                $balance = $account->journalLines->sum('credit') - $account->journalLines->sum('debit');
                return [
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'subcategory' => $account->subcategory ?? 'Current',
                    'balance' => $balance,
                ];
            })
            ->filter(fn($item) => $item['balance'] != 0)
            ->values()
            ->toArray();
    }

    private function getEquity(string $asOfDate, int $fiscalPeriodId): array
    {
        return Account::where('account_type', 'equity')
            ->with(['journalLines' => function($q) use ($asOfDate, $fiscalPeriodId) {
                $q->whereHas('journalEntry', function($jq) use ($asOfDate, $fiscalPeriodId) {
                    $jq->where('fiscal_period_id', $fiscalPeriodId)
                       ->where('status', 'posted')
                       ->where('journal_date', '<=', $asOfDate);
                });
            }])
            ->get()
            ->map(function($account) {
                $balance = $account->journalLines->sum('credit') - $account->journalLines->sum('debit');
                return [
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'balance' => $balance,
                ];
            })
            ->filter(fn($item) => $item['balance'] != 0)
            ->values()
            ->toArray();
    }

    public function exportToExcel(array $balanceSheet, array $params): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // Implementation using Laravel Excel
    }

    public function exportToPdf(array $balanceSheet, array $params): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // Implementation using dompdf or snappy
    }
}
```

### Controller

```php
// BalanceSheetController.php
namespace App\Http\Controllers;

use App\Services\BalanceSheetService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BalanceSheetController extends Controller
{
    public function __construct(
        private BalanceSheetService $balanceSheetService
    ) {}

    public function index()
    {
        $fiscalPeriods = FiscalPeriod::orderBy('start_date', 'desc')->get();
        
        return Inertia::render('Reports/BalanceSheet', [
            'fiscalPeriods' => $fiscalPeriods,
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'as_of_date' => 'required|date',
            'fiscal_period_id' => 'required|exists:fiscal_periods,id',
            'comparison_date' => 'nullable|date',
            'detail_level' => 'in:summary,detailed',
        ]);

        $balanceSheet = $this->balanceSheetService->generate(
            $validated['as_of_date'],
            $validated['fiscal_period_id'],
            $validated['comparison_date'] ?? null,
            $validated['detail_level'] ?? 'summary'
        );

        return response()->json($balanceSheet);
    }

    public function exportExcel(Request $request)
    {
        $validated = $request->validate([...]);
        $balanceSheet = $this->balanceSheetService->generate(...);
        return $this->balanceSheetService->exportToExcel($balanceSheet, $validated);
    }

    public function exportPdf(Request $request)
    {
        $validated = $request->validate([...]);
        $balanceSheet = $this->balanceSheetService->generate(...);
        return $this->balanceSheetService->exportToPdf($balanceSheet, $validated);
    }
}
```

### Routes

```php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/balance-sheet', [BalanceSheetController::class, 'index'])
            ->name('balance-sheet.index');
        Route::post('/balance-sheet/generate', [BalanceSheetController::class, 'generate'])
            ->name('balance-sheet.generate');
        Route::post('/balance-sheet/export-excel', [BalanceSheetController::class, 'exportExcel'])
            ->name('balance-sheet.export-excel');
        Route::post('/balance-sheet/export-pdf', [BalanceSheetController::class, 'exportPdf'])
            ->name('balance-sheet.export-pdf');
    });
});
```

---

## Dependencies

- Epic 4 (Chart of Accounts) - DONE ✅
- Epic 5 (Journal Entry & Posting) - DONE ✅
- Story 6.3 (P&L Statement) - for Current Year Profit/Loss
- Account model needs `subcategory` field (Current / Non-Current)
- Laravel Excel package
- PDF library

---

## Testing Requirements

### Unit Tests

```php
// tests/Unit/Services/BalanceSheetServiceTest.php
test('calculate asset balances correctly', function() {
    // Setup: Asset accounts dengan journal lines
    // Assert: Balances calculated correctly
});

test('calculate liability balances correctly', function() {
    // Setup: Liability accounts dengan journal lines
    // Assert: Balances calculated correctly
});

test('calculate equity balances correctly', function() {
    // Setup: Equity accounts + current year P&L
    // Assert: Total equity includes current year profit/loss
});

test('verify accounting equation balance', function() {
    // Setup: Complete balance sheet data
    // Assert: Assets = Liabilities + Equity
});

test('calculate financial ratios correctly', function() {
    // Setup: Balance sheet data
    // Assert: Current ratio, debt to equity ratio calculated correctly
});
```

---

## Definition of Done

- [x] BalanceSheetService created
- [x] BalanceSheetController dengan methods
- [x] Routes registered
- [x] React component dengan filter dan display
- [x] Summary vs Detailed view
- [x] Comparative Balance Sheet (optional)
- [x] Financial ratios calculation
- [x] Balance verification
- [x] Export to Excel
- [x] Export to PDF
- [x] Unit tests (80%+ coverage)
- [x] Feature tests
- [x] Manual testing
- [x] Code review passed
- [x] Merged to main

---

## Notes

- Balance Sheet adalah snapshot pada tanggal tertentu (vs P&L yang periode)
- Accounting equation MUST balance - critical validation
- Financial ratios provide quick health indicators
- Current Year P&L integration critical untuk accurate equity
- Consider adding trend analysis (multiple periods)
- Future: Add cash flow statement to complete financial statements
