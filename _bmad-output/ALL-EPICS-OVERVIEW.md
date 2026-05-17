# AKUBOOK ERP - ALL EPICS OVERVIEW

## PHASE 1 - FOUNDATION 

### Epic 1: Core System Setup & Infrastructure (5 stories) 
- Laravel 13 + React 19 + Inertia.js 2
- Database schema foundation
- Authentication system
- Audit logging system

### Epic 2: User Management & Access Control (5 stories) 
- Spatie permission integration
- User CRUD operations
- Role & permission management
- Branch-level data access control
- Separation of duties enforcement

### Epic 3: Company & Organization Structure (5 stories) 
- Company settings
- Branch management
- Department management
- Position management
- Warehouse management

## PHASE 2 - ACCOUNTING FOUNDATION 

### Epic 4: Chart of Accounts & Fiscal Periods (3 stories) 
- Chart of accounts structure
- Industry-specific CoA templates
- Fiscal period management

### Epic 5: Journal Entry & Posting System (5 stories) 
- Manual journal entry creation
- Journal entry posting
- Journal entry reversal
- Auto-generated journals from sales
- Auto-generated journals from purchases

### Epic 6: Financial Reporting (4 stories) 
- Trial balance report
- General ledger report
- Profit & loss statement
- Balance sheet

### Epic 7: Data Migration from Accurate (5 stories) 
- Chart of accounts import
- Master data import
- Opening balances import
- Historical transactions import
- Post-migration reconciliation

## PHASE 3 - SALES & PURCHASING (NOT STARTED ⏳)

### Epic 8: Customer & Sales Management (~12-15 stories)
- Customer CRUD
- Quotation
- Sales Order
- Delivery Order
- Sales Invoice
- Sales Return
- Customer Payments

### Epic 9: Supplier & Purchasing Management (~12-15 stories)
- Supplier CRUD
- Purchase Request
- Purchase Order
- Goods Receipt
- Purchase Invoice
- Purchase Return
- Supplier Payments

### Epic 10: Document Printing System (~8-10 stories)
- Dot matrix print templates
- Edit-before-print
- Print preview
- Print history

## PHASE 4 - INVENTORY (NOT STARTED ⏳)

### Epic 11: Inventory Management & Valuation (~10-12 stories)
- Item master
- Stock tracking
- Stock opname
- Stock transfer
- Inventory valuation (FIFO/Average)

## PHASE 5 - CASH & BANK (NOT STARTED ⏳)

### Epic 12: Cash & Bank Management (~8-10 stories)
- Cash accounts
- Bank accounts
- Bank reconciliation
- Payment/Receipt vouchers
- Cash flow report

## PHASE 6 - TAX (NOT STARTED ⏳)

### Epic 13: Tax Management & Compliance (~8-10 stories)
- Tax configuration
- Tax calculation
- Faktur Pajak
- E-Faktur export
- Tax reporting

## PHASE 7 - FIXED ASSETS (NOT STARTED ⏳)

### Epic 14: Fixed Assets Management (~8-10 stories)
- Asset registration
- Depreciation calculation
- Depreciation journal
- Asset disposal
- Asset reports

## PHASE 8 - HRM & ATTENDANCE (NOT STARTED ⏳)

### Epic 15: Employee & HR Management (~8-10 stories)
- Employee CRUD
- Employee assignment
- Leave management (Cuti)
- Employee documents

### Epic 16: Attendance Management & ZKTeco Integration (~10-12 stories)
- Online attendance (Absensi Online)
- ZKTeco integration
- Shift management
- Overtime tracking (Lembur)
- Attendance reports

## PHASE 9 - PAYROLL (NOT STARTED ⏳)

### Epic 17: Payroll Processing & Integration (~10-12 stories)
- Salary components
- Payroll calculation
- Attendance integration
- Tax calculation (PPh 21)
- Payroll reports
- Bank transfer file

## PHASE 10 - REPORTING & ANALYTICS (NOT STARTED ⏳)

### Epic 18: Dashboard & Analytics (~6-8 stories)
- Role-based dashboards
- Real-time metrics
- Drill-down capability
- Dashboard refresh

### Epic 19: Comprehensive Reporting System (~8-10 stories)
- Financial reports
- Operational reports
- HR reports
- Custom report builder
- Export functionality

## CROSS-CUTTING (NOT STARTED ⏳)

### Epic 20: Progressive Web App & Offline Capability (~8-10 stories)
- PWA manifest
- Service worker
- Offline clock in/out
- Offline data sync
- Encryption

---

## SUMMARY

**Total Epics:** 20
**Total Estimated Stories:** ~180 stories

**Current Progress:**
-  Complete: Epic 1-7 (32 stories)
- ⏳ Not Started: Epic 8-20 (~148 stories)

**Completion:** 32/180 stories (18%)

**Next Priority:**
- Epic 8-9: Sales & Purchasing (core business operations)
- Epic 11: Inventory (stock management)
- Epic 15-17: HRM, Attendance, Payroll (employee management)
