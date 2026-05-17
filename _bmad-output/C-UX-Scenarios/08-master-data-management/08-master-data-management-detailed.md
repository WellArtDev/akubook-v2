# Scenario 08: Master Data Management

**User:** Admin / Data Manager  
**Priority:** MEDIUM (Foundation)  
**Frequency:** Weekly  
**Success Metric:** Data entry in <5 minutes per record

---

## Scenario Goal

Admin manages master data (customers, suppliers, products, accounts) with bulk import, validation, and data integrity checks.

---

## User Context

**Who:** Admin (Lisa) managing master data

**When:** Initial setup, ongoing maintenance

**Why:** Maintain accurate master data for transactions, reporting, compliance

**Current Pain (from Accurate):** Manual entry, no bulk import, duplicate data, inconsistent formats

---

## Sunshine Path (Happy Flow)

### Step 1: Customer Management

**Page:** Customer List

**User Action:**
- Opens Customers module
- Clicks "Add Customer"

**System Shows:**
- Customer form:
  - Customer name
  - NPWP (tax ID)
  - Address
  - Phone, Email
  - Payment terms
  - Credit limit

**User Input:**
- Fills customer details:
  - Name: PT Toko Elektronik Jaya
  - NPWP: 01.234.567.8-901.000
  - Address: Jl. Sudirman No. 123, Jakarta
  - Phone: 021-12345678
  - Email: info@tokojaya.com
  - Payment Terms: Net 30
  - Credit Limit: Rp 100,000,000

**System Response:**
- Validates NPWP format
- Checks for duplicates
- Creates customer: CUST-001
- Shows "Customer Created" status

**Next:** Add more customers or bulk import

---

### Step 2: Bulk Import Customers

**Page:** Customer Import

**User Action:**
- Clicks "Import Customers"
- Downloads template (Excel)

**System Shows:**
- Import template with columns:
  - Customer Name, NPWP, Address, Phone, Email, Payment Terms, Credit Limit

**User Input:**
- Fills template with 50 customers
- Uploads file (Excel/CSV)

**System Response:**
- Validates data:
  - ✅ 45 customers valid
  - ⚠️ 3 customers missing NPWP
  - ❌ 2 customers duplicate
- Shows validation report

**User Input:**
- Fixes errors in template
- Re-uploads file

**System Response:**
- Imports 50 customers successfully
- Shows "Import Complete" status

**Next:** Done (customers imported)

---

### Step 3: Product Management

**Page:** Product List

**User Action:**
- Opens Products module
- Clicks "Add Product"

**System Shows:**
- Product form:
  - Product code
  - Product name
  - Category
  - Unit of measure
  - Purchase price
  - Selling price
  - Reorder level

**User Input:**
- Fills product details:
  - Code: SPK-JBL-EON615
  - Name: Speaker JBL EON615
  - Category: Audio Equipment
  - Unit: PCS
  - Purchase Price: Rp 5,000,000
  - Selling Price: Rp 6,500,000
  - Reorder Level: 5 units

**System Response:**
- Validates product code (unique)
- Creates product: PROD-001
- Shows "Product Created" status

**Next:** Add more products or bulk import

---

### Step 4: Supplier Management

**Page:** Supplier List

**User Action:**
- Opens Suppliers module
- Clicks "Add Supplier"

**System Shows:**
- Supplier form:
  - Supplier name
  - NPWP (tax ID)
  - Address
  - Phone, Email
  - Payment terms
  - Bank account

**User Input:**
- Fills supplier details:
  - Name: PT Supplier Audio
  - NPWP: 02.345.678.9-012.000
  - Address: Jl. Gatot Subroto No. 456, Jakarta
  - Phone: 021-87654321
  - Email: sales@supplieraudio.com
  - Payment Terms: Net 30
  - Bank: BCA 1234567890

**System Response:**
- Validates NPWP format
- Checks for duplicates
- Creates supplier: SUPP-001
- Shows "Supplier Created" status

**Next:** Done (supplier created)

---

### Step 5: Chart of Accounts Management

**Page:** Chart of Accounts

**User Action:**
- Opens Chart of Accounts module
- Clicks "Add Account"

**System Shows:**
- Account form:
  - Account code
  - Account name
  - Account type (Asset/Liability/Equity/Revenue/Expense)
  - Parent account (optional)

**User Input:**
- Fills account details:
  - Code: 1-1110
  - Name: Cash in Bank - BCA
  - Type: Asset
  - Parent: 1-1100 (Cash and Bank)

**System Response:**
- Validates account code (unique)
- Creates account: ACC-001
- Shows "Account Created" status

**Next:** Done (account created)

---

### Step 6: Data Validation & Cleanup

**Page:** Data Quality Dashboard

**User Action:**
- Opens Data Quality module
- Clicks "Run Validation"

**System Shows:**
- Validation results:
  - ✅ Customers: 95/100 valid (5 missing NPWP)
  - ✅ Products: 250/250 valid
  - ⚠️ Suppliers: 18/20 valid (2 missing bank account)
  - ✅ Accounts: 150/150 valid

**User Input:**
- Clicks "Fix Issues"
- Updates missing data

**System Response:**
- Shows updated validation results:
  - ✅ All data valid
- Shows "Data Quality: 100%" status

**Next:** Done (data validated)

---

## Pages/Screens Needed

1. **Customer List** - View and manage customers
2. **Customer Import** - Bulk import customers
3. **Product List** - View and manage products
4. **Supplier List** - View and manage suppliers
5. **Chart of Accounts** - View and manage accounts
6. **Data Quality Dashboard** - Validate and cleanup data

---

## Data Models Required

### Tables

**customers**
- id, company_id, customer_code, customer_name
- npwp, address, phone, email
- payment_terms, credit_limit, is_active
- created_at, updated_at

**suppliers**
- id, company_id, supplier_code, supplier_name
- npwp, address, phone, email
- payment_terms, bank_name, bank_account
- is_active, created_at, updated_at

**products**
- id, company_id, product_code, product_name
- category_id, unit_of_measure, purchase_price, selling_price
- reorder_level, is_active, created_at, updated_at

**product_categories**
- id, company_id, category_name, parent_id
- created_at, updated_at

**chart_of_accounts**
- id, company_id, account_code, account_name
- account_type, parent_id, is_active
- created_at, updated_at

---

## Acceptance Criteria

**Functional:**
- ✅ CRUD operations for all master data
- ✅ Bulk import (Excel/CSV)
- ✅ Data validation (NPWP format, duplicates)
- ✅ Data quality dashboard
- ✅ Search and filter

**Performance:**
- ✅ Single record creation in <5 seconds
- ✅ Bulk import 1000 records in <2 minutes
- ✅ Search results in <1 second

**Security:**
- ✅ Only authorized users can edit master data
- ✅ Audit trail for all changes
- ✅ Soft delete (no permanent deletion)

**UX:**
- ✅ Clear validation messages
- ✅ Bulk import template
- ✅ Duplicate detection
- ✅ Auto-complete for related data

---

## Design Notes

**Tone:**
- Efficient, accurate (foundation data)
- Clear validation messages
- Helpful guidance for bulk import

**UX Principles:**
- Bulk operations (save time)
- Validation before save (prevent errors)
- Auto-complete (reduce typing)
- Duplicate detection (maintain integrity)

**Mobile Consideration:**
- Master data management desktop-only (complex input)

---

## Related Scenarios

- **01: Company Setup** - Initial master data setup
- **02: Sales Order Flow** - Uses customer and product data
- **06: Purchase Order Flow** - Uses supplier and product data

---

## Accurate Feature Parity

**Accurate Master Data includes:**
- Customer, supplier, product management
- Chart of accounts

**AkuBook Enhancement:**
- Bulk import (Accurate limited)
- Data quality dashboard (Accurate doesn't have this)
- Duplicate detection (Accurate manual)
- NPWP validation (Accurate manual)

---

**Scenario Status:** ✅ Ready for Implementation  
**Next:** Design wireframes for 6 pages in this flow
