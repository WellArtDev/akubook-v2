# Scenario 12: e-Faktur & Tax Reporting - Overview

**Project:** AkuBook  
**Scenario:** 12 - e-Faktur & Tax Reporting  
**Created:** 2026-05-13  
**Status:** ✅ Complete - All 6 pages designed

---

## Scenario Summary

**User:** Sari (Finance Admin)  
**Goal:** Generate e-Faktur and SPT Masa PPN in < 1 hour (vs 1 day manual)  
**Success Metric:** Tax compliance automated, no DJP rejection, no penalties

---

## Pages Designed

### Page 12.1: Tax Dashboard
e-Faktur alerts, compliance summary, unprocessed invoices, submission deadlines

### Page 12.2: Sales Invoice List (Unprocessed)
Filterable list with NPWP validation, batch selection for e-Faktur generation

### Page 12.3: e-Faktur Generation
Auto-generate e-Faktur with progress tracking, validation, error handling

### Page 12.4: e-Faktur Review
Spot-check generated e-Faktur, validation indicators, edit/delete options

### Page 12.5: XML Export
Generate DJP-compliant XML file, download, upload instructions

### Page 12.6: SPT Masa PPN
Calculate net PPN, generate report, e-Billing code, submission tracking

---

## Design Patterns Established

### 1. NPWP Validation
- Real-time validation (15 digits, valid format)
- Visual indicators (green checkmark / red X)
- Non-PKP customer handling

### 2. PPN Rate Auto-Selection
- Date-based logic (11% before 2026, 12% after)
- Automatic calculation
- Clear display of rate used

### 3. Batch Processing
- Select multiple invoices
- Progress tracking
- Error handling with retry

### 4. DJP Compliance
- XML format validation
- e-Faktur number assignment
- SPT calculation accuracy

### 5. Deadline Tracking
- Countdown to submission deadline
- Alert notifications
- Penalty warnings

---

## Technical Specifications

### APIs Required
1. Tax Dashboard API
2. Unprocessed Invoices API
3. e-Faktur Generation Batch API
4. e-Faktur Review API
5. XML Export API
6. SPT Masa PPN API

### Key Features
- Automated e-Faktur generation
- NPWP validation
- PPN rate auto-selection (11% / 12%)
- DJP-compliant XML export
- SPT Masa PPN calculation
- e-Billing code generation

---

## Success Metrics

### User Success
- ✅ e-Faktur generation in < 1 hour
- ✅ No manual e-Faktur entry
- ✅ XML export one-click
- ✅ SPT ready for submission

### Business Success
- ✅ Tax compliance maintained
- ✅ No DJP rejection
- ✅ No late submission penalties
- ✅ Audit-ready tax records

---

## Files Created

```
12-efaktur-tax-reporting/
├── 12-efaktur-tax-reporting.md
├── 12.1-tax-dashboard/
│   └── 12.1-tax-dashboard.md
├── 12.2-sales-invoice-list/
│   └── 12.2-sales-invoice-list.md
├── 12.3-efaktur-generation/
│   └── 12.3-efaktur-generation.md
├── 12.4-efaktur-review/
│   └── 12.4-efaktur-review.md
├── 12.5-xml-export/
│   └── 12.5-xml-export.md
└── 12.6-spt-masa-ppn/
    └── 12.6-spt-masa-ppn.md
```

---

_Scenario 12: e-Faktur & Tax Reporting - Complete 2026-05-13_
