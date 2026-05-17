# Scenario 12: e-Faktur Tax Reporting

**User:** Finance Admin / Tax Officer  
**Priority:** HIGH (Compliance)  
**Frequency:** Monthly  
**Success Metric:** e-Faktur export in <30 minutes

---

## Scenario Goal

Finance Admin exports sales and purchase invoices to e-Faktur format for tax reporting to DJP (Direktorat Jenderal Pajak).

---

## User Context

**Who:** Sari (Finance Admin) or Tax Officer responsible for VAT reporting

**When:** Monthly (before tax reporting deadline)

**Why:** Comply with Indonesian tax regulations, report VAT to DJP

**Current Pain (from Accurate):** Manual e-Faktur data entry, format errors, time-consuming, compliance risk

---

## Sunshine Path (Happy Flow)

### Step 1: Review Tax Transactions

**Page:** Tax Dashboard

**User Action:**
- Opens Tax module
- Selects period (April 2026)

**System Shows:**
- Tax summary:
  - Sales invoices: 120 (PPN Keluaran: Rp 300M)
  - Purchase invoices: 80 (PPN Masukan: Rp 200M)
  - Net VAT payable: Rp 100M
  - e-Faktur status:
    - ✅ 115 sales invoices ready
    - ⚠️ 5 sales invoices missing NPWP
    - ✅ 78 purchase invoices ready
    - ⚠️ 2 purchase invoices missing e-Faktur number

**User Input:**
- Reviews tax summary
- Clicks "Fix Issues"

**System Response:**
- Shows transactions with missing data

**Next:** Fix missing data

---

### Step 2: Fix Missing Data

**Page:** Tax Data Validation

**User Action:**
- Reviews transactions with missing data

**System Shows:**
- Missing data list:
  - Invoice INV-2026-04-116: Customer NPWP missing
  - Invoice INV-2026-04-117: Customer NPWP missing
  - Invoice INV-2026-04-118: Customer NPWP missing
  - Invoice INV-2026-04-119: Customer NPWP missing
  - Invoice INV-2026-04-120: Customer NPWP missing
  - Purchase INV-SUP-2026-04-079: e-Faktur number missing
  - Purchase INV-SUP-2026-04-080: e-Faktur number missing

**User Input:**
- Clicks invoice to edit
- Updates customer NPWP
- Updates e-Faktur number
- Saves changes

**System Response:**
- Validates NPWP format
- Updates invoice data
- Re-checks validation
- Shows "All data valid" status

**Next:** Export to e-Faktur

---

### Step 3: Export Sales Invoices (e-Faktur Keluaran)

**Page:** e-Faktur Export

**User Action:**
- Clicks "Export Sales Invoices"
- Selects period (April 2026)

**System Shows:**
- Export options:
  - Format: e-Faktur CSV (DJP format)
  - Include: All posted invoices
  - Exclude: Cancelled invoices
  - Total invoices: 120
  - Total PPN: Rp 300,000,000

**User Input:**
- Reviews export summary
- Clicks "Export"

**System Response:**
- Generates e-Faktur CSV file:
  - FK (Faktur Keluaran) header
  - FAPR (Faktur Pajak) detail
  - LT (Lawan Transaksi) customer data
  - OF (Objek Faktur) item detail
- Downloads file: eFaktur_Keluaran_April_2026.csv
- Shows "Export Complete" status

**Next:** Import to e-Faktur application

---

### Step 4: Export Purchase Invoices (e-Faktur Masukan)

**Page:** e-Faktur Export

**User Action:**
- Clicks "Export Purchase Invoices"
- Selects period (April 2026)

**System Shows:**
- Export options:
  - Format: e-Faktur CSV (DJP format)
  - Include: All posted invoices
  - Exclude: Cancelled invoices
  - Total invoices: 80
  - Total PPN: Rp 200,000,000

**User Input:**
- Reviews export summary
- Clicks "Export"

**System Response:**
- Generates e-Faktur CSV file:
  - PM (Pajak Masukan) header
  - FAPR (Faktur Pajak) detail
  - LT (Lawan Transaksi) supplier data
  - OF (Objek Faktur) item detail
- Downloads file: eFaktur_Masukan_April_2026.csv
- Shows "Export Complete" status

**Next:** Import to e-Faktur application

---

### Step 5: Generate SPT Masa PPN

**Page:** SPT Masa PPN

**User Action:**
- Clicks "Generate SPT Masa PPN"
- Selects period (April 2026)

**System Shows:**
- SPT Masa PPN summary:
  - **A. Penyerahan Barang dan Jasa (PPN Keluaran)**
    - Ekspor: Rp 0
    - Penyerahan dalam negeri: Rp 2,500,000,000
    - PPN Keluaran: Rp 300,000,000
  - **B. Perolehan Barang dan Jasa (PPN Masukan)**
    - Impor: Rp 0
    - Perolehan dalam negeri: Rp 1,666,666,667
    - PPN Masukan: Rp 200,000,000
  - **C. Perhitungan PPN Kurang/Lebih Bayar**
    - PPN Keluaran: Rp 300,000,000
    - PPN Masukan: Rp 200,000,000
    - PPN Kurang Bayar: Rp 100,000,000

**User Input:**
- Reviews SPT summary
- Clicks "Export to PDF"

**System Response:**
- Generates SPT Masa PPN PDF
- Downloads file: SPT_Masa_PPN_April_2026.pdf

**Next:** Submit to DJP

---

### Step 6: Record Tax Payment

**Page:** Tax Payment

**User Action:**
- Clicks "Record Tax Payment"
- Enters payment details

**System Shows:**
- Tax payment form:
  - Tax type: PPN
  - Period: April 2026
  - Amount: Rp 100,000,000
  - Payment date: 2026-05-15
  - Payment method: Bank transfer
  - NTPN (Nomor Transaksi Penerimaan Negara)

**User Input:**
- Fills payment details
- Uploads payment receipt
- Clicks "Record Payment"

**System Response:**
- Creates tax payment record
- Auto-posts journal entry:
  - DR: VAT Payable (Rp 100,000,000)
  - CR: Cash in Bank (Rp 100,000,000)
- Shows "Payment Recorded" status

**Next:** Done (tax reporting complete)

---

## Pages/Screens Needed

1. **Tax Dashboard** - Tax summary and status
2. **Tax Data Validation** - Fix missing data
3. **e-Faktur Export** - Export sales and purchase invoices
4. **SPT Masa PPN** - Generate tax return
5. **Tax Payment** - Record tax payment

---

## Data Models Required

### Tables

**tax_invoices**
- id, company_id, invoice_id, invoice_type (sales/purchase)
- tax_period, npwp, efaktur_number, tax_base, tax_amount
- status (draft/posted/exported), exported_at
- created_at, updated_at

**tax_exports**
- id, company_id, export_type (sales/purchase), tax_period
- file_path, invoice_count, total_tax_amount
- exported_by, exported_at, created_at, updated_at

**tax_payments**
- id, company_id, tax_type (ppn/pph), tax_period
- amount, payment_date, payment_method, ntpn
- receipt_path, journal_entry_id, created_at, updated_at

**spt_masa_ppn**
- id, company_id, tax_period, ppn_keluaran, ppn_masukan
- ppn_kurang_bayar, status (draft/submitted), pdf_path
- submitted_at, created_at, updated_at

---

## e-Faktur CSV Format

**FK (Faktur Keluaran) Header:**
```
FK,KD_JENIS_TRANSAKSI,FG_PENGGANTI,NOMOR_FAKTUR,MASA_PAJAK,TAHUN_PAJAK,TANGGAL_FAKTUR,NPWP,NAMA,ALAMAT_LENGKAP,JUMLAH_DPP,JUMLAH_PPN,JUMLAH_PPNBM,ID_KETERANGAN_TAMBAHAN,FG_UANG_MUKA,UANG_MUKA_DPP,UANG_MUKA_PPN,UANG_MUKA_PPNBM,REFERENSI
```

**FAPR (Faktur Pajak) Detail:**
```
FAPR,KODE_OBJEK,NAMA,HARGA_SATUAN,JUMLAH_BARANG,HARGA_TOTAL,DISKON,DPP,PPN,TARIF_PPNBM,PPNBM
```

---

## Auto-Posting Rules

**Tax Payment:**
- DR: VAT Payable (PPN Kurang Bayar)
- CR: Cash in Bank
- **Trigger:** When tax payment recorded

**Example Entry:**
```
DR: VAT Payable                         Rp 100,000,000
    CR: Cash in Bank - BCA              Rp 100,000,000
```

---

## Acceptance Criteria

**Functional:**
- ✅ Export sales invoices to e-Faktur format
- ✅ Export purchase invoices to e-Faktur format
- ✅ Generate SPT Masa PPN
- ✅ Record tax payments
- ✅ Validate NPWP and e-Faktur numbers
- ✅ Auto-post tax payment journal entry

**Performance:**
- ✅ Export 1000+ invoices in <2 minutes
- ✅ SPT generation in <30 seconds

**Security:**
- ✅ Only authorized users can export tax data
- ✅ Audit trail for all tax activities

**UX:**
- ✅ Clear validation messages
- ✅ One-click export
- ✅ DJP-compliant CSV format

---

## Design Notes

**Tone:**
- Professional, compliant (regulatory requirement)
- Clear validation messages
- Helpful guidance for DJP format

**UX Principles:**
- Validation before export (prevent errors)
- One-click export (fast processing)
- DJP-compliant format (no manual editing)

**Mobile Consideration:**
- Tax reporting desktop-only (complex compliance)

---

## Related Scenarios

- **02: Sales Order Flow** - Sales invoices source
- **06: Purchase Order Flow** - Purchase invoices source
- **03: Monthly Close** - Tax reporting part of close

---

## Accurate Feature Parity

**Accurate e-Faktur includes:**
- e-Faktur export
- SPT Masa PPN

**AkuBook Enhancement:**
- Auto-validation (Accurate manual)
- One-click export (Accurate multi-step)
- Tax payment recording (Accurate limited)

---

**Scenario Status:** ✅ Ready for Implementation  
**Next:** Design wireframes for 5 pages in this flow
