# Target Users: AkuBook

**Date:** 2026-05-12

---

## User Overview

AkuBook serves multiple user roles within a company, from primary users who manage core business operations to secondary users who perform specific tasks. All users share a common need for attendance tracking, but their module access and permissions vary based on their role.

---

## Primary Users

### 1. Finance Admin / Accounting

**Role:** Manage financial operations and reporting

**Daily Activities:**
- Record transactions (AP/AR)
- Bank reconciliation
- Generate financial reports
- Monthly closing process
- Tax reporting

**Current State:**
- Login to Accurate + Excel
- Manual data compilation for reports
- Export-import between systems
- Monthly close takes multiple days

**Frustrations:**
- Data scattered across Accurate + Excel + attendance machine
- Manual reconciliation time-consuming
- Monthly close bottleneck (waiting for data from other departments)
- Report generation requires multiple sources

**Goals:**
- Faster monthly close (reduce from days to hours)
- Real-time financial visibility
- Automated reconciliation
- One-click report generation

**Module Access (Configurable via RBAC):**
- Accounting (full CRUD)
- Reports & Dashboard
- Bank reconciliation
- Tax management
- AP/AR management

### 2. HRD Manager

**Role:** Manage human resources and payroll

**Daily Activities:**
- Monitor attendance
- Process leave requests
- Calculate payroll
- Manage employee data
- Handle overtime and allowances

**Current State:**
- Manual attendance data pull from machine
- Excel for payroll calculation
- Manual approval via WhatsApp/paper
- Scattered employee data

**Frustrations:**
- Manual attendance data entry (time-consuming, error-prone)
- Approval bottleneck (waiting for physical signatures)
- Payroll calculation prone to errors
- No real-time attendance visibility

**Goals:**
- Automated attendance sync (no manual data pull)
- Flexible approval flow (manual or automatic)
- Accurate payroll calculation
- Real-time attendance monitoring

**Module Access (Configurable via RBAC):**
- HRM (full CRUD)
- Attendance monitoring
- Payroll processing
- Leave management
- Employee database
- Company announcements

### 3. Purchasing Manager

**Role:** Manage procurement and supplier relationships

**Daily Activities:**
- Create purchase orders
- Manage suppliers
- Track inventory levels
- Approve purchase requests
- Monitor delivery schedules

**Current State:**
- Accurate for PO creation
- Manual approval process
- Limited real-time inventory visibility
- Excel for supplier tracking

**Frustrations:**
- Approval delays (manual process)
- Can't see real-time inventory across warehouses
- Supplier data scattered
- PO tracking manual

**Goals:**
- Streamlined approval flow
- Real-time inventory visibility
- Centralized supplier management
- Automated PO tracking

**Module Access (Configurable via RBAC):**
- Purchasing (full CRUD)
- Inventory (view + planning)
- Supplier management
- Purchase orders
- Approval workflows

---

## Secondary Users

### 4. Sales Team

**Role:** Manage customer orders and sales operations

**Daily Activities:**
- Create sales orders
- Check inventory availability
- Generate invoices
- Track commissions
- Manage customer relationships

**Current State:**
- Accurate for SO and invoicing
- Manual inventory check (call warehouse)
- Excel for commission tracking
- Limited customer history visibility

**Frustrations:**
- Can't check real-time inventory (have to ask warehouse)
- Commission calculation manual and delayed
- Customer order history scattered
- Invoice generation slow

**Goals:**
- Real-time inventory visibility
- Automated commission tracking
- Fast invoice generation
- Complete customer history

**Module Access (Configurable via RBAC):**
- Sales orders (CRUD)
- Customers (CRUD)
- Inventory (view-only)
- Invoicing
- Commission tracking (view)
- Reports (sales-specific)

### 5. Warehouse Staff

**Role:** Manage physical inventory and logistics

**Daily Activities:**
- Stock in/out operations
- Generate surat jalan (delivery notes)
- Inventory counting
- Stock transfers between warehouses
- Receive deliveries from suppliers

**Current State:**
- Accurate for stock movements
- Manual surat jalan creation
- Paper-based inventory count
- Limited multi-warehouse visibility

**Frustrations:**
- Manual surat jalan generation
- Inventory count reconciliation tedious
- Can't see stock in other warehouses easily
- Stock transfer paperwork heavy

**Goals:**
- Fast surat jalan generation
- Digital inventory counting
- Real-time multi-warehouse visibility
- Streamlined stock transfers

**Module Access (Configurable via RBAC):**
- Inventory (CRUD for stock movements)
- Surat jalan generation
- Stock transfers
- Inventory counting
- Receiving (from suppliers)
- Warehouse management

### 6. General Staff

**Role:** Employees who need basic system access

**Daily Activities:**
- Clock in/out (attendance)
- Submit leave requests
- Submit overtime requests
- View payslip
- Read company announcements

**Current State:**
- Manual attendance (fingerprint machine)
- Leave request via paper/WhatsApp
- Payslip printed and distributed
- Announcements via WhatsApp group

**Frustrations:**
- Manual attendance (sometimes forget to clock in)
- Leave approval slow (manual process)
- Can't access payslip history easily
- Miss important announcements

**Goals:**
- Easy attendance (mobile + face recognition)
- Fast leave approval
- Digital payslip access
- Centralized announcements

**Module Access (Configurable via RBAC):**
- Attendance (self-service: clock in/out)
- Leave requests (submit + view status)
- Overtime requests (submit + view status)
- Payslip (view-only)
- Company announcements (view-only)
- Personal profile (limited edit)

---

## Universal Need: Attendance

**All users require attendance tracking, but with different permissions:**

**Admin/HRD:**
- View all attendance records
- Generate attendance reports
- Approve/reject attendance corrections
- Configure attendance rules

**Managers:**
- View team attendance
- Approve leave/overtime requests
- Generate team reports

**Staff:**
- Clock in/out (geo-location, face recognition, or ZKTeco)
- View own attendance history
- Submit attendance corrections
- Request leave/overtime

**Attendance Methods:**
- **Geo-location:** For field staff (sales, delivery)
- **Face recognition:** For office/warehouse staff
- **ZKTeco integration:** For companies with existing hardware
- **Mobile app:** For remote/mobile workers (future roadmap)

---

## Role-Based Access Control (RBAC)

### System Design

**Role Management:**
- Admin can define custom roles
- Per role: assign module access (enable/disable per module)
- Flexible: Create new roles as company needs evolve

**Permission Levels:**
- **Full CRUD:** Create, Read, Update, Delete
- **View + Limited Actions:** Read + specific operations (e.g., submit leave request)
- **View-only:** Read access only
- **No access:** Module hidden from user

**Example Role Configurations:**

**Finance Admin:**
- Accounting: Full CRUD
- Reports: Full access
- Dashboard: Full access
- HRM: No access
- Purchasing: No access

**HRD Manager:**
- HRM: Full CRUD
- Attendance: Full access (all employees)
- Payroll: Full CRUD
- Accounting: View-only (for payroll verification)
- Purchasing: No access

**Purchasing Manager:**
- Purchasing: Full CRUD
- Inventory: View + planning
- Suppliers: Full CRUD
- Accounting: View-only (for budget tracking)
- HRM: No access

**Sales Staff:**
- Sales Orders: CRUD
- Customers: CRUD
- Inventory: View-only
- Invoicing: Create + view
- Commission: View-only
- Accounting: No access

**Warehouse Staff:**
- Inventory: CRUD (stock movements)
- Surat Jalan: CRUD
- Stock Transfers: CRUD
- Purchasing: View-only (incoming deliveries)
- Sales: View-only (outgoing orders)
- Accounting: No access

**General Staff:**
- Attendance: Self-service (own records)
- Leave: Submit + view status
- Overtime: Submit + view status
- Payslip: View-only (own)
- Announcements: View-only
- All other modules: No access

**Owner/Director:**
- All modules: Full access
- System configuration: Full access
- User management: Full access
- Reports: All reports across modules

### Flexibility

**Multi-Role Users:**
- One user can have multiple roles
- Permissions are cumulative (union of all assigned roles)
- Example: Owner = Finance + HRM + Purchasing + Sales + Admin

**Custom Roles:**
- Company can create custom roles beyond defaults
- Mix and match module permissions
- Example: "Finance + HRM Hybrid" role for small companies

**Dynamic Permissions:**
- Permissions can be changed without system restart
- Role changes take effect immediately
- Audit trail for permission changes

---

## User Journey Highlights

### Finance Admin: Monthly Close

**Before AkuBook:**
1. Export data from Accurate
2. Pull attendance data from machine manually
3. Compile in Excel
4. Reconcile discrepancies
5. Generate reports manually
6. **Time:** 3-5 days

**With AkuBook:**
1. All data already in one system
2. Attendance auto-synced
3. One-click report generation
4. Automated reconciliation
5. **Time:** Few hours

### HRD: Payroll Processing

**Before AkuBook:**
1. Manual attendance data entry from machine
2. Calculate overtime in Excel
3. Manual leave deduction
4. Calculate payroll in Excel
5. Cross-check with finance
6. Print and distribute payslips
7. **Time:** 2-3 days, error-prone

**With AkuBook:**
1. Attendance already in system
2. Overtime auto-calculated
3. Leave auto-deducted
4. Payroll auto-calculated
5. Digital payslip distribution
6. **Time:** Few hours, accurate

### Sales: Create Order

**Before AkuBook:**
1. Customer calls for order
2. Call warehouse to check stock
3. Wait for warehouse response
4. Create SO in Accurate
5. Generate invoice manually
6. **Time:** 30-60 minutes per order

**With AkuBook:**
1. Customer calls for order
2. Check real-time inventory in system
3. Create SO (auto-checks stock)
4. Auto-generate invoice
5. **Time:** 5-10 minutes per order

### Warehouse: Stock Out

**Before AkuBook:**
1. Receive SO from sales
2. Pick items
3. Create surat jalan manually
4. Update stock in Accurate
5. File paperwork
6. **Time:** 20-30 minutes per order

**With AkuBook:**
1. Receive SO notification
2. Pick items (mobile-friendly picking list)
3. Auto-generate surat jalan
4. Stock auto-updated
5. Digital record
6. **Time:** 5-10 minutes per order

### Staff: Leave Request

**Before AkuBook:**
1. Fill paper form
2. Get manager signature
3. Submit to HRD
4. Wait for approval (days)
5. Manual tracking

**With AkuBook:**
1. Submit leave request in system
2. Manager gets notification
3. Approve/reject in system
4. Staff gets instant notification
5. Auto-tracked in attendance

---

## Success Metrics

### User Adoption
- Active users per role
- Login frequency
- Feature usage per role
- Mobile vs desktop usage

### Efficiency Gains
- Monthly close time reduction
- Payroll processing time reduction
- Order processing time reduction
- Approval cycle time reduction

### User Satisfaction
- NPS per user role
- Support tickets per role
- Feature requests per role
- Training completion rate

### Business Impact
- Reduced manual errors
- Faster decision-making (real-time data)
- Improved compliance (audit trail)
- Cost savings (tool consolidation)
