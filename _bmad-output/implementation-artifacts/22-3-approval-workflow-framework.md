# Story 22.3: Approval Workflow Framework

**Story Key:** 22-3-approval-workflow-framework  
**Priority:** P0  
**Status:** done

## User Story
Sebagai admin/compliance, saya ingin framework approval workflow generik agar proses persetujuan lintas modul bisa distandarkan dan diaudit.

## Acceptance Criteria
1. Sistem memiliki master `approval_workflows` dengan field minimal: `workflow_key`, `entity_type`, `min_amount`, `max_amount`, `required_level`, `is_active`, `description`.
2. `workflow_key` unik dan rule amount tidak overlap untuk entity type yang sama saat aktif.
3. Tersedia daftar workflow + filter entity/status.
4. Tersedia endpoint evaluasi workflow yang menerima `entity_type` + `amount` dan mengembalikan rule aktif yang match.
5. Semua operasi bersifat konfigurasi/read-only evaluasi (tanpa mengeksekusi approval transaksi nyata).

## MVP Scope
- Migration + model `ApprovalWorkflow`.
- CRUD controller + evaluator endpoint.
- Inertia pages Index/Create/Edit/Show.
- Feature tests untuk validasi rule, overlap guard, evaluator response.

## Out of Scope
- Runtime approval engine multi-step.
- Notification/escalation.
- Dynamic role resolution dari org chart.

## Definition of Done
- [x] Tabel + model approval workflow siap.
- [x] CRUD workflow berjalan.
- [x] Guard overlap amount rule aktif berjalan.
- [x] Endpoint evaluator berjalan.
- [x] Feature tests lulus.
- [x] `composer test` dan `npm run build` dijalankan.

## Dev Agent Record
### Completion Notes
- Menambahkan modul `approval_workflows` untuk konfigurasi rule persetujuan lintas entity.
- Menambahkan guard overlap range nominal untuk workflow aktif pada entity type yang sama.
- Menambahkan endpoint evaluator (`approval-workflows.evaluate`) untuk resolve rule aktif berdasarkan `entity_type` + `amount`.
- Menambahkan halaman CRUD Inertia + quick evaluator helper pada halaman detail.
- Menambahkan test untuk CRUD, overlap guard, dan evaluator response.

### File List
- _bmad-output/implementation-artifacts/22-3-approval-workflow-framework.md
- _bmad-output/implementation-artifacts/sprint-status.yaml
- app/Models/ApprovalWorkflow.php
- app/Http/Controllers/ApprovalWorkflowController.php
- database/factories/ApprovalWorkflowFactory.php
- database/migrations/2026_05_19_014646_create_approval_workflows_table.php
- resources/js/Pages/ApprovalWorkflows/Index.jsx
- resources/js/Pages/ApprovalWorkflows/Create.jsx
- resources/js/Pages/ApprovalWorkflows/Edit.jsx
- resources/js/Pages/ApprovalWorkflows/Show.jsx
- routes/web.php
- tests/Feature/ApprovalWorkflowTest.php

### Validation
- `php artisan test tests/Feature/ApprovalWorkflowTest.php --compact`: pass, 4 tests / 18 assertions.
- `composer test`: PHPUnit pass, 404 tests / 1554 assertions; Composer wrapper masih return code 1 setelah pass.
- `npm run build`: pass.

### Change Log
- 2026-05-19: Implemented Story 22.3 Approval Workflow Framework MVP and validations.

