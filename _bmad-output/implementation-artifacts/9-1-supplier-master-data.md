# Story 9.1: Supplier Master Data

**Epic:** 9 - Supplier & Purchase Management  
**Story ID:** 9.1  
**Story Key:** 9-1-supplier-master-data  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** P0 (Foundation)

---

## User Story

**Sebagai** Purchasing Staff  
**Saya ingin** manage supplier master data  
**Sehingga** saya dapat track supplier information dan payment terms

---

## Business Context

Supplier Master Data adalah foundation untuk purchase cycle:
- **Supplier Information**: Name, code, category, tax info
- **Payment Terms**: Credit terms, payment methods
- **Contact Management**: Multiple contacts per supplier
- **Address Management**: Billing & shipping addresses
- **Performance Tracking**: Delivery performance, quality rating

---

## Acceptance Criteria

### AC1: Supplier CRUD
- Create, edit, view, soft delete supplier
- Supplier code auto-generated (SUPP-YYYY-NNNN)
- Supplier category (Raw Material, Packaging, Service, etc.)
- Tax ID (NPWP), tax type (PKP/Non-PKP)
- Payment terms (Net 0, 7, 14, 30, 45, 60)

### AC2: Contact Management
- Multiple contacts per supplier
- Primary contact flag
- Contact fields: name, position, phone, email

### AC3: Address Management
- Multiple addresses per supplier
- Address type (billing, shipping, both)
- Default address flag

### AC4: Supplier Performance
- Delivery on-time rate
- Quality rating (1-5 stars)
- Total purchase amount
- Last purchase date

---

## Database Schema

`sql
CREATE TABLE suppliers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    supplier_code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    tax_id VARCHAR(50),
    tax_type ENUM('pkp', 'non_pkp') DEFAULT 'non_pkp',
    payment_terms VARCHAR(50),
    phone VARCHAR(50),
    email VARCHAR(255),
    website VARCHAR(255),
    notes TEXT,
    delivery_rating DECIMAL(3,2) DEFAULT 0,
    quality_rating DECIMAL(3,2) DEFAULT 0,
    total_purchase_amount DECIMAL(15,2) DEFAULT 0,
    last_purchase_date DATE NULL,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

CREATE TABLE supplier_contacts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    supplier_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    position VARCHAR(100),
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255),
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE supplier_addresses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    supplier_id BIGINT NOT NULL,
    address_type ENUM('billing', 'shipping', 'both') NOT NULL,
    street_address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Indonesia',
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
`

---

## Definition of Done

- [ ] Migrations created
- [ ] Models & relationships
- [ ] Controller & routes
- [ ] Form requests & validation
- [ ] React components (Index, Create, Edit, Show)
- [ ] Supplier code auto-generation
- [ ] Tests (80%+ coverage)
- [ ] Merged to main

---

## Notes

- Supplier code: SUPP-YYYY-NNNN
- Similar structure to Customer (Story 8.1)
- Performance metrics updated via purchase transactions
