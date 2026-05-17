# Accurate Online Feature Research - Master Index

**Research Date:** 2026-05-12  
**Purpose:** Complete feature parity analysis for AkuBook ERP development  
**Source:** https://help.accurate.id/product/fitur-aol

---

## Research Status

✅ **14/14 modules researched** (all librarian agents complete)

---

## Module Research Summary

### Core Modules (Completed)

1. **Dashboard** (bg_f12a97f0) ✅
   - 10 customizable widgets
   - Real-time KPIs and metrics
   - File: `01-dashboard.md`

2. **Buku Besar (General Ledger)** (bg_93b59bba) ✅
   - Chart of accounts
   - Journal entries and posting
   - File: `02-general-ledger.md`

3. **Penjualan (Sales)** (bg_db4a26ae) ✅
   - 28 features (quotation, SO, invoice, delivery, return)
   - File: `03-sales.md`

4. **Pembelian (Purchasing)** (bg_db4a26ae) ✅
   - 17 features (PR, PO, receiving, payment)
   - File: `04-purchasing.md`

5. **Persediaan (Inventory)** (bg_6046d489) ✅
   - 26 features (multi-warehouse, COGS, stock movements)
   - File: `05-inventory.md`

### Configuration & Setup (Completed)

6. **Pengaturan (Settings)** (bg_59138044) ✅
   - 6 features (users, roles, preferences, integrations)
   - File: `06-settings.md`

7. **Perusahaan (Company)** (bg_59138044) ✅
   - 16 features (company profile, multi-branch, fiscal year)
   - File: `07-company.md`

### Financial Modules (Completed)

8. **Kas & Bank (Cash & Bank)** (bg_6c9362ed) ✅
   - 7 features (cash management, bank reconciliation, payments)
   - File: `08-cash-bank.md`

9. **Asset Tetap (Fixed Assets)** (bg_6e3be842) ✅
   - 10 features (depreciation, disposal, tracking)
   - File: `10-fixed-assets.md`

### Tax & Compliance (Completed)

10. **Smartlink Tax** (bg_cfd20c79) ✅
    - 3 features (PPN, PPh, e-Faktur integration)
    - File: `09-tax-integration.md` ✅ SAVED

### Manufacturing (Completed)

11. **Manufaktur** (bg_3fc9d9f9) ✅
    - 19 features (BOM, production orders, work orders)
    - File: `11-manufacturing.md`

### Reporting & Analytics (Completed)

12. **Laporan (Reports)** (bg_544d8fc5) ✅
    - 9 report categories (financial, operational, tax)
    - File: `12-reports.md`

### Advanced Features (Completed)

13. **Additional Features** (bg_c5d0436c) ✅
    - 2FA (Google Authenticator)
    - AI Akuntansi (Ailita)
    - PPN adjustment mechanism
    - File: `13-additional-features.md`

14. **Accurate Insight** (bg_c031c443) ✅
    - Business intelligence and analytics
    - KPI tracking and dashboards
    - File: `14-accurate-insight.md`

---

## Key Findings

### Feature Count by Module

| Module | Feature Count | Priority for AkuBook |
|--------|---------------|---------------------|
| Penjualan (Sales) | 18 | HIGH (distributor core) |
| Persediaan (Inventory) | 26 | HIGH (multi-warehouse) |
| Manufaktur (Manufacturing) | 19 | MEDIUM (Phase 2) |
| Pembelian (Purchasing) | 17 | HIGH (distributor core) |
| Perusahaan (Company) | 16 | HIGH (setup foundation) |
| Dashboard | 10 | HIGH (user experience) |
| Buku Besar (GL) | 10 | HIGH (accounting core) |
| Asset Tetap (Fixed Assets) | 10 | MEDIUM (Phase 2) |
| Laporan (Reports) | 9 | HIGH (decision making) |
| Kas & Bank (Cash & Bank) | 7 | HIGH (cash flow) |
| Pengaturan (Settings) | 6 | HIGH (configuration) |
| Smartlink Tax | 3 | HIGH (Indonesian compliance) |

**Total Accurate Features:** 161+ documented features

---

## Critical Features for AkuBook MVP

### Must-Have (Phase 1)

1. **Dashboard** - Real-time widgets, customizable
2. **General Ledger** - Chart of accounts, journal entries, auto-posting
3. **Sales** - Quotation, SO, Invoice, Delivery (surat jalan)
4. **Purchasing** - PR, PO, Receiving, Payment
5. **Inventory** - Multi-warehouse, stock movements, COGS calculation
6. **Cash & Bank** - Payment processing, bank reconciliation
7. **Company Setup** - Company profile, fiscal year, multi-company
8. **Settings** - Users, roles, RBAC, module configuration
9. **Reports** - Financial statements, operational reports
10. **Tax Integration** - PPN calculation, e-Faktur (basic)

### Phase 2 (Post-MVP)

11. **Fixed Assets** - Depreciation, disposal
12. **Manufacturing** - BOM, production orders
13. **Advanced Tax** - Full e-Faktur integration, Coretax
14. **Analytics** - Accurate Insight equivalent
15. **Additional Features** - 2FA, AI insights

---

## AkuBook Differentiators (Beyond Accurate)

### Included in MVP

1. **Attendance Module** (geo-location + face recognition + ZKTeco)
2. **HRM Module** (payroll, leave management, overtime)
3. **WhatsApp Integration** (Wablas - notifications to employees/customers/suppliers)
4. **Mobile Native App** (attendance, leave, payslip, notifications)
5. **One-Time Payment** (vs Accurate subscription)
6. **On-Premise Option** (vs Accurate cloud-only)
7. **Modular UI** (enable/disable features for cleaner UX)

---

## Implementation Priority Matrix

### High Priority (MVP - 6 months)

**Core Accounting:**
- General Ledger (auto-posting from all modules)
- Chart of Accounts setup
- Journal entries (manual + auto)
- Bank reconciliation

**Sales & Distribution:**
- Customer master
- Quotation → SO → Invoice → Delivery (surat jalan)
- Pricing rules and discounts
- AR management

**Purchasing:**
- Supplier master
- PR → PO → Receiving → Payment
- AP management

**Inventory:**
- Multi-warehouse management
- Stock in/out/transfer
- COGS calculation (FIFO/Average)
- Stock opname (physical count)

**Cash Management:**
- Cash accounts
- Bank accounts
- Payment vouchers
- Bank reconciliation

**Tax (Basic):**
- PPN calculation (11%, 12%)
- Tax codes per item
- DPP support
- Basic e-Faktur export

**Reporting:**
- Balance Sheet
- Profit & Loss
- Cash Flow Statement
- Trial Balance
- AR/AP Aging
- Sales/Purchase reports
- Inventory reports

**Configuration:**
- Company setup wizard
- User management + RBAC
- Module enable/disable
- Fiscal year setup

**AkuBook Differentiators:**
- Attendance (geo + face + ZKTeco)
- HRM (payroll, leave, overtime)
- WhatsApp notifications (Wablas)
- Mobile app (attendance, leave, payslip)

### Medium Priority (Phase 2 - 6-12 months)

- Fixed Assets (depreciation)
- Manufacturing (BOM, production)
- Advanced Tax (full e-Faktur, Coretax)
- Advanced Reports (custom report builder)
- Analytics & Insights (BI dashboards)

### Low Priority (Phase 3 - 12+ months)

- AI features (Ailita equivalent)
- Advanced manufacturing (MRP, capacity planning)
- Project management
- CRM features
- E-commerce integration

---

## Technical Architecture Implications

### Database Complexity

**Estimated Tables:**
- Core: ~50 tables (companies, users, roles, settings)
- Accounting: ~30 tables (GL, journals, accounts, periods)
- Sales: ~20 tables (customers, quotations, orders, invoices, deliveries)
- Purchasing: ~20 tables (suppliers, PRs, POs, receivings, payments)
- Inventory: ~25 tables (items, warehouses, stock movements, COGS)
- Cash & Bank: ~15 tables (accounts, transactions, reconciliation)
- Tax: ~10 tables (tax codes, rates, e-faktur queue)
- Fixed Assets: ~10 tables (assets, depreciation, disposal)
- Manufacturing: ~20 tables (BOM, production orders, work orders)
- HRM: ~20 tables (employees, payroll, leave, overtime)
- Attendance: ~10 tables (attendance records, devices, rules)
- Reports: ~5 tables (saved reports, templates)

**Total: ~235 tables** (estimated for full feature parity)

### API Endpoints

**Estimated REST API endpoints:**
- Auth: ~5 endpoints
- Companies: ~10 endpoints
- Users & Roles: ~15 endpoints
- Accounting: ~30 endpoints
- Sales: ~40 endpoints
- Purchasing: ~35 endpoints
- Inventory: ~30 endpoints
- Cash & Bank: ~20 endpoints
- Tax: ~15 endpoints
- Fixed Assets: ~15 endpoints
- Manufacturing: ~25 endpoints
- HRM: ~25 endpoints
- Attendance: ~20 endpoints
- Reports: ~20 endpoints
- Settings: ~15 endpoints

**Total: ~320 API endpoints** (estimated for full feature parity)

### Integration Points

**External Integrations:**
1. **Wablas API** (WhatsApp notifications)
2. **ZKTeco SDK** (attendance hardware)
3. **e-Faktur PJAP** (tax invoice submission)
4. **Coretax CTAS** (new tax system)
5. **Email SMTP** (invoice delivery, notifications)
6. **SMS Gateway** (optional, backup for WA)

---

## Development Effort Estimate

### MVP (Phase 1) - 6 months solo dev

**Breakdown:**
- Database schema design: 2 weeks
- API backend (Laravel): 12 weeks
- Web frontend (Breeze + Livewire): 8 weeks
- Mobile app (React Native/Flutter): 6 weeks
- Integrations (Wablas, ZKTeco): 2 weeks
- Testing & bug fixes: 4 weeks

**Total: 34 weeks (~8 months realistic for solo dev)**

### Phase 2 - 6 months

- Fixed Assets module: 3 weeks
- Manufacturing module: 6 weeks
- Advanced Tax (e-Faktur full): 4 weeks
- Analytics & Insights: 4 weeks
- Testing & refinement: 4 weeks

**Total: 21 weeks (~5 months)**

---

## Next Steps

1. ✅ **Research Complete** - All 12 modules documented
2. ⏭️ **Create Product Brief** - Compile strategic foundation
3. ⏭️ **Create Scenarios** (Phase 3) - Break down features into user flows
4. ⏭️ **UX Design** (Phase 4) - Design screens and interactions
5. ⏭️ **Development** - Start with MVP modules

---

## Research Artifacts

All research results saved to:
- `D:\DEV\akubook-app\_bmad-output\accurate-research\`

Individual module files:
- `01-dashboard.md` - Dashboard widgets and KPIs
- `02-general-ledger.md` - GL and accounting core
- `03-sales.md` - Sales module (28 features)
- `04-purchasing.md` - Purchasing module (17 features)
- `05-inventory.md` - Inventory and warehouse (26 features)
- `06-settings.md` - System configuration
- `07-company.md` - Company setup and multi-branch
- `08-cash-bank.md` - Cash and bank management
- `09-tax-integration.md` - Tax and e-Faktur ✅
- `10-fixed-assets.md` - Fixed asset management
- `11-manufacturing.md` - Manufacturing operations
- `12-reports.md` - Reporting capabilities
- `13-additional-features.md` - 2FA, AI, advanced features
- `14-accurate-insight.md` - Analytics and BI

---

**Research completed:** 2026-05-12  
**Total research time:** ~15 minutes (12 parallel librarian agents)  
**Total features documented:** 161+  
**Ready for:** Product Brief compilation and Phase 3 (Scenarios)
