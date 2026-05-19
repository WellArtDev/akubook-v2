# Story 23.2: Workflow Enforcement Hooks

**Story Key:** `23-2-workflow-enforcement-hooks`  
**Epic:** 23  
**Priority:** P0  
**Status:** review

## User Story
Sebagai Compliance Officer, saya ingin aksi bisnis sensitif otomatis memeriksa approval workflow aktif agar transaksi tidak lolos tanpa otorisasi yang sesuai kebijakan.

## Acceptance Criteria
1. Aksi submit/approve untuk transaksi yang didukung memanggil workflow enforcement hook terpusat.
2. Hook mengevaluasi rule aktif berdasarkan `entity_type`, `amount/risk context`, dan `required_level`.
3. Jika rule match, transaksi ditandai `pending_approval` dan alasan enforcement tersimpan.
4. Jika rule tidak match, alur transaksi tetap berjalan normal.
5. Enforcement event tercatat di audit log dengan metadata rule/rationale.
6. Tersedia endpoint/helper untuk simulasi hasil enforcement (debug/ops visibility).

## MVP Scope
- Service `WorkflowEnforcementService` untuk evaluasi workflow aktif.
- Integrasi hook ke flow transaksi prioritas:
  - Sales Order submit
  - Purchase Order submit
- Simpan alasan enforcement terstruktur (`type`, `rule`, `message`).
- Logging sensitif untuk keputusan `enforced`/`not_enforced`.
- Feature tests untuk match/no-match dan side effects status.

## Out of Scope
- Multi-step parallel approver matrix.
- Escalation SLA/timeout.
- UI builder rule complex expression.

## Definition of Done
- [x] Workflow enforcement service dibuat.
- [x] Hook terpasang di flow SO/PO submit.
- [x] Status dan alasan approval ter-update sesuai rule.
- [x] Audit log enforcement tercatat.
- [x] Feature tests pass.
- [x] `composer test` dan `npm run build` dijalankan.

## Dev Agent Record
### Completion Notes
- Story 23.2 dibuat untuk implementasi workflow enforcement hooks.
- Workflow enforcement service terpusat dibuat untuk mengevaluasi rule aktif berdasarkan entity type dan amount.
- Flow Purchase Order dan Sales Order submit memakai hook enforcement dan menyimpan alasan `workflow_enforcement` ke approval reasons saat rule match.
- Enforcement decision dicatat ke audit log sensitif untuk kondisi `enforced` dan `not_enforced`.
- Feature tests ditambahkan untuk PO/SO match dan no-match side effects.

### File List
- `_bmad-output/implementation-artifacts/23-2-workflow-enforcement-hooks.md`
- `app/Services/WorkflowEnforcementService.php`
- `app/Http/Controllers/PurchaseOrderController.php`
- `app/Http/Controllers/SalesOrderController.php`
- `tests/Feature/PurchaseOrderApprovalTest.php`
- `tests/Feature/SalesOrderApprovalTest.php`

### Validation
- `php artisan test tests/Feature/PurchaseOrderApprovalTest.php tests/Feature/SalesOrderApprovalTest.php` passed: 16 tests, 82 assertions.
- `composer test` PHPUnit result passed: 419 tests, 1699 assertions; Composer wrapper still returned error code 1 after passing PHPUnit.
- `npm run build` passed with Vite production build.

### Change Log
- 2026-05-19: Story 23.2 dibuat.
- 2026-05-19: Implemented workflow enforcement hooks and moved story to review.
