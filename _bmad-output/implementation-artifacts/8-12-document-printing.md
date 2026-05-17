# Story 8.12: Document Printing

**Epic:** 8 - Customer & Sales Management  
**Story ID:** 8.12  
**Story Key:** 8-12-document-printing  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** P1 (Core)

---

## User Story

**Sebagai** User  
**Saya ingin** print sales documents  
**Sehingga** saya dapat provide physical copies ke customer

---

## Business Context

Document Printing untuk generate PDF:
- **Quotation**: Professional quote document
- **Sales Order**: Order confirmation
- **Delivery Order**: Shipping document
- **Invoice**: Tax invoice (Faktur Pajak)
- **Credit Note**: Return document

---

## Acceptance Criteria

### AC1: Quotation Print
- Company header & logo
- Quotation details
- Customer details
- Line items table
- Terms & conditions
- Signature section

### AC2: Sales Order Print
- Similar to quotation
- Add delivery address
- Add payment terms

### AC3: Delivery Order Print
- DO details
- Customer & delivery address
- Items table (product, qty, unit)
- Driver & vehicle info
- Signature section (sender & receiver)

### AC4: Invoice Print
- Tax invoice format
- NPWP details
- Invoice details
- Line items dengan tax breakdown
- Payment instructions
- Due date prominent

### AC5: Credit Note Print
- Credit note details
- Original invoice reference
- Return reason
- Items table
- Amount to credit

### AC6: Print Options
- Print preview
- Download PDF
- Send via email
- Print directly

---

## Technical Specifications

`php
// DocumentPrintService
public function generateQuotationPDF(\\);
public function generateSalesOrderPDF(\\);
public function generateDeliveryOrderPDF(\\);
public function generateInvoicePDF(\\);
public function generateCreditNotePDF(\\);
`

### PDF Templates
- Use Laravel DomPDF
- Blade templates untuk each document
- Company logo & branding
- Professional layout

---

## Definition of Done

- [ ] DocumentPrintService created
- [ ] PDF templates (Blade)
- [ ] Print controller & routes
- [ ] Print preview UI
- [ ] Download PDF
- [ ] Email PDF
- [ ] Tests (80%+ coverage)
- [ ] Merged to main

---

## Notes

- PDF library: Laravel DomPDF
- Template location: resources/views/pdf/
- Logo: storage/app/public/logo.png
- Font: Use web-safe fonts
- Paper size: A4
