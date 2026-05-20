<?php

use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\ChartOfAccountsImportController;
use App\Http\Controllers\HistoricalTransactionsImportController;
use App\Http\Controllers\MasterDataImportController;
use App\Http\Controllers\OpeningBalancesImportController;
use App\Http\Controllers\PostMigrationReconciliationController;
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

Route::get('/manifest.webmanifest', App\Http\Controllers\PwaManifestController::class)
    ->name('pwa.manifest');
Route::get('/service-worker.js', App\Http\Controllers\ServiceWorkerController::class)
    ->name('pwa.service-worker');
Route::get('/healthz', App\Http\Controllers\HealthCheckController::class)
    ->name('healthz');

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

    Route::get('/master-data', [MasterDataImportController::class, 'index'])
        ->name('master-data.index');
    Route::post('/master-data/preview', [MasterDataImportController::class, 'preview'])
        ->name('master-data.preview');
    Route::post('/master-data/import', [MasterDataImportController::class, 'import'])
        ->name('master-data.import');

    Route::get('/opening-balances', [OpeningBalancesImportController::class, 'index'])
        ->name('opening-balances.index');
    Route::post('/opening-balances/preview', [OpeningBalancesImportController::class, 'preview'])
        ->name('opening-balances.preview');
    Route::post('/opening-balances/import', [OpeningBalancesImportController::class, 'import'])
        ->name('opening-balances.import');

    Route::get('/historical-transactions', [HistoricalTransactionsImportController::class, 'index'])
        ->name('historical-transactions.index');
    Route::post('/historical-transactions/preview', [HistoricalTransactionsImportController::class, 'preview'])
        ->name('historical-transactions.preview');
    Route::post('/historical-transactions/import', [HistoricalTransactionsImportController::class, 'import'])
        ->name('historical-transactions.import');

    Route::get('/post-migration-reconciliation', [PostMigrationReconciliationController::class, 'index'])
        ->name('post-migration-reconciliation.index');
    Route::post('/post-migration-reconciliation/run', [PostMigrationReconciliationController::class, 'run'])
        ->name('post-migration-reconciliation.run');
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
    Route::resource('sales-quotations', App\Http\Controllers\SalesQuotationController::class);
    Route::post('sales-quotations/{sales_quotation}/send', [App\Http\Controllers\SalesQuotationController::class, 'send'])
        ->name('sales-quotations.send');
    Route::post('sales-quotations/{sales_quotation}/approve', [App\Http\Controllers\SalesQuotationController::class, 'approve'])
        ->name('sales-quotations.approve');
    Route::post('sales-quotations/{sales_quotation}/reject', [App\Http\Controllers\SalesQuotationController::class, 'reject'])
        ->name('sales-quotations.reject');
    Route::post('sales-quotations/{sales_quotation}/revise', [App\Http\Controllers\SalesQuotationController::class, 'revise'])
        ->name('sales-quotations.revise');
    Route::post('sales-quotations/{sales_quotation}/duplicate', [App\Http\Controllers\SalesQuotationController::class, 'duplicate'])
        ->name('sales-quotations.duplicate');
    Route::post('sales-quotations/{sales_quotation}/convert', [App\Http\Controllers\SalesQuotationController::class, 'convert'])
        ->name('sales-quotations.convert');

    Route::resource('sales-orders', App\Http\Controllers\SalesOrderController::class);
    Route::post('sales-orders/{sales_order}/submit-approval', [App\Http\Controllers\SalesOrderController::class, 'submitForApproval'])
        ->name('sales-orders.submit-approval');
    Route::post('sales-orders/{sales_order}/approve', [App\Http\Controllers\SalesOrderController::class, 'approve'])
        ->name('sales-orders.approve');
    Route::post('sales-orders/{sales_order}/reject', [App\Http\Controllers\SalesOrderController::class, 'reject'])
        ->name('sales-orders.reject');
    Route::post('sales-orders/{sales_order}/cancel', [App\Http\Controllers\SalesOrderController::class, 'cancel'])
        ->name('sales-orders.cancel');
    Route::post('sales-orders/{sales_order}/duplicate', [App\Http\Controllers\SalesOrderController::class, 'duplicate'])
        ->name('sales-orders.duplicate');

    Route::get('sales-order-approvals', [App\Http\Controllers\SalesOrderApprovalController::class, 'index'])
        ->name('sales-order-approvals.index');
    Route::get('sales-order-approvals/{sales_order_approval}', [App\Http\Controllers\SalesOrderApprovalController::class, 'show'])
        ->name('sales-order-approvals.show');
    Route::post('sales-order-approvals/{sales_order_approval}/approve', [App\Http\Controllers\SalesOrderApprovalController::class, 'approve'])
        ->name('sales-order-approvals.approve');
    Route::post('sales-order-approvals/{sales_order_approval}/reject', [App\Http\Controllers\SalesOrderApprovalController::class, 'reject'])
        ->name('sales-order-approvals.reject');
    Route::post('sales-order-approvals/bulk-approve', [App\Http\Controllers\SalesOrderApprovalController::class, 'bulkApprove'])
        ->name('sales-order-approvals.bulk-approve');

    Route::resource('delivery-orders', App\Http\Controllers\DeliveryOrderController::class);
    Route::post('delivery-orders/{delivery_order}/confirm', [App\Http\Controllers\DeliveryOrderController::class, 'confirm'])
        ->name('delivery-orders.confirm');
    Route::post('delivery-orders/{delivery_order}/ship', [App\Http\Controllers\DeliveryOrderController::class, 'ship'])
        ->name('delivery-orders.ship');
    Route::post('delivery-orders/{delivery_order}/deliver', [App\Http\Controllers\DeliveryOrderController::class, 'markDelivered'])
        ->name('delivery-orders.deliver');
    Route::post('delivery-orders/{delivery_order}/cancel', [App\Http\Controllers\DeliveryOrderController::class, 'cancel'])
        ->name('delivery-orders.cancel');
    
    // Sales Invoices
    Route::resource('sales-invoices', App\Http\Controllers\SalesInvoiceController::class);
    Route::post('sales-invoices/{sales_invoice}/send', [App\Http\Controllers\SalesInvoiceController::class, 'send'])
        ->name('sales-invoices.send');
    Route::post('sales-invoices/{sales_invoice}/post', [App\Http\Controllers\SalesInvoiceController::class, 'post'])
        ->name('sales-invoices.post');
    Route::post('sales-invoices/{sales_invoice}/cancel', [App\Http\Controllers\SalesInvoiceController::class, 'cancel'])
        ->name('sales-invoices.cancel');
    Route::post('sales-invoices/{sales_invoice}/record-payment', [App\Http\Controllers\SalesInvoiceController::class, 'recordPayment'])
        ->name('sales-invoices.record-payment');
    
    Route::resource('sales-returns', App\Http\Controllers\SalesReturnController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('sales-returns/{sales_return}/approve', [App\Http\Controllers\SalesReturnController::class, 'approve'])
        ->name('sales-returns.approve');
    Route::post('sales-returns/{sales_return}/receive', [App\Http\Controllers\SalesReturnController::class, 'receive'])
        ->name('sales-returns.receive');
    Route::post('sales-returns/{sales_return}/complete', [App\Http\Controllers\SalesReturnController::class, 'complete'])
        ->name('sales-returns.complete');
    Route::post('sales-returns/{sales_return}/reject', [App\Http\Controllers\SalesReturnController::class, 'reject'])
        ->name('sales-returns.reject');

    // Customer Payments
    Route::resource('customer-payments', App\Http\Controllers\CustomerPaymentController::class);
    Route::post('customer-payments/{customer_payment}/post', [App\Http\Controllers\CustomerPaymentController::class, 'post'])
        ->name('customer-payments.post');
    Route::post('customer-payments/{customer_payment}/void', [App\Http\Controllers\CustomerPaymentController::class, 'void'])
        ->name('customer-payments.void');
    Route::get('api/unpaid-invoices', [App\Http\Controllers\CustomerPaymentController::class, 'getUnpaidInvoices'])
        ->name('api.unpaid-invoices');

    // Supplier Payments
    Route::resource('supplier-payments', App\Http\Controllers\SupplierPaymentController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('supplier-payments/{supplier_payment}/post', [App\Http\Controllers\SupplierPaymentController::class, 'post'])
        ->name('supplier-payments.post');
    Route::post('supplier-payments/{supplier_payment}/void', [App\Http\Controllers\SupplierPaymentController::class, 'void'])
        ->name('supplier-payments.void');
    Route::get('api/unpaid-purchase-invoices', [App\Http\Controllers\SupplierPaymentController::class, 'getUnpaidInvoices'])
        ->name('api.unpaid-purchase-invoices');
    
    Route::resource('suppliers', App\Http\Controllers\SupplierController::class);
    Route::resource('salary-components', App\Http\Controllers\SalaryComponentController::class);
    Route::resource('data-retention-policies', App\Http\Controllers\DataRetentionPolicyController::class);
    Route::resource('data-retention-executions', App\Http\Controllers\DataRetentionExecutionController::class)->only(['index', 'store', 'show']);
    Route::resource('approval-workflows', App\Http\Controllers\ApprovalWorkflowController::class);
    Route::post('approval-workflows/evaluate', [App\Http\Controllers\ApprovalWorkflowController::class, 'evaluate'])->name('approval-workflows.evaluate');
    Route::get('audit-logs', [App\Http\Controllers\AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('sensitive-actions', [App\Http\Controllers\SensitiveActionController::class, 'index'])->name('sensitive-actions.index');
    Route::resource('sensitive-alerts', App\Http\Controllers\SensitiveAlertController::class)->only(['index', 'store']);
    Route::resource('compliance-export-packs', App\Http\Controllers\ComplianceExportPackController::class)->only(['index', 'store', 'show']);
    Route::get('compliance-export-packs/{compliance_export_pack}/download', [App\Http\Controllers\ComplianceExportPackController::class, 'download'])->name('compliance-export-packs.download');
    Route::get('governance-dashboard-v2', [App\Http\Controllers\GovernanceDashboardV2Controller::class, 'index'])->name('governance-dashboard-v2.index');
    Route::get('role-dashboard', [App\Http\Controllers\RoleDashboardController::class, 'index'])->name('role-dashboard.index');
    Route::get('role-dashboard/metrics', [App\Http\Controllers\RoleDashboardController::class, 'metrics'])->name('role-dashboard.metrics');
    Route::post('role-dashboard/preference', [App\Http\Controllers\RoleDashboardController::class, 'preference'])->name('role-dashboard.preference');
    Route::get('role-dashboard/drilldown/{widget}', [App\Http\Controllers\RoleDashboardController::class, 'drilldown'])->name('role-dashboard.drilldown');
    Route::get('payroll-runs', [App\Http\Controllers\PayrollRunController::class, 'index'])->name('payroll-runs.index');
    Route::resource('payroll-bank-transfers', App\Http\Controllers\PayrollBankTransferController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('payroll-bank-transfers/{payroll_bank_transfer}/download', [App\Http\Controllers\PayrollBankTransferController::class, 'download'])->name('payroll-bank-transfers.download');
    Route::get('payroll-reports', [App\Http\Controllers\PayrollReportController::class, 'index'])->name('payroll-reports.index');
    Route::get('financial-reports', [App\Http\Controllers\FinancialReportController::class, 'index'])->name('financial-reports.index');
    Route::get('operational-reports', [App\Http\Controllers\OperationalReportController::class, 'index'])->name('operational-reports.index');
    Route::get('sales-reports', [App\Http\Controllers\SalesReportController::class, 'index'])->name('sales-reports.index');
    Route::get('sales-reports/export', [App\Http\Controllers\SalesReportController::class, 'export'])->name('sales-reports.export');
    Route::get('sales-dashboard', [App\Http\Controllers\SalesDashboardController::class, 'index'])->name('sales-dashboard.index');
    Route::get('customer-statements', [App\Http\Controllers\CustomerStatementController::class, 'index'])->name('customer-statements.index');
    Route::get('customer-statements/pdf', [App\Http\Controllers\CustomerStatementController::class, 'pdf'])->name('customer-statements.pdf');
    Route::get('purchase-reports', [App\Http\Controllers\PurchaseReportController::class, 'index'])->name('purchase-reports.index');
    Route::get('purchase-reports/export', [App\Http\Controllers\PurchaseReportController::class, 'export'])->name('purchase-reports.export');
    Route::get('purchase-dashboard', [App\Http\Controllers\PurchaseDashboardController::class, 'index'])->name('purchase-dashboard.index');
    Route::get('hr-reports', [App\Http\Controllers\HrReportController::class, 'index'])->name('hr-reports.index');
    Route::get('report-exports/financial', [App\Http\Controllers\ReportExportController::class, 'financial'])->name('report-exports.financial');
    Route::get('report-exports/payroll', [App\Http\Controllers\ReportExportController::class, 'payroll'])->name('report-exports.payroll');
    Route::get('report-exports/custom-reports/{custom_report}', [App\Http\Controllers\ReportExportController::class, 'customReport'])->name('report-exports.custom-report');
    Route::resource('custom-reports', App\Http\Controllers\CustomReportController::class);
    Route::get('security-audit', [App\Http\Controllers\SecurityAuditController::class, 'index'])->name('security-audit.index');
    Route::get('compliance-reports', [App\Http\Controllers\ComplianceReportController::class, 'index'])->name('compliance-reports.index');
    Route::resource('employees', App\Http\Controllers\EmployeeController::class);
    Route::resource('employee-assignments', App\Http\Controllers\EmployeeAssignmentController::class);
    Route::resource('employee-documents', App\Http\Controllers\EmployeeDocumentController::class);
    Route::resource('leave-requests', App\Http\Controllers\LeaveRequestController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('leave-requests/{leave_request}/approve', [App\Http\Controllers\LeaveRequestController::class, 'approve'])
        ->name('leave-requests.approve');
    Route::post('leave-requests/{leave_request}/reject', [App\Http\Controllers\LeaveRequestController::class, 'reject'])
        ->name('leave-requests.reject');
    Route::post('leave-requests/{leave_request}/cancel', [App\Http\Controllers\LeaveRequestController::class, 'cancel'])
        ->name('leave-requests.cancel');
        Route::resource('attendance-records', App\Http\Controllers\AttendanceRecordController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('attendance-records/{attendance_record}/check-out', [App\Http\Controllers\AttendanceRecordController::class, 'checkOut'])->name('attendance-records.check-out');
        Route::get('offline-sync', [App\Http\Controllers\OfflineSyncController::class, 'index'])->name('offline-sync.index');
        Route::post('offline-sync', [App\Http\Controllers\OfflineSyncController::class, 'sync'])->name('offline-sync.sync');
        Route::post('offline-attendance-sync', [App\Http\Controllers\OfflineAttendanceSyncController::class, 'sync'])->name('offline-attendance-sync.sync');

        Route::resource('work-shifts', App\Http\Controllers\WorkShiftController::class);
        Route::resource('employee-shift-assignments', App\Http\Controllers\EmployeeShiftAssignmentController::class);

        Route::resource('overtime-records', App\Http\Controllers\OvertimeRecordController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('overtime-records/{overtime_record}/approve', [App\Http\Controllers\OvertimeRecordController::class, 'approve'])->name('overtime-records.approve');
        Route::post('overtime-records/{overtime_record}/reject', [App\Http\Controllers\OvertimeRecordController::class, 'reject'])->name('overtime-records.reject');
        Route::post('overtime-records/{overtime_record}/cancel', [App\Http\Controllers\OvertimeRecordController::class, 'cancel'])->name('overtime-records.cancel');

        Route::get('attendance-reports', [App\Http\Controllers\AttendanceReportController::class, 'index'])->name('attendance-reports.index');

    Route::get('zkteco-devices', [App\Http\Controllers\ZktecoAttendanceController::class, 'devicesIndex'])
        ->name('zkteco-devices.index');
    Route::get('zkteco-devices/create', [App\Http\Controllers\ZktecoAttendanceController::class, 'devicesCreate'])
        ->name('zkteco-devices.create');
    Route::post('zkteco-devices', [App\Http\Controllers\ZktecoAttendanceController::class, 'devicesStore'])
        ->name('zkteco-devices.store');
    Route::get('zkteco-devices/{zkteco_device}', [App\Http\Controllers\ZktecoAttendanceController::class, 'devicesShow'])
        ->name('zkteco-devices.show');
    Route::delete('zkteco-devices/{zkteco_device}', [App\Http\Controllers\ZktecoAttendanceController::class, 'devicesDestroy'])
        ->name('zkteco-devices.destroy');
    Route::resource('zkteco-attendance', App\Http\Controllers\ZktecoAttendanceController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('cash-accounts', App\Http\Controllers\CashAccountController::class);
    Route::resource('bank-accounts', App\Http\Controllers\BankAccountController::class);
    Route::resource('tax-configurations', App\Http\Controllers\TaxConfigurationController::class);
    Route::get('tax-calculations', [App\Http\Controllers\TaxCalculationController::class, 'index'])
        ->name('tax-calculations.index');
    Route::post('tax-calculations/calculate', [App\Http\Controllers\TaxCalculationController::class, 'calculateApi'])
        ->name('tax-calculations.calculate');
    Route::get('tax-reports', [App\Http\Controllers\TaxReportingController::class, 'index'])
        ->name('tax-reports.index');

    Route::resource('faktur-pajaks', App\Http\Controllers\FakturPajakController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('faktur-pajaks/{faktur_pajak}/issue', [App\Http\Controllers\FakturPajakController::class, 'issue'])
        ->name('faktur-pajaks.issue');
    Route::post('faktur-pajaks/{faktur_pajak}/cancel', [App\Http\Controllers\FakturPajakController::class, 'cancel'])
        ->name('faktur-pajaks.cancel');

    Route::resource('e-faktur-exports', App\Http\Controllers\EFakturExportController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('e-faktur-exports/{e_faktur_export}/download', [App\Http\Controllers\EFakturExportController::class, 'download'])
        ->name('e-faktur-exports.download');

    Route::resource('bank-reconciliations', App\Http\Controllers\BankReconciliationController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('bank-reconciliations/{bank_reconciliation}/reconcile', [App\Http\Controllers\BankReconciliationController::class, 'reconcile'])
        ->name('bank-reconciliations.reconcile');
    Route::post('bank-reconciliation-lines/{bank_reconciliation_line}/match', [App\Http\Controllers\BankReconciliationController::class, 'matchLine'])
        ->name('bank-reconciliation-lines.match');
    Route::post('bank-reconciliation-lines/{bank_reconciliation_line}/unmatch', [App\Http\Controllers\BankReconciliationController::class, 'unmatchLine'])
        ->name('bank-reconciliation-lines.unmatch');
    Route::resource('vouchers', App\Http\Controllers\VoucherController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('vouchers/{voucher}/post', [App\Http\Controllers\VoucherController::class, 'post'])
        ->name('vouchers.post');
    Route::post('vouchers/{voucher}/cancel', [App\Http\Controllers\VoucherController::class, 'cancel'])
        ->name('vouchers.cancel');
    Route::get('cash-flow-reports', [App\Http\Controllers\CashFlowReportController::class, 'index'])
        ->name('cash-flow-reports.index');
    Route::resource('items', App\Http\Controllers\ItemController::class);
    Route::resource('stock-transactions', App\Http\Controllers\StockTransactionController::class)->only(['index', 'create', 'store']);
    Route::resource('stock-opnames', App\Http\Controllers\StockOpnameController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('stock-opnames/{stock_opname}/confirm', [App\Http\Controllers\StockOpnameController::class, 'confirm'])
        ->name('stock-opnames.confirm');
    Route::resource('stock-transfers', App\Http\Controllers\StockTransferController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('stock-transfers/{stock_transfer}/confirm', [App\Http\Controllers\StockTransferController::class, 'confirm'])
        ->name('stock-transfers.confirm');
    Route::get('inventory-valuations', [App\Http\Controllers\InventoryValuationController::class, 'index'])
        ->name('inventory-valuations.index');
    Route::resource('fixed-assets', App\Http\Controllers\FixedAssetController::class);
    Route::resource('asset-disposals', App\Http\Controllers\AssetDisposalController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('asset-disposals/{asset_disposal}/post', [App\Http\Controllers\AssetDisposalController::class, 'post'])
        ->name('asset-disposals.post');
    Route::get('asset-depreciations', [App\Http\Controllers\AssetDepreciationController::class, 'index'])
        ->name('asset-depreciations.index');
    Route::get('asset-depreciation-journals', [App\Http\Controllers\AssetDepreciationJournalController::class, 'index'])
        ->name('asset-depreciation-journals.index');
    Route::post('asset-depreciation-journals/run', [App\Http\Controllers\AssetDepreciationJournalController::class, 'run'])
        ->name('asset-depreciation-journals.run');
    Route::get('asset-reports', [App\Http\Controllers\AssetReportController::class, 'index'])
        ->name('asset-reports.index');
    
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
    Route::post('purchase-orders/{purchase_order}/submit-approval', [App\Http\Controllers\PurchaseOrderController::class, 'submit'])
        ->name('purchase-orders.submit-approval');
    Route::post('purchase-orders/{purchase_order}/approve', [App\Http\Controllers\PurchaseOrderController::class, 'approve'])
        ->name('purchase-orders.approve');
    Route::post('purchase-orders/{purchase_order}/reject', [App\Http\Controllers\PurchaseOrderController::class, 'reject'])
        ->name('purchase-orders.reject');
    Route::post('purchase-orders/{purchase_order}/cancel', [App\Http\Controllers\PurchaseOrderController::class, 'cancel'])
        ->name('purchase-orders.cancel');

    Route::get('purchase-order-approvals', [App\Http\Controllers\PurchaseOrderApprovalController::class, 'index'])
        ->name('purchase-order-approvals.index');
    Route::get('purchase-order-approvals/{purchase_order_approval}', [App\Http\Controllers\PurchaseOrderApprovalController::class, 'show'])
        ->name('purchase-order-approvals.show');
    Route::post('purchase-order-approvals/{purchase_order_approval}/approve', [App\Http\Controllers\PurchaseOrderApprovalController::class, 'approve'])
        ->name('purchase-order-approvals.approve');
    Route::post('purchase-order-approvals/{purchase_order_approval}/reject', [App\Http\Controllers\PurchaseOrderApprovalController::class, 'reject'])
        ->name('purchase-order-approvals.reject');
    Route::post('purchase-order-approvals/bulk-approve', [App\Http\Controllers\PurchaseOrderApprovalController::class, 'bulkApprove'])
        ->name('purchase-order-approvals.bulk-approve');

    Route::resource('goods-receipts', App\Http\Controllers\GoodsReceiptController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('goods-receipts/{goods_receipt}/receive', [App\Http\Controllers\GoodsReceiptController::class, 'receive'])
        ->name('goods-receipts.receive');
    Route::post('goods-receipts/{goods_receipt}/cancel', [App\Http\Controllers\GoodsReceiptController::class, 'cancel'])
        ->name('goods-receipts.cancel');

    Route::resource('purchase-invoices', App\Http\Controllers\PurchaseInvoiceController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('purchase-invoices/{purchase_invoice}/post', [App\Http\Controllers\PurchaseInvoiceController::class, 'post'])
        ->name('purchase-invoices.post');
    Route::post('purchase-invoices/{purchase_invoice}/cancel', [App\Http\Controllers\PurchaseInvoiceController::class, 'cancel'])
        ->name('purchase-invoices.cancel');

    Route::resource('purchase-returns', App\Http\Controllers\PurchaseReturnController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('purchase-returns/{purchase_return}/approve', [App\Http\Controllers\PurchaseReturnController::class, 'approve'])
        ->name('purchase-returns.approve');
    Route::post('purchase-returns/{purchase_return}/receive', [App\Http\Controllers\PurchaseReturnController::class, 'receive'])
        ->name('purchase-returns.receive');
    Route::post('purchase-returns/{purchase_return}/complete', [App\Http\Controllers\PurchaseReturnController::class, 'complete'])
        ->name('purchase-returns.complete');
    Route::post('purchase-returns/{purchase_return}/reject', [App\Http\Controllers\PurchaseReturnController::class, 'reject'])
        ->name('purchase-returns.reject');

    Route::get('supplier-statements', [App\Http\Controllers\SupplierStatementController::class, 'index'])
        ->name('supplier-statements.index');
    Route::get('supplier-statements/pdf', [App\Http\Controllers\SupplierStatementController::class, 'pdf'])
        ->name('supplier-statements.pdf');

    Route::get('dot-matrix-templates/defaults', [App\Http\Controllers\DotMatrixTemplateController::class, 'defaults'])
        ->name('dot-matrix-templates.defaults');
    Route::resource('dot-matrix-templates', App\Http\Controllers\DotMatrixTemplateController::class);

    Route::get('print-drafts/{print_draft}/preview', [App\Http\Controllers\PrintDraftController::class, 'preview'])
        ->name('print-drafts.preview');
    Route::post('print-drafts/{print_draft}/mark-ready', [App\Http\Controllers\PrintDraftController::class, 'markReady'])
        ->name('print-drafts.mark-ready');
    Route::post('print-drafts/{print_draft}/record-print', [App\Http\Controllers\PrintDraftController::class, 'recordPrint'])
        ->name('print-drafts.record-print');
    Route::resource('print-drafts', App\Http\Controllers\PrintDraftController::class);
    Route::resource('print-histories', App\Http\Controllers\PrintHistoryController::class)->only(['index', 'show']);
    Route::get('release-readiness', App\Http\Controllers\ReleaseReadinessController::class)
        ->name('release-readiness.index');
});

