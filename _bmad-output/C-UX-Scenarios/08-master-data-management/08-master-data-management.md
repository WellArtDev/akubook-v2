# 08: Pak Hendra's Master Data Management

**Project:** AkuBook  
**Created:** 2026-05-12  
**Method:** Whiteport Design Studio (WDS)

---

## Transaction (Q1)

**What this scenario covers:**
Add/edit customers, suppliers, products, employees — full CRUD control over master data

---

## Business Goal (Q2)

**Goal:** 🌟 TERTIARY: Data Sovereignty (Full Control)  
**Objective:** Company owns their data 100%, full CRUD control, no vendor restrictions

---

## User & Situation (Q3)

**Persona:** Pak Hendra (Company Owner, TERTIARY) or Admin  
**Situation:** New supplier onboarding, need to add to system. Or product catalog update. Or new employee hire. Need full CRUD control without vendor restrictions.

---

## Driving Forces (Q4)

**Hope:** Full CRUD control over master data, data ownership confirmed, no vendor lock-in, can export anytime.

**Worry:** Data locked by vendor, can't export, migration nightmare if switch software, vendor hostage situation.

---

## Device & Starting Point (Q5 + Q6)

**Device:** Desktop  
**Entry:** As-needed — new supplier/customer/product/employee onboarding, or periodic master data updates.

---

## Best Outcome (Q7)

**User Success:**
Master data added/updated successfully, available across all modules immediately (Sales, Purchasing, Inventory, HRM). Full data ownership confirmed, can export anytime.

**Business Success:**
Data quality maintained, business continuity ensured, no vendor lock-in. Master data serves as single source of truth across all modules.

---

## Shortest Path (Q8)

1. **Main Dashboard** — Pak Hendra clicks "Master Data" menu
2. **Customer Management** — Add/edit customers (CRUD operations)
3. **Supplier Management** — Add/edit suppliers (CRUD operations)
4. **Product Management** — Add/edit products (CRUD operations)
5. **Employee Management** — Add/edit employees (CRUD operations) ✓

---

## Trigger Map Connections

**Persona:** Pak Hendra (Company Owner, TERTIARY)

**Driving Forces Addressed:**
- ✅ **Want:** Data sovereignty (full control, no vendor restrictions)
- ❌ **Fear:** Data locked by vendor, migration nightmare, vendor hostage

**Business Goal:** 🌟 TERTIARY: Data sovereignty → full control → business freedom

---

## Scenario Steps

| Step | Folder | Purpose | Exit Action |
|------|--------|---------|-------------|
| 08.1 | `08.1-main-dashboard/` | Access master data menu | Click "Master Data" |
| 08.2 | `08.2-customer-management/` | Manage customers (CRUD) | Add/edit customer |
| 08.3 | `08.3-supplier-management/` | Manage suppliers (CRUD) | Add/edit supplier |
| 08.4 | `08.4-product-management/` | Manage products (CRUD) | Add/edit product |
| 08.5 | `08.5-employee-management/` | Manage employees (CRUD) | Add/edit employee ✓ |

---

_Scenario 08: Pak Hendra's Master Data Management_
