---
stepsCompleted: ["step-01-init", "step-02-discovery", "step-02b-vision", "step-02c-executive-summary", "step-03-success"]
inputDocuments:
  - "_bmad-output/A-Product-Brief/product-brief.md"
  - "_bmad-output/accurate-research/00-INDEX.md"
  - "_bmad-output/accurate-research/COMPLETION-SUMMARY.md"
  - "_bmad-output/C-UX-Scenarios/00-ux-scenarios.md"
  - "_bmad-output/C-UX-Scenarios/ALL-SCENARIOS-SUMMARY.md"
documentCounts:
  briefCount: 1
  researchCount: 2
  scenarioCount: 2
  projectDocsCount: 0
classification:
  projectType: "web_app"
  domain: "business_software_erp"
  complexity: "medium-high"
  projectContext: "greenfield"
workflowType: 'prd'
---

# Product Requirements Document - AkuBook

**Author:** WellArtDev
**Date:** 2026-05-14

## Executive Summary

AkuBook adalah all-in-one ERP untuk medium enterprises di Indonesia yang mengintegrasikan accounting, inventory management, dan human resource management dalam satu platform self-hosted. Target market adalah established distributors, retailers, dan service companies (100+ employees, Rp 1M+ revenue/month) yang frustrated dengan subscription costs, tool sprawl, dan lack of data control dari solusi existing seperti Accurate, Jurnal, atau Mekari.

Medium enterprises di Indonesia saat ini terjebak dalam fragmentasi sistem: accounting di satu software, inventory di spreadsheet, HRM di sistem terpisah. Hasilnya adalah manual data entry berulang (satu transaksi = 3x input), inkonsistensi data, dan ketidakmampuan mendapatkan business insight yang holistik. Mereka membutuhkan ERP yang powerful seperti enterprise solutions, namun tanpa complexity dan cost yang prohibitive.

AkuBook menyelesaikan ini dengan native integration antar semua module (95%+ transactions auto-post to journal), one-time payment model (50% lebih murah dari Accurate), dan self-hosted deployment yang memberikan full data control. Finance Admin hanya review dan approve, bukan manual entry. Sistem ini production-ready untuk berbagai industri dengan workflow yang familiar bagi pengguna Accurate, termasuk support untuk dot matrix printer dan data migration tool dari Accurate (offline & online).

### What Makes This Special

**Native Integration, Bukan API Glue**
Transaksi penjualan otomatis generate journal entries, attendance online langsung mempengaruhi payroll calculation, inventory movement terintegrasi dengan cost accounting. Bukan silo terpisah yang dipaksa berkomunikasi via API. Satu database transaction ensure data integrity - journal entries harus balance, no corruption.

**One-Time Payment Ownership Model**
No vendor lock-in, no monthly subscription bleeding. Deploy di infrastructure sendiri (on-premise atau cloud pilihan sendiri). Full control atas data - critical untuk data sovereignty. Full customization capability karena source code accessible.

**Accurate-Compatible Workflow**
Dirancang untuk smooth migration dari Accurate. Familiar interface, similar workflow, bahkan support dot matrix printer untuk dokumen bisnis. Includes data migration tool untuk import dari Accurate (offline & online). Reducing training time dan adoption friction.

**Built for Indonesian Businesses**
Bahasa Indonesia first. Fiscal year dan reporting sesuai standar Indonesia. Support untuk business practices lokal (dot matrix printing, specific document formats). Timezone, currency, dan localization yang proper. Tax compliance (e-Faktur, PPN, PPh) built-in.

## Project Classification

**Project Type:** Web Application (SaaS B2B)
- Desktop-first web application dengan responsive design
- Mobile native app untuk attendance (geo-location, face recognition)
- Multi-tenant architecture (multi-company, multi-branch, multi-warehouse)
- Self-hosted deployment option dengan cloud alternative

**Domain:** Business Software / ERP
- Accounting + Inventory + HRM integration
- Indonesian tax compliance (e-Faktur, PPN, PPh)
- Not pure fintech (no payment processing/banking core)
- Standard business software dengan compliance requirements

**Complexity:** Medium-High
- Multi-module integration dengan real-time data sync
- Indonesian tax compliance dan accounting standards
- Audit trail dan data integrity requirements (database transactions)
- Not as complex as healthcare/fintech, tapi bukan simple CRUD app

**Project Context:** Greenfield
- New product built from scratch
- Informed by Accurate feature research (161+ features documented)
- 17 UX scenarios designed (95 pages total)
- Target: feature parity dengan Accurate Online untuk core modules

## Success Criteria

### User Success

**Finance Admin / Accounting:**
- Monthly close completion time: 3 days → 4-6 hours (80%+ time reduction)
- 95%+ transactions auto-post to journal tanpa manual entry
- Zero manual reconciliation antar sistem (accounting, inventory, HRM)
- "Aha!" moment: Pertama kali lihat sales invoice otomatis generate journal entry yang balance

**HRD Manager:**
- Payroll processing time: 2 days → 4 hours (75%+ time reduction)
- Zero manual attendance data pull dari machine (auto-sync)
- Zero manual payroll calculation errors (automated calculation)
- "Relief" moment: Payroll selesai dalam 4 jam dengan confidence 100% accurate

**Business Owner:**
- Real-time business visibility: Dashboard show current state tanpa tunggu end-of-month report
- Decision-making speed: Hours bukan days untuk dapat insight
- "Empowerment" moment: Buka dashboard, langsung paham cash position, AR/AP aging, inventory levels

**Purchasing & Sales Team:**
- Approval workflow streamlined: Hours bukan days untuk PO/SO approval
- Real-time inventory visibility across warehouses
- "Efficiency" moment: Create PO, auto-post inventory dan AP, no manual follow-up

### Business Success

**Year 1 (Month 1-12):**
- 10-20 paying customers (businesses successfully migrated dari Accurate)
- Revenue target: Rp 500M - 1B (average Rp 50M per customer)
- Customer success rate: 80%+ customers complete migration dan go-live
- Time-to-value: 2-4 weeks dari install sampai productive use
- Churn rate: <10% (1-2 customers max)

**Year 2 (Month 13-24):**
- 100+ active installations
- Revenue target: Rp 5B+ (mix of new customers + recurring support)
- Market validation: 3+ industry verticals (distributor, retail, service)
- Customer satisfaction: NPS 50+ (promoters > detractors)

**Year 3 (Month 25-36):**
- 500+ businesses using AkuBook
- Developer community: 50+ active contributors
- Platform ecosystem: 10+ third-party integrations/extensions
- Market position: Top 3 ERP untuk SME Indonesia

### Technical Success

**Performance:**
- Page load time: <2 seconds untuk dashboard dan list views
- Report generation: <5 seconds untuk standard reports (P&L, Balance Sheet)
- Real-time sync: <1 second untuk inventory updates across modules
- Uptime: 99.5%+ (acceptable downtime untuk self-hosted model)

**Data Integrity:**
- Zero accounting errors (journal entries always balance)
- 100% audit trail coverage (every transaction logged)
- Database transaction integrity (no partial commits)
- Zero data loss during migration dari Accurate

**Integration:**
- 95%+ transactions auto-post to journal (primary metric)
- ZKTeco attendance device integration: 100% sync success rate
- WhatsApp notification delivery: 95%+ success rate
- Accurate data migration: 90%+ data accuracy

### Measurable Outcomes

**3-Month Success (Post-Launch):**
- 3-5 pilot customers live dan productive
- 90%+ auto-posting achieved (target: 95%)
- Zero critical bugs in production
- Average time-to-value: 3 weeks

**6-Month Success:**
- 10+ paying customers
- 95%+ auto-posting achieved (primary metric hit)
- Customer testimonials: 5+ positive reviews
- Feature parity: 80% Accurate core features implemented

**12-Month Success:**
- 20+ paying customers
- Revenue: Rp 1B+
- Market validation: 3+ industries using AkuBook
- Community: 100+ active users in support forum

## Product Scope

### MVP - Minimum Viable Product

**Core Modules (Must Have):**
1. **Foundation & Setup**
   - Company profile, multi-branch, fiscal year
   - User management, RBAC
   - Chart of accounts setup
   - Industry templates (distributor, retail, service)

2. **Accounting Core**
   - Journal entries (manual & auto-generated)
   - Posting & period close
   - Financial reports (Trial Balance, General Ledger, P&L, Balance Sheet)
   - Audit trail

3. **Sales & Purchasing**
   - Sales Order → Invoice → Delivery → Payment
   - Purchase Order → Receiving → Payment
   - Auto-posting to journal (AR/AP, Revenue/COGS, Inventory)
   - Customer & supplier management

4. **Inventory Management**
   - Item master data
   - Multi-warehouse stock tracking
   - Stock movements & transfers
   - Cost accounting (FIFO/Average)
   - Auto-posting inventory transactions

5. **Human Resources**
   - Employee master data
   - Attendance management (online: geo-location + face recognition)
   - Leave management & approval
   - Payroll processing dengan auto-posting
   - Payroll components (salary, allowances, deductions)

**Critical Integrations:**
- ZKTeco attendance device integration
- WhatsApp notifications (via Wablas)
- Accurate data migration tool (offline & online)

**Compliance (Must Have for Indonesian Market):**
- e-Faktur generation & export
- PPN calculation & reporting
- Tax compliance (basic)

### Growth Features (Post-MVP)

**Phase 2 (Month 7-12):**
- Fixed Assets module (depreciation, disposal)
- Advanced Tax (PPh, full e-Faktur integration)
- Bank reconciliation (auto-matching)
- Advanced reporting & analytics
- Mobile app (React Native/Flutter) untuk attendance & approval

**Phase 3 (Year 2):**
- Manufacturing module (BOM, production orders)
- Project management
- CRM capabilities
- E-commerce integration
- API ecosystem untuk third-party integrations

### Vision (Future)

**Year 3+:**
- Multi-currency support
- Multi-language (English, regional languages)
- AI-powered insights (Accurate Insight competitor)
- Marketplace untuk industry-specific add-ons
- Open-source core dengan commercial support model
- Platform play: Third-party developers build extensions
- Regional expansion: Southeast Asia markets
