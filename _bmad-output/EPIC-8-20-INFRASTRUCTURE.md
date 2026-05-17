Epic 8-20 akan membutuhkan models dan controllers tambahan:

EPIC 8 (Sales):
- Models: Quotation, DeliveryOrder, SalesInvoice, SalesReturn, CustomerPayment
- Controllers: QuotationController, DeliveryOrderController, SalesInvoiceController

EPIC 9 (Purchasing):
- Models: Supplier, PurchaseRequest, PurchaseOrder, GoodsReceipt, PurchaseInvoice, PurchaseReturn, SupplierPayment
- Controllers: SupplierController, PurchaseRequestController, PurchaseOrderController

EPIC 11 (Inventory):
- Models: Item, StockMovement, StockOpname, StockTransfer
- Controllers: ItemController, StockController

EPIC 12 (Cash & Bank):
- Models: CashAccount, BankAccount, PaymentVoucher, ReceiptVoucher
- Controllers: CashController, BankController

EPIC 13 (Tax):
- Models: TaxConfiguration, FakturPajak
- Controllers: TaxController

EPIC 14 (Fixed Assets):
- Models: Asset, Depreciation
- Controllers: AssetController

EPIC 15 (HR):
- Models: Employee, Leave
- Controllers: EmployeeController, LeaveController

EPIC 16 (Attendance):
- Models: Attendance, Shift, Overtime
- Controllers: AttendanceController, ShiftController

EPIC 17 (Payroll):
- Models: SalaryComponent, Payroll
- Controllers: PayrollController

Preparing infrastructure...
