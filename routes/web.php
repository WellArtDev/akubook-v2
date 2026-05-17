<?php

use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\ChartOfAccountsImportController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public API routes (for registration)
Route::get('/api/branches', [BranchController::class, 'index'])->name('api.branches.index');

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Component Showcase (Development Only)
    Route::get('/components', function () {
        return Inertia::render('ComponentShowcase');
    })->name('components.showcase');
});

require __DIR__.'/auth.php';

// Phase 2 - Accounting Foundation
Route::middleware(['auth', 'permission:manage-fiscal-periods'])->group(function () {
    Route::resource('fiscal-periods', App\Http\Controllers\FiscalPeriodController::class);
    Route::post('fiscal-periods/{fiscal_period}/close', [App\Http\Controllers\FiscalPeriodController::class, 'close'])
        ->name('fiscal-periods.close')
        ->middleware('permission:close-fiscal-periods');
    Route::post('fiscal-periods/{fiscal_period}/reopen', [App\Http\Controllers\FiscalPeriodController::class, 'reopen'])
        ->name('fiscal-periods.reopen')
        ->middleware('permission:reopen-fiscal-periods');
});

Route::middleware(['auth'])->prefix('migration')->name('migration.')->group(function () {
    Route::get('/chart-of-accounts', [ChartOfAccountsImportController::class, 'index'])
        ->name('chart-of-accounts.index');
    Route::post('/chart-of-accounts/preview', [ChartOfAccountsImportController::class, 'preview'])
        ->name('chart-of-accounts.preview');
    Route::post('/chart-of-accounts/import', [ChartOfAccountsImportController::class, 'import'])
        ->name('chart-of-accounts.import');
});

Route::middleware(['auth', 'permission:manage-journal-entries'])->group(function () {
    Route::resource('journal-entries', App\Http\Controllers\JournalEntryController::class);
    Route::post('journal-entries/{journal_entry}/post', [App\Http\Controllers\JournalEntryController::class, 'post'])
        ->name('journal-entries.post')
        ->middleware('permission:post-journal-entries');
    Route::post('journal-entries/{journal_entry}/reverse', [App\Http\Controllers\JournalEntryController::class, 'reverse'])
        ->name('journal-entries.reverse')
        ->middleware('permission:reverse-journal-entries');
});

// Financial Reports
Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/trial-balance', [App\Http\Controllers\TrialBalanceController::class, 'index'])
        ->name('trial-balance.index');
    Route::post('/trial-balance/generate', [App\Http\Controllers\TrialBalanceController::class, 'generate'])
        ->name('trial-balance.generate');
    Route::post('/trial-balance/export-excel', [App\Http\Controllers\TrialBalanceController::class, 'exportExcel'])
        ->name('trial-balance.export-excel');
    Route::post('/trial-balance/export-pdf', [App\Http\Controllers\TrialBalanceController::class, 'exportPdf'])
        ->name('trial-balance.export-pdf');
    
    Route::get('/general-ledger', [App\Http\Controllers\GeneralLedgerController::class, 'index'])
        ->name('general-ledger.index');
    Route::post('/general-ledger/generate', [App\Http\Controllers\GeneralLedgerController::class, 'generate'])
        ->name('general-ledger.generate');
    Route::post('/general-ledger/export-excel', [App\Http\Controllers\GeneralLedgerController::class, 'exportExcel'])
        ->name('general-ledger.export-excel');
    Route::post('/general-ledger/export-pdf', [App\Http\Controllers\GeneralLedgerController::class, 'exportPdf'])
        ->name('general-ledger.export-pdf');
    
    Route::get('/profit-loss', [App\Http\Controllers\ProfitLossController::class, 'index'])
        ->name('profit-loss.index');
    Route::post('/profit-loss/generate', [App\Http\Controllers\ProfitLossController::class, 'generate'])
        ->name('profit-loss.generate');
    Route::post('/profit-loss/export-excel', [App\Http\Controllers\ProfitLossController::class, 'exportExcel'])
        ->name('profit-loss.export-excel');
    Route::post('/profit-loss/export-pdf', [App\Http\Controllers\ProfitLossController::class, 'exportPdf'])
        ->name('profit-loss.export-pdf');
    
    Route::get('/balance-sheet', [App\Http\Controllers\BalanceSheetController::class, 'index'])
        ->name('balance-sheet.index');
    Route::post('/balance-sheet/generate', [App\Http\Controllers\BalanceSheetController::class, 'generate'])
        ->name('balance-sheet.generate');
    Route::post('/balance-sheet/export-excel', [App\Http\Controllers\BalanceSheetController::class, 'exportExcel'])
        ->name('balance-sheet.export-excel');
    Route::post('/balance-sheet/export-pdf', [App\Http\Controllers\BalanceSheetController::class, 'exportPdf'])
        ->name('balance-sheet.export-pdf');
});


// Phase 3 - Sales & Purchasing
Route::middleware('auth')->group(function () {
    Route::resource('customers', App\Http\Controllers\CustomerController::class);
    Route::resource('sales-orders', App\Http\Controllers\SalesOrderController::class);
    Route::post('sales-orders/{sales_order}/submit-approval', [App\Http\Controllers\SalesOrderController::class, 'submitForApproval'])
        ->name('sales-orders.submit-approval');
    Route::post('sales-orders/{sales_order}/approve', [App\Http\Controllers\SalesOrderController::class, 'approve'])
        ->name('sales-orders.approve');
    
    // Sales Invoices
    Route::resource('sales-invoices', App\Http\Controllers\SalesInvoiceController::class);
    Route::post('sales-invoices/{sales_invoice}/send', [App\Http\Controllers\SalesInvoiceController::class, 'send'])
        ->name('sales-invoices.send');
    Route::post('sales-invoices/{sales_invoice}/cancel', [App\Http\Controllers\SalesInvoiceController::class, 'cancel'])
        ->name('sales-invoices.cancel');
    Route::post('sales-invoices/{sales_invoice}/record-payment', [App\Http\Controllers\SalesInvoiceController::class, 'recordPayment'])
        ->name('sales-invoices.record-payment');
    
    // Customer Payments
    Route::resource('customer-payments', App\Http\Controllers\CustomerPaymentController::class);
    Route::post('customer-payments/{customer_payment}/post', [App\Http\Controllers\CustomerPaymentController::class, 'post'])
        ->name('customer-payments.post');
    Route::post('customer-payments/{customer_payment}/void', [App\Http\Controllers\CustomerPaymentController::class, 'void'])
        ->name('customer-payments.void');
    Route::get('api/unpaid-invoices', [App\Http\Controllers\CustomerPaymentController::class, 'getUnpaidInvoices'])
        ->name('api.unpaid-invoices');
    
    Route::resource('suppliers', App\Http\Controllers\SupplierController::class);
    
    // Purchase Requests
    Route::resource('purchase-requests', App\Http\Controllers\PurchaseRequestController::class);
    Route::post('purchase-requests/{purchase_request}/submit', [App\Http\Controllers\PurchaseRequestController::class, 'submit'])
        ->name('purchase-requests.submit');
    Route::post('purchase-requests/{purchase_request}/approve', [App\Http\Controllers\PurchaseRequestController::class, 'approve'])
        ->name('purchase-requests.approve');
    Route::post('purchase-requests/{purchase_request}/reject', [App\Http\Controllers\PurchaseRequestController::class, 'reject'])
        ->name('purchase-requests.reject');
    Route::post('purchase-requests/{purchase_request}/cancel', [App\Http\Controllers\PurchaseRequestController::class, 'cancel'])
        ->name('purchase-requests.cancel');
    
    Route::resource('purchase-orders', App\Http\Controllers\PurchaseOrderController::class);
    Route::post('purchase-orders/create-from-prs', [App\Http\Controllers\PurchaseOrderController::class, 'createFromPRs'])
        ->name('purchase-orders.create-from-prs');
    Route::get('api/approved-prs', [App\Http\Controllers\PurchaseOrderController::class, 'getApprovedPRs'])
        ->name('api.approved-prs');
    Route::post('purchase-orders/{purchase_order}/submit', [App\Http\Controllers\PurchaseOrderController::class, 'submit'])
        ->name('purchase-orders.submit');
    Route::post('purchase-orders/{purchase_order}/approve', [App\Http\Controllers\PurchaseOrderController::class, 'approve'])
        ->name('purchase-orders.approve');
    Route::post('purchase-orders/{purchase_order}/reject', [App\Http\Controllers\PurchaseOrderController::class, 'reject'])
        ->name('purchase-orders.reject');
    Route::post('purchase-orders/{purchase_order}/cancel', [App\Http\Controllers\PurchaseOrderController::class, 'cancel'])
        ->name('purchase-orders.cancel');
});
