# 10. Fixed Assets (Aset Tetap)

## Overview

Fixed Assets module in Accurate Online manages complete asset lifecycle from acquisition to disposal. Automates depreciation calculation, tracks asset location/category, supports Indonesian fiscal compliance (UU PPh Pasal 11), and maintains audit trail for all asset changes.

**Priority**: MEDIUM (Phase 2)  
**Complexity**: Medium - automated depreciation, fiscal compliance, multi-location tracking  
**Indonesian Tax Compliance**: Full support for fiscal depreciation per DJP regulations

---

## Core Features (10 Total)

### 1. Asset Registration & Master Data
**Function**: Record and manage complete asset information  
**Key Capabilities**:
- Asset name, code, acquisition date, usage date
- Purchase price, salvage value, useful life
- Category, location, department assignment
- Automatic journal creation on acquisition
- Import bulk assets via Excel
- Opening balance support (equity account)

**Recording Methods**:
- Direct payment (cash/bank)
- Purchase invoice (credit terms)
- Intermediate account for proper journal flow

### 2. Asset Categories
**Function**: Group assets for reporting and management  
**Key Capabilities**:
- Custom category creation
- Filter assets by category
- Category-based reporting
- Hierarchical grouping

### 3. Tax Asset Categories (Kategori Aset Tetap Pajak)
**Function**: Fiscal depreciation grouping per DJP regulations  
**Key Capabilities**:
- Kelompok 1-4 for non-building assets (4-20 years)
- Permanent/non-permanent building classification
- Automatic fiscal depreciation rate assignment
- Separate book vs tax depreciation tracking

**DJP Asset Groups** (UU PPh Pasal 11 ayat 6):
| Group | Useful Life | Straight-Line Rate | Declining Balance Rate |
|-------|-------------|-------------------|----------------------|
| 1 | 4 years | 25% | 50% |
| 2 | 8 years | 12.5% | 25% |
| 3 | 16 years | 6.25% | 12.5% |
| 4 | 20 years | 5% | 10% |
| Building (Permanent) | 20 years | 5% | N/A (straight-line only) |
| Building (Non-Permanent) | 10 years | 10% | N/A (straight-line only) |

### 4. Depreciation Calculation (Automatic)
**Function**: Auto-calculate monthly depreciation with multiple methods  
**Supported Methods**:
- **Straight-Line (Garis Lurus)**: Equal depreciation each period
  - Formula: `(Cost - Salvage Value) / Useful Life`
  - Example: Rp 240M car, 4 years = Rp 5M/month
- **Declining Balance (Saldo Menurun)**: Higher depreciation in early years
  - Formula: `Book Value × Depreciation Rate`
  - Double declining balance option available
- **Sum of Years Digits (Jumlah Digit Tahun)**: Accelerated depreciation
  - Formula: `Depreciable Cost × (Remaining Life / Sum of Digits)`
- **No Depreciation**: For land or non-depreciable assets

**Fiscal vs Commercial Depreciation**:
- Separate calculation for book (commercial) and tax (fiscal)
- Fiscal starts from acquisition date (UU PPh Pasal 11 ayat 3)
- Buildings: straight-line only (fiscal requirement)
- Non-buildings: choice of straight-line or declining balance
- Automatic journal generation at month-end

### 5. Asset Changes (Perubahan Aset Tetap)
**Function**: Record asset modifications without creating new asset  
**Change Types**:
- **Value Changes**: Add/reduce asset value (renovation, impairment)
- **Information Updates**: Location, department, category changes
- **Depreciation Method Change**: Switch between methods
- **Useful Life Adjustment**: Extend/reduce economic life
- **Audit Trail**: Full history of all changes with reasons

**Use Cases**:
- Major renovation increasing asset value
- Asset relocation between branches
- Accounting policy change (method switch)
- Asset upgrade extending useful life

### 6. Asset Disposal (Disposisi Aset Tetap)
**Function**: Record asset retirement, sale, or write-off  
**Disposal Types**:
- Sale (with gain/loss calculation)
- Scrapping/write-off (damaged/obsolete)
- Donation
- Loss/theft

**Automatic Calculations**:
- Book value at disposal date
- Accumulated depreciation reversal
- Gain/loss on disposal
- Journal entries:
  - DR: Cash/Receivable (if sold)
  - DR: Accumulated Depreciation
  - DR/CR: Gain/Loss on Disposal
  - CR: Fixed Asset

**Cancellation**: Can reverse disposal if recorded in error

### 7. Asset Transfer (Pindah Aset Tetap)
**Function**: Move assets between locations/departments  
**Key Capabilities**:
- Inter-location transfer
- Department reassignment
- Maintains depreciation continuity
- Transfer history tracking

### 8. Asset by Location (Aset Per Lokasi)
**Function**: View asset distribution across locations  
**Key Capabilities**:
- Multi-location asset tracking
- Location-based reporting
- Asset count per location
- Value summary by location

### 9. Asset Revaluation
**Function**: Adjust asset value to fair market value  
**Key Capabilities**:
- Revaluation increase/decrease
- Revaluation surplus account
- Impact on depreciation calculation
- Compliance with PSAK 16 (Indonesian GAAP)

### 10. Asset Maintenance Tracking
**Function**: Record maintenance costs and schedules  
**Key Capabilities**:
- Maintenance history log
- Scheduled maintenance alerts
- Maintenance cost allocation
- Capitalize vs expense decision support

---

## Depreciation Schedules

### Straight-Line Example
**Asset**: Laptop Rp 10,000,000  
**Useful Life**: 4 years (Group 1)  
**Salvage Value**: Rp 0  
**Annual Depreciation**: Rp 10,000,000 × 25% = Rp 2,500,000  
**Monthly Depreciation**: Rp 208,333

| Year | Beginning Balance | Depreciation | Accumulated | Book Value |
|------|------------------|--------------|-------------|------------|
| 1 | 10,000,000 | 2,500,000 | 2,500,000 | 7,500,000 |
| 2 | 7,500,000 | 2,500,000 | 5,000,000 | 5,000,000 |
| 3 | 5,000,000 | 2,500,000 | 7,500,000 | 2,500,000 |
| 4 | 2,500,000 | 2,500,000 | 10,000,000 | 0 |

### Declining Balance Example
**Asset**: Laptop Rp 10,000,000  
**Rate**: 50% (Group 1 declining balance)

| Year | Beginning Balance | Depreciation (50%) | Accumulated | Book Value |
|------|------------------|-------------------|-------------|------------|
| 1 | 10,000,000 | 5,000,000 | 5,000,000 | 5,000,000 |
| 2 | 5,000,000 | 2,500,000 | 7,500,000 | 2,500,000 |
| 3 | 2,500,000 | 1,250,000 | 8,750,000 | 1,250,000 |
| 4 | 1,250,000 | 625,000 | 9,375,000 | 625,000 |

---

## Key Workflows

### Workflow 1: Asset Acquisition (Cash Purchase)
1. Create intermediate account (Transaksi Aktiva Tetap)
2. Record payment: Kas & Bank → Pembayaran
   - DR: Transaksi Aktiva Tetap
   - CR: Cash/Bank
3. Register asset: Aset Tetap → Aset Tetap → Tambah
   - Fill asset details (name, date, category, useful life, method)
   - Expenditure account: Transaksi Aktiva Tetap
   - Amount: Purchase price
4. System creates journal:
   - DR: Fixed Asset (specific category)
   - CR: Transaksi Aktiva Tetap (clears intermediate account)
5. Asset ready for depreciation at month-end

### Workflow 2: Monthly Depreciation
1. Navigate to: Perusahaan → Proses Akhir Bulan
2. System auto-generates depreciation journal:
   - DR: Depreciation Expense
   - CR: Accumulated Depreciation
3. Review: Laporan → Laporan Penyusutan
4. Verify book value: Laporan → Daftar Aktiva Tetap

### Workflow 3: Asset Disposal (Sale)
1. Navigate to: Aset Tetap → Disposisi Aset Tetap
2. Select asset to dispose
3. Enter disposal details:
   - Disposal date
   - Check "Aset Dijual"
   - Sale price
   - Receiving account (Cash/Bank/Receivable)
   - Gain/Loss account
4. System calculates:
   - Book value at disposal
   - Gain/Loss = Sale Price - Book Value
5. Auto-journal:
   - DR: Cash/Receivable (sale price)
   - DR: Accumulated Depreciation
   - DR/CR: Gain/Loss on Disposal
   - CR: Fixed Asset (original cost)

### Workflow 4: Asset Revaluation
1. Navigate to: Aset Tetap → Perubahan Aset Tetap
2. Select asset to revalue
3. Choose change type: "Revaluasi Data"
4. Enter new fair value
5. System creates revaluation surplus/deficit
6. Depreciation recalculated from revalued amount

---

## Indonesian Tax Compliance

### Fiscal Depreciation Rules (UU PPh Pasal 11)
1. **Start Date**: Depreciation begins from acquisition/usage date (Pasal 11 ayat 3)
2. **Method Restrictions**:
   - Buildings: Straight-line ONLY
   - Non-buildings: Straight-line OR declining balance
3. **Rate Table**: Must follow DJP grouping (Pasal 11 ayat 6)
4. **Book vs Tax**:
   - Commercial depreciation: reported in financial statements
   - Fiscal depreciation: reported in SPT Tahunan
   - Differences tracked in Report Depreciation List

### Tax Reporting
- **Laporan Penyusutan Fiskal**: Fiscal depreciation schedule
- **Difference Interim Depreciation**: Book-tax reconciliation
- **SPT Tahunan**: Annual tax return with fiscal depreciation

### Common Fiscal Scenarios
- **Laptop (Group 1)**: 4 years, 25% straight-line or 50% declining
- **Vehicle (Group 2)**: 8 years, 12.5% straight-line or 25% declining
- **Machinery (Group 3)**: 16 years, 6.25% straight-line or 12.5% declining
- **Office Building (Permanent)**: 20 years, 5% straight-line only

---

## Priority for AkuBook MVP

**MEDIUM Priority** because:
- **Not Critical for Launch**: Basic accounting works without asset tracking
- **Phase 2 Value**: Needed for businesses with significant fixed assets
- **Compliance Requirement**: Essential for Indonesian tax reporting (DJP)
- **Automation Benefit**: Saves time vs manual depreciation calculation
- **Audit Support**: Provides complete asset lifecycle documentation

**Implementation Sequence**:
1. Phase 1: Core accounting (GL, AP, AR, Inventory)
2. **Phase 2**: Fixed Assets (this module)
3. Phase 3: Advanced features (multi-currency, consolidation)

---

**Source**: Accurate Online Help Documentation (https://help.accurate.id/product/asset-tetap/)  
**Last Updated**: May 2026  
**Compliance**: UU PPh Pasal 11 (Indonesian Income Tax Law)
