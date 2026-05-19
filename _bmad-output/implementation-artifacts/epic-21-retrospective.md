# Epic 21 Retrospective

**Epic:** 21 - Platform Hardening & Stability  
**Date:** 2026-05-19  
**Status:** done

## Story Status Summary

| Story | Status | Ringkasan |
|---|---|---|
| 21.1 Test Wrapper Stability | review | Script test wrapper dibersihkan; PHPUnit pass tetap terlapor code 1 di wrapper composer. |
| 21.2 Vite OXC Migration | review | Migrasi plugin React ke versi kompatibel Vite 8; warning esbuild lama hilang. |
| 21.3 Lint & Static Guardrails | review | Guardrail command ditambah (`composer guardrails:*`); Pint dijadikan non-blocking karena legacy formatting debt. |
| 21.4 Security & Permission Audit | review | Halaman audit route security + deteksi public/unprotected mutation route. |
| 21.5 Release Readiness Checklist | review | Checklist readiness terpusat (route critical + env sanity) dengan pass/fail summary. |

## Outcome Epic 21

1. Baseline stabilitas platform naik: ada guardrail command, audit security route, dan checklist release readiness.
2. Warning Vite esbuild berhasil dihilangkan lewat upgrade plugin React.
3. Visibilitas risiko release meningkat lewat halaman audit + readiness checks.
4. Masalah lama `composer test` wrapper return code 1 tetap ada walau seluruh PHPUnit suite pass.

## Validasi Teknis

- Targeted feature tests Story 21 pass.
- `composer test`: **PHPUnit pass 392 tests / 1479 assertions**, namun wrapper composer tetap return code 1.
- `npm run build`: pass, warning Vite esbuild lama tidak muncul lagi.

## What Went Well

- Incremental hardening dilakukan tanpa mengganggu flow bisnis modul besar (finance/inventory/hr/payroll).
- Pattern Inertia + Laravel tetap konsisten: cepat implement halaman audit/checklist.
- Pemetaan route kritikal (PWA/service worker/security/dashboard/report) jadi eksplisit dan bisa diaudit cepat.

## Challenges & Debt

1. **Composer wrapper false-failure** masih unresolved untuk `composer test`/script yang memanggil `@php artisan test`.
2. **Pint baseline** belum bisa dijadikan hard gate karena banyak legacy formatting debt lintas file.
3. Security audit masih route/middleware level; belum sampai permission matrix per role/action.

## Action Items

1. Isolasi root cause wrapper composer code 1 (shell/ansi/exit propagation) dan buat workaround permanen.  
2. Jalankan repo-wide format normalization bertahap supaya `vendor/bin/pint --test` bisa jadi mandatory gate.  
3. Tambahkan permission coverage matrix (route x role x action) di audit layer berikutnya.  
4. Integrasikan readiness checklist ke SOP release agar dipakai sebelum deploy.

## Next Epic Preparation (Epic 22)

- Pastikan gate release untuk Epic 22 pakai baseline:
  - `composer guardrails:config`
  - `php artisan test`
  - `npm run build`
  - security audit page
  - release readiness page
- Keep risk note: sampai wrapper issue selesai, keputusan release pakai sumber kebenaran `php artisan test` langsung + log evidence.
