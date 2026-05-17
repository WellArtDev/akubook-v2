---
stepsCompleted: ["step-01-init", "step-02-context", "step-03-decisions"]
inputDocuments:
  - "_bmad-output/planning-artifacts/prd.md"
  - "_bmad-output/A-Product-Brief/product-brief.md"
  - "_bmad-output/C-UX-Scenarios/00-ux-scenarios.md"
  - "_bmad-output/accurate-research/00-INDEX.md"
workflowType: 'architecture'
project_name: 'AkuBook'
user_name: 'WellArtDev'
date: '2026-05-14'
---

# Architecture Decision Document

_This document builds collaboratively through step-by-step discovery. Sections are appended as we work through each architectural decision together._

## Project Context Analysis

### Requirements Overview

**Functional Requirements:**

AkuBook adalah all-in-one ERP dengan 5 core modules yang terintegrasi native:

1. **Foundation & Setup**: Company profile, multi-branch/warehouse, fiscal year management, user management dengan RBAC, chart of accounts setup dengan industry templates
2. **Accounting Core**: Journal entries (manual + auto-generated), posting & period close, financial reports (Trial Balance, GL, P&L, Balance Sheet), complete audit trail
3. **Sales & Purchasing**: Full cycle SO→Invoice→Delivery→Payment dan PO→Receiving→Payment dengan auto-posting ke journal (AR/AP, Revenue/COGS, Inventory)
4. **Inventory Management**: Multi-warehouse stock tracking, stock movements & transfers, cost accounting (FIFO/Average), auto-posting inventory transactions
5. **Human Resources**: Employee master data, attendance management (online: geo-location + face recognition, offline: ZKTeco integration), leave management & approval, payroll processing dengan auto-posting

**Critical Integrations:**
- ZKTeco attendance device integration (100% sync success rate)
- WhatsApp notifications via Wablas (95%+ delivery rate)
- Accurate data migration tool (offline & online, 90%+ accuracy)

**Tax Compliance (Indonesian Market):**
- e-Faktur generation & XML export
- PPN calculation & reporting
- PPh compliance (basic)

**Non-Functional Requirements:**

**Performance:**
- Page load time: <2 seconds untuk dashboard dan list views
- Report generation: <5 seconds untuk standard reports (P&L, Balance Sheet)
- Real-time sync: <1 second untuk inventory updates across modules
- Uptime: 99.5%+ (acceptable untuk self-hosted model)

**Data Integrity:**
- Zero accounting errors (journal entries always balance)
- 100% audit trail coverage (every transaction logged)
- Database transaction integrity (no partial commits)
- Zero data loss during migration dari Accurate

**Integration & Automation:**
- 95%+ transactions auto-post to journal (PRIMARY SUCCESS METRIC)
- Native integration bukan API glue - satu database transaction
- Real-time data sync across all modules

**Security & Compliance:**
- Role-based access control (RBAC) dengan granular permissions
- Complete audit trail untuk compliance
- Data sovereignty (self-hosted deployment option)
- Indonesian tax compliance (e-Faktur, PPN, PPh)

**Scale & Complexity:**

- Primary domain: **Full-stack web application** (desktop-first responsive) + **mobile native** (attendance-focused)
- Complexity level: **Medium-High**
  - Multi-module integration dengan real-time sync
  - Indonesian tax compliance dan accounting standards
  - Not as complex as healthcare/fintech, tapi bukan simple CRUD app
- Estimated architectural components: **15-20 major modules**
  - Core: Auth, RBAC, Audit Trail, Multi-tenant
  - Business: Accounting, Sales, Purchasing, Inventory, HRM, Tax
  - Support: Reports, Dashboard, Notifications, Data Migration

### Technical Constraints & Dependencies

**Platform Requirements:**
- Web: Desktop-first responsive design (Laravel 13 + React 18)
- Mobile: Native app untuk attendance (geo-location + face recognition)
- Database: PostgreSQL 17 (ACID compliance untuk accounting integrity)
- Deployment: Self-hosted (on-premise atau cloud pilihan user)

**Integration Dependencies:**
- ZKTeco attendance devices (existing hardware investment)
- WhatsApp Business API (via Wablas)
- Accurate Online/Offline (data migration source)

**Compliance Constraints:**
- Indonesian accounting standards
- e-Faktur format dan submission requirements
- PPN/PPh calculation rules
- Audit trail requirements untuk tax compliance

**Performance Constraints:**
- Real-time sync requirement (<1 second)
- Report generation speed (<5 seconds)
- 95%+ auto-posting success rate (core value proposition)

### Cross-Cutting Concerns Identified

**1. Auto-Posting Engine (CRITICAL)**
- 95%+ transactions must auto-generate journal entries
- Database transaction integrity (all-or-nothing commits)
- Journal entry validation (debit = credit always)
- Affects: Sales, Purchasing, Inventory, HRM modules

**2. Multi-Tenant Data Isolation**
- Multi-company, multi-branch, multi-warehouse support
- Data isolation dan access control per tenant
- Affects: All modules

**3. Audit Trail & Compliance**
- Every transaction logged dengan user, timestamp, before/after state
- Immutable audit log untuk compliance
- Affects: All modules

**4. Real-Time Sync**
- Inventory updates across warehouses
- Dashboard refresh
- Attendance sync dari devices
- Affects: Inventory, Dashboard, HRM modules

**5. Authentication & Authorization (RBAC)**
- Granular permissions per module dan action
- Role-based access control
- Affects: All modules

**6. Tax Calculation Engine**
- PPN calculation (11% atau exempt)
- e-Faktur generation
- Tax reporting
- Affects: Sales, Purchasing modules

**7. Notification System**
- WhatsApp notifications untuk approvals, alerts
- Email notifications (optional)
- Affects: All modules dengan approval workflows

## Architectural Decisions

### Tech Stack

**Backend:**
- Framework: Laravel 13 (PHP 8.3+)
- Rationale: Mature ecosystem, excellent ORM (Eloquent), built-in queue system, strong community support untuk Indonesian developers

**Frontend:**
- Framework: React 18 + Inertia.js
- Rationale: SPA experience dengan server-side routing, code sharing dengan mobile, component reusability

**Database:**
- Primary: PostgreSQL 17
- Rationale: ACID compliance critical untuk accounting integrity, excellent JSON support, mature replication

**Mobile:**
- Framework: React Native
- Rationale: Code sharing dengan web frontend, native performance untuk geo-location + face recognition, single codebase maintenance

### Architecture Pattern

**Monolithic Modular Architecture**
- Rationale: Simpler deployment untuk self-hosted model, easier transaction management across modules, lower operational complexity
- Module boundaries: Accounting, Sales, Purchasing, Inventory, HRM, Tax
- Communication: Event-driven untuk loose coupling

**Domain-Driven Design (DDD)**
- Bounded contexts per module
- Shared kernel untuk cross-cutting concerns (Auth, Audit, Multi-tenant)
- Domain events untuk cross-module integration

**Event-Driven Communication**
- Events untuk auto-posting triggers (SalesInvoiceCreated → JournalEntryGenerated)
- Async processing untuk non-critical operations (notifications, reports)
- Sync processing untuk accounting transactions (data integrity)

**Repository Pattern**
- Data access abstraction
- Testability dan maintainability
- Easy migration path jika perlu switch database

### Key Architectural Components

**1. Auto-Posting Engine (CRITICAL)**
- Responsibility: Generate journal entries dari business transactions
- Pattern: Event-driven + Template-based posting rules
- Guarantee: Database transaction wraps business transaction + journal entry generation
- Success metric: 95%+ auto-posting rate

**2. Multi-Tenant Manager**
- Responsibility: Data isolation per company/branch/warehouse
- Pattern: Schema-per-tenant (PostgreSQL schemas)
- Security: Row-level security policies
- Performance: Connection pooling per tenant

**3. Audit Trail Service**
- Responsibility: Immutable log semua transactions
- Pattern: Event sourcing untuk audit events
- Storage: Separate audit tables (append-only)
- Compliance: Timestamp, user, before/after state

**4. Tax Calculation Engine**
- Responsibility: PPN calculation, e-Faktur generation
- Pattern: Strategy pattern untuk tax rules
- Compliance: Indonesian tax regulations (11% PPN, exempt items)
- Integration: e-Faktur XML export

**5. Real-Time Sync Service**
- Responsibility: Inventory updates, dashboard refresh
- Pattern: WebSocket (Laravel Echo + Pusher/Redis)
- Performance: <1 second sync latency
- Scope: Inventory movements, attendance updates

**6. Notification Service**
- Responsibility: WhatsApp + Email notifications
- Pattern: Queue-based async processing
- Integration: Wablas API untuk WhatsApp
- Reliability: 95%+ delivery rate

### Data Architecture

**Database Design:**
- Single PostgreSQL instance dengan multiple schemas (one per tenant)
- Schema isolation untuk data security
- Shared tables untuk system-wide data (users, roles, system config)

**Transaction Management:**
- Database transactions untuk accounting operations (ACID guarantee)
- Two-phase commit untuk cross-module operations
- Rollback strategy untuk failed auto-posting

**Audit & Event Sourcing:**
- Event store untuk audit trail (append-only)
- Event replay capability untuk debugging
- Retention policy: 7 years (Indonesian compliance)

**Reporting Architecture (CQRS):**
- Write model: Transactional tables (normalized)
- Read model: Materialized views untuk reports (denormalized)
- Sync: Trigger-based atau scheduled refresh
- Performance: <5 seconds report generation

### Deployment Architecture

**Self-Hosted Model:**
- Docker containers untuk easy deployment
- Docker Compose untuk local/on-premise
- Kubernetes option untuk cloud deployment
- Database backup strategy (daily + transaction log)

**Scalability:**
- Vertical scaling primary (single server sufficient untuk 100-500 users)
- Horizontal scaling option (load balancer + read replicas)
- Queue workers untuk async processing

**Security:**
- HTTPS mandatory
- Database encryption at rest
- API authentication (Laravel Sanctum)
- RBAC enforcement at application layer

### Integration Architecture

**ZKTeco Integration:**
- Pull-based sync (scheduled jobs)
- SDK integration atau REST API
- Fallback: Manual CSV import

**WhatsApp Integration:**
- Wablas API (third-party service)
- Queue-based sending (async)
- Retry logic untuk failed deliveries

**Accurate Migration:**
- ETL pipeline (Extract-Transform-Load)
- Validation layer (data integrity checks)
- Rollback capability (backup before migration)
- Support: Accurate Online API + Offline database export
