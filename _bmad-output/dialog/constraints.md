# Constraints: AkuBook

**Date:** 2026-05-12

---

## Design Parameters

Constraints are not limitations — they are **fixed points** that guide design decisions and ensure quality, security, and sustainability.

---

## FIXED (Non-Negotiable)

### Timeline Constraints

**Launch Timeline:**
- **Status:** Flexible (no hard deadline)
- **Principle:** Quality over speed
- **Rationale:** Solo dev, first Laravel project — rushing would compromise security and quality

**Phased Rollout:**
- **Status:** To be decided
- **Options:** MVP → Iterate, or Full feature set → Launch
- **Decision point:** After MVP scope defined

### Budget Constraints

**Development Budget:**
- **Status:** Solo developer (bootstrap mode)
- **Constraint:** No budget for hiring additional developers
- **Implication:** Scope must be achievable by one person
- **Mitigation:** Leverage existing libraries, focus on core features first

**Infrastructure Budget:**
- **Status:** Minimal external costs
- **Constraint:** On-premise deployment = client provides hardware
- **Implication:** Must design for resource-efficient deployment
- **Mitigation:** Optimize for standard SME hardware (not enterprise-grade)

**Marketing Budget:**
- **Status:** Word-of-mouth primary channel
- **Constraint:** No budget for paid advertising initially
- **Implication:** Product quality and client success drive growth
- **Mitigation:** Focus on referral-friendly features (easy demo, clear ROI)

### Technical Constraints (CRITICAL)

#### Security (Zero Tolerance)

**1. Input Validation (MANDATORY):**
- **Constraint:** ALL form inputs MUST be filtered and validated
- **Implementation:** Laravel Form Requests for every form
- **Rationale:** Prevent SQL injection, XSS, data corruption
- **Verification:** Code review, security audit before launch

**2. URL Parameter Filtering (MANDATORY):**
- **Constraint:** ALL URL parameters MUST be validated
- **Implementation:** Route model binding, validation middleware
- **Rationale:** Prevent unauthorized access, data leakage
- **Verification:** Penetration testing, security scan

**3. Database Security (MANDATORY):**
- **Constraint:** Safe schema design, proper indexing, transactions
- **Implementation:**
  - Use Eloquent ORM (no raw SQL unless necessary)
  - Database transactions for multi-step operations
  - Soft deletes for audit trail
  - Foreign key constraints
  - Proper indexing for performance
- **Rationale:** Data integrity, audit compliance, performance
- **Verification:** Database schema review, migration testing

**4. Authorization (MANDATORY):**
- **Constraint:** RBAC (Role-Based Access Control) for ALL operations
- **Implementation:** Laravel Policies, Gates, Middleware
- **Rationale:** Prevent unauthorized access, data breaches
- **Verification:** Authorization matrix testing, penetration testing

**5. Audit Logging (MANDATORY):**
- **Constraint:** Log ALL critical operations
- **Implementation:** Auditable trait, activity log
- **Rationale:** Compliance, forensics, debugging
- **Verification:** Audit log completeness check

#### Code Quality (Zero Tolerance)

**1. No Duplicate Code (MANDATORY):**
- **Constraint:** DRY (Don't Repeat Yourself) principle
- **Implementation:**
  - Extract to Services for business logic
  - Extract to Traits for shared behavior
  - Extract to Helpers for utility functions
  - Extract to Components for UI patterns
- **Rationale:** Maintainability, consistency, bug reduction
- **Verification:** Code review, static analysis (PHPStan, Psalm)

**2. Type Safety (MANDATORY):**
- **Constraint:** Use type hints and return types
- **Implementation:** PHP 8.2+ strict types
- **Rationale:** Catch errors early, better IDE support, documentation
- **Verification:** Static analysis, CI checks

**3. PSR-12 Compliance (MANDATORY):**
- **Constraint:** Follow PSR-12 coding standards
- **Implementation:** PHP CS Fixer, Laravel Pint
- **Rationale:** Consistency, readability, professional code
- **Verification:** Automated formatting checks in CI

**4. Documentation (MANDATORY):**
- **Constraint:** Code must be self-documenting + comments for complex logic
- **Implementation:**
  - Clear naming (variables, methods, classes)
  - PHPDoc for public methods
  - README for each module
  - Architecture decision records (ADR)
- **Rationale:** Maintainability, onboarding, knowledge transfer
- **Verification:** Documentation coverage check

#### Architecture (MANDATORY)

**1. Scalable (MANDATORY):**
- **Constraint:** Must support growth without major refactor
- **Implementation:**
  - Caching (Redis/Memcached)
  - Queue system (Laravel Queues)
  - Database query optimization
  - Pagination for large datasets
  - Lazy loading for relationships
- **Rationale:** 100+ users, multi-warehouse, high transaction volume
- **Verification:** Load testing, performance benchmarks

**2. Maintainable (MANDATORY):**
- **Constraint:** Code must be easy to understand and modify
- **Implementation:**
  - Thin controllers (business logic in Services)
  - Service layer pattern
  - Repository pattern (if needed)
  - Clear separation of concerns
  - Modular architecture
- **Rationale:** Solo dev, long-term maintenance, future team growth
- **Verification:** Code complexity metrics, maintainability index

**3. Secure (MANDATORY):**
- **Constraint:** Security-first design
- **Implementation:**
  - CSRF protection (Laravel default)
  - Rate limiting (API, login, sensitive operations)
  - Secure sessions (httpOnly, secure, sameSite)
  - Password hashing (bcrypt)
  - Two-factor authentication (future)
  - Security headers (CSP, HSTS, X-Frame-Options)
- **Rationale:** Financial data, employee data, compliance
- **Verification:** Security audit, penetration testing, OWASP checklist

### Tech Stack (Fixed)

**Backend:**
- **Framework:** Laravel 11+ (decided)
- **Language:** PHP 8.2+ (decided)
- **Database:** 
  - Development: SQLite (decided)
  - Production: PostgreSQL (decided)
- **Rationale:** Laravel ecosystem, mature, well-documented, first Laravel project for learning

**Frontend:**
- **Stack:** Laravel Breeze (decided)
- **Styling:** Tailwind CSS (decided)
- **JavaScript:** Alpine.js (Breeze default)
- **Rationale:** Simple, fast, integrated with Laravel

**Deployment:**
- **Constraint:** Must support on-premise deployment
- **Implication:** 
  - No cloud-only dependencies
  - Must run on standard SME hardware
  - Installation must be straightforward
  - Documentation for self-hosting

### Integrations (Must-Have)

**1. ZKTeco (Attendance Hardware):**
- **Constraint:** Must integrate with ZKTeco fingerprint/face recognition devices
- **Implementation:** ZKTeco SDK, API integration
- **Rationale:** Many Indonesian SMEs already have ZKTeco hardware
- **Verification:** Integration testing with real ZKTeco device

**2. Wablas (WhatsApp Gateway):**
- **Constraint:** Must integrate with Wablas for WA notifications
- **Implementation:** Wablas API
- **Rationale:** WhatsApp is primary business communication in Indonesia
- **Verification:** Integration testing, notification delivery confirmation

### Business Model (Fixed)

**Pricing:**
- **Constraint:** One-time payment (non-negotiable)
- **Rationale:** Core differentiation, market positioning
- **Implication:** Revenue model based on initial sale + support contracts

**Architecture:**
- **Constraint:** Modular system (non-negotiable)
- **Rationale:** Core product concept, competitive advantage
- **Implication:** Module enable/disable must be rock-solid

---

## FLEXIBLE (Can Be Decided Later)

### Brand Identity

**Logo:**
- **Status:** Not decided yet
- **Timeline:** Can be decided during design phase
- **Constraint:** Must be professional, trustworthy (financial software)

**Colors:**
- **Status:** Not decided yet
- **Timeline:** Can be decided during design phase
- **Constraint:** Must be accessible (WCAG AA), professional

**Visual Identity:**
- **Status:** Open
- **Timeline:** Can evolve over time
- **Constraint:** Consistency once established

### Timeline

**Launch Date:**
- **Status:** Flexible
- **Principle:** Quality over speed
- **Decision point:** When MVP is secure, tested, and documented

**Phased Rollout:**
- **Status:** To be decided
- **Options:**
  - MVP (core modules) → Iterate based on feedback
  - Full feature set → Launch when complete
- **Decision point:** After MVP scope defined

### Features

**Module Priority:**
- **Status:** Can be phased
- **Core modules (MVP):**
  - Accounting
  - Inventory
  - Sales
  - Purchasing
  - Attendance
  - HRM (basic)
- **Phase 2 modules:**
  - Manufacturing
  - Projects
  - CRM
  - POS
  - E-commerce

**Nice-to-Have Features:**
- **Status:** Can be deferred
- **Examples:**
  - Mobile app (roadmap, not MVP)
  - Advanced reporting (can start simple)
  - Third-party integrations (beyond ZKTeco, Wablas)
  - Multi-currency (if not needed by initial clients)

---

## Constraint Implications

### Development Process

**Code Review (MANDATORY):**
- **Frequency:** Before every merge to main
- **Checklist:**
  - Input validation present?
  - URL parameters validated?
  - No duplicate code?
  - Type hints present?
  - PSR-12 compliant?
  - Security considerations addressed?
  - Tests written?

**Security Audit (MANDATORY):**
- **Frequency:** Before launch, quarterly thereafter
- **Scope:**
  - Input validation coverage
  - Authorization matrix completeness
  - SQL injection vulnerability scan
  - XSS vulnerability scan
  - CSRF protection verification
  - Rate limiting effectiveness

**Performance Testing (MANDATORY):**
- **Frequency:** Before launch, after major features
- **Metrics:**
  - Response time <2s for 95% of requests
  - Support 100+ concurrent users
  - Database query optimization (N+1 prevention)
  - Memory usage within limits

**Documentation (MANDATORY):**
- **Frequency:** Continuous (as code is written)
- **Deliverables:**
  - API documentation
  - Module documentation
  - Installation guide (on-premise)
  - User manual (per role)
  - Architecture decision records

### Architecture Decisions

**Service Layer (MANDATORY):**
- **Rationale:** Thin controllers, testable business logic, no duplication
- **Implementation:** Services for all business operations
- **Example:** `AccountingService`, `PayrollService`, `InventoryService`

**Repository Pattern (OPTIONAL):**
- **Decision:** Use if complex queries, otherwise Eloquent directly
- **Rationale:** Don't over-engineer, but prepare for complexity

**Event-Driven (RECOMMENDED):**
- **Rationale:** Decouple modules, enable extensibility
- **Implementation:** Laravel Events for cross-module communication
- **Example:** `SalesOrderCreated` event → Inventory listener, Accounting listener

**Queue System (MANDATORY for async operations):**
- **Rationale:** Don't block user for slow operations
- **Implementation:** Laravel Queues (database driver for simplicity)
- **Use cases:** Email notifications, report generation, data import

**Caching (MANDATORY for performance):**
- **Rationale:** Reduce database load, improve response time
- **Implementation:** Redis (production), file cache (development)
- **Use cases:** Dashboard metrics, reports, lookup data

### Testing Strategy

**Unit Tests (MANDATORY):**
- **Coverage:** 90%+ for Services and Models
- **Focus:** Business logic, calculations, validations

**Feature Tests (MANDATORY):**
- **Coverage:** All Controllers and API endpoints
- **Focus:** Request/response, authorization, validation

**Browser Tests (RECOMMENDED):**
- **Coverage:** Critical user flows
- **Focus:** End-to-end workflows (create SO, process payroll, etc.)
- **Tool:** Laravel Dusk or Playwright

**Security Tests (MANDATORY):**
- **Coverage:** All input points, authorization checks
- **Focus:** SQL injection, XSS, CSRF, unauthorized access

---

## Risk Mitigation

### Solo Dev Risk

**Risk:** Single point of failure, knowledge concentration

**Mitigation:**
- **Documentation:** Comprehensive, up-to-date
- **Code quality:** Self-explanatory, well-structured
- **Version control:** Git, frequent commits, clear messages
- **Backup:** Code, database, documentation

### First Laravel Project Risk

**Risk:** Learning curve, potential mistakes

**Mitigation:**
- **Follow Laravel conventions:** Don't reinvent the wheel
- **Use Laravel best practices:** Official docs, Laracasts, community patterns
- **Code review:** Self-review checklist, static analysis tools
- **Testing:** Comprehensive test coverage catches mistakes early

### Security Risk

**Risk:** Financial data, employee data, compliance

**Mitigation:**
- **Security-first design:** Every feature considers security
- **Input validation:** Zero tolerance, automated checks
- **Authorization:** RBAC everywhere, tested thoroughly
- **Audit logging:** Complete trail for forensics
- **Security audit:** Before launch, quarterly thereafter
- **Penetration testing:** Third-party if budget allows

### Scalability Risk

**Risk:** Performance degradation as data grows

**Mitigation:**
- **Database optimization:** Proper indexing, query optimization
- **Caching:** Aggressive caching for read-heavy operations
- **Queueing:** Async processing for slow operations
- **Load testing:** Simulate 100+ concurrent users
- **Monitoring:** Performance metrics, slow query log

### Maintainability Risk

**Risk:** Code becomes unmaintainable over time

**Mitigation:**
- **No duplicate code:** DRY principle enforced
- **Clear architecture:** Service layer, modular design
- **Documentation:** Code + architecture + decisions
- **Refactoring:** Regular cleanup, technical debt management
- **Static analysis:** Automated code quality checks

---

## Success Criteria (Constraints Met)

**Before Launch:**
- ✅ All input validation implemented and tested
- ✅ All URL parameters validated
- ✅ Zero duplicate code (verified by static analysis)
- ✅ Database schema reviewed and secure
- ✅ 90%+ test coverage
- ✅ Security audit passed
- ✅ Performance benchmarks met (100+ users, <2s response)
- ✅ Documentation complete (installation, user manual, API)
- ✅ On-premise deployment tested
- ✅ ZKTeco integration working
- ✅ Wablas integration working

**Ongoing:**
- ✅ Code review for every change
- ✅ Security audit quarterly
- ✅ Performance monitoring
- ✅ Documentation kept up-to-date
- ✅ Technical debt managed

---

## Constraint Summary

**FIXED (Non-Negotiable):**
1. **Security:** Input validation, URL filtering, database security, RBAC, audit logging
2. **Code Quality:** No duplicate code, type safety, PSR-12, documentation
3. **Architecture:** Scalable, maintainable, secure
4. **Tech Stack:** Laravel, PostgreSQL, on-premise support
5. **Integrations:** ZKTeco, Wablas
6. **Business Model:** One-time payment, modular architecture

**FLEXIBLE:**
1. **Brand:** Logo, colors, visual identity
2. **Timeline:** Launch date, phased rollout
3. **Features:** Module priority, nice-to-haves

**PRINCIPLE:**
> "Quality, security, and maintainability are non-negotiable. Everything else is flexible."
