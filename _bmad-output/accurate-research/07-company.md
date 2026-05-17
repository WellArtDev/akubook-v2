# Company (Perusahaan) Module

## Overview

Company module = foundation for multi-branch distributor operations. 16 features covering company profile, multi-branch hierarchy, fiscal year management, currencies, tax settings, numbering formats, and organizational structure.

**Critical for AkuBook**: Multi-branch setup enables independent branch operations with centralized consolidation—core requirement for distributor business model.

---

## Feature Categories

### 1. Company Profile & Settings
- Company information (name, address, contact, NPWP)
- Fiscal year & accounting period
- Opening balance setup
- Tax configuration (PKP status, NPWP, NITKU)

### 2. Multi-Branch Management (Add-on)
- Branch hierarchy & structure
- Branch-specific access control
- Inter-branch transactions
- Consolidated reporting

### 3. Master Data
- Currencies (multi-currency support)
- Payment terms
- Shipping methods
- FOB terms
- Tax codes

### 4. Organizational Structure
- Departments
- Projects (cost/profit centers)
- Employees
- Payroll components

### 5. Automation & Control
- Recurring transactions
- Transaction favorites
- Approval workflows
- Activity logs
- Calendar & reminders

### 6. Period Management
- Month-end closing (Proses Akhir Bulan)
- Period locking
- Date restrictions
- Fiscal year transitions

---

## Detailed Features

### 1. Company Information
**Path**: Pengaturan > Preferensi > Perusahaan

**Setup**:
- Name, address, phone, fax
- Start date (tanggal mulai data) - critical for opening balance
- Accounting period (default: Jan-Dec)
- Base currency (IDR for Indonesia)

**Opening Balance**:
- Set via tanggal mulai data (e.g., 31 Dec 2025 for 1 Jan 2026 start)
- Input saldo awal for: Accounts, Customers, Vendors, Inventory, Assets, Employees
- Opening Balance Equity account auto-created as contra account
- Must balance (Debit = Credit) after all saldo awal input
- Run Proses Akhir Bulan on start date to zero out Opening Balance Equity

**Evidence**: [Pengaturan Preferensi Perusahaan](https://account.co.id/pengaturan-preferensi-perusahaan-di-accurate-online/), [Opening Balance Setup](https://aplikasiakuntansi.co.id/cara-mengisi-saldo-awal-master-data-accurate-online/)

---

### 2. Multi-Branch (Cabang) - Add-on Feature
**Path**: Perusahaan > Cabang

**Architecture**:
- 1 database = all branches (not separate databases)
- Default branch \"Kantor Pusat\" created free on activation
- Additional branches charged per branch (99,900/month or 999,000/year per branch)

**Branch Hierarchy**:
```
Kantor Pusat (HQ)
├── Cabang Jakarta
├── Cabang Bandung
└── Cabang Surabaya
```

**Setup Process**:
1. Install Multi Branch add-on from Accurate Store
2. Default \"Kantor Pusat\" branch auto-created (free)
3. Add new branches: Perusahaan > Cabang > Tambah Baru
4. Configure branch info: name, address, NITKU (if tax-registered)
5. Set user access per branch (tab Daftar Pengguna)

**Access Control**:
- Per-branch user access (uncheck \"Semua Pengguna\" to restrict)
- Users see only assigned branches
- Prevents cross-branch data leakage
- Admin can access all branches

**Transaction Assignment**:
- Select branch when creating transactions (if user has multi-branch access)
- Can set default branch for: Items, Customers, Vendors, Accounts
- Branch field appears in transaction forms (top-right)

**Reporting**:
- Laba/Rugi per cabang (P&L by branch)
- Neraca per cabang (Balance Sheet by branch)
- Consolidated reports across all branches
- Filter reports by single/multiple branches

**Inter-Branch Transactions**:
- Transfer inventory between branches
- Inter-branch fund transfers
- Automatic journal entries for inter-branch movements

**Deactivation**:
- Deactivate branches (except default) to stop billing
- Set \"Non Aktif = Ya\" on branch record
- Billing stops next period

**Evidence**: [Fitur Cabang](https://solusiakuntansiindonesia.com/mengenal-fitur-cabang-di-accurate-online), [Multi Branch Setup](https://softwareaccounting.id/tag/apa-fungsi-fitur-multi-cabang-di-accurate-online), [Deactivate Branch](https://help.accurate.id/product/accurate-online/accurate-store/nonaktif-cabang)

---

### 3. Fiscal Year & Period Management

**Fiscal Year Setup**:
- Set in Pengaturan > Preferensi > Perusahaan > Periode Akuntansi
- Default: January - December
- Can customize for non-calendar fiscal years
- Determines when closing entries auto-execute

**Month-End Closing (Proses Akhir Bulan)**:
**Path**: Perusahaan > Proses Akhir Bulan

**Process**:
1. Select currency (IDR or foreign)
2. Enter exchange rate if using foreign currency
3. System auto-generates:
   - Asset depreciation journal
   - Exchange rate difference journal (if multi-currency)
4. Locks period after completion

**Year-End Closing**:
- Auto-executes when crossing fiscal year boundary
- Moves P&L balance to Retained Earnings (Laba Ditahan)
- Account set in Pengaturan > Preferensi > Akun Perkiraan > Perusahaan > Laba Ditahan
- No manual closing entries needed

**Period Locking**:
**Path**: Pengaturan > Preferensi > Pembatasan > Tanggal Transaksi

**Options**:
1. **Tidak Dibatasi**: No restrictions
2. **Berdasarkan Rentang Waktu**: Restrict by days before/after current date
   - Example: Prevent transactions >5 days old or >5 days future
3. **Berdasarkan Tanggal Tertentu**: Hard date cutoffs
   - Example: Block all transactions before 31 Dec 2025
4. **Berdasarkan Jumlah Hari Setelah Akhir Periode**: Days after period end
   - Example: Lock period 1 day after month-end

**Restriction Types**:
- **Peringati Jika**: Warning only (can proceed)
- **Cegah Jika**: Hard block (cannot proceed)

**Scope**:
- Semua Transaksi: All transactions
- Transaksi Berjurnal: Only transactions that create journal entries

**Evidence**: [Tutup Buku](https://abckotaraya.id/tutup-buku-accurate-online/), [Period Locking](https://help.accurate.id/product/accurate-online/fitur-aol/pengaturan/preferensi/cara-membuka-fitur-pembatasan/), [Fiscal Year](https://mitraku.id/aktivitas-tutup-buku-pada-accurate-online/)

---

### 4. Currencies (Mata Uang)
**Path**: Perusahaan > Mata Uang

**Setup**:
1. Add currency: Search by country or code (USD, EUR, SGD, etc.)
2. System auto-fills currency symbol & flag
3. Configure default accounts for each currency:
   - Piutang Usaha (Accounts Receivable)
   - Utang Usaha (Accounts Payable)
   - Uang Muka Penjualan (Sales Advance)
   - Uang Muka Pembelian (Purchase Advance)
   - Selisih Kurs (Exchange Rate Difference)

**Multi-Currency Accounts**:
- Create accounts with foreign currency for: Kas/Bank, Piutang, Aset Lancar Lainnya, Aset Lainnya, Hutang, Kewajiban Jangka Pendek, Kewajiban Jangka Panjang
- Set currency when creating account
- System prompts for exchange rate (auto-fetches current rate, can override)

**Transactions**:
- Select currency-specific account in transactions
- System auto-calculates exchange rate
- Exchange rate difference auto-journaled during Proses Akhir Bulan

**Evidence**: [Mata Uang Setup](https://help.accurate.id/product/accurate-online/fitur-aol/perusahaan/mata-uang/mengatur-mata-uang), [Multi-Currency](https://cpssoft.com/blog/fitur/multi-mata-uang-dengan-accurate-online/)

---

### 5. Tax Configuration (Pajak)
**Path**: Perusahaan > Pajak

**Activation**:
1. Pengaturan > Preferensi > Fitur > Perusahaan > Check \"Pajak\"
2. Fill NPWP info: Pengaturan > Preferensi > Pajak > Info Perusahaan
   - NPWP (16-digit NIK-based format since July 2024)
   - PKP confirmation date
   - Tax details

**Tax Codes**:
- Default: PPN 11% auto-created on activation
- Add custom tax codes: Perusahaan > Pajak > Tambah Baru
- Types: PPN, PPh 21, PPh 22, PPh 23, PPh 4(2), PPh Final, etc.
- Set percentage & default accounts (receivable/payable)

**Tax Assignment**:
- Items: Set tax code in Barang & Jasa > Tab Penjualan/Pembelian
- Contacts: Set tax status in Pelanggan/Pemasok > Tab Pajak
- Transactions: Auto-applies tax if item/contact configured

**NITKU (Branch Tax ID)**:
- Available after activating Pajak feature & filling NPWP
- Add NITKU per branch: Perusahaan > Cabang > NITKU field
- Required for branch-specific tax reporting

**Evidence**: [Tax Setup](https://www.szetoaccurate.com/cara-input-transaksi-ppn-di-accurate-online/), [NITKU](https://help.accurate.id/product/accurate-online/fitur-aol/perusahaan/cabang/nitku-cabang), [16-digit NPWP](https://indonesia.acclime.com/insights/new-16-digit-tax-id-nik-npwp/)

---

### 6. Numbering Formats (Penomoran)
**Path**: Pengaturan > Penomoran

**Setup**:
1. Create numbering format: Pengaturan > Penomoran > Tambah Baru
2. Select transaction type (Faktur Penjualan, Faktur Pembelian, etc.)
3. Name format (e.g., \"Faktur Penjualan Jakarta 2026\")

**Reset Options**:
- Tidak Reset: Continuous numbering
- Reset Setiap Hari: Daily reset
- Reset Setiap Bulan: Monthly reset
- Reset Setiap Tahun: Yearly reset

**Components**:
- Teks Pemisah: Custom text (INV, PO, BAYAR, etc.)
- Counter: Sequential number (set digit count, e.g., 5 = 00001)
- Hari: Day (21)
- Bulan: Month (07)
- Bulan Romawi: Roman month (VII)
- Tahun Singkat: Short year (26)
- Tahun: Full year (2026)

**Format Examples**:
- `INV/07/26/00001` = Invoice, July 2026, sequence 1
- `JKT-2607-00123` = Jakarta branch, July 26, sequence 123
- `PO/2026/00045` = Purchase Order, 2026, sequence 45

**Access Control**:
- Assign to specific users/groups/branches
- Uncheck \"Semua Pengguna\" to restrict
- Users see only assigned numbering formats

**Usage**:
- Select format in transaction form (top-right dropdown)
- Auto-increments on save
- Prevents duplicate numbers

**Evidence**: [Penomoran Setup](https://help.accurate.id/product/accurate-online/fitur-aol/pengaturan/penomoran/cara-membuat-penomoran-otomatis/), [Numbering Examples](https://penjualanonline.id/penomoran-accurate-online)

---

### 7. Payment Terms (Syarat Pembayaran)
**Path**: Perusahaan > Syarat Pembayaran

**Setup**:
- Create terms: Name, days (e.g., \"Net 30\" = 30 days)
- Assign to customers/vendors
- Auto-calculates due dates in transactions

---

### 8. Shipping Methods (Pengiriman)
**Path**: Perusahaan > Pengiriman

**Setup**:
- Add shipping methods (JNE, TIKI, Gojek, etc.)
- Assign to sales transactions
- Track shipping info per transaction

---

### 9. FOB Terms
**Path**: Perusahaan > FOB

**Setup**:
- Define FOB terms (FOB Destination, FOB Shipping Point, etc.)
- Assign to purchase/sales transactions
- Clarifies ownership transfer point

---

### 10. Departments & Projects
**Path**: Perusahaan > Departemen / Proyek

**Departments**:
- Organizational units (Sales, Marketing, Operations, etc.)
- Assign to transactions for cost tracking
- Report by department

**Projects**:
- Cost/profit centers
- Track project-specific revenue & expenses
- Project P&L reports

**Evidence**: [Departments & Projects](https://help.accurate.id/product/accurate-online/fitur-aol/perusahaan/menambahkan-data-departemen-dan-proyek)

---

### 11. Employees (Karyawan)
**Path**: Perusahaan > Karyawan

**Setup**:
- Employee master data: name, ID, position, department
- Assign to transactions (sales rep, purchaser, etc.)
- Link to payroll (if using payroll module)

---

### 12. Payroll Components (Gaji dan Tunjangan)
**Path**: Perusahaan > Gaji dan Tunjangan

**Setup**:
- Define salary components (base salary, allowances, deductions)
- Assign to employees
- Auto-calculate in payroll transactions

---

### 13. Recurring Transactions (Transaksi Berulang)
**Path**: Perusahaan > Transaksi Berulang

**Setup**:
1. Create template from existing transaction
2. Set recurrence: Daily, Weekly, Monthly, Yearly
3. Set start/end dates
4. System auto-generates transactions per schedule

**Use Cases**:
- Monthly rent payments
- Recurring subscriptions
- Regular supplier orders

---

### 14. Transaction Favorites (Transaksi Favorit)
**Path**: Perusahaan > Transaksi Favorit

**Setup**:
- Save frequently-used transactions as templates
- Quick-create from favorites
- Reduces data entry time

---

### 15. Approval Workflows (Approval)
**Path**: Perusahaan > Approval

**Setup**:
1. Define approval rules per transaction type
2. Set approvers (single/multi-level)
3. Transactions require approval before posting

**Workflow**:
- User creates transaction (status: Pending)
- Approver reviews & approves/rejects
- Approved transactions post to ledger

**Evidence**: [Approval Setup](https://help.accurate.id/product/accurate-online/fitur-aol/pengaturan/penyetuju-transaksi/atur-penyetuju-transaksi)

---

### 16. Activity Logs (Log Aktivitas)
**Path**: Perusahaan > Log Aktivitas

**Tracking**:
- User actions (create, edit, delete)
- Timestamps
- IP addresses
- Audit trail for compliance

---

## Multi-Branch Architecture

### Centralized vs. Decentralized Models

**Centralized (Recommended for AkuBook)**:
- HQ controls: Chart of Accounts, Items, Pricing, Tax Settings
- Branches: Input transactions only
- Consolidation: Auto-aggregated at HQ
- Reporting: HQ sees all, branches see own only

**Decentralized**:
- Branches: Full autonomy (own items, pricing, accounts)
- HQ: Consolidation only
- Use case: Franchises, independent subsidiaries

### Inter-Branch Transactions

**Inventory Transfer**:
1. Source branch: Persediaan > Transfer Antar Gudang
2. Select destination branch
3. System creates:
   - Debit: Inventory (destination)
   - Credit: Inventory (source)

**Fund Transfer**:
1. Source branch: Kas & Bank > Transfer Bank
2. Select destination branch account
3. System creates:
   - Debit: Cash (destination)
   - Credit: Cash (source)

**Inter-Branch Sales**:
- Branch A sells to Branch B
- Use internal pricing (transfer pricing)
- Eliminates in consolidated reports

### Consolidation

**Automatic**:
- Run consolidated reports: Laporan > Keuangan > Select \"Semua Cabang\"
- System auto-eliminates inter-branch transactions
- Produces group-level financials

**Manual Adjustments**:
- Create consolidation journals if needed
- Eliminate inter-company profits
- Adjust for transfer pricing

---

## Priority for AkuBook

### Phase 1: Foundation (Week 1-2)
1. **Company Profile**: Set name, address, NPWP, fiscal year
2. **Opening Balance**: Input saldo awal for all accounts (critical!)
3. **Currencies**: Add USD, EUR if needed for imports
4. **Tax Setup**: Activate Pajak, configure PPN 11%
5. **Numbering**: Create formats for invoices, POs, payments

### Phase 2: Multi-Branch (Week 3-4)
1. **Install Multi Branch**: Accurate Store > Multi Branch
2. **Create Branches**: Jakarta, Bandung, Surabaya (example)
3. **User Access**: Assign users to branches
4. **Branch Accounts**: Create branch-specific Kas/Bank accounts
5. **Test Transactions**: Input sample sales/purchases per branch

### Phase 3: Organizational Structure (Week 5-6)
1. **Departments**: Sales, Warehouse, Admin
2. **Projects**: Optional (if tracking project-based costs)
3. **Employees**: Add sales reps, warehouse staff
4. **Payment Terms**: Net 30, Net 60, COD

### Phase 4: Automation (Week 7-8)
1. **Recurring Transactions**: Monthly rent, utilities
2. **Approval Workflows**: PO approval, payment approval
3. **Transaction Favorites**: Common sales/purchase templates

### Phase 5: Period Management (Ongoing)
1. **Month-End Closing**: Run Proses Akhir Bulan monthly
2. **Period Locking**: Lock prior months after closing
3. **Year-End Closing**: Auto-executes at fiscal year-end

---

## Multi-Branch Scenarios

### Scenario 1: Independent Branch Operations
**Setup**:
- Each branch: Own Kas/Bank, Inventory, Customers, Vendors
- HQ: Consolidated view only
- Access: Branch users see own branch only

**Workflow**:
1. Branch inputs sales/purchases
2. Branch manages own inventory
3. HQ runs consolidated reports monthly
4. No inter-branch transactions

**Use Case**: Retail chains, regional offices

---

### Scenario 2: Central Inventory, Branch Sales
**Setup**:
- HQ: Manages inventory, purchasing
- Branches: Sales only (no inventory)
- Inventory transfers: HQ → Branches

**Workflow**:
1. HQ purchases inventory
2. HQ transfers inventory to branches
3. Branches sell from transferred inventory
4. HQ tracks branch-level profitability

**Use Case**: Distributors with central warehouse

---

### Scenario 3: Shared Inventory, Branch Autonomy
**Setup**:
- Shared inventory pool across branches
- Branches: Independent sales, purchasing
- Inter-branch transfers allowed

**Workflow**:
1. Any branch can purchase inventory
2. Branches transfer inventory as needed
3. Consolidated inventory tracking
4. Branch-level P&L with transfer pricing

**Use Case**: AkuBook (distributor with multiple warehouses)

---

## Evidence Summary

**Official Docs**:
- [Perusahaan Module](https://help.accurate.id/product/perusahaan) (16 features confirmed)
- [Multi Branch](https://help.accurate.id/product/cabang)
- [Proses Akhir Bulan](https://help.accurate.id/product/proses-akhir-bulan)
- [Mata Uang](https://help.accurate.id/product/mata-uang)
- [Pajak](https://help.accurate.id/product/pajak)

**Implementation Guides**:
- [Setup Awal Database](https://abckotaraya.id/setup-awal-database-accurate-online/)
- [Opening Balance](https://aplikasiakuntansi.co.id/cara-mengisi-saldo-awal-master-data-accurate-online/)
- [Multi Branch Setup](https://solusiakuntansiindonesia.com/mengenal-fitur-cabang-di-accurate-online)
- [Tutup Buku](https://abckotaraya.id/tutup-buku-accurate-online/)
- [Period Locking](https://softwareaccounting.id/setting-preferensi-pembatasan-pada-accurate-online)

**Best Practices**:
- [Multi-Branch for Distributors](https://trainingaccurate.com/blog/accurate-online-untuk-bisnis-banyak-cabang/)
- [Tax Configuration](https://www.szetoaccurate.com/cara-input-transaksi-ppn-di-accurate-online/)
- [Numbering Formats](https://help.accurate.id/product/accurate-online/fitur-aol/pengaturan/penomoran/cara-membuat-penomoran-otomatis/)

---

## Next Steps

1. **Review**: Validate Company module features against AkuBook requirements
2. **Design**: Map AkuBook branch structure to Accurate Online multi-branch
3. **Test**: Create test database with 2-3 branches, input sample transactions
4. **Document**: Create AkuBook-specific setup guide based on this doc
5. **Train**: Train team on multi-branch workflows

**Downstream Dependencies**:
- Inventory module (branch-level stock tracking)
- Sales module (branch-level sales)
- Purchasing module (branch-level purchasing)
- Reporting module (consolidated & branch-level reports)
