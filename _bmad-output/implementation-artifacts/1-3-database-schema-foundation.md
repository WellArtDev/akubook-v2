# Story 1.3: Database Schema Foundation

**Epic:** 1 - Core System Setup & Infrastructure  
**Story ID:** 1.3  
**Story Key:** 1-3-database-schema-foundation  
**Status:** ready-for-dev  
**Created:** 2026-05-14

---

## User Story

**As a** backend developer  
**I want** a well-documented, verified database schema with proper relationships and constraints  
**So that** we have a solid data foundation for the AkuBook ERP system

---

## Business Context

AkuBook adalah integrated ERP system yang membutuhkan database schema yang robust, normalized, dan well-documented. Story ini memastikan existing migrations sudah correct dan ter-dokumentasi dengan baik.

**Business Value:**
- Data integrity melalui proper constraints
- Scalable schema untuk future modules
- Clear documentation untuk team understanding
- Foundation untuk all business logic

**Success Impact:**
- Backend team understand schema structure
- Data relationships clearly defined
- Migration rollback works correctly
- Schema ready untuk production deployment

---

## Acceptance Criteria

### AC1: Existing Migrations Verified
**Given** 21 migrations already exist  
**When** migrations are reviewed  
**Then**
- All migrations follow Laravel naming convention
- Up/down methods properly implemented
- No duplicate table names
- Timestamps added to all tables
- Foreign keys have proper constraints
- Indexes defined for frequently queried columns

### AC2: Database Schema Documentation Created
**Given** migrations are verified  
**When** schema documentation is generated  
**Then**
- ERD (Entity Relationship Diagram) created
- Table descriptions documented
- Column descriptions documented
- Relationships documented
- Constraints documented
- Indexes documented

### AC3: Data Integrity Constraints Verified
**Given** schema has relationships  
**When** constraints are checked  
**Then**
- Foreign keys have ON DELETE/UPDATE actions
- Unique constraints on business keys (code, email)
- NOT NULL constraints on required fields
- Default values set appropriately
- Check constraints for valid data ranges

### AC4: Migration Rollback Tested
**Given** all migrations ran successfully  
**When** rollback is tested  
**Then**
- `php artisan migrate:rollback` works
- All tables dropped in correct order
- No orphaned tables remain
- Re-migration works without errors

### AC5: Database Seeder Created
**Given** schema is verified  
**When** seeder is created  
**Then**
- Development seed data for all tables
- Realistic test data
- Proper relationships maintained
- Seeder can run multiple times (idempotent)

---

## Tasks/Subtasks

### Task 1: Review and Verify All Existing Migrations (AC1)
- [ ] **1.1** Review Laravel base migrations (users, cache, jobs tables)
  - [ ] Verify users table has proper columns and indexes
  - [ ] Verify cache table structure
  - [ ] Verify jobs table for queue system
- [ ] **1.2** Review organization structure migrations
  - [ ] Verify branches table (code unique, proper columns)
  - [ ] Verify departments table (code unique, proper columns)
  - [ ] Verify positions table (code unique, proper columns)
  - [ ] Verify warehouses table (branch_id foreign key, proper constraints)
- [ ] **1.3** Review security & compliance migrations
  - [ ] Verify audit_logs table structure
  - [ ] Verify Spatie permission tables (roles, permissions, model_has_roles, etc.)
- [ ] **1.4** Review accounting migrations
  - [ ] Verify accounts table (chart of accounts structure)
  - [ ] Verify fiscal_periods table (date ranges, status)
  - [ ] Verify journal_entries table (proper foreign keys)
  - [ ] Verify journal_entry_lines table (debit/credit columns, foreign keys)
- [ ] **1.5** Review sales & purchasing migrations
  - [ ] Verify customers table structure
  - [ ] Verify sales_orders and sales_order_lines tables
  - [ ] Verify suppliers table structure (currently empty - note for future)
  - [ ] Verify purchase_orders and purchase_order_lines tables
  - [ ] Verify items table structure (currently empty - note for future)
- [ ] **1.6** Create migration verification checklist document
  - [ ] Document all tables with their purposes
  - [ ] List all foreign key relationships
  - [ ] List all unique constraints
  - [ ] List all indexes
  - [ ] Save as `database/schema/migration-verification.md`

### Task 2: Create Comprehensive ERD Documentation (AC2)
- [ ] **2.1** Create Mermaid ERD for organization structure
  - [ ] Diagram: branches, departments, positions, warehouses relationships
  - [ ] Include cardinality (1:1, 1:N, N:M)
- [ ] **2.2** Create Mermaid ERD for accounting module
  - [ ] Diagram: accounts, fiscal_periods, journal_entries, journal_entry_lines
  - [ ] Show double-entry bookkeeping relationships
- [ ] **2.3** Create Mermaid ERD for sales & purchasing
  - [ ] Diagram: customers, sales_orders, sales_order_lines
  - [ ] Diagram: suppliers, purchase_orders, purchase_order_lines, items
- [ ] **2.4** Create Mermaid ERD for security & users
  - [ ] Diagram: users, roles, permissions, model_has_roles, audit_logs
  - [ ] Show Spatie permission relationships
- [ ] **2.5** Create master ERD combining all modules
  - [ ] Show cross-module relationships (e.g., users → branches)
  - [ ] Save as `database/schema/erd.md`

### Task 3: Create Schema Documentation (AC2)
- [ ] **3.1** Create main schema README
  - [ ] Overview of database design principles
  - [ ] List of all tables with brief descriptions
  - [ ] Naming conventions used
  - [ ] Save as `database/schema/README.md`
- [ ] **3.2** Document organization structure tables
  - [ ] branches: purpose, columns, relationships, constraints
  - [ ] departments: purpose, columns, relationships, constraints
  - [ ] positions: purpose, columns, relationships, constraints
  - [ ] warehouses: purpose, columns, relationships, constraints
- [ ] **3.3** Document accounting tables
  - [ ] accounts: chart of accounts structure, account types
  - [ ] fiscal_periods: period management, status transitions
  - [ ] journal_entries: transaction headers, posting status
  - [ ] journal_entry_lines: debit/credit lines, balancing rules
- [ ] **3.4** Document sales & purchasing tables
  - [ ] customers: customer management, credit limits
  - [ ] sales_orders: order workflow, status transitions
  - [ ] suppliers: supplier management (note: migration incomplete)
  - [ ] purchase_orders: PO workflow, status transitions
  - [ ] items: inventory items (note: migration incomplete)
- [ ] **3.5** Document security & audit tables
  - [ ] users: user accounts, branch assignment
  - [ ] Spatie permission tables: RBAC structure
  - [ ] audit_logs: audit trail structure
- [ ] **3.6** Create constraints documentation
  - [ ] List all foreign key constraints with ON DELETE/UPDATE actions
  - [ ] List all unique constraints
  - [ ] List all check constraints
  - [ ] List all default values
  - [ ] Save as `database/schema/constraints.md`
- [ ] **3.7** Create indexes documentation
  - [ ] List all indexes with their purposes
  - [ ] Document composite indexes
  - [ ] Document unique indexes
  - [ ] Save as `database/schema/indexes.md`

### Task 4: Verify Data Integrity Constraints (AC3)
- [ ] **4.1** Verify foreign key constraints
  - [ ] Check all foreign keys have proper ON DELETE actions (CASCADE, RESTRICT, SET NULL)
  - [ ] Check all foreign keys have proper ON UPDATE actions
  - [ ] Verify foreign key indexes exist
- [ ] **4.2** Verify unique constraints
  - [ ] Check all business keys (code fields) have unique constraints
  - [ ] Check email fields have unique constraints where appropriate
  - [ ] Verify composite unique constraints where needed
- [ ] **4.3** Verify NOT NULL constraints
  - [ ] Check required fields have NOT NULL constraints
  - [ ] Verify nullable fields are intentionally nullable
- [ ] **4.4** Verify default values
  - [ ] Check boolean fields have appropriate defaults
  - [ ] Check status fields have appropriate defaults
  - [ ] Check numeric fields have appropriate defaults (e.g., 0 for amounts)
- [ ] **4.5** Verify check constraints
  - [ ] Check enum fields have valid value constraints
  - [ ] Check numeric ranges have appropriate constraints
  - [ ] Check date ranges have appropriate constraints
- [ ] **4.6** Create constraint verification report
  - [ ] Document all verified constraints
  - [ ] Note any missing or incorrect constraints
  - [ ] Save as `database/schema/constraint-verification-report.md`

### Task 5: Test Migration Rollback (AC4)
- [ ] **5.1** Backup current database state
  - [ ] Run `pg_dump akubook > backup_before_rollback.sql`
- [ ] **5.2** Test rollback of recent migrations
  - [ ] Run `php artisan migrate:rollback --step=5`
  - [ ] Verify 5 migrations rolled back successfully
  - [ ] Check tables dropped in correct order
  - [ ] Verify no foreign key constraint errors
- [ ] **5.3** Test re-migration
  - [ ] Run `php artisan migrate`
  - [ ] Verify all 5 migrations re-ran successfully
  - [ ] Check tables recreated correctly
  - [ ] Verify foreign keys recreated
- [ ] **5.4** Test full rollback
  - [ ] Run `php artisan migrate:rollback --all`
  - [ ] Verify all tables dropped
  - [ ] Check no orphaned tables remain
  - [ ] Verify migrations table is empty
- [ ] **5.5** Test full re-migration
  - [ ] Run `php artisan migrate`
  - [ ] Verify all 21 migrations ran successfully
  - [ ] Check all tables created
  - [ ] Verify all foreign keys created
- [ ] **5.6** Restore database if needed
  - [ ] If any issues, restore from backup
  - [ ] Document any rollback issues found
- [ ] **5.7** Create rollback test report
  - [ ] Document rollback test results
  - [ ] Note any issues or improvements needed
  - [ ] Save as `database/schema/rollback-test-report.md`

### Task 6: Create Development Seeder (AC5)
- [ ] **6.1** Create DevelopmentSeeder class
  - [ ] Create `database/seeders/DevelopmentSeeder.php`
  - [ ] Implement idempotent seeding (clear existing data first)
- [ ] **6.2** Seed organization structure data
  - [ ] Seed 3 branches (Jakarta, Bandung, Surabaya)
  - [ ] Seed 3 departments (Finance, Sales, Warehouse)
  - [ ] Seed 3 positions (Finance Manager, Sales Manager, Warehouse Manager)
  - [ ] Seed 2 warehouses (Jakarta, Bandung)
- [ ] **6.3** Seed user data
  - [ ] Seed admin user (admin@akubook.com)
  - [ ] Seed 2 regular users with different branches
  - [ ] Assign proper roles and permissions
- [ ] **6.4** Seed customer data
  - [ ] Seed 3 customers with realistic data
  - [ ] Include different customer types (individual, company)
  - [ ] Set credit limits and payment terms
- [ ] **6.5** Note incomplete migrations
  - [ ] Add comment about suppliers table (migration incomplete)
  - [ ] Add comment about items table (migration incomplete)
  - [ ] Skip seeding for incomplete tables
- [ ] **6.6** Test seeder execution
  - [ ] Run `php artisan db:seed --class=DevelopmentSeeder`
  - [ ] Verify all data inserted correctly
  - [ ] Check foreign key relationships maintained
- [ ] **6.7** Test seeder idempotency
  - [ ] Run seeder again
  - [ ] Verify no duplicate data
  - [ ] Verify no constraint violations
- [ ] **6.8** Verify seeded data
  - [ ] Check data counts in all tables
  - [ ] Verify relationships are correct
  - [ ] Test data integrity

### Task 7: Create Testing Documentation
- [ ] **7.1** Create manual testing guide
  - [ ] Document how to verify migrations
  - [ ] Document how to test rollback
  - [ ] Document how to run seeder
  - [ ] Save as `database/schema/testing-guide.md`
- [ ] **7.2** Create automated test suite (optional)
  - [ ] Create migration test cases
  - [ ] Create constraint test cases
  - [ ] Create seeder test cases

---

## Developer Context

### Current State Analysis
**Existing Database (from Story 1.1):**
- ✅ PostgreSQL 17 configured
- ✅ Database `akubook` created
- ✅ 21 migrations ran successfully
- ✅ All tables created

**What This Story Actually Needs:**
Since migrations already exist and ran, this story focuses on:
1. **Verification** - Review all migrations for correctness
2. **Documentation** - Create ERD + schema docs
3. **Testing** - Test rollback + re-migration
4. **Seeding** - Create development seed data
5. **Validation** - Ensure data integrity constraints

### Critical Implementation Notes

#### 🚨 MUST DO:
1. **Review All Migrations**
   - Check each migration file for proper structure
   - Verify foreign keys have constraints
   - Ensure indexes on foreign keys
   - Check unique constraints on business keys

2. **Create ERD (Entity Relationship Diagram)**
   - Use Mermaid syntax for ERD
   - Show all tables and relationships
   - Include cardinality (1:1, 1:N, N:M)
   - Save as `database/schema/erd.md`

3. **Document Schema**
   - Create `database/schema/README.md`
   - Document each table purpose
   - Document column meanings
   - Document relationships
   - Document constraints

4. **Test Migration Rollback**
   - Run `php artisan migrate:rollback --step=5`
   - Verify tables dropped correctly
   - Run `php artisan migrate` again
   - Verify re-migration works

5. **Create Database Seeder**
   - Create `database/seeders/DevelopmentSeeder.php`
   - Seed realistic test data
   - Maintain referential integrity
   - Make idempotent (can run multiple times)

#### ⚠️ MUST NOT DO:
1. **Don't Modify Existing Migrations** - They already ran in production
2. **Don't Drop Tables Manually** - Use migration rollback
3. **Don't Add New Migrations** - This story is verification only
4. **Don't Seed Production Data** - Only development data
5. **Don't Break Foreign Key Constraints** - Maintain referential integrity

#### 🔍 Verification Checklist:
- [ ] All 21 migrations reviewed
- [ ] ERD created with all relationships
- [ ] Schema documentation complete
- [ ] Rollback tested successfully
- [ ] Re-migration works
- [ ] Seeder created with test data
- [ ] Seeder runs without errors
- [ ] Foreign key constraints verified
- [ ] Unique constraints verified
- [ ] Indexes verified

### Existing Migrations (21 total)

**Laravel Base (3):**
- `0001_01_01_000000_create_users_table.php`
- `0001_01_01_000001_create_cache_table.php`
- `0001_01_01_000002_create_jobs_table.php`

**Organization Structure (4):**
- `2026_05_06_043155_create_branches_table.php`
- `2026_05_06_043156_create_departments_table.php`
- `2026_05_06_043156_create_positions_table.php`
- `2026_05_06_043157_create_warehouses_table.php`

**Security & Compliance (2):**
- `2026_05_13_151516_create_audit_logs_table.php`
- `2026_05_13_151516_create_permission_tables.php` (Spatie)

**User Management (1):**
- `2026_05_13_151517_add_branch_id_to_users_table.php`

**Accounting (4):**
- `2026_05_13_152357_create_accounts_table.php`
- `2026_05_13_152357_create_fiscal_periods_table.php`
- `2026_05_13_152615_create_journal_entries_table.php`
- `2026_05_13_152616_create_journal_entry_lines_table.php`

**Sales & Purchasing (7):**
- `2026_05_14_002319_create_customers_table.php`
- `2026_05_14_002322_create_sales_orders_table.php`
- `2026_05_14_002323_create_sales_order_lines_table.php`
- `2026_05_14_002524_create_items_table.php` ⚠️ (empty migration)
- `2026_05_14_002802_create_suppliers_table.php` ⚠️ (empty migration)
- `2026_05_14_002803_create_purchase_orders_table.php`
- `2026_05_14_002803_create_purchase_order_lines_table.php`

**⚠️ Known Issues:**
- `suppliers` table migration is empty (only timestamps)
- `items` table migration is empty (only timestamps)
- These tables cannot be seeded until migrations are completed

---

## Testing Requirements

### Migration Tests
- Test rollback of all migrations
- Test re-migration after rollback
- Verify no orphaned tables
- Verify foreign key constraints work

### Seeder Tests
- Run seeder multiple times (idempotent)
- Verify all tables have data
- Verify relationships maintained
- Verify no constraint violations

### Manual Testing
1. Run `php artisan migrate:status` - all should show "Ran"
2. Run `php artisan migrate:rollback --step=5`
3. Check tables dropped: `php artisan db:show`
4. Run `php artisan migrate` again
5. Run `php artisan db:seed --class=DevelopmentSeeder`
6. Verify data in database

---

## Definition of Done

- [ ] All acceptance criteria met
- [ ] All tasks and subtasks completed
- [ ] ERD created and saved
- [ ] Schema documentation complete
- [ ] Migration rollback tested
- [ ] Re-migration works
- [ ] Development seeder created
- [ ] Seeder runs successfully
- [ ] All foreign keys verified
- [ ] All constraints verified
- [ ] All tests pass

---

## Dependencies

**Upstream:**
- Story 1.1: Laravel Application Setup (DONE - in review)
- Story 1.2: React + Inertia.js Frontend Setup (DONE - in review)

**Downstream:**
- Story 1.4: Authentication System
- Story 1.5: Audit Logging System
- All future backend stories depend on this

---

## Resources

### Official Documentation
- Laravel Migrations: https://laravel.com/docs/13.x/migrations
- Laravel Seeding: https://laravel.com/docs/13.x/seeding
- PostgreSQL Constraints: https://www.postgresql.org/docs/17/ddl-constraints.html
- Mermaid ERD: https://mermaid.js.org/syntax/entityRelationshipDiagram.html

### Tools
- DBeaver: https://dbeaver.io/
- dbdiagram.io: https://dbdiagram.io/
- Laravel Schema Designer: VS Code extension

### Project-Specific
- Product Brief: `_bmad-output/planning-artifacts/product-brief.md`
- Sprint Status: `_bmad-output/implementation-artifacts/sprint-status.yaml`
- Story 1.1: `_bmad-output/implementation-artifacts/1-1-laravel-application-setup.md`
- Story 1.2: `_bmad-output/implementation-artifacts/1-2-react-inertiajs-frontend-setup.md`

---

## Dev Agent Record

### Implementation Plan
Story created with comprehensive Tasks/Subtasks breakdown. Ready for development.

### Debug Log
- 2026-05-14: Story recreated with detailed Tasks/Subtasks
- Previous version lacked implementation guidance
- Added 7 major tasks with 40+ subtasks
- Noted incomplete migrations (suppliers, items tables)

### Completion Notes
Story file created and ready for `dev-story` workflow.

---

## File List

**Files to be created:**
- `database/schema/README.md` - Main schema documentation
- `database/schema/erd.md` - Entity Relationship Diagram
- `database/schema/migration-verification.md` - Migration verification checklist
- `database/schema/constraints.md` - Constraints documentation
- `database/schema/indexes.md` - Indexes documentation
- `database/schema/constraint-verification-report.md` - Constraint verification results
- `database/schema/rollback-test-report.md` - Rollback test results
- `database/schema/testing-guide.md` - Manual testing guide
- `database/seeders/DevelopmentSeeder.php` - Development data seeder

**Files to be reviewed:**
- All 21 migration files in `database/migrations/`

---

## Change Log

- **2026-05-14:** Story recreated with comprehensive Tasks/Subtasks breakdown
- **2026-05-14:** Added detailed implementation guidance in Developer Context
- **2026-05-14:** Noted incomplete migrations (suppliers, items tables)
- **2026-05-14:** Status set to ready-for-dev

---

**Story Created:** 2026-05-14  
**Ready for Development:** Yes  
**Estimated Effort:** 4-6 hours (verification + documentation + seeding)
