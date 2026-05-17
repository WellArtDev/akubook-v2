# Accurate Online Feature Research - Tax Integration

**Research Date:** 2026-05-12
**Source:** https://help.accurate.id/product/smartlink-tax

---

[Content from bg_cfd20c79 - Tax Integration research]

## 1. Core Tax Features

### 1.1 PPN (VAT) Management
**Tax Rate Support:**
- PPN 11% (legacy)
- PPN 12% (current, effective 2025)
- Multiple DPP (Dasar Pengenaan Pajak) options: 11/12, 100%, 10%, 20%, 30%, 40%

**PPN Calculation:**
- Auto-calculate PPN on sales/purchase transactions
- DPP formula: `$F{salesInvoice.dppAmount}`
- Tax code assignment per item/service
- Tax rate override per transaction line

**PPN Tracking:**
- PPN Keluaran (Output VAT) from sales
- PPN Masukan (Input VAT) from purchases
- Tax reconciliation between transactions and e-Faktur

### 1.2 PPh (Income Tax) Management
- PPh calculation (not detailed in docs, but implied by "Smartlink Tax" scope)
- Integration with GL for tax posting

### 1.3 e-Faktur Integration
**Two Systems:**

**A. e-Faktur Legacy (PJAP-based)**
- Connect to DJP e-Filing via PJAP (Penyedia Jasa Aplikasi Perpajakan)
- Install PJAP app from Accurate Store
- One PJAP per database
- Direct sync: no manual export/import

**B. e-Faktur CTAS (Coretax)**
- New DJP Coretax system integration
- Via Pajak.io PJAP
- Upload Faktur Pajak Keluaran to Coretax
- Requires PIC + Penandatangan setup

**e-Faktur Workflow:**
1. Transaction created in Sales/Purchasing
2. Tax calculated with NSFP (Nomor Seri Faktur Pajak)
3. Upload to DJP via PJAP
4. Match transaction ↔ e-Faktur by NSFP
5. Email Faktur Pajak to customer

### 1.4 Email Faktur Pajak
- Auto-match Sales Invoice ↔ Bukti Faktur Pajak by NSFP
- Send tax invoice PDF to customer email
- Email address from Customer Master
- Track sent invoices (disappear from pending list after send)

---

## 2. Tax Calculation Logic

### 2.1 PPN Calculation Rules
```
Base Amount (DPP) = Transaction Amount × DPP Percentage
PPN Amount = DPP × PPN Rate

Example (DPP 11/12, PPN 12%):
Transaction: Rp 1,000,000
DPP = 1,000,000 × (11/12) = Rp 916,667
PPN = 916,667 × 12% = Rp 110,000
Total = 1,000,000 + 110,000 = Rp 1,110,000
```

### 2.2 Tax Code Assignment
- Set default tax codes per item/service (Barang/Jasa master)
- Tax Penjualan (Sales Tax Code)
- Tax Pembelian (Purchase Tax Code)
- Override on transaction line if needed

### 2.3 DPP Configuration
**Default DPP Setting:**
- Menu: Pengaturan → Preferensi → Pajak
- Set default DPP (e.g., 11/12 for most cases)
- Per-transaction override available

---

## 3. e-Faktur Integration Requirements

### 3.1 PJAP Setup (Legacy)
**Installation:**
1. Menu: Smartlink → e-Faktur Legacy
2. Click "Hubungkan" → redirects to Accurate Store
3. Choose PJAP app (paid subscription)
4. Install PJAP app
5. Register/connect database to PJAP account

**Limitation:** One PJAP per database

### 3.2 Coretax CTAS Setup
**Prerequisites:**
1. Add PIC (Person in Charge) in Pajak.io
2. Add Penandatangan Faktur in Pajak.io

**Configuration:**
1. Menu: Smartlink Tax → e-Faktur CTAS
2. Auto-connect to same NPWP as legacy e-Faktur
3. Pengaturan → Pihak Berwenang e-Faktur
4. Add Penandatangan Faktur + Pembuat Faktur
5. Save → ready to upload

**Upload Workflow:**
- Create sales invoice with PPN
- Upload Faktur Pajak Keluaran to Coretax via CTAS
- System generates NSFP
- Match with transaction

### 3.3 Tax Invoice Data Requirements
**Mandatory Fields:**
- NPWP (Nomor Pokok Wajib Pajak)
- NITKU (for foreign entities)
- Kode Negara (country code)
- Nomor Faktur Pajak (can be blank, auto-generated)
- Kode Pajak (tax code per line item)
- DPP Amount
- PPN Amount

---

## 4. AkuBook Implementation Requirements

### Database Schema

```sql
-- Tax Codes
tax_codes (
  code VARCHAR(20) PRIMARY KEY,
  name VARCHAR(100),
  rate DECIMAL(5,2),
  type ENUM('PPN', 'PPh21', 'PPh22', 'PPh23', 'PPh4(2)'),
  dpp_percentage DECIMAL(5,2),
  effective_from DATE,
  effective_to DATE
)

-- Tax Rates History
tax_rates (
  id BIGINT PRIMARY KEY,
  tax_type VARCHAR(20),
  rate DECIMAL(5,2),
  effective_from DATE,
  effective_to DATE
)

-- e-Faktur Queue
efaktur_queue (
  id BIGINT PRIMARY KEY,
  invoice_id BIGINT,
  nsfp VARCHAR(50),
  status ENUM('pending', 'uploaded', 'approved', 'rejected'),
  pjap_provider VARCHAR(50),
  uploaded_at TIMESTAMP,
  error_message TEXT
)

-- e-Faktur Signers
efaktur_signers (
  id BIGINT PRIMARY KEY,
  company_id BIGINT,
  name VARCHAR(100),
  npwp VARCHAR(20),
  role ENUM('penandatangan', 'pembuat'),
  is_active BOOLEAN
)
```

### API Integration Points

**PJAP Integration:**
- POST `/api/efaktur/upload` - Upload Faktur Pajak
- GET `/api/efaktur/status/{nsfp}` - Check upload status
- POST `/api/efaktur/approve` - Approve faktur
- GET `/api/efaktur/download/{nsfp}` - Download approved PDF

**Coretax CTAS Integration:**
- POST `/api/coretax/upload` - Upload to Coretax
- GET `/api/coretax/status/{nsfp}` - Check status
- POST `/api/coretax/spt/export` - Export SPT XML

---

## Key Business Rules

1. **One PJAP per database** - cannot mix multiple PJAP providers
2. **NSFP uniqueness** - each invoice gets unique NSFP
3. **Tax rate effective dates** - system must enforce correct rate per transaction date
4. **DPP calculation** - must support multiple DPP percentages
5. **Email automation** - auto-match and send after e-Faktur approval
6. **XML immutability** - changes must be in source transactions, not exported XML
