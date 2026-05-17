# 6. Settings (Pengaturan)

## Overview

Settings module in Accurate Online provides system configuration and user management foundation. Controls access, permissions, preferences, integrations, notifications, and security for multi-user operations.

**Priority**: HIGH (configuration foundation)  
**Complexity**: Medium - RBAC model, API integration, security policies

---

## Features List (6 Total)

### 1. User Management (Manajemen Pengguna)

**Function**: Create and manage user accounts  
**Key Capabilities**:
- 2 user levels: Admin (full access) vs Operator (restricted)
- Email/SMS invitation system
- User activation/deactivation
- Password reset
- 2FA setup (email/SMS verification)
- Unlimited users (subscription-based)

**User Levels**:
- **Admin**: Full system access, can manage users, change settings
- **Operator**: Restricted access based on Access Group assignment

**Invitation Process**:
1. Admin creates user account
2. System sends email/SMS invite
3. User clicks link, sets password
4. 2FA verification (if enabled)
5. User active

---

### 2. Access Groups (Grup Akses)

**Function**: Role-based access control (RBAC)  
**Key Capabilities**:
- Unlimited custom access groups
- 11 permission modules:
  1. Dashboard
  2. Sales
  3. Purchasing
  4. Inventory
  5. Cash & Bank
  6. General Ledger
  7. Fixed Assets
  8. Manufacturing
  9. Reports
  10. Settings
  11. Company
- Granular permissions per module (view, create, edit, delete, approve)
- Assign multiple users to one group
- Assign multiple groups to one user (cumulative permissions)

**Permission Levels**:
- **View**: Read-only access
- **Create**: Add new records
- **Edit**: Modify existing records
- **Delete**: Remove records
- **Approve**: Approve transactions (workflow)

**Default Groups**:
- Admin (full access)
- Finance (GL, AR, AP, Cash & Bank)
- Sales (Sales, Inventory view)
- Purchasing (Purchasing, Inventory view)
- Warehouse (Inventory, Delivery)

---

### 3. Preferences (Preferensi)

**Function**: Operator restrictions and system preferences  
**Key Capabilities**:
- 5 restriction categories:
  1. **Transaction Restrictions**: Limit transaction types per user
  2. **Date Restrictions**: Lock past periods, prevent backdating
  3. **Branch Restrictions**: Limit access to specific branches
  4. **Department Restrictions**: Limit access to specific departments
  5. **Amount Restrictions**: Set transaction amount limits
- Default settings per user
- Override capabilities (admin only)

**Restriction Examples**:
- Operator A: Can only create Sales Invoices (no edit/delete)
- Operator B: Cannot post transactions before 2026-01-01
- Operator C: Can only access Branch Jakarta
- Operator D: Cannot approve transactions > Rp 10M

---

### 4. Integrations (Integrasi)

**Function**: Third-party system integration  
**Key Capabilities**:
- **OAuth 2.0 API**: RESTful API for external systems
- **SmartLink Bank Sync**: Auto-import bank statements (BCA, Mandiri, BNI, BRI)
- **E-Commerce Integration**: Tokopedia, Shopee, Lazada sync
- **E-Faktur Integration**: SmartLink Tax for e-Faktur generation
- **Third-Party Ecosystem**: Marketplace for add-ons

**API Features**:
- OAuth 2.0 authentication
- 15-day token validity
- Refresh token mechanism
- Rate limiting (per subscription tier)
- Webhook support (future roadmap)

**SmartLink Bank Sync**:
- Auto-import bank statements daily
- Auto-match transactions
- Reconciliation automation
- Supported banks: BCA, Mandiri, BNI, BRI

---

### 5. Notifications (Notifikasi)

**Function**: Email/SMS alerts for events  
**Key Capabilities**:
- Email notifications (SMTP config per branch)
- SMS notifications (via SMS gateway)
- Event triggers:
  - Low stock alerts
  - Overdue invoices
  - Payment reminders
  - Approval requests
  - System errors
- Custom notification rules
- Recipient groups

**SMTP Configuration**:
- Per-branch SMTP settings
- Support for Gmail, Outlook, custom SMTP
- Test email function
- Attachment support

---

### 6. Security (Keamanan)

**Function**: Audit logs, period locks, token management  
**Key Capabilities**:
- **Audit Logs**: Track all user actions (who, what, when)
- **Period Locks**: Lock past periods to prevent changes
- **Token Management**: API token generation and revocation
- **Session Validation**: Auto-logout after inactivity
- **IP Whitelisting**: Restrict access by IP (future)
- **2FA Enforcement**: Require 2FA for all users

**Audit Log Details**:
- User ID, action type, timestamp
- Before/after values
- IP address, device info
- Immutable log (cannot be edited/deleted)

**Period Lock**:
- Lock by month/year
- Prevent backdating
- Admin override capability
- Unlock with approval

---

## RBAC Model

### Permission Structure

**11 Modules** × **5 Permission Levels** = 55 permission points

**Example Access Group: "Finance Manager"**
- Dashboard: View
- Sales: View, Create, Edit (no Delete)
- Purchasing: View, Create, Edit (no Delete)
- Inventory: View only
- Cash & Bank: View, Create, Edit, Delete, Approve
- General Ledger: View, Create, Edit, Delete, Approve
- Fixed Assets: View, Create, Edit
- Manufacturing: No access
- Reports: View all
- Settings: No access
- Company: No access

---

## Default Settings

### Restrictive by Default
- New users: No access until granted
- New access groups: No permissions until configured
- API tokens: Disabled until generated
- Notifications: Disabled until configured

### Recommended Baseline
1. **Admin Group**: Full access (1-2 users)
2. **Finance Group**: GL, AR, AP, Cash & Bank (full access)
3. **Sales Group**: Sales, Inventory (view), Reports (sales only)
4. **Purchasing Group**: Purchasing, Inventory (view), Reports (purchasing only)
5. **Warehouse Group**: Inventory (full), Delivery (full)

---

## Security Policies

### 8 Recommended Controls

1. **2FA Enforcement**: Require for all admin users
2. **Password Policy**: Min 8 chars, complexity requirements
3. **Session Timeout**: Auto-logout after 30 min inactivity
4. **Period Lock**: Lock prior month on 5th of current month
5. **Approval Workflow**: Require approval for transactions > Rp 10M
6. **Audit Log Review**: Monthly review by admin
7. **API Token Rotation**: Rotate tokens every 90 days
8. **User Access Review**: Quarterly review of user permissions

---

## Multi-User Scenarios

### Scenario 1: Small Business (5 users)
- 1 Admin (owner)
- 1 Finance (accountant)
- 2 Sales (sales team)
- 1 Warehouse (stock keeper)

**Access Groups**:
- Admin: Full access
- Finance: GL, AR, AP, Cash & Bank, Reports
- Sales: Sales, Inventory (view), Reports (sales)
- Warehouse: Inventory, Delivery

### Scenario 2: Medium Business (20 users)
- 2 Admins (owner + IT)
- 3 Finance (accountant + 2 AP/AR clerks)
- 5 Sales (sales team)
- 3 Purchasing (procurement team)
- 5 Warehouse (multi-branch)
- 2 Managers (view-only, all modules)

**Access Groups**:
- Admin: Full access
- Finance Manager: GL, AR, AP, Cash & Bank (full), Reports (all)
- AP Clerk: AP, Cash & Bank (payments only)
- AR Clerk: AR, Cash & Bank (receipts only)
- Sales: Sales, Inventory (view), Reports (sales)
- Purchasing: Purchasing, Inventory (view), Reports (purchasing)
- Warehouse: Inventory, Delivery (branch-specific)
- Manager: View-only all modules, Reports (all)

---

## Integration Points

### Upstream
- User authentication for all modules
- Permission checks on every action
- Audit logging for all transactions

### Downstream
- API access for external systems
- Bank sync for Cash & Bank module
- E-commerce sync for Sales module
- E-Faktur sync for Tax module

---

## Priority for AkuBook MVP

### Phase 1 (Must Have):
1. User Management (admin + operator levels)
2. Access Groups (RBAC with 11 modules)
3. Preferences (operator restrictions)
4. Security (audit logs, period locks)

### Phase 2 (Should Have):
5. Integrations (OAuth 2.0 API)
6. Notifications (email/SMS alerts)

### Phase 3 (Nice to Have):
7. SmartLink Bank Sync
8. E-Commerce Integration
9. Advanced security (IP whitelisting, 2FA enforcement)

---

## Technical Notes

### Data Model
- **Users**: User ID, email, level (admin/operator), status, 2FA enabled
- **Access Groups**: Group ID, name, permissions (JSON)
- **User-Group Mapping**: Many-to-many relationship
- **Audit Logs**: User ID, action, timestamp, before/after, IP
- **API Tokens**: Token ID, user ID, expiry, scopes

### Permission Check Logic
```
if (user.level == 'admin') {
  return true; // Admin has full access
}

// Operator: check access groups
$permissions = [];
foreach (user.accessGroups as $group) {
  $permissions = array_merge($permissions, $group.permissions);
}

// Cumulative permissions (OR logic)
return in_array($requiredPermission, $permissions);
```

---

## Common Pitfalls

1. **Over-Permissioning**: Granting too many permissions (principle of least privilege)
2. **No Audit Log Review**: Logs accumulate without review
3. **Weak Passwords**: No password policy enforcement
4. **No 2FA**: Single-factor authentication risk
5. **No Period Lock**: Past periods remain editable
6. **API Token Leakage**: Tokens shared or exposed
7. **No User Access Review**: Stale permissions accumulate

---

**Source**: Accurate Online Help Documentation (https://help.accurate.id/product/pengaturan/)  
**Last Updated**: May 2026  
**Compliance**: RBAC best practices, audit trail requirements
