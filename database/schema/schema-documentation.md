# AkuBook ERP - Database Schema Documentation

**Project:** AkuBook ERP  
**Database:** PostgreSQL 17  
**Generated:** 2026-05-14

---

## Table of Contents

1. [Overview](#overview)
2. [Organization Module](#organization-module)
3. [Security Module](#security-module)
4. [Accounting Module](#accounting-module)
5. [Sales Module](#sales-module)
6. [Purchasing Module](#purchasing-module)
7. [Data Types & Conventions](#data-types--conventions)
8. [Indexes & Performance](#indexes--performance)

---

## Overview

### Database Architecture

AkuBook ERP uses PostgreSQL 17 with Laravel 11 migrations. The schema is organized into logical modules:

- **Organization:** Branches, departments, positions, warehouses
- **Security:** Users, roles, permissions (Spatie), sessions, audit logs
- **Accounting:** Chart of accounts, fiscal periods, journal entries
- **Sales:** Customers, sales orders, order lines
- **Purchasing:** Suppliers, purchase orders, order lines

### Key Design Principles

1. **Multi-Branch Support:** Branch-level data isolation
2. **Soft Deletes:** Data retention for audit trails
3. **Audit Trail:** created_by/updated_by tracking
4. **RBAC:** Flexible role-based access control
5. **Data Integrity:** Foreign key constraints with appropriate actions

---

## Organization Module

### branches

**Purpose:** Multi-branch organization structure

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| code | varchar(20) | UNIQUE, NOT NULL | Branch code (e.g., "HQ", "BR01") |
| name | varchar(255) | NOT NULL | Branch name |
| address | text | nullable | Physical address |
| phone | varchar(255) | nullable | Contact phone |
| email | varchar(255) | nullable | Contact email |
| is_active | boolean | default: true | Active status |
| created_at | timestamp | nullable | Creation timestamp |
| updated_at | timestamp | nullable | Last update timestamp |
| deleted_at | timestamp | nullable | Soft delete timestamp |

**Relationships:**
- Has many: warehouses, users, sales_orders, purchase_orders

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (code)
- INDEX (deleted_at) - for soft delete queries

---

### departments

**Purpose:** Organizational departments

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| code | varchar(20) | UNIQUE, NOT NULL | Department code |
| name | varchar(255) | NOT NULL | Department name |
| description | text | nullable | Department description |
| is_active | boolean | default: true | Active status |
| created_at | timestamp | nullable | Creation timestamp |
| updated_at | timestamp | nullable | Last update timestamp |
| deleted_at | timestamp | nullable | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (code)
- INDEX (deleted_at)

---

### positions

**Purpose:** Employee positions/job titles

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| code | varchar(20) | UNIQUE, NOT NULL | Position code |
| name | varchar(255) | NOT NULL | Position name |
| description | text | nullable | Position description |
| is_active | boolean | default: true | Active status |
| created_at | timestamp | nullable | Creation timestamp |
| updated_at | timestamp | nullable | Last update timestamp |
| deleted_at | timestamp | nullable | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (code)
- INDEX (deleted_at)

---

### warehouses

**Purpose:** Inventory storage locations

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| branch_id | bigint | FK, NOT NULL | Parent branch |
| code | varchar(20) | UNIQUE, NOT NULL | Warehouse code |
| name | varchar(255) | NOT NULL | Warehouse name |
| address | text | nullable | Physical address |
| is_active | boolean | default: true | Active status |
| created_at | timestamp | nullable | Creation timestamp |
| updated_at | timestamp | nullable | Last update timestamp |
| deleted_at | timestamp | nullable | Soft delete timestamp |

**Relationships:**
- Belongs to: branches (branch_id)

**Foreign Keys:**
- branch_id → branches(id) ON DELETE RESTRICT

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (code)
- INDEX (branch_id)
- INDEX (deleted_at)

---

## Security Module

### users

**Purpose:** System users (Laravel default + branch extension)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| branch_id | bigint | FK, nullable | Assigned branch |
| name | varchar(255) | NOT NULL | User full name |
| email | varchar(255) | UNIQUE, NOT NULL | Login email |
| email_verified_at | timestamp | nullable | Email verification timestamp |
| password | varchar(255) | NOT NULL | Hashed password |
| remember_token | varchar(100) | nullable | Remember me token |
| created_at | timestamp | nullable | Creation timestamp |
| updated_at | timestamp | nullable | Last update timestamp |

**Relationships:**
- Belongs to: branches (branch_id)
- Has many: audit_logs, journal_entries, sales_orders, purchase_orders
- Has many: model_has_roles, sessions

**Foreign Keys:**
- branch_id → branches(id) ON DELETE SET NULL

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (email)
- INDEX (branch_id)

---

### sessions

**Purpose:** User session management (Laravel default)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | varchar(255) | PK | Session ID |
| user_id | bigint | FK, nullable | Associated user |
| ip_address | varchar(45) | nullable | Client IP address |
| user_agent | text | nullable | Client user agent |
| payload | longtext | NOT NULL | Session data |
| last_activity | integer | NOT NULL | Last activity timestamp |

**Relationships:**
- Belongs to: users (user_id)

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE

**Indexes:**
- PRIMARY KEY (id)
- INDEX (user_id)
- INDEX (last_activity)

---

### audit_logs

**Purpose:** System-wide audit trail

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| user_id | bigint | FK, nullable | User who performed action |
| event | varchar(255) | NOT NULL | Event type (created, updated, deleted) |
| auditable_type | varchar(255) | NOT NULL | Model class name |
| auditable_id | bigint | NOT NULL | Model ID |
| old_values | json | nullable | Previous values |
| new_values | json | nullable | New values |
| url | text | nullable | Request URL |
| ip_address | varchar(45) | nullable | Client IP |
| user_agent | text | nullable | Client user agent |
| created_at | timestamp | nullable | Event timestamp |
| updated_at | timestamp | nullable | Last update timestamp |

**Relationships:**
- Belongs to: users (user_id)
- Polymorphic: auditable (auditable_type, auditable_id)

**Foreign Keys:**
- user_id → users(id) ON DELETE SET NULL

**Indexes:**
- PRIMARY KEY (id)
- INDEX (user_id)
- INDEX (auditable_type, auditable_id)
- INDEX (created_at)

---

### roles (Spatie Permission)

**Purpose:** User roles for RBAC

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| name | varchar(255) | UNIQUE, NOT NULL | Role name |
| guard_name | varchar(255) | NOT NULL | Guard name (web) |
| created_at | timestamp | nullable | Creation timestamp |
| updated_at | timestamp | nullable | Last update timestamp |

**Relationships:**
- Has many: model_has_roles, role_has_permissions

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (name, guard_name)

---

### permissions (Spatie Permission)

**Purpose:** System permissions for RBAC

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| name | varchar(255) | UNIQUE, NOT NULL | Permission name |
| guard_name | varchar(255) | NOT NULL | Guard name (web) |
| created_at | timestamp | nullable | Creation timestamp |
| updated_at | timestamp | nullable | Last update timestamp |

**Relationships:**
- Has many: role_has_permissions, model_has_permissions

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (name, guard_name)

---

### model_has_roles (Spatie Permission)

**Purpose:** Assign roles to models (users)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| role_id | bigint | FK, NOT NULL | Role ID |
| model_type | varchar(255) | NOT NULL | Model class name |
| model_id | bigint | NOT NULL | Model ID (user_id) |

**Relationships:**
- Belongs to: roles (role_id)
- Polymorphic: model (model_type, model_id)

**Foreign Keys:**
- role_id → roles(id) ON DELETE CASCADE

**Indexes:**
- PRIMARY KEY (role_id, model_id, model_type)
- INDEX (model_id, model_type)

---

### role_has_permissions (Spatie Permission)

**Purpose:** Assign permissions to roles

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| permission_id | bigint | FK, NOT NULL | Permission ID |
| role_id | bigint | FK, NOT NULL | Role ID |

**Relationships:**
- Belongs to: permissions (permission_id)
- Belongs to: roles (role_id)

**Foreign Keys:**
- permission_id → permissions(id) ON DELETE CASCADE
- role_id → roles(id) ON DELETE CASCADE

**Indexes:**
- PRIMARY KEY (permission_id, role_id)

---

### model_has_permissions (Spatie Permission)

**Purpose:** Direct permission assignment to models

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| permission_id | bigint | FK, NOT NULL | Permission ID |
| model_type | varchar(255) | NOT NULL | Model class name |
| model_id | bigint | NOT NULL | Model ID |

**Relationships:**
- Belongs to: permissions (permission_id)
- Polymorphic: model (model_type, model_id)

**Foreign Keys:**
- permission_id → permissions(id) ON DELETE CASCADE

**Indexes:**
- PRIMARY KEY (permission_id, model_id, model_type)
- INDEX (model_id, model_type)

---

## Accounting Module

### accounts

**Purpose:** Chart of accounts (hierarchical)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| parent_id | bigint | FK, nullable | Parent account (self-reference) |
| code | varchar(20) | UNIQUE, NOT NULL | Account code |
| name | varchar(255) | NOT NULL | Account name |
| account_type | enum | NOT NULL | asset, liability, equity, revenue, expense |
| normal_balance | enum | NOT NULL | debit, credit |
| description | text | nullable | Account description |
| is_active | boolean | default: true | Active status |
| created_at | timestamp | nullable | Creation timestamp |
| updated_at | timestamp | nullable | Last update timestamp |
| deleted_at | timestamp | nullable | Soft delete timestamp |

**Relationships:**
- Self-referencing: parent_id → accounts(id)
- Has many: journal_entry_lines

**Foreign Keys:**
- parent_id → accounts(id) ON DELETE RESTRICT

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (code)
- INDEX (parent_id)
- INDEX (account_type)
- INDEX (deleted_at)

**Enums:**
- account_type: asset, liability, equity, revenue, expense
- normal_balance: debit, credit

---

### fiscal_periods

**Purpose:** Accounting periods (monthly/yearly)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| code | varchar(20) | UNIQUE, NOT NULL | Period code (e.g., "2024-01") |
| name | varchar(255) | NOT NULL | Period name |
| start_date | date | NOT NULL | Period start date |
| end_date | date | NOT NULL | Period end date |
| status | enum | NOT NULL | open, closed |
| created_at | timestamp | nullable | Creation timestamp |
| updated_at | timestamp | nullable | Last update timestamp |

**Relationships:**
- Has many: journal_entries

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (code)
- INDEX (start_date, end_date)
- INDEX (status)

**Enums:**
- status: open, closed

---

### journal_entries

**Purpose:** Accounting journal entries (header)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| fiscal_period_id | bigint | FK, NOT NULL | Fiscal period |
| entry_number | varchar(50) | UNIQUE, NOT NULL | Entry number (auto-generated) |
| entry_date | date | NOT NULL | Entry date |
| description | text | nullable | Entry description |
| reference | varchar(255) | nullable | External reference |
| status | enum | NOT NULL | draft, posted, reversed |
| created_by | bigint | FK, nullable | User who created |
| posted_by | bigint | FK, nullable | User who posted |
| posted_at | timestamp | nullable | Posted timestamp |
| reversed_at | timestamp | nullable | Reversed timestamp |
| created_at | timestamp | nullable | Creation timestamp |
| updated_at | timestamp | nullable | Last update timestamp |
| deleted_at | timestamp | nullable | Soft delete timestamp |

**Relationships:**
- Belongs to: fiscal_periods (fiscal_period_id)
- Belongs to: users (created_by, posted_by)
- Has many: journal_entry_lines

**Foreign Keys:**
- fiscal_period_id → fiscal_periods(id) ON DELETE RESTRICT
- created_by → users(id) ON DELETE SET NULL
- posted_by → users(id) ON DELETE SET NULL

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (entry_number)
- INDEX (fiscal_period_id)
- INDEX (entry_date)
- INDEX (status)
- INDEX (created_by)
- INDEX (posted_by)
- INDEX (deleted_at)

**Enums:**
- status: draft, posted, reversed

---

### journal_entry_lines

**Purpose:** Journal entry line items (details)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| journal_entry_id | bigint | FK, NOT NULL | Parent journal entry |
| account_id | bigint | FK, NOT NULL | Account |
| debit | decimal(20,2) | default: 0.00 | Debit amount |
| credit | decimal(20,2) | default: 0.00 | Credit amount |
| description | text | nullable | Line description |
| created_at | timestamp | nullable | Creation timestamp |
| updated_at | timestamp | nullable | Last update timestamp |

**Relationships:**
- Belongs to: journal_entries (journal_entry_id)
- Belongs to: accounts (account_id)

**Foreign Keys:**
- journal_entry_id → journal_entries(id) ON DELETE CASCADE
- account_id → accounts(id) ON DELETE RESTRICT

**Indexes:**
- PRIMARY KEY (id)
- INDEX (journal_entry_id)
- INDEX (account_id)

**Constraints:**
- CHECK: debit >= 0
- CHECK: credit >= 0
- CHECK: NOT (debit > 0 AND credit > 0) - one side only

---

## Sales Module

### customers

**Purpose:** Customer master data

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| code | varchar(20) | UNIQUE, NOT NULL | Customer code |
| name | varchar(255) | NOT NULL | Customer name |
| customer_type | enum | default: company | individual, company |
| contact_person | varchar(255) | nullable | Contact person name |
| email | varchar(255) | nullable | Contact email |
| phone | varchar(255) | nullable | Contact phone |
| address | text | nullable | Physical address |
| city | varchar(255) | nullable | City |
| tax_id | varchar(255) | nullable | Tax ID (NPWP) |
| credit_limit | decimal(20,2) | default: 0.00 | Credit limit |
| payment_terms_days | integer | default: 0 | Payment terms (days) |
| is_active | boolean | default: true | Active status |
| notes | text | nullable | Additional notes |
| created_by | bigint | FK, nullable | User who created |
| updated_by | bigint | FK, nullable | User who last updated |
| created_at | timestamp | nullable | Creation timestamp |
| updated_at | timestamp | nullable | Last update timestamp |
| deleted_at | timestamp | nullable | Soft delete timestamp |

**Relationships:**
- Has many: sales_orders
- Belongs to: users (created_by, updated_by)

**Foreign Keys:**
- created_by → users(id) ON DELETE SET NULL
- updated_by → users(id) ON DELETE SET NULL

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (code)
- INDEX (customer_type)
- INDEX (is_active)
- INDEX (created_by)
- INDEX (updated_by)
- INDEX (deleted_at)

**Enums:**
- customer_type: individual, company

---

### sales_orders

**Purpose:** Sales order headers

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| customer_id | bigint | FK, NOT NULL | Customer |
| branch_id | bigint | FK, NOT NULL | Branch |
| order_number | varchar(50) | UNIQUE, NOT NULL | Order number (auto-generated) |
| order_date | date | NOT NULL | Order date |
| customer_po | varchar(255) | nullable | Customer PO number |
| status | enum | NOT NULL | draft, confirmed, processing, completed, cancelled |
| subtotal | decimal(20,2) | default: 0.00 | Subtotal amount |
| tax_amount | decimal(20,2) | default: 0.00 | Tax amount |
| discount_amount | decimal(20,2) | default: 0.00 | Discount amount |
| total_amount | decimal(20,2) | default: 0.00 | Total amount |
| notes | text | nullable | Order notes |
| created_by | bigint | FK, nullable | User who created |
| approved_by | bigint | FK, nullable | User who approved |
| approved_at | timestamp | nullable | Approval timestamp |
| created_at | timestamp | nullable | Creation timestamp |
| updated_at | timestamp | nullable | Last update timestamp |
| deleted_at | timestamp | nullable | Soft delete timestamp |

**Relationships:**
- Belongs to: customers (customer_id)
- Belongs to: branches (branch_id)
- Belongs to: users (created_by, approved_by)
- Has many: sales_order_lines

**Foreign Keys:**
- customer_id → customers(id) ON DELETE RESTRICT
- branch_id → branches(id) ON DELETE RESTRICT
- created_by → users(id) ON DELETE SET NULL
- approved_by → users(id) ON DELETE SET NULL

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (order_number)
- INDEX (customer_id)
- INDEX (branch_id)
- INDEX (order_date)
- INDEX (status)
- INDEX (created_by)
- INDEX (approved_by)
- INDEX (deleted_at)

**Enums:**
- status: draft, confirmed, processing, completed, cancelled

---

### sales_order_lines

**Purpose:** Sales order line items

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| sales_order_id | bigint | FK, NOT NULL | Parent sales order |
| item_id | bigint | FK, NOT NULL | Item/product |
| description | text | nullable | Line description |
| quantity | decimal(15,2) | NOT NULL | Quantity |
| unit_price | decimal(20,2) | NOT NULL | Unit price |
| discount_amount | decimal(20,2) | default: 0.00 | Discount amount |
| tax_amount | decimal(20,2) | default: 0.00 | Tax amount |
| line_total | decimal(20,2) | default: 0.00 | Line total |
| created_at | timestamp | nullable | Creation timestamp |
| updated_at | timestamp | nullable | Last update timestamp |

**Relationships:**
- Belongs to: sales_orders (sales_order_id)
- Belongs to: items (item_id)

**Foreign Keys:**
- sales_order_id → sales_orders(id) ON DELETE CASCADE
- item_id → items(id) ON DELETE RESTRICT

**Indexes:**
- PRIMARY KEY (id)
- INDEX (sales_order_id)
- INDEX (item_id)

**Constraints:**
- CHECK: quantity > 0
- CHECK: unit_price >= 0

---

## Purchasing Module

### suppliers

**Purpose:** Supplier master data

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| code | varchar(20) | UNIQUE | Supplier code |
| name | varchar(255) | | Supplier name |
| address | text | | Physical address |
| phone | varchar(255) | | Contact phone |
| email | varchar(255) | | Contact email |
| is_active | boolean | | Active status |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Status:** ⚠️ **MIGRATION EMPTY - NOT IMPLEMENTED**

**Expected Relationships:**
- Has many: purchase_orders

---

### purchase_orders

**Purpose:** Purchase order headers

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| supplier_id | bigint | FK, NOT NULL | Supplier |
| branch_id | bigint | FK, NOT NULL | Branch |
| po_number | varchar(50) | UNIQUE, NOT NULL | PO number (auto-generated) |
| po_date | date | NOT NULL | PO date |
| status | enum | NOT NULL | draft, confirmed, received, completed, cancelled |
| subtotal | decimal(20,2) | default: 0.00 | Subtotal amount |
| tax_amount | decimal(20,2) | default: 0.00 | Tax amount |
| discount_amount | decimal(20,2) | default: 0.00 | Discount amount |
| total_amount | decimal(20,2) | default: 0.00 | Total amount |
| notes | text | nullable | PO notes |
| created_by | bigint | FK, nullable | User who created |
| approved_by | bigint | FK, nullable | User who approved |
| approved_at | timestamp | nullable | Approval timestamp |
| created_at | timestamp | nullable | Creation timestamp |
| updated_at | timestamp | nullable | Last update timestamp |
| deleted_at | timestamp | nullable | Soft delete timestamp |

**Relationships:**
- Belongs to: suppliers (supplier_id)
- Belongs to: branches (branch_id)
- Belongs to: users (created_by, approved_by)
- Has many: purchase_order_lines

**Foreign Keys:**
- supplier_id → suppliers(id) ON DELETE RESTRICT
- branch_id → branches(id) ON DELETE RESTRICT
- created_by → users(id) ON DELETE SET NULL
- approved_by → users(id) ON DELETE SET NULL

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (po_number)
- INDEX (supplier_id)
- INDEX (branch_id)
- INDEX (po_date)
- INDEX (status)
- INDEX (created_by)
- INDEX (approved_by)
- INDEX (deleted_at)

**Enums:**
- status: draft, confirmed, received, completed, cancelled

---

### purchase_order_lines

**Purpose:** Purchase order line items

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| purchase_order_id | bigint | FK, NOT NULL | Parent purchase order |
| item_id | bigint | FK, NOT NULL | Item/product |
| description | text | nullable | Line description |
| quantity | decimal(15,2) | NOT NULL | Quantity |
| unit_price | decimal(20,2) | NOT NULL | Unit price |
| discount_amount | decimal(20,2) | default: 0.00 | Discount amount |
| tax_amount | decimal(20,2) | default: 0.00 | Tax amount |
| line_total | decimal(20,2) | default: 0.00 | Line total |
| created_at | timestamp | nullable | Creation timestamp |
| updated_at | timestamp | nullable | Last update timestamp |

**Relationships:**
- Belongs to: purchase_orders (purchase_order_id)
- Belongs to: items (item_id)

**Foreign Keys:**
- purchase_order_id → purchase_orders(id) ON DELETE CASCADE
- item_id → items(id) ON DELETE RESTRICT

**Indexes:**
- PRIMARY KEY (id)
- INDEX (purchase_order_id)
- INDEX (item_id)

**Constraints:**
- CHECK: quantity > 0
- CHECK: unit_price >= 0

---

### items

**Purpose:** Product/item master data

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| code | varchar(20) | UNIQUE | Item code |
| name | varchar(255) | | Item name |
| description | text | | Item description |
| price | decimal(20,2) | | Item price |
| is_active | boolean | | Active status |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Status:** ⚠️ **MIGRATION EMPTY - NOT IMPLEMENTED**

**Expected Relationships:**
- Has many: sales_order_lines
- Has many: purchase_order_lines

---

## Data Types & Conventions

### Standard Data Types

| Type | Usage | Example |
|------|-------|---------|
| bigint | Primary keys, foreign keys | id, user_id |
| varchar(20) | Codes | branch_code, account_code |
| varchar(255) | Names, emails | name, email |
| text | Long text | description, notes, address |
| decimal(20,2) | Money amounts | total_amount, unit_price |
| decimal(15,2) | Quantities | quantity |
| date | Dates without time | order_date, start_date |
| timestamp | Dates with time | created_at, posted_at |
| boolean | True/false flags | is_active |
| enum | Fixed value sets | status, account_type |
| json | Structured data | old_values, new_values |

### Naming Conventions

1. **Tables:** Plural, snake_case (e.g., `sales_orders`)
2. **Columns:** Singular, snake_case (e.g., `customer_id`)
3. **Foreign Keys:** `{table}_id` (e.g., `branch_id`)
4. **Timestamps:** Laravel standard (`created_at`, `updated_at`, `deleted_at`)
5. **Enums:** Lowercase, underscore-separated (e.g., `draft`, `in_progress`)

### Common Patterns

1. **Soft Deletes:** `deleted_at` timestamp (nullable)
2. **Audit Trail:** `created_by`, `updated_by` (foreign keys to users)
3. **Status Tracking:** `status` enum + related timestamps
4. **Code + Name:** Most master data has `code` (unique) + `name`
5. **Active Flag:** `is_active` boolean (default: true)

---

## Indexes & Performance

### Index Strategy

1. **Primary Keys:** Auto-indexed (id)
2. **Foreign Keys:** Indexed for join performance
3. **Unique Constraints:** Auto-indexed (code, email, etc.)
4. **Soft Deletes:** Indexed (deleted_at) for WHERE IS NULL queries
5. **Status Fields:** Indexed for filtering
6. **Date Fields:** Indexed for range queries
7. **Composite Indexes:** For common multi-column queries

### Query Optimization Tips

1. **Use Indexes:** Ensure WHERE/JOIN columns are indexed
2. **Avoid SELECT *:** Select only needed columns
3. **Use Eager Loading:** Prevent N+1 queries (Laravel)
4. **Soft Delete Queries:** Always include `deleted_at IS NULL` or use Laravel scopes
5. **Date Ranges:** Use BETWEEN for date range queries
6. **Enum Filtering:** Fast due to indexed status columns

### Performance Considerations

1. **Large Tables:** sales_orders, purchase_orders, journal_entry_lines
2. **Frequent Queries:** Customer/supplier lookups, order status checks
3. **Heavy Joins:** Order lines with items, journal lines with accounts
4. **Audit Logs:** Can grow very large - consider archiving strategy

---

## Notes

1. **Items & Suppliers:** Migrations are empty - implementation pending
2. **Soft Deletes:** Used extensively for data retention
3. **Multi-Branch:** Branch-level isolation supported throughout
4. **RBAC:** Spatie Laravel Permission for flexible access control
5. **Audit Trail:** Comprehensive tracking via audit_logs table

---

**Generated:** 2026-05-14  
**Status:** ✅ Complete (except items/suppliers)  
**Next Steps:** Implement items and suppliers migrations
