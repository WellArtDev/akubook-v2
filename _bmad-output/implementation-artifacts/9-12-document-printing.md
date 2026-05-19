# Story 9.12: Document Printing
**Epic:** 9 | **Story ID:** 9.12 | **Key:** 9-12-document-printing | **Priority:** P1
**Status:** done

## User Story
**Sebagai** User, **Saya ingin** print purchase documents, **Sehingga** provide physical copies

## Acceptance Criteria
- Purchase Request printable
- Purchase Order printable
- Goods Receipt printable
- Purchase Invoice printable
- Debit Note printable
- Print preview available
- Download/print history captured through existing print draft workflow

## MVP Scope
- Extend existing print draft system to support all Epic 9 purchase document types.
- Add source document lookup/detail support for purchase request, purchase invoice, and debit note.
- Keep existing purchase order and goods receipt support.
- Cover create-page access for all required purchase document types with feature tests.

## Out of Scope
- New binary PDF renderer dependency.
- Email sending integration.
- Custom per-document print templates beyond existing dot-matrix template workflow.

## Definition of Done
- [x] Purchase document types registered in `PrintDraft::DOCUMENT_TYPES`.
- [x] Source document selection supports Purchase Request, Purchase Order, Goods Receipt, Purchase Invoice, Debit Note.
- [x] Print preview remains available via existing `print-drafts.preview` flow.
- [x] Print recording remains available via existing `print-drafts.record-print` flow.
- [x] Feature tests cover purchase document type create-page access.
- [x] Full regression (`composer test`) passing.
- [x] Frontend build (`npm run build`) passing.

## Notes
- Mirror sales document printing (Story 8.12)
- Existing print draft workflow used instead of adding unverified DomPDF dependency.

## Dev Agent Record
### Completion Notes
- Extended existing print draft document type registry for all required purchase documents.
- Added purchase request, purchase invoice, and debit note source document lookup/detail support in `PrintDraftController`.
- Added regression coverage proving all required purchase document types can open print draft creation flow.

### File List
- `app/Http/Controllers/PrintDraftController.php`
- `app/Models/PrintDraft.php`
- `tests/Feature/PrintDraftTest.php`
- `_bmad-output/implementation-artifacts/9-12-document-printing.md`
- `_bmad-output/implementation-artifacts/sprint-status.yaml`

### Validation
- `php artisan test tests/Feature/PrintDraftTest.php` (7 passed, 18 assertions)
- `composer test` (461 passed, 2032 assertions)
- `npm run build` (passed)

### Change Log
- 2026-05-19: Extended print draft workflow for Epic 9 purchase documents and moved story to review.
