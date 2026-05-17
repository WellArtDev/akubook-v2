# Story 8.11: Sales Dashboard

**Epic:** 8 - Customer & Sales Management  
**Story ID:** 8.11  
**Story Key:** 8-11-sales-dashboard  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** P2 (Nice to Have)

---

## User Story

**Sebagai** Sales Manager  
**Saya ingin** view sales dashboard  
**Sehingga** saya dapat monitor real-time sales performance

---

## Business Context

Sales Dashboard untuk quick overview:
- **KPIs**: Sales today, this month, this year
- **Charts**: Sales trend, top products, top customers
- **Alerts**: Pending approvals, overdue invoices
- **Quick Actions**: Create quotation, view reports

---

## Acceptance Criteria

### AC1: KPI Cards
- Today's sales
- This month's sales (vs target)
- This year's sales
- Pending quotations
- Pending approvals
- Overdue invoices

### AC2: Sales Trend Chart
- Line chart: Last 12 months
- Compare with previous year
- Filter by product/customer

### AC3: Top 10 Lists
- Top 10 customers (by sales)
- Top 10 products (by quantity)
- Top 10 salespeople

### AC4: Recent Activity
- Recent quotations
- Recent orders
- Recent invoices
- Recent payments

### AC5: Alerts & Notifications
- Pending approvals count
- Overdue invoices count
- Low stock alerts (for sales)

---

## Technical Specifications

`php
// SalesDashboardService
public function getKPIs();
public function getSalesTrend(\\ = 12);
public function getTopCustomers(\\ = 10);
public function getTopProducts(\\ = 10);
public function getTopSalespeople(\\ = 10);
public function getRecentActivity(\\ = 10);
public function getAlerts();
`

---

## Definition of Done

- [ ] SalesDashboardService created
- [ ] Dashboard controller & route
- [ ] React dashboard component
- [ ] KPI cards
- [ ] Charts (Chart.js)
- [ ] Top 10 lists
- [ ] Recent activity
- [ ] Alerts
- [ ] Tests (80%+ coverage)
- [ ] Merged to main

---

## Notes

- Cache dashboard data (5 minutes)
- Real-time updates via polling
- Responsive design
- Export dashboard to PDF
