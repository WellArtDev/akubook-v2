# Story 6.3: Profit & Loss Statement

**Epic:** 6 - Financial Reporting  
**Story ID:** 6.3  
**Story Key:** 6-3-profit-loss-statement  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** High

---

## User Story

**Sebagai** Finance Manager  
**Saya ingin** generate Profit & Loss Statement untuk periode tertentu  
**Sehingga** saya dapat melihat revenue, expenses, dan net profit/loss perusahaan

---

## Business Context

Profit & Loss (P&L) Statement adalah core financial report:
- **Performance Measurement**: Menampilkan profitability dalam periode
- **Revenue vs Expenses**: Breakdown semua income dan costs
- **Net Profit/Loss**: Bottom line untuk decision making
- **Comparative Analysis**: Compare dengan periode sebelumnya

P&L Structure:
```
Revenue
  - Sales Revenue
  - Other Income
= Total Revenue

Cost of Goods Sold (COGS)
= Gross Profit

Operating Expenses
  - Salary Expense
  - Rent Expense
  - Utilities
  - etc.
= Operating Profit

Other Income/Expenses
  - Interest Income
  - Interest Expense
= Net Profit Before Tax

Tax Expense
= Net Profit After Tax
```

---

## Acceptance Criteria

### AC1: P&L Filter Form

**Given** user adalah Finance Manager  
**When** user mengakses halaman P&L Statement  
**Then** user melihat filter form dengan:
- Fiscal Period (dropdown - required)
- Date Range (from_date, to_date - required)
- Comparison Period (optional - untuk comparative P&L)
- Detail Level (Summary / Detailed)
- Generate Report button

### AC2: P&L Display Structure

**When** user click "Generate Report"  
**Then** system menampilkan P&L dengan struktur:

```
PROFIT & LOSS STATEMENT
Period: [Fiscal Period Name]
Date Range: [from_date] to [to_date]

REVENUE
  Sales Revenue                           25,000,000
  Service Revenue                          5,000,000
  Other Income                             1,000,000
                                        -------------
TOTAL REVENUE                             31,000,000

COST OF GOODS SOLD
  Cost of Goods Sold                      15,000,000
                                        -------------
GROSS PROFIT                              16,000,000
Gross Profit Margin: 51.6%

OPERATING EXPENSES
  Salary Expense                           5,000,000
  Rent Expense                             2,000,000
  Utilities Expense                          500,000
  Marketing Expense                        1,000,000
  Depreciation Expense                       500,000
                                        -------------
TOTAL OPERATING EXPENSES                   9,000,000

OPERATING PROFIT                           7,000,000
Operating Profit Margin: 22.6%

OTHER INCOME/(EXPENSES)
  Interest Income                            200,000
  Interest Expense                          (300,000)
  Foreign Exchange Gain/(Loss)               100,000
                                        -------------
TOTAL OTHER INCOME/(EXPENSES)                  0

NET PROFIT BEFORE TAX                      7,000,000

TAX EXPENSE
  Income Tax Expense                       1,750,000
                                        -------------
NET PROFIT AFTER TAX                       5,250,000
Net Profit Margin: 16.9%
```

### AC3: Calculation Logic

**Given** filter parameters (fiscal_period_id, from_date, to_date)  
**When** system calculate P&L  
**Then**:

1. **Revenue Accounts** (account_type = 'revenue'):
   - Sum all posted journal lines (credit - debit) dalam period
   - Group by account category (Sales, Service, Other)
   - Calculate Total Revenue

2. **COGS Accounts** (account_type = 'expense', category = 'COGS'):
   - Sum all posted journal lines (debit - credit) dalam period
   - Calculate Gross Profit = Total Revenue - COGS
   - Calculate Gross Profit Margin = (Gross Profit / Total Revenue) * 100%

3. **Operating Expense Accounts** (account_type = 'expense', category = 'Operating'):
   - Sum all posted journal lines (debit - credit) dalam period
   - Group by subcategory
   - Calculate Operating Profit = Gross Profit - Operating Expenses
   - Calculate Operating Profit Margin = (Operating Profit / Total Revenue) * 100%

4. **Other Income/Expenses** (account_type = 'expense', category = 'Other'):
   - Sum all posted journal lines
   - Income: credit - debit (positive)
   - Expense: debit - credit (negative, show in parentheses)

5. **Tax Expense**:
   - Sum all posted journal lines untuk tax accounts
   - Calculate Net Profit After Tax

6. **Net Profit Margin** = (Net Profit After Tax / Total Revenue) * 100%

### AC4: Detailed vs Summary View

**When** user select "Detail Level = Detailed"  
**Then** show all individual accounts dengan amounts

**When** user select "Detail Level = Summary"  
**Then** show only category subtotals (hide individual accounts)

### AC5: Comparative P&L

**When** user select Comparison Period  
**Then** display side-by-side comparison:

```
                                Current Period    Previous Period    Variance    Variance %
REVENUE
  Sales Revenue                    25,000,000         20,000,000    5,000,000        25.0%
  ...
TOTAL REVENUE                      31,000,000         28,000,000    3,000,000        10.7%
...
NET PROFIT AFTER TAX                5,250,000          4,200,000    1,050,000        25.0%
```

### AC6: Export Functionality

**When** user click "Export to Excel"  
**Then** system generate Excel file dengan:
- Company header
- P&L structure dengan formatting
- Subtotals bold
- Negative amounts in parentheses
- Filename: `profit-loss-[fiscal_period]-[date].xlsx`

**When** user click "Export to PDF"  
**Then** system generate PDF dengan professional layout

---

## Technical Specifications

### Database Query

```php
// ProfitLossService.php
namespace App\Services;

use App\Models\Account;
use App\Models\JournalLine;
use App\Models\FiscalPeriod;

class ProfitLossService
{
    public function generate(
        int $fiscalPeriodId,
        string $fromDate,
        string $toDate,
        ?int $comparisonPeriodId = null,
        string $detailLevel = 'summary'
    ): array {
        $currentPeriod = $this->calculatePeriod($fiscalPeriodId, $fromDate, $toDate);
        
        $comparisonPeriod = null;
        if ($comparisonPeriodId) {
            $fiscalPeriod = FiscalPeriod::findOrFail($comparisonPeriodId);
            $comparisonPeriod = $this->calculatePeriod(
                $comparisonPeriodId,
                $fiscalPeriod->start_date,
                $fiscalPeriod->end_date
            );
        }

        return [
            'current_period' => $currentPeriod,
            'comparison_period' => $comparisonPeriod,
            'detail_level' => $detailLevel,
        ];
    }

    private function calculatePeriod(int $fiscalPeriodId, string $fromDate, string $toDate): array
    {
        // 1. Get Revenue
        $revenue = $this->getRevenue($fiscalPeriodId, $fromDate, $toDate);
        $totalRevenue = array_sum(array_column($revenue, 'amount'));

        // 2. Get COGS
        $cogs = $this->getCOGS($fiscalPeriodId, $fromDate, $toDate);
        $totalCOGS = array_sum(array_column($cogs, 'amount'));

        // 3. Calculate Gross Profit
        $grossProfit = $totalRevenue - $totalCOGS;
        $grossProfitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        // 4. Get Operating Expenses
        $operatingExpenses = $this->getOperatingExpenses($fiscalPeriodId, $fromDate, $toDate);
        $totalOperatingExpenses = array_sum(array_column($operatingExpenses, 'amount'));

        // 5. Calculate Operating Profit
        $operatingProfit = $grossProfit - $totalOperatingExpenses;
        $operatingProfitMargin = $totalRevenue > 0 ? ($operatingProfit / $totalRevenue) * 100 : 0;

        // 6. Get Other Income/Expenses
        $otherIncomeExpenses = $this->getOtherIncomeExpenses($fiscalPeriodId, $fromDate, $toDate);
        $totalOtherIncomeExpenses = array_sum(array_column($otherIncomeExpenses, 'amount'));

        // 7. Calculate Net Profit Before Tax
        $netProfitBeforeTax = $operatingProfit + $totalOtherIncomeExpenses;

        // 8. Get Tax Expense
        $taxExpense = $this->getTaxExpense($fiscalPeriodId, $fromDate, $toDate);
        $totalTaxExpense = array_sum(array_column($taxExpense, 'amount'));

        // 9. Calculate Net Profit After Tax
        $netProfitAfterTax = $netProfitBeforeTax - $totalTaxExpense;
        $netProfitMargin = $totalRevenue > 0 ? ($netProfitAfterTax / $totalRevenue) * 100 : 0;

        return [
            'revenue' => $revenue,
            'total_revenue' => $totalRevenue,
            'cogs' => $cogs,
            'total_cogs' => $totalCOGS,
            'gross_profit' => $grossProfit,
            'gross_profit_margin' => $grossProfitMargin,
            'operating_expenses' => $operatingExpenses,
            'total_operating_expenses' => $totalOperatingExpenses,
            'operating_profit' => $operatingProfit,
            'operating_profit_margin' => $operatingProfitMargin,
            'other_income_expenses' => $otherIncomeExpenses,
            'total_other_income_expenses' => $totalOtherIncomeExpenses,
            'net_profit_before_tax' => $netProfitBeforeTax,
            'tax_expense' => $taxExpense,
            'total_tax_expense' => $totalTaxExpense,
            'net_profit_after_tax' => $netProfitAfterTax,
            'net_profit_margin' => $netProfitMargin,
        ];
    }

    private function getRevenue(int $fiscalPeriodId, string $fromDate, string $toDate): array
    {
        return Account::where('account_type', 'revenue')
            ->with(['journalLines' => function($q) use ($fiscalPeriodId, $fromDate, $toDate) {
                $q->whereHas('journalEntry', function($jq) use ($fiscalPeriodId, $fromDate, $toDate) {
                    $jq->where('fiscal_period_id', $fiscalPeriodId)
                       ->where('status', 'posted')
                       ->whereBetween('journal_date', [$fromDate, $toDate]);
                });
            }])
            ->get()
            ->map(function($account) {
                $amount = $account->journalLines->sum('credit') - $account->journalLines->sum('debit');
                return [
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'category' => $account->category,
                    'amount' => $amount,
                ];
            })
            ->filter(fn($item) => $item['amount'] != 0)
            ->values()
            ->toArray();
    }

    private function getCOGS(int $fiscalPeriodId, string $fromDate, string $toDate): array
    {
        return Account::where('account_type', 'expense')
            ->where('category', 'COGS')
            ->with(['journalLines' => function($q) use ($fiscalPeriodId, $fromDate, $toDate) {
                $q->whereHas('journalEntry', function($jq) use ($fiscalPeriodId, $fromDate, $toDate) {
                    $jq->where('fiscal_period_id', $fiscalPeriodId)
                       ->where('status', 'posted')
                       ->whereBetween('journal_date', [$fromDate, $toDate]);
                });
            }])
            ->get()
            ->map(function($account) {
                $amount = $account->journalLines->sum('debit') - $account->journalLines->sum('credit');
                return [
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'amount' => $amount,
                ];
            })
            ->filter(fn($item) => $item['amount'] != 0)
            ->values()
            ->toArray();
    }

    private function getOperatingExpenses(int $fiscalPeriodId, string $fromDate, string $toDate): array
    {
        return Account::where('account_type', 'expense')
            ->where('category', 'Operating')
            ->with(['journalLines' => function($q) use ($fiscalPeriodId, $fromDate, $toDate) {
                $q->whereHas('journalEntry', function($jq) use ($fiscalPeriodId, $fromDate, $toDate) {
                    $jq->where('fiscal_period_id', $fiscalPeriodId)
                       ->where('status', 'posted')
                       ->whereBetween('journal_date', [$fromDate, $toDate]);
                });
            }])
            ->get()
            ->map(function($account) {
                $amount = $account->journalLines->sum('debit') - $account->journalLines->sum('credit');
                return [
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'subcategory' => $account->subcategory,
                    'amount' => $amount,
                ];
            })
            ->filter(fn($item) => $item['amount'] != 0)
            ->values()
            ->toArray();
    }

    private function getOtherIncomeExpenses(int $fiscalPeriodId, string $fromDate, string $toDate): array
    {
        return Account::where('account_type', 'expense')
            ->where('category', 'Other')
            ->orWhere(function($q) {
                $q->where('account_type', 'revenue')
                  ->where('category', 'Other');
            })
            ->with(['journalLines' => function($q) use ($fiscalPeriodId, $fromDate, $toDate) {
                $q->whereHas('journalEntry', function($jq) use ($fiscalPeriodId, $fromDate, $toDate) {
                    $jq->where('fiscal_period_id', $fiscalPeriodId)
                       ->where('status', 'posted')
                       ->whereBetween('journal_date', [$fromDate, $toDate]);
                });
            }])
            ->get()
            ->map(function($account) {
                // Income: credit - debit (positive)
                // Expense: debit - credit (negative)
                $amount = $account->journalLines->sum('credit') - $account->journalLines->sum('debit');
                return [
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'amount' => $amount,
                ];
            })
            ->filter(fn($item) => $item['amount'] != 0)
            ->values()
            ->toArray();
    }

    private function getTaxExpense(int $fiscalPeriodId, string $fromDate, string $toDate): array
    {
        return Account::where('account_type', 'expense')
            ->where('category', 'Tax')
            ->with(['journalLines' => function($q) use ($fiscalPeriodId, $fromDate, $toDate) {
                $q->whereHas('journalEntry', function($jq) use ($fiscalPeriodId, $fromDate, $toDate) {
                    $jq->where('fiscal_period_id', $fiscalPeriodId)
                       ->where('status', 'posted')
                       ->whereBetween('journal_date', [$fromDate, $toDate]);
                });
            }])
            ->get()
            ->map(function($account) {
                $amount = $account->journalLines->sum('debit') - $account->journalLines->sum('credit');
                return [
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'amount' => $amount,
                ];
            })
            ->filter(fn($item) => $item['amount'] != 0)
            ->values()
            ->toArray();
    }

    public function exportToExcel(array $profitLoss, array $params): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // Implementation using Laravel Excel
    }

    public function exportToPdf(array $profitLoss, array $params): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // Implementation using dompdf or snappy
    }
}
```

### Controller

```php
// ProfitLossController.php
namespace App\Http\Controllers;

use App\Services\ProfitLossService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfitLossController extends Controller
{
    public function __construct(
        private ProfitLossService $profitLossService
    ) {}

    public function index()
    {
        $fiscalPeriods = FiscalPeriod::orderBy('start_date', 'desc')->get();
        
        return Inertia::render('Reports/ProfitLoss', [
            'fiscalPeriods' => $fiscalPeriods,
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'fiscal_period_id' => 'required|exists:fiscal_periods,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'comparison_period_id' => 'nullable|exists:fiscal_periods,id',
            'detail_level' => 'in:summary,detailed',
        ]);

        $profitLoss = $this->profitLossService->generate(
            $validated['fiscal_period_id'],
            $validated['from_date'],
            $validated['to_date'],
            $validated['comparison_period_id'] ?? null,
            $validated['detail_level'] ?? 'summary'
        );

        return response()->json($profitLoss);
    }

    public function exportExcel(Request $request)
    {
        $validated = $request->validate([...]);
        $profitLoss = $this->profitLossService->generate(...);
        return $this->profitLossService->exportToExcel($profitLoss, $validated);
    }

    public function exportPdf(Request $request)
    {
        $validated = $request->validate([...]);
        $profitLoss = $this->profitLossService->generate(...);
        return $this->profitLossService->exportToPdf($profitLoss, $validated);
    }
}
```

### Routes

```php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/profit-loss', [ProfitLossController::class, 'index'])
            ->name('profit-loss.index');
        Route::post('/profit-loss/generate', [ProfitLossController::class, 'generate'])
            ->name('profit-loss.generate');
        Route::post('/profit-loss/export-excel', [ProfitLossController::class, 'exportExcel'])
            ->name('profit-loss.export-excel');
        Route::post('/profit-loss/export-pdf', [ProfitLossController::class, 'exportPdf'])
            ->name('profit-loss.export-pdf');
    });
});
```

---

## Dependencies

- Epic 4 (Chart of Accounts) - DONE ✅
- Epic 5 (Journal Entry & Posting) - DONE ✅
- Account model needs `category` and `subcategory` fields
- Laravel Excel package
- PDF library

---

## Testing Requirements

### Unit Tests

```php
// tests/Unit/Services/ProfitLossServiceTest.php
test('calculate revenue correctly', function() {
    // Setup: Revenue accounts dengan journal lines
    // Assert: Total revenue matches expected
});

test('calculate gross profit correctly', function() {
    // Setup: Revenue dan COGS accounts
    // Assert: Gross Profit = Revenue - COGS
});

test('calculate operating profit correctly', function() {
    // Setup: Revenue, COGS, Operating Expenses
    // Assert: Operating Profit = Gross Profit - Operating Expenses
});

test('calculate net profit after tax correctly', function() {
    // Setup: Full P&L accounts
    // Assert: Net Profit calculation correct
});

test('calculate profit margins correctly', function() {
    // Setup: Revenue dan profits
    // Assert: Margins calculated as percentages
});
```

---

## Definition of Done

- [x] ProfitLossService created
- [x] ProfitLossController dengan methods
- [x] Routes registered
- [x] React component dengan filter dan display
- [x] Summary vs Detailed view
- [x] Comparative P&L (optional)
- [x] Export to Excel
- [x] Export to PDF
- [x] Unit tests (80%+ coverage)
- [x] Feature tests
- [x] Manual testing
- [x] Code review passed
- [x] Merged to main

---

## Notes

- P&L adalah key financial statement untuk management
- Margin calculations critical untuk performance analysis
- Comparative P&L valuable untuk trend analysis
- Consider adding charts/graphs untuk visual representation
- Future: Add budget vs actual comparison
