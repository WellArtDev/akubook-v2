# Scenario 15: Stock Opname (Physical Inventory Count)

**User:** Warehouse Manager / Finance Admin  
**Priority:** HIGH (Inventory accuracy)  
**Frequency:** Quarterly/Annually  
**Success Metric:** Count completed in <1 day (vs 3 days manual)

---

## Scenario Goal

Warehouse Manager conducts physical inventory count, reconciles with system records, and adjusts discrepancies with auto-posted journal entries.

---

## User Context

**Who:** Warehouse Manager (Dedi) conducting count, Finance Admin (Sari) verifying adjustments

**When:** Quarterly or annually (regulatory requirement)

**Why:** Verify physical stock matches system records, identify shrinkage, maintain inventory accuracy

**Current Pain (from Accurate):** Manual count sheets, slow reconciliation, error-prone, takes 3 days

---

## Sunshine Path (Happy Flow)

### Step 1: Create Stock Opname

**Page:** Stock Opname Dashboard

**User Action:**
- Opens Stock Opname module
- Clicks "New Stock Opname"

**System Shows:**
- Stock opname form:
  - Opname Date: 2026-05-13
  - Warehouse: Warehouse A (Jakarta)
  - Count Type: Full count / Cycle count
  - Status: Draft

**User Input:**
- Selects count type: "Full count"
- Selects warehouse: "Warehouse A"
- Clicks "Create Opname"

**System Response:**
- Creates stock opname: SO-2026-05-001
- Generates count sheets (all products in warehouse)
- Status: "In Progress"
- Shows "Opname Created" status

**Next:** Print count sheets

---

### Step 2: Print Count Sheets

**Page:** Count Sheets

**User Action:**
- Clicks "Print Count Sheets"

**System Shows:**
- Count sheet options:
  - Format: PDF / Excel
  - Group by: Category / Location
  - Include: Product code, name, unit, system quantity

**User Input:**
- Selects format: "PDF"
- Selects grouping: "By Category"
- Clicks "Generate"

**System Response:**
- Generates count sheets (PDF)
- Downloads file: Count_Sheets_SO-2026-05-001.pdf
- Shows "Count Sheets Generated" status

**Next:** Conduct physical count

---

### Step 3: Enter Count Results

**Page:** Count Entry

**User Action (Warehouse Staff):**
- Conducts physical count
- Enters count results in system

**System Shows:**
- Count entry form:
  - Product list (250 products)
  - Columns: Product, System Qty, Counted Qty, Variance

**User Input:**
- Enters counted quantities:
  - Speaker JBL EON615: System 50, Counted 48, Variance -2
  - Mixer Yamaha MG16XU: System 30, Counted 30, Variance 0
  - Cable XLR 10m: System 100, Counted 105, Variance +5
  - ... (248 more products)
- Clicks "Save Count"

**System Response:**
- Saves count results
- Calculates variances
- Highlights discrepancies (variance ≠ 0)
- Shows "Count Saved" status

**Next:** Review variances

---

### Step 4: Review Variances

**Page:** Variance Report

**User Action (Warehouse Manager):**
- Reviews variance report

**System Shows:**
- Variance summary:
  - Total products counted: 250
  - Products with variance: 15
  - Total variance value: Rp 25,000,000
- Variance detail:
  - Speaker JBL EON615: -2 units (Rp -10,000,000)
  - Cable XLR 10m: +5 units (Rp +500,000)
  - Microphone Shure SM58: -3 units (Rp -4,500,000)
  - ... (12 more products)

**User Input:**
- Reviews variances
- Investigates large discrepancies
- Adds notes for variances
- Clicks "Approve Adjustments"

**System Response:**
- Updates opname status: "Approved"
- Prepares adjustment entries

**Next:** Post adjustments

---

### Step 5: Post Stock Adjustments

**Page:** Adjustment Posting

**User Action:**
- Clicks "Post Adjustments"

**System Shows:**
- Adjustment preview:
  - Total adjustments: 15 products
  - Total value: Rp 25,000,000 (loss)
  - Journal entry preview:
    - DR: Inventory Loss Expense (Rp 25,000,000)
    - CR: Inventory (Rp 25,000,000)

**User Input:**
- Reviews adjustment preview
- Clicks "Confirm Posting"

**System Response:**
- Updates inventory quantities:
  - Speaker JBL EON615: 50 → 48 units
  - Cable XLR 10m: 100 → 105 units
  - Microphone Shure SM58: 20 → 17 units
- Auto-posts journal entry:
  - DR: Inventory Loss Expense (Rp 25,000,000)
  - CR: Inventory (Rp 25,000,000)
- Updates opname status: "Posted"
- Shows "Adjustments Posted" status

**Next:** Finance Admin reviews entry

---

### Step 6: Finance Admin Reviews Journal Entry

**Page:** Journal Entry (Auto-Posted)

**User Action (Finance Admin - Sari):**
- Receives notification: "Stock opname journal entry posted"
- Opens journal entry detail

**System Shows:**
- Auto-posted opname journal entry:
  - DR: Inventory Loss Expense: Rp 25,000,000
  - CR: Inventory - Speaker JBL EON615: Rp 10,000,000
  - CR: Inventory - Microphone Shure SM58: Rp 4,500,000
  - CR: Inventory - Other products: Rp 10,500,000
- Audit trail: SO-2026-05-001 by Warehouse Manager
- Variance report attached

**User Input:**
- Reviews entry details
- Reviews variance report
- Verifies large discrepancies investigated
- Marks as "Reviewed"

**System Response:**
- Updates entry status
- Sends confirmation to Warehouse Manager
- Archives opname report

**Next:** Done (opname complete)

---

## Pages/Screens Needed

1. **Stock Opname Dashboard** - Create opname
2. **Count Sheets** - Generate count sheets
3. **Count Entry** - Enter count results
4. **Variance Report** - Review variances
5. **Adjustment Posting** - Post adjustments
6. **Journal Entry (Auto-Posted)** - Finance Admin review

---

## Data Models Required

### Tables

**stock_opnames**
- id, company_id, warehouse_id, opname_number, opname_date
- count_type (full/cycle), status (draft/in_progress/approved/posted)
- total_products, products_with_variance, total_variance_value
- approved_by, approved_at, posted_by, posted_at
- journal_entry_id, created_at, updated_at

**stock_opname_items**
- id, opname_id, product_id, system_quantity, counted_quantity
- variance_quantity, unit_cost, variance_value, notes
- counted_by, counted_at, created_at, updated_at

**stock_opname_adjustments**
- id, opname_id, product_id, adjustment_quantity, adjustment_value
- journal_entry_id, created_at, updated_at

---

## Auto-Posting Rules

**Stock Opname Adjustment (Loss):**
- DR: Inventory Loss Expense
- CR: Inventory (per product)
- **Trigger:** When opname adjustments posted (negative variance)

**Stock Opname Adjustment (Gain):**
- DR: Inventory (per product)
- CR: Inventory Gain (Other Income)
- **Trigger:** When opname adjustments posted (positive variance)

**Example Entry:**
```
DR: Inventory Loss Expense               Rp 25,000,000
    CR: Inventory - Speaker JBL EON615   Rp 10,000,000
    CR: Inventory - Microphone SM58      Rp  4,500,000
    CR: Inventory - Other products       Rp 10,500,000
```

---

## Acceptance Criteria

**Functional:**
- ✅ Create stock opname (full/cycle count)
- ✅ Generate count sheets (PDF/Excel)
- ✅ Enter count results
- ✅ Calculate variances
- ✅ Review and approve adjustments
- ✅ Post adjustments to inventory
- ✅ Auto-post journal entries

**Performance:**
- ✅ Count sheet generation in <2 minutes
- ✅ Variance calculation in <1 minute
- ✅ Adjustment posting in <2 minutes

**Security:**
- ✅ Only authorized users can create opname
- ✅ Approval workflow for adjustments
- ✅ Audit trail for all activities

**UX:**
- ✅ Clear variance indicators
- ✅ Mobile-friendly count entry
- ✅ One-click adjustment posting

---

## Design Notes

**Tone:**
- Efficient, accurate (critical process)
- Clear variance indicators (red/green)
- Helpful guidance for large discrepancies

**UX Principles:**
- Mobile-first count entry (warehouse staff on the go)
- Auto-calculate variances (no manual math)
- Approval workflow (prevent errors)
- Auto-posting (eliminate manual entry)

**Mobile Consideration:**
- Count entry mobile-optimized (warehouse staff)
- Variance review desktop-optimized (manager)

---

## Related Scenarios

- **07: Inventory Movement** - Inventory adjustment
- **03: Monthly Close** - Opname part of close process

---

## Accurate Feature Parity

**Accurate Stock Opname includes:**
- Physical count entry
- Variance calculation
- Adjustment posting

**AkuBook Enhancement:**
- Mobile-friendly count entry (Accurate desktop-only)
- Auto-posted journal entries (Accurate manual)
- Variance report (Accurate limited)
- One-click adjustment posting (Accurate multi-step)

---

**Scenario Status:** ✅ Ready for Implementation  
**Next:** Design wireframes for 6 pages in this flow
