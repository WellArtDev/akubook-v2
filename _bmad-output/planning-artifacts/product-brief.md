# Product Brief: AkuBook

**Date:** 2026-05-12  
**Author:** WellArtDev  
**Status:** Draft v1

## Executive Summary

AkuBook adalah comprehensive ERP system yang dirancang khusus untuk medium enterprises di Indonesia. Sistem ini mengintegrasikan accounting, inventory management, dan human resource management dalam satu platform self-hosted yang memberikan full control atas data tanpa biaya subscription berulang.

Medium enterprises di Indonesia saat ini terjebak dalam fragmentasi sistem - accounting di satu software, inventory di spreadsheet, HRM di sistem terpisah. Hasilnya: manual data entry berulang, inkonsistensi data, dan ketidakmampuan mendapatkan business insight yang holistik. Mereka membutuhkan ERP yang powerful seperti enterprise solutions, namun tanpa complexity dan cost yang prohibitive.

AkuBook menyelesaikan ini dengan native integration antar semua module, industry-aware setup templates, dan self-hosted deployment yang memberikan data sovereignty. Sistem ini production-ready untuk berbagai industri - dari distributor sound system hingga toko roti - dengan workflow yang familiar bagi pengguna Accurate.

## The Problem

**Fragmentasi Sistem yang Mahal dan Tidak Efisien**

Medium enterprises di Indonesia menghadapi dilema: ERP enterprise terlalu mahal dan complex, sementara software accounting standalone tidak cukup untuk operasional mereka yang sudah berkembang.

**Pain Points Konkret:**

1. **Manual Data Entry Berulang**: Transaksi penjualan dicatat di sistem inventory, lalu di-input ulang ke accounting software, kemudian di-entry lagi ke spreadsheet untuk analisis. Satu transaksi = 3x input.

2. **Inkonsistensi Data**: Ketika data tersebar di multiple systems, versi truth menjadi kabur. Finance report tidak match dengan inventory report. HR data tidak sync dengan payroll calculation.

3. **Blind Spots dalam Decision Making**: Owner tidak bisa melihat real-time business health karena data tersebar. Butuh tunggu end-of-month manual consolidation untuk dapat insight.

4. **Subscription Fatigue**: Biaya subscription berulang untuk multiple software menggerus margin. Belum lagi biaya training dan maintenance untuk setiap sistem.

5. **Data Sovereignty Concerns**: Data bisnis sensitif berada di cloud provider asing. Compliance dan privacy menjadi concern, terutama untuk bisnis yang handle data karyawan dan customer.

**Siapa yang Merasakan:**
- **Accounting Staff**: Overwhelmed dengan manual reconciliation antar sistem
- **Finance Team**: Kesulitan generate consolidated reports untuk decision making
- **Owner**: Tidak punya real-time visibility atas business performance
- **HR Staff**: Manual calculation payroll dari attendance data yang tidak terintegrasi
- **Sales Team**: Workflow terhambat karena harus switch antar multiple systems

**Cost of Status Quo:**
- Waktu terbuang untuk manual data entry dan reconciliation
- Errors dan inkonsistensi data yang impact decision making
- Biaya subscription berulang untuk multiple software
- Missed opportunities karena delayed insights

## The Solution

**Integrated ERP Platform dengan Self-Hosted Freedom**

AkuBook adalah full-stack web application yang mengintegrasikan accounting, inventory, dan HRM dalam satu platform cohesive. Built dengan Laravel 13, React 18, dan PostgreSQL 17, sistem ini memberikan enterprise-grade capabilities dengan deployment flexibility.

**Core Experience:**

1. **Native Integration**: Transaksi penjualan otomatis generate journal entries. Attendance online langsung mempengaruhi payroll calculation. Inventory movement terintegrasi dengan cost accounting. Bukan silo terpisah yang dipaksa berkomunikasi.

2. **Industry-Aware Setup**: Setup wizard dengan pre-configured templates untuk berbagai industri. Distributor sound system mendapat chart of accounts dan workflow yang berbeda dengan toko roti atau bengkel. Sistem langsung "just works" tanpa extensive configuration.

3. **Self-Hosted Deployment**: Deploy di infrastructure sendiri - on-premise atau cloud pilihan sendiri. Full control atas data, no monthly subscription bleeding, dan customization capability tanpa vendor lock-in.

4. **Familiar Workflow**: Interface dan workflow dirancang familiar bagi pengguna Accurate - reducing training time dan adoption friction. Termasuk support untuk dot matrix printer untuk dokumen bisnis.

5. **Complete Audit Trail**: Setiap transaksi dan perubahan ter-log untuk compliance requirements. Database transactions ensure data integrity - journal entries harus balance, no corruption.

**What You Can Do:**

- **Manage Organization**: Multi-branch, multi-department, multi-warehouse operations dengan proper access control
- **Accounting Operations**: Chart of accounts, fiscal periods, journal entries, posting, financial reports (Trial Balance, General Ledger, P&L, Balance Sheet)
- **Sales & Purchasing**: Quotation, SO, DO, Invoice, Returns - dengan auto journal generation dan inventory integration
- **Inventory Management**: Stock tracking, warehouse transfers, cost accounting, reorder points
- **Human Resources**: Employee management, attendance (online/offline), leave management, payroll processing
- **Reporting & Analytics**: Real-time dashboards, customizable reports, export capabilities

## What Makes This Different

**1. Integration Magic**

Bukan collection of modules yang dipaksa berkomunikasi via API. Ini native integration dari ground up. Satu transaksi penjualan trigger cascade: inventory berkurang, journal entry ter-generate, customer balance ter-update, sales report ter-refresh - semua dalam satu database transaction.

**2. Self-Hosted Freedom**

No vendor lock-in. No monthly subscription bleeding. Data stays in-house - critical untuk data sovereignty. Deploy di infrastructure pilihan sendiri. Full customization capability karena source code accessible.

**3. Industry-Aware Templates**

Setup wizard dengan pre-configured chart of accounts, workflows, dan reports untuk berbagai industri. Distributor, retail, bakery, workshop - masing-masing dapat template yang sesuai. Sistem production-ready dari hari pertama.

**4. Accurate-Compatible Workflow**

Dirancang untuk smooth migration dari Accurate. Familiar interface, similar workflow, bahkan support dot matrix printer untuk dokumen bisnis. Includes data migration tool untuk import dari Accurate (offline & online).

**5. Built for Indonesian Businesses**

Bahasa Indonesia first. Fiscal year dan reporting sesuai standar Indonesia. Support untuk business practices lokal (dot matrix printing, specific document formats). Timezone, currency, dan localization yang proper.

**Unfair Advantage:**

Execution speed dan focus. Tidak mencoba jadi everything untuk everyone. Target jelas: medium enterprises di Indonesia yang butuh integrated ERP tanpa enterprise complexity dan cost. Build fast, iterate based on real user feedback, deliver value incrementally.

## Who This Serves

**Primary Users:**

**1. Accounting Staff**
- **Need**: Efficient monthly closing, accurate journal entries, easy reconciliation
- **Success**: Complete monthly closing faster dengan fewer errors, no manual data entry antar sistem

**2. Finance Team**
- **Need**: Real-time financial reports, consolidated view, analysis capabilities
- **Success**: Generate reports on-demand tanpa waiting for manual consolidation, confident decision making

**3. Business Owner**
- **Need**: Real-time business visibility, holistic insights, control over data
- **Success**: Dashboard yang show business health at a glance, make informed decisions quickly

**4. HR Staff**
- **Need**: Automated payroll calculation, integrated attendance, leave management
- **Success**: Process payroll tanpa manual calculation errors, attendance data automatically integrated

**5. Sales Team**
- **Need**: Fast transaction processing, integrated workflow, document printing
- **Success**: Process sales orders efficiently, print invoices dengan dot matrix printer, no system switching

**6. Admin/IT**
- **Need**: Easy deployment, maintainable system, proper access control
- **Success**: Deploy dan maintain sistem tanpa extensive technical expertise, proper RBAC implementation

**Secondary Users:**

- **Warehouse Staff**: Inventory tracking, stock movements, receiving goods
- **Department Managers**: Department-level reports, approval workflows
- **Auditors**: Complete audit trail, compliance reports

## Success Criteria

**User Success Signals:**

- Accounting team completes monthly closing **30% faster** than previous manual process
- HR team processes payroll **without calculation errors** for 3 consecutive months
- Management accesses real-time reports **daily** instead of waiting for monthly manual reports
- Staff onboards and uses system **within 1 week** without extensive training
- Data migration from Accurate completed with **100% data integrity** verification

**Business Objectives:**

- All 13 modules delivered and **operational** in production environment
- System handles client's transaction volume **without performance degradation**
- **Zero critical bugs** in production after go-live
- Client accepts system as **complete replacement** for Accurate
- Print functionality working for all documents with **dot matrix printer support**

**Technical Metrics:**

- Database transactions ensure **zero data corruption**
- Journal entries always **balance** (debit = credit)
- Audit trail captures **100% of transactions** and changes
- System uptime **>99%** in production
- Page load times **<2 seconds** for standard operations

**Adoption Indicators:**

- Client's team operates system **independently** after initial training
- User satisfaction score **>4/5** after 3 months usage
- Feature utilization rate **>70%** for core modules
- Support ticket volume **decreases** after first month

## Scope

**Phase 1 & 2 - Foundation & Accounting Core** ✅ **DELIVERED**

- Authentication & Authorization (Spatie Laravel Permission)
- Organization Management (Branch, Department, Position, Warehouse)
- User & Role Management dengan RBAC
- Company Settings & Auto Numbering
- Complete Audit Logging
- Chart of Accounts dengan hierarchical structure
- Fiscal Period Management
- Journal Entry System dengan double-entry validation
- Financial Reports (Trial Balance, General Ledger, P&L, Balance Sheet)
- Industry-specific CoA templates
- Data migration tool dari Accurate

**Phase 3 - Sales & Purchasing** (Next Priority)

- Customer & Supplier Management
- Sales cycle: Quotation → SO → DO → Invoice → Payment
- Purchase cycle: PR → PO → Receipt → Invoice → Payment
- Returns processing (sales & purchase)
- Dot matrix print templates dengan edit-before-print
- Auto journal generation dari transactions

**Phase 4 - Inventory Management**

- Item master data dengan categories
- Stock tracking multi-warehouse
- Stock movements & transfers
- Cost accounting (FIFO/Average)
- Reorder points & stock alerts
- Physical stock opname

**Phase 5 - Human Resources**

- Employee master data
- Attendance management (online/offline)
- Leave management & approval
- Payroll processing dengan auto calculation
- Payroll components (salary, allowances, deductions)
- Payroll reports & slip generation

**Explicitly Out of Scope (for now):**

- Manufacturing/production module
- Project management
- CRM capabilities beyond basic customer management
- E-commerce integration
- Mobile apps (web-responsive only)
- Multi-currency (IDR only for v1)
- Multi-language (Bahasa Indonesia only for v1)

## Vision

**Year 1: Accurate Replacement**

Menjadi viable replacement untuk Accurate bagi medium enterprises di Indonesia. Focus pada core ERP capabilities dengan superior integration dan self-hosted flexibility. Target: 10-20 businesses successfully migrated dari Accurate.

**Year 2: Industry Leader**

Expand industry templates dan vertical-specific features. Menjadi go-to ERP untuk specific industries (distributor, retail, service businesses). Add marketplace untuk industry-specific add-ons dan customizations. Target: 100+ active installations.

**Year 3: Platform Play**

Evolve menjadi platform dengan API ecosystem. Third-party developers dapat build integrations dan extensions. Community-driven development untuk industry-specific modules. Open-source core dengan commercial support model. Target: 500+ businesses, thriving developer community.

**Long-term Aspiration:**

Democratize enterprise-grade ERP untuk medium businesses di Indonesia dan Southeast Asia. Prove bahwa powerful business software tidak harus mahal atau complex. Build sustainable business model yang align dengan customer success - bukan subscription trap.

**What Success Looks Like:**

Dalam 3 tahun, ketika medium enterprise di Indonesia butuh ERP, mereka tidak lagi terpaksa pilih antara "too expensive" atau "too limited". AkuBook menjadi obvious choice: powerful, affordable, self-hosted, dan built untuk Indonesian businesses.

---

**Next Steps:**

1. ✅ Foundation & Accounting Core delivered (Phase 1 & 2)
2. 🎯 Create UX Design untuk Sales & Purchasing module (Phase 3)
3. 🎯 Create Architecture decisions document
4. 📋 Sprint planning untuk Phase 3 implementation
