# Scenario 01: Company Setup & Configuration

**User:** Owner / Admin  
**Priority:** HIGH (MVP blocker)  
**Frequency:** Once per company  
**Success Metric:** Company ready for operations in <30 minutes

---

## Scenario Goal

New user sets up their company profile, selects industry type, and gets auto-configured modules ready for use.

---

## User Context

**Who:** Company owner or designated admin setting up AkuBook for the first time

**When:** First login after installation/purchase

**Why:** Need to configure company before any transactions can be recorded

**Current Pain (from Accurate):** Complex setup wizard, unclear what to configure first, easy to miss critical settings

---

## Sunshine Path (Happy Flow)

### Step 1: Welcome & Industry Selection

**Page:** Welcome / Setup Wizard

**User Action:**
- Sees welcome screen
- Clicks "Mulai Setup"

**System Shows:**
- Company name input
- Industry type dropdown (Distributor, Retail, Manufacturing, Service, Other)
- Number of employees (range selector)
- Number of warehouses/locations

**User Input:**
- Company name: "PT Distributor Sound System"
- Industry: Distributor
- Employees: 100+
- Warehouses: 3

**System Response:**
- Validates inputs
- Shows preview of modules that will be enabled
- "Berdasarkan pilihan Anda, kami akan mengaktifkan: Accounting, Multi-Warehouse Inventory, Sales, Purchasing, Surat Jalan, Attendance, HRM"

**Next:** Click "Lanjutkan"

---

### Step 2: Company Profile Details

**Page:** Company Profile

**System Shows:**
- Company legal name
- NPWP (tax ID)
- Address
- Phone, Email
- Logo upload (optional)

**User Input:**
- Fills company details
- Uploads logo (optional)

**System Response:**
- Validates NPWP format
- Saves company profile

**Next:** Click "Lanjutkan"

---

### Step 3: Fiscal Year & Currency

**Page:** Fiscal Settings

**System Shows:**
- Fiscal year start month (default: January)
- Currency (default: IDR)
- Tax settings (PPN rate: 12%, DPP: 11/12)

**User Input:**
- Confirms defaults or adjusts
- Fiscal year: January - December
- Currency: IDR
- PPN: 12%

**System Response:**
- Saves fiscal settings
- Creates default fiscal periods

**Next:** Click "Lanjutkan"

---

### Step 4: Chart of Accounts

**Page:** Chart of Accounts Setup

**System Shows:**
- "Kami telah menyiapkan Chart of Accounts standar untuk Distributor"
- Preview of account structure (Assets, Liabilities, Equity, Revenue, Expenses)
- Option to customize or use default

**User Action:**
- Reviews default COA
- Clicks "Gunakan Default" (or "Customize" for advanced users)

**System Response:**
- Creates default chart of accounts
- Sets up account mappings for auto-posting

**Next:** Click "Lanjutkan"

---

### Step 5: Warehouse Setup

**Page:** Warehouse Configuration

**System Shows:**
- "Anda memilih 3 gudang. Mari kita setup:"
- Warehouse 1: Name, Location, Is Main Warehouse?
- Warehouse 2: Name, Location
- Warehouse 3: Name, Location

**User Input:**
- Warehouse 1: "Gudang Pusat Jakarta" (Main)
- Warehouse 2: "Gudang Surabaya"
- Warehouse 3: "Gudang Bandung"

**System Response:**
- Creates warehouse records
- Sets up stock accounts per warehouse

**Next:** Click "Lanjutkan"

---

### Step 6: First User & Admin Setup

**Page:** User Management

**System Shows:**
- Current user (owner) details
- Role assignment: Owner (all access)
- Option to add more users now or later

**User Action:**
- Confirms own role
- Clicks "Tambah Nanti" (will add users later)

**System Response:**
- Assigns Owner role with full permissions

**Next:** Click "Selesai"

---

### Step 7: Setup Complete

**Page:** Setup Complete / Dashboard

**System Shows:**
- "Setup selesai! AkuBook siap digunakan."
- Quick start checklist:
  - ✅ Company profile configured
  - ✅ Modules enabled (Accounting, Sales, Purchasing, Inventory, HRM, Attendance)
  - ✅ Chart of Accounts created
  - ✅ Warehouses configured
  - ⏭️ Next: Add users
  - ⏭️ Next: Add customers & suppliers
  - ⏭️ Next: Add products
- Button: "Ke Dashboard"

**User Action:**
- Clicks "Ke Dashboard"

**System Response:**
- Redirects to main dashboard
- Shows empty state widgets with hints

---

## Key Pages

1. **Welcome / Setup Wizard** - Industry selection
2. **Company Profile** - Legal details, NPWP, logo
3. **Fiscal Settings** - Fiscal year, currency, tax
4. **Chart of Accounts** - Default or custom COA
5. **Warehouse Configuration** - Multi-warehouse setup
6. **User Management** - First admin user
7. **Setup Complete** - Confirmation & next steps
8. **Dashboard** - Main landing page

---

## Auto-Configuration Logic

**Based on Industry Selection:**

**Distributor:**
- Enable: Accounting, Multi-Warehouse Inventory, Sales, Purchasing, Surat Jalan, Attendance, HRM
- Dashboard widgets: Stock levels per warehouse, Pending POs, Pending SOs, Cash flow, Attendance
- COA: Distributor-specific accounts (COGS, Inventory per warehouse)

**Retail:**
- Enable: Accounting, POS, Inventory, CRM, Loyalty, Attendance, HRM
- Dashboard widgets: Today's sales, Transactions, Top products, Cash flow
- COA: Retail-specific accounts (POS sales, customer deposits)

**Manufacturing:**
- Enable: Accounting, BOM, Production, Inventory, Purchasing, Sales, Attendance, HRM
- Dashboard widgets: Production orders, WIP, Material usage, Finished goods
- COA: Manufacturing-specific accounts (WIP, manufacturing overhead)

---

## Validation Rules

**Company Name:**
- Required, min 3 characters
- No special characters except spaces, hyphens, periods

**NPWP:**
- Format: XX.XXX.XXX.X-XXX.XXX
- 15 digits with separators
- Validate checksum (if applicable)

**Fiscal Year:**
- Start month: 1-12
- Cannot overlap with existing fiscal years (if multi-year support)

**Warehouse:**
- At least 1 warehouse required
- Exactly 1 main warehouse
- Unique warehouse names

---

## Error Handling

**Invalid NPWP:**
- Show: "Format NPWP tidak valid. Contoh: 01.234.567.8-901.234"
- Highlight field in red
- Allow skip (can update later)

**Duplicate Warehouse Name:**
- Show: "Nama gudang sudah digunakan. Gunakan nama lain."
- Highlight field

**Network Error During Setup:**
- Show: "Koneksi terputus. Perubahan disimpan otomatis. Lanjutkan?"
- Auto-save progress
- Allow resume from last step

---

## Success Criteria

**Setup Complete When:**
- ✅ Company profile saved
- ✅ Fiscal year configured
- ✅ Chart of accounts created
- ✅ At least 1 warehouse configured
- ✅ Owner user has full permissions
- ✅ Modules auto-enabled based on industry

**User Can Now:**
- Add users and assign roles
- Add customers, suppliers, products
- Create transactions (SO, PO, etc.)
- Record attendance
- Process payroll

---

## Design Notes

**Tone:**
- Supportive, guiding (first-time setup)
- Clear progress indicator (Step 1 of 6)
- Helpful hints and examples
- "Anda bisa mengubah ini nanti" for non-critical fields

**UX Principles:**
- Linear flow (no skipping steps)
- Auto-save progress (can resume if interrupted)
- Smart defaults (minimize input)
- Preview before commit (show what will be created)
- Celebrate completion (positive reinforcement)

**Mobile Consideration:**
- Setup wizard desktop-only (complex input)
- Mobile users redirected to desktop or simplified mobile setup (future)

---

## Related Scenarios

- **10: User Management & RBAC** - Add users after setup
- **11: Module Configuration** - Enable/disable modules later
- **02: Sales Order** - First transaction after setup
- **03: Purchase Order** - First transaction after setup

---

## Accurate Feature Parity

**Accurate Company Setup includes:**
- Company profile (16 features)
- Multi-branch management
- Fiscal year configuration
- Currency settings
- Tax configuration
- Chart of accounts setup

**AkuBook Enhancement:**
- Industry-aware auto-configuration (Accurate doesn't have this)
- Simpler wizard flow (Accurate more complex)
- Module enable/disable preview (Accurate all modules always on)

---

**Scenario Status:** ✅ Ready for UX Design  
**Next:** Design wireframes for 7 pages in this flow
