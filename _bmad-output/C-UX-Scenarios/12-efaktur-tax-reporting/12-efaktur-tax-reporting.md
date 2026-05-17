# 12: Sari's e-Faktur & Tax Reporting

**Project:** AkuBook  
**Created:** 2026-05-12  
**Method:** Whiteport Design Studio (WDS)

---

## Transaction (Q1)

**What this scenario covers:**
Month-end: Generate e-Faktur for sales invoices → export XML → upload to DJP → generate SPT Masa PPN

---

## Business Goal (Q2)

**Goal:** 🚀 SECONDARY: Tax Compliance (Indonesian Regulations)  
**Objective:** e-Faktur generation automated, SPT Masa PPN ready in < 1 hour vs 1 day manual

---

## User & Situation (Q3)

**Persona:** Sari (Finance Admin, PRIMARY)  
**Situation:** Month-end (before 15th of next month). Sari needs to generate e-Faktur for all sales invoices, export XML, upload to DJP portal, and generate SPT Masa PPN. Biasanya 1 hari (manual e-Faktur entry). Target: < 1 jam.

---

## Driving Forces (Q4)

**Hope:** e-Faktur auto-generated from sales invoices, XML export one-click, SPT Masa PPN ready, DJP submission on time.

**Worry:** Manual e-Faktur entry tedious, XML format errors, DJP rejection, late submission penalties (2% per month).

---

## Device & Starting Point (Q5 + Q6)

**Device:** Desktop  
**Entry:** Monthly routine — before 15th of next month, Sari starts e-Faktur generation from Tax module.

---

## Best Outcome (Q7)

**User Success:**
e-Faktur auto-generated for all sales invoices, XML exported, uploaded to DJP successfully, SPT Masa PPN generated. Total time < 1 hour. No DJP rejection, no penalties.

**Business Success:**
Tax compliance maintained, no penalties, audit-ready tax records, Finance Admin freed from manual e-Faktur entry.

---

## Shortest Path (Q8)

1. **Dashboard (Tax)** — Sari sees "e-Faktur Due" notification (before 15th), clicks to start
2. **Sales Invoice (List - Unprocessed)** — Sari sees 50+ sales invoices without e-Faktur, clicks "Generate e-Faktur Batch"
3. **e-Faktur Generation** — System auto-generates e-Faktur for all invoices:
   - NPWP validation (customer tax ID)
   - PPN calculation (11% or 12% based on invoice date)
   - e-Faktur number assignment (from DJP range)
4. **e-Faktur Review** — Sari spot-checks e-Faktur details, verifies DPP (Dasar Pengenaan Pajak) correct
5. **Export XML** — Sari clicks "Export XML", system generates DJP-compliant XML file
6. **Upload to DJP** — Sari uploads XML to DJP portal (external), receives approval
7. **SPT Masa PPN** — Sari generates SPT Masa PPN report (PPN Keluaran - PPN Masukan), submits to DJP ✓

---

## Trigger Map Connections

**Persona:** Sari (Finance Admin, PRIMARY)

**Driving Forces Addressed:**
- ✅ **Want:** Tax compliance automated, no manual e-Faktur entry
- ❌ **Fear:** DJP rejection, late submission penalties, audit findings

**Business Goal:** 🚀 SECONDARY: Tax compliance → automated e-Faktur → Finance Admin freed up

---

## Scenario Steps

| Step | Folder | Purpose | Exit Action |
|------|--------|---------|-------------|
| 12.1 | `12.1-tax-dashboard/` | See e-Faktur notification | Click "Generate e-Faktur" |
| 12.2 | `12.2-sales-invoice-list/` | Select unprocessed invoices | Click "Generate Batch" |
| 12.3 | `12.3-efaktur-generation/` | Auto-generate e-Faktur | Click "Review e-Faktur" |
| 12.4 | `12.4-efaktur-review/` | Spot-check e-Faktur details | Click "Export XML" |
| 12.5 | `12.5-xml-export/` | Generate DJP XML file | Download XML |
| 12.6 | `12.6-spt-masa-ppn/` | Generate SPT Masa PPN report | Submit to DJP ✓ |

## e-Faktur Validation Rules

**NPWP Validation:**
- Customer NPWP must be valid (15 digits)
- Non-PKP customers: e-Faktur not required

**PPN Rate Selection:**
- Invoice date < 2026-01-01: 11%
- Invoice date ≥ 2026-01-01: 12%
- System auto-selects based on invoice date

**DPP Calculation:**
- DPP (Dasar Pengenaan Pajak) = Invoice Amount (excluding PPN)
- PPN = DPP × Rate (11% or 12%)
- Total = DPP + PPN

**e-Faktur Number:**
- Format: 010.000-YY.NNNNNNNN
- Range assigned by DJP
- Sequential numbering

## Integration Points

**Upstream:**
- Sales Invoices (source data)
- Customer Master (NPWP validation)
- Company Settings (DJP credentials, e-Faktur range)

**Downstream:**
- General Ledger (PPN Keluaran account)
- Tax Reports (SPT Masa PPN)
- Audit Trail (e-Faktur history)

---

_Scenario 12: Sari's e-Faktur & Tax Reporting_
