# Design Decisions Log

## Step 1a: Client Profile

**Date:** 2026-05-12

### Organisation
- Solo developer, first Laravel project
- No prior formal design process experience
- Learning Laravel while building

### Key People
- WellArtDev: Founder, developer, sole decision maker
- Client: End user who needs the solution

### Decision Culture
- Fast individual decisions
- Plan detail first, execute fast
- Client validates outcome

### Internal Driver
- Client need for accounting + attendance (geo/face) + HRM
- Benchmark: Accurate Online + custom features
- Success = working solution delivered to client

## Step 02: Vision

**Date:** 2026-05-12

### Vision Statement
AkuBook adalah solusi bisnis all-in-one yang membebaskan bisnis Indonesia dari subscription fatigue dan vendor lock-in. One-time payment, on-premise control, unified dashboard untuk accounting + attendance + HRM.

### Key Differentiators
- One-time payment (no subscription)
- On-premise deployment option
- All-in-one: accounting (full like Accurate) + attendance (geo/face/ZKTeco) + HRM (payroll/leave/lembur)
- Horizontal solution for all business types (distributor, retail, jasa)
- ZKTeco integration + mobile app roadmap

### Problem Solved
- Multiple separate tools
- Subscription fatigue
- No on-premise option
- Vendor lock-in

## Step 03: Positioning

**Date:** 2026-05-12

### Positioning Statement
Untuk small & mid-size companies di Indonesia yang lelah dengan subscription fatigue dan data scattered, AkuBook adalah all-in-one ERP dengan one-time payment, on-premise option, dan modular architecture.

### Target Customer
- Small & mid-size companies (10-100+ employees)
- Industries: Distributor, retail, jasa, manufacturing
- Indonesia market

### Category
All-in-one ERP for SME (not just accounting, not just HRIS)

### Key Differentiators
1. One-time payment (vs subscription)
2. On-premise deployment option
3. Modular architecture (extend without breaking core)
4. Data import from existing tools (Accurate, Jurnal, etc.)
5. True all-in-one (accounting + attendance + HRM)

### Alternatives
Accurate, Jurnal, Kerjoo, Mekari (all subscription SaaS, cloud-only)

### Support Model
- Base: Included support + updates
- Paid: Priority support + new modules

## Step 05: Business Model

**Date:** 2026-05-12

### Business Model Type
B2B (Business-to-Business) targeting small & mid-size companies

### Buyer vs User
- Buyer: Company owner/decision maker
- Users: Multi-role (owner, finance, sales, purchasing, staff) — unlimited per license

### Pricing Structure
- **Base license:** One-time payment per company (all modules included)
- **Modular system:** All features included, auto-enable by industry, flexible enable/disable
- **Multi-company:** Unlimited companies per license
- **Add-ons:** Custom features/integrations (paid)
- **Support:** 1 month included, then per-incident or monthly retainer

### Key Implications
- Simple pricing (no per-user fees, no tiered plans)
- Modular architecture critical for enable/disable
- Onboarding flow: industry selection → auto-config
- Sales demo: show industry flexibility
- Upsell: support contracts, custom dev, training

## Step 06: Business Customers

**Date:** 2026-05-12

### Ideal Customer Profile
- **Size:** 100+ employees, Rp 1M+ revenue/month
- **Industry:** Distributors (primary), retail, manufacturing (secondary)
- **Maturity:** Established business, already using software (Accurate, etc.)
- **Pain:** Tool sprawl, subscription fatigue, want on-premise

### Decision-Making
- **Decision maker:** Director/Owner (budget authority)
- **Evaluator:** HRD/Operations Manager (testing, recommendation)
- **Users:** 100+ staff (multi-role)
- **Process:** Discovery (1-2w) → Evaluation/trial (2-4w) → Approval (1-2w)
- **Cycle:** 1-3 months (not impulse buy)

### Distributor-Specific Needs
- Multi-warehouse, PO to suppliers, SO to retailers
- Inventory tracking per location, surat jalan
- Field sales attendance, warehouse staff attendance
- COGS, AP/AR, multi-location P&L

### Sales Implications
- Need trial/demo period (30 days)
- Must convince both evaluator (operational fit) and director (ROI)
- Data migration from Accurate critical
- Show industry-specific workflow in demo

## Step 07: Target Users

**Date:** 2026-05-12

### Primary Users (Full Module Access)
- **Finance Admin/Accounting:** Daily accounting, monthly close, AP/AR, reports
- **HRD Manager:** Payroll, leave management, attendance monitoring, employee data
- **Purchasing Manager:** PO creation, supplier management, inventory planning

### Secondary Users (Limited Module Access)
- **Sales Team:** Customer orders, invoicing, inventory check, commission tracking
- **Warehouse Staff:** Stock in/out, surat jalan, inventory count, stock transfer
- **General Staff:** Attendance, leave request, overtime, payslip, announcements

### Universal Need
- **All users:** Attendance tracking (geo/face/ZKTeco) with role-based permissions

### Current State & Frustrations
- Login: Accurate + Excel + manual attendance data pull from machine
- Approval: Manual (WhatsApp/print-sign-scan) → bottleneck
- Data scattered → manual compilation for reports
- Monthly close: Multiple days (time-consuming)

### Goals
- Flexible approval flow (manual or automatic)
- Attendance auto-sync (no manual data pull)
- One dashboard for all data
- Faster monthly close (days → hours)

### RBAC (Role-Based Access Control)
- Admin defines custom roles
- Per role: module access (enable/disable)
- Permission levels: Full CRUD, View + Limited Actions, View-only, No access
- Multi-role users supported (permissions cumulative)
- Dynamic permissions (changes take effect immediately)

## Step 07a: Product Concept

**Date:** 2026-05-12

### Core Structural Idea
**"Modular Business OS dengan Industry-Aware Configuration"**

AkuBook = business operating system (bukan "accounting software + add-ons"). Semua modules built-in, user activate sesuai kebutuhan. Seperti smartphone: all capability ada, tinggal pilih app mana yang dipakai.

### Implementation Principles
1. **Industry-First Onboarding:** Setup company → pilih industry → auto-enable relevant modules
2. **Module-Centric Architecture:** Every function = independent module, communicate via unified data layer
3. **Role-Based Experience:** Login → see only modules you have access to, personalized dashboard

### Rationale
- **Vs Accurate/Jurnal:** Mereka accounting-first (features bolted-on) → AkuBook business-first (all functions equal)
- **Flexibility:** Company evolve? Enable different modules (no migration, no new software)
- **Simplicity:** One login, one interface, one data model
- **Ownership:** One-time payment, all modules included

### Key Features From Concept
- Industry templates (pre-configured module sets)
- Unified data model (customers, products, transactions shared across modules)
- Cross-module workflows (PO → Receiving → Accounting auto-flow)
- Personalized dashboards (role-based, module-based)
- Module marketplace (future: third-party modules)

## Step 08: Success Criteria

**Date:** 2026-05-12

### Primary Success Metric
**"Semua transaksi otomatis tercatat di jurnal tanpa manual entry"**

Finance Admin hanya review & approve, bukan create & input manual.

### Measurable Criteria
1. **Auto-posting rate:** 95%+ of transactions auto-post to journal
2. **Manual entry time:** 80% reduction (from 40-60 hrs/month to 8-12 hrs/month)
3. **User satisfaction:** 8+/10 - "I just review, not re-enter data"
4. **Data accuracy:** <1% error rate in auto-posted entries

### Timeline
- **Month 1:** Setup & training, first successful auto-posts
- **Months 2-3:** Adoption phase (70%+ auto-posting, 50% time reduction)
- **Months 4-6:** Optimization (90%+ auto-posting, 70% time reduction)
- **Month 6+:** Target achieved (95%+ auto-posting, 80% time reduction)

### Cross-Module Auto-Posting
- Sales order → AR/Sales/COGS journal entries
- Purchase order → Inventory/AP journal entries
- Payroll → Salary expense/payables journal entries
- Attendance overtime → auto-flow to payroll → journal
- Stock transfer → inventory account adjustments

### Ultimate Success
> "Client says: 'AkuBook ini komplit semua ada dan lebih enteng dari Accurate — semua hitungan terhubung, lihat jurnal jadi enak.'"

## Step 09: Competitive Landscape

**Date:** 2026-05-12

### Primary Competitors
- **Accurate Online:** Market leader, strong accounting core, subscription Rp 500k/month
- **Jurnal (Mekari):** Modern UI, cloud-native, subscription Rp 300-800k/month
- **Kerjoo:** Affordable, limited features, subscription Rp 200k/month

### Why Customers Stick with Accurate
- Familiarity (accountants know it)
- Trust (proven track record)
- Switching cost (historical data, trained staff)
- Risk aversion ("nobody gets fired for choosing Accurate")

### Do-Nothing Alternative
- Short-term: Continue inefficient processes, subscription costs
- Long-term: Business risk (audit issues, compliance), paperless trend makes manual unsustainable

### AkuBook's Unfair Advantage (Defensible)

**1. Price (50% cheaper):**
- AkuBook: Rp 25M one-time
- Accurate (hypothetical lifetime): Rp 50M
- Accurate (subscription): Rp 30M over 5 years
- **Why defensible:** Lean operation, competitors can't match without sacrificing margin

**2. Seamless Integration & Automation:**
- Cross-module auto-posting (95%+)
- WhatsApp integration built-in (Wablas) for employee/customer/supplier notifications
- **Why defensible:** Unified data model from day one, competitors need major refactor

**3. Modular Flexibility:**
- Enable/disable features (cleaner UI)
- **Why defensible:** Accurate/Jurnal monolithic architecture (all features always visible)

**4. On-Premise Option:**
- Full data control, compliance-friendly
- **Why defensible:** Cloud-only competitors need architecture redesign

### Reality Check
If Accurate launches "Lifetime + HRM + On-Premise":
- AkuBook still wins on: Price (50% cheaper), WA integration (built-in), Modular UI (cleaner), Execution speed (startup agility)

### Switching Triggers
- Monthly bill review → "Paying Rp 6M/year?"
- Need on-premise → "Audit requires data control"
- Need HRM integration → "Why separate tools?"
- WA notification need → "Can we auto-send via WA?"

## Step 10: Constraints

**Date:** 2026-05-12

### FIXED (Non-Negotiable)

**Timeline:**
- Flexible (no hard deadline) — quality over speed

**Budget:**
- Solo dev (bootstrap mode)
- No hire budget, minimal external costs

**Technical (CRITICAL - Zero Tolerance):**
- ✅ **Input validation:** ALL form inputs filtered (Form Requests)
- ✅ **URL parameter filtering:** ALL URL params validated
- ✅ **No duplicate code:** DRY principle (Services/Traits/Helpers)
- ✅ **Database security:** Safe schema, transactions, soft deletes
- ✅ **Scalable:** Caching, queueing, optimization
- ✅ **Maintainable:** Clean code, PSR-12, type hints, documentation
- ✅ **Secure:** RBAC, CSRF, rate limiting, audit logging

**Tech Stack:**
- Laravel 11+, PHP 8.2+, PostgreSQL (production)
- Must support on-premise deployment

**Integrations:**
- ZKTeco (attendance hardware)
- Wablas (WA gateway)

**Business Model:**
- One-time payment (non-negotiable)
- Modular architecture (non-negotiable)

### FLEXIBLE

**Brand:**
- Logo, colors, visual identity (not decided yet)

**Timeline:**
- Launch date flexible (quality over speed)
- Phased rollout (can be decided later)

**Features:**
- Module priority (can be phased)
- Nice-to-haves (can be deferred)

### Implications

**Before Launch:**
- Code review mandatory (security, quality, no duplication)
- Security audit required
- Performance testing (100+ users, <2s response)
- 90%+ test coverage
- Documentation complete

**Principle:**
> "Quality, security, and maintainability are non-negotiable. Everything else is flexible."

## Step 10a: Platform Strategy

**Date:** 2026-05-12

### Primary Platforms (MVP)

**1. Web Application:**
- Laravel + Breeze + Tailwind
- Desktop-first design, responsive down to mobile
- Full ERP features (Accounting, Sales, Purchasing, Inventory, HRM, Attendance)
- Primary users: Finance, HRD, Purchasing, Sales, Warehouse

**2. Mobile Native App:**
- Cross-platform (React Native or Flutter — to be decided)
- Mobile-first design
- Features: Attendance (geo/face), Leave requests, Payslip, Notifications
- Primary users: Staff, field workers

### API-First Architecture (CRITICAL)

- RESTful API backend (Laravel Sanctum)
- Web app = API consumer
- Mobile app = API consumer
- Future integrations = API consumers
- **Why:** Decouple frontend/backend, support multiple clients

### Backend Integrations (MVP)

**1. Wablas (WhatsApp Gateway):**
- Send notifications to employees, customers, suppliers
- Event-driven (Laravel Events → Queue → Wablas API)
- One-way (send only, no receive/chatbot in MVP)

**2. ZKTeco (Attendance Hardware):**
- Integrate with existing fingerprint/face recognition devices
- Real-time push or scheduled pull
- Device → Backend API → Attendance module

### Device Priority

- **Desktop:** Primary (complex workflows, data entry)
- **Mobile:** Primary (attendance, simple tasks)
- **Tablet:** Secondary (warehouse, field ops)

### Offline Functionality

- **Web:** Always-online (on-premise LAN)
- **Mobile:** Offline-first for attendance, read-only for viewing

### Native Device Features (Mobile)

- Camera (face recognition attendance)
- GPS (geo-location attendance)
- Push notifications
- Biometric authentication (Face ID / Touch ID)

## Step 11: Tone of Voice

**Date:** 2026-05-12

### Tone Attributes

1. **Professional tapi Approachable** — Trustworthy untuk financial data, tapi tidak intimidating
2. **Clear & Direct** — No ambiguity, instruksi jelas (data-critical context)
3. **Helpful & Supportive** — Guide user, bukan intimidate (first-time ERP users)
4. **Indonesian-Friendly** — Natural Bahasa Indonesia, bukan terjemahan kaku

### Examples

| Context | Right Tone |
|---------|------------|
| Button | "Simpan" (not "Simpan Data" or "Oke gas!") |
| Error | "Email tidak valid" (not "Kesalahan validasi" or "Waduh salah") |
| Empty state | "Belum ada transaksi" (not "Tidak ada data" or "Kosong melompong") |
| Loading | "Memproses..." (not "Memproses permintaan..." or "Tunggu bentar...") |
| Success | "Berhasil disimpan" (not "Operasi berhasil" or "Yeay berhasil!") |

### Do's & Don'ts

**Do's:**
- Natural Bahasa Indonesia (not literal translations)
- Short and direct
- Helpful hints for first-time users
- Consistent terminology

**Don'ts:**
- Terlalu formal/kaku (bukan surat resmi)
- Terlalu casual/slang (bukan chat WA)
- Ambiguous (data-critical context)
- Mix English-Indonesia tanpa alasan

### Tone by Context

- **High-stakes** (delete, posting): More formal, explicit consequences
- **Routine** (daily tasks): Lighter, efficient
- **Errors**: Helpful, solution-oriented (not blaming)
- **Success**: Brief, positive
- **First-time**: More supportive, more guidance

## Step 12: Product Brief Synthesis

**Date:** 2026-05-12

### Final Narrative Presented

Strategic foundation presented as coherent story covering:
- Vision (all-in-one ERP, one-time payment, on-premise)
- Target (SME Indonesia, distributors primary)
- Problem (subscription fatigue, tool sprawl, no data control)
- Positioning (50% cheaper, seamless integration, modular UI)
- Success (95%+ auto-posting, "lebih enteng dari Accurate")
- Reality (solo dev, quality/security non-negotiable)
- Unfair advantage (price, integration, modular, on-premise, Indonesian workflow)

### User Confirmation

✅ Confirmed - "ok nice"

### Brief Generated

**Location:** `_bmad-output/A-Product-Brief/product-brief.md`

**Includes:**
- Strategic summary (3 paragraphs)
- Vision statement and core elements
- Positioning with all components
- Target users (primary + secondary)
- Product concept (Modular Business OS)
- Business model (B2B, one-time payment)
- Success criteria (95%+ auto-posting)
- Competitive landscape (unfair advantages)
- Constraints (quality/security non-negotiable)
- Platform strategy (web + mobile, API-first)
- Tone of voice (professional tapi approachable)
- Feature scope (161+ Accurate features researched)
- Next steps (Phase 3: Scenarios, Phase 4: UX Design)

### Completion

**Timestamp:** 2026-05-12  
**Status:** Phase 1 Complete  
**Next:** Phase 3 - UX Scenarios (skip Phase 2 Trigger Mapping)
