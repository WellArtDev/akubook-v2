# Story 8.12: Document Printing

**Epic:** 8 - Customer & Sales Management  
**Story ID:** 8.12  
**Story Key:** 8-12-document-printing  
**Status:** review  
**Priority:** P1 (Should Have)

---

## User Story

**Sebagai** User  
**Saya ingin** print sales documents  
**Sehingga** dapat provide physical copies untuk customer dan internal records

---

## Acceptance Criteria

### AC1: Sales Document Types
- Sales Quotation printable
- Sales Order printable
- Delivery Order printable
- Sales Invoice printable
- Credit Note printable

### AC2: Print Workflow
- Print preview available through print draft workflow
- Document source lookup available per sales document type
- Print history can be recorded
- Existing dot-matrix template workflow remains compatible

### AC3: Download/Email MVP
- Existing print draft preview output can be used for browser print/download flow
- Email and binary PDF renderer dependency deferred until delivery channel is confirmed

---

## MVP Scope

- Extend `PrintDraft` document type whitelist for all required sales documents
- Add source document lookup/detail for sales quotation, sales order, and credit note
- Keep existing sales invoice and delivery order print support
- Add feature test coverage for sales document create-page access
- Preserve purchase document printing from Story 9.12

## Out of Scope

- New DomPDF dependency
- Email delivery integration
- Custom per-document Blade PDF templates
- Direct printer integration

---

## Definition of Done

- [x] Sales quotation print type supported
- [x] Sales order print type supported
- [x] Delivery order print type supported
- [x] Sales invoice print type supported
- [x] Credit note print type supported
- [x] Print draft preview workflow remains covered
- [x] Tests added/updated
- [x] `composer test` passes
- [x] `npm run build` passes
- [x] Story and sprint status updated to review

---

## Notes

- Implementation uses existing print draft and dot-matrix template workflow instead of introducing unverified PDF/email dependencies.
- Mirrors Story 9.12 purchase document printing extension.

---

## Dev Agent Record

### Completion Notes

- Extended print draft document whitelist with all required sales document types.
- Added source document lookup/detail for sales quotations, sales orders, and sales returns as credit notes.
- Preserved existing delivery order, sales invoice, and purchase document print support.
- Added feature coverage proving all Story 8.12 sales document types can open the print draft create workflow.

### File List

- `app/Http/Controllers/PrintDraftController.php`
- `app/Models/PrintDraft.php`
- `tests/Feature/PrintDraftTest.php`
- `_bmad-output/implementation-artifacts/8-12-document-printing.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation

- `php artisan test tests/Feature/PrintDraftTest.php` passed: 8 tests, 23 assertions.
- `composer test` passed: 474 tests, 474 passed, 2171 assertions.
- `npm run build` passed.

### Change Log

- 2026-05-19: Implemented sales document printing MVP and moved story to review.
