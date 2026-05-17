# Story 8.9: Sales Reports

**Epic:** 8 - Customer & Sales Management  
**Story ID:** 8.9  
**Story Key:** 8-9-sales-reports  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** P1 (Core)

---

## User Story

**Sebagai** Sales Manager  
**Saya ingin** view sales reports dan analytics  
**Sehingga** saya dapat monitor sales performance dan make decisions

---

## Business Context

Sales Reports untuk analyze sales performance:
- **Sales by Period**: Daily, weekly, monthly, yearly
- **Sales by Customer**: Top customers, customer analysis
- **Sales by Product**: Best sellers, product performance
- **Sales by Salesperson**: Individual performance
- **Sales Pipeline**: Quotation → Order → Invoice

---

## Acceptance Criteria

### AC1: Sales Summary Report
- Total sales by period
- Number of transactions
- Average order value
- Growth rate (vs previous period)
- Filter by date range, customer, product, salesperson

### AC2: Sales by Customer Report
- Customer name, total sales, order count
- Sort by sales amount
- Show customer category
- Export to Excel/PDF

### AC3: Sales by Product Report
- Product name, quantity sold, revenue
- Profit margin per product
- Best sellers ranking
- Slow-moving items

### AC4: Sales by Salesperson Report
- Salesperson name, sales amount, order count
- Achievement vs target
- Commission calculation
- Ranking

### AC5: Sales Pipeline Report
- Quotations (pending, approved, converted)
- Sales orders (by status)
- Invoices (paid, unpaid, overdue)
- Conversion rates

### AC6: Aging Report
- Invoices by age (0-30, 31-60, 61-90, >90 days)
- Outstanding amount by customer
- Overdue analysis

---

## Technical Specifications

`php
// SalesReportService
public function getSalesSummary(\\, \\, \\);
public function getSalesByCustomer(\\, \\);
public function getSalesByProduct(\\, \\);
public function getSalesBySalesperson(\\, \\);
public function getSalesPipeline();
public function getAgingReport(\\);
`

---

## Definition of Done

- [ ] SalesReportService created
- [ ] SalesReportController created
- [ ] All 6 reports implemented
- [ ] React components
- [ ] Export to Excel/PDF
- [ ] Filters & date range
- [ ] Tests (80%+ coverage)
- [ ] Merged to main

---

## Notes

- Use raw SQL for performance
- Cache report results (5 minutes)
- Export via Laravel Excel
- Charts using Chart.js
