# Scenario 07: Inventory Movement

**User:** Warehouse Manager / Staff  
**Priority:** HIGH (Stock accuracy)  
**Frequency:** Daily  
**Success Metric:** Real-time stock updates, 100% accuracy

---

## Scenario Goal

Warehouse Manager tracks inventory movements (transfers, adjustments, stock opname) with real-time updates and auto-posted journal entries.

---

## User Context

**Who:** Warehouse Manager (Dedi) managing stock, Warehouse Staff executing movements

**When:** Daily warehouse operations

**Why:** Maintain accurate stock levels, prevent stockouts, track inventory value

**Current Pain (from Accurate):** Manual stock cards, delayed updates, reconciliation errors, no real-time visibility

---

## Sunshine Path (Happy Flow)

### Step 1: View Inventory Dashboard

**Page:** Inventory Dashboard

**User Action:**
- Opens Inventory module
- Views stock summary

**System Shows:**
- Stock overview (3 warehouses):
  - Total SKUs: 250
  - Total value: Rp 2.5B
  - Low stock alerts: 12 items
  - Out of stock: 3 items
- Recent movements (last 24 hours)
- Stock by warehouse

**User Input:**
- Reviews dashboard
- Clicks "View Stock Details"

**System Response:**
- Shows detailed stock list

**Next:** Review stock levels

---

### Step 2: Inter-Warehouse Transfer

**Page:** Stock Transfer Form

**User Action:**
- Clicks "New Transfer"
- Selects source and destination warehouses

**System Shows:**
- Transfer form:
  - From: Warehouse A (Jakarta)
  - To: Warehouse B (Surabaya)
  - Transfer Date: 2026-05-13
  - Item list (empty)

**User Input:**
- Adds items:
  - Speaker JBL EON615: 5 units
  - Mixer Yamaha MG16XU: 3 units
- Clicks "Create Transfer"

**System Response:**
- Creates transfer: TRF-2026-05-001
- Status: "Pending Shipment"
- Generates transfer document (PDF)

**Next:** Ship goods

---

### Step 3: Ship Goods

**Page:** Transfer Shipment

**User Action (Warehouse A Staff):**
- Picks goods for transfer
- Clicks "Ship Goods"
- Confirms items shipped

**System Shows:**
- Transfer details:
  - TRF-2026-05-001
  - Items: 2 items, 8 units
  - Destination: Warehouse B

**User Input:**
- Confirms shipment
- Enters tracking number (optional)
- Clicks "Confirm Shipment"

**System Response:**
- Updates transfer status: "In Transit"
- Reduces stock in Warehouse A:
  - Speaker JBL EON615: -5 units
  - Mixer Yamaha MG16XU: -3 units
- Creates "Goods in Transit" account entry
- Notifies Warehouse B

**Next:** Receive goods at destination

---

### Step 4: Receive Goods

**Page:** Transfer Receipt

**User Action (Warehouse B Staff):**
- Receives notification: "Transfer TRF-2026-05-001 in transit"
- Goods arrive
- Clicks "Receive Goods"

**System Shows:**
- Expected items:
  - Speaker JBL EON615: 5 units
  - Mixer Yamaha MG16XU: 3 units

**User Input:**
- Confirms received quantities:
  - Speaker JBL EON615: 5 units ✓
  - Mixer Yamaha MG16XU: 3 units ✓
- Clicks "Confirm Receipt"

**System Response:**
- Updates transfer status: "Completed"
- Increases stock in Warehouse B:
  - Speaker JBL EON615: +5 units
  - Mixer Yamaha MG16XU: +3 units
- Clears "Goods in Transit" account
- Auto-posts journal entry (if needed)

**Next:** Done (transfer complete)

---

### Step 5: Stock Adjustment

**Page:** Stock Adjustment Form

**User Action:**
- Clicks "New Adjustment"
- Selects warehouse and reason

**System Shows:**
- Adjustment form:
  - Warehouse: Warehouse A
  - Adjustment Date: 2026-05-13
  - Reason: Damaged goods
  - Item list (empty)

**User Input:**
- Adds items:
  - Speaker JBL EON615: -2 units (damaged)
- Enters notes: "Water damage during storage"
- Clicks "Post Adjustment"

**System Response:**
- Creates adjustment: ADJ-2026-05-001
- Updates stock:
  - Speaker JBL EON615: -2 units
- Auto-posts journal entry:
  - DR: Inventory Loss Expense
  - CR: Inventory
- Shows "Adjustment Posted" status

**Next:** Finance Admin reviews entry

---

### Step 6: Finance Admin Reviews Journal Entry

**Page:** Journal Entry (Auto-Posted)

**User Action (Finance Admin - Sari):**
- Receives notification: "Stock adjustment journal entry posted"
- Opens journal entry detail

**System Shows:**
- Auto-posted adjustment journal entry:
  - DR: Inventory Loss Expense: Rp 10,000,000
  - CR: Inventory - Speaker JBL EON615: Rp 10,000,000
- Audit trail: ADJ-2026-05-001 by Warehouse Manager

**User Input:**
- Reviews entry details
- Verifies reason and amount
- Marks as "Reviewed"

**System Response:**
- Updates entry status
- Sends confirmation to Warehouse Manager

**Next:** Done (adjustment complete)

---

## Pages/Screens Needed

1. **Inventory Dashboard** - Stock overview and alerts
2. **Stock Transfer Form** - Create inter-warehouse transfer
3. **Transfer Shipment** - Ship goods from source
4. **Transfer Receipt** - Receive goods at destination
5. **Stock Adjustment Form** - Adjust stock levels
6. **Journal Entry (Auto-Posted)** - Finance Admin review

---

## Data Models Required

### Tables

**inventory_movements**
- id, company_id, warehouse_id, product_id, movement_type
- quantity, unit_cost, total_value, reference_type, reference_id
- created_by, created_at, updated_at

**stock_transfers**
- id, company_id, from_warehouse_id, to_warehouse_id
- transfer_number, transfer_date, status (pending/in_transit/completed)
- tracking_number, shipped_by, shipped_at, received_by, received_at
- created_at, updated_at

**stock_transfer_items**
- id, transfer_id, product_id, quantity
- created_at, updated_at

**stock_adjustments**
- id, company_id, warehouse_id, adjustment_number, adjustment_date
- reason, notes, status (draft/posted), journal_entry_id
- created_by, created_at, updated_at

**stock_adjustment_items**
- id, adjustment_id, product_id, quantity_before, quantity_after
- quantity_change, unit_cost, total_value
- created_at, updated_at

**inventory_balances**
- id, company_id, warehouse_id, product_id
- quantity_on_hand, quantity_reserved, quantity_available
- average_cost, total_value, last_movement_at
- created_at, updated_at

---

## Auto-Posting Rules

**Stock Adjustment (Loss):**
- DR: Inventory Loss Expense
- CR: Inventory
- **Trigger:** When adjustment posted (negative quantity)

**Stock Adjustment (Gain):**
- DR: Inventory
- CR: Inventory Gain (Other Income)
- **Trigger:** When adjustment posted (positive quantity)

**Example Entry (Loss):**
```
DR: Inventory Loss Expense               Rp 10,000,000
    CR: Inventory - Speaker JBL EON615   Rp 10,000,000
```

---

## Acceptance Criteria

**Functional:**
- ✅ Real-time stock updates
- ✅ Inter-warehouse transfers
- ✅ Stock adjustments with reasons
- ✅ Journal entries auto-posted
- ✅ Low stock alerts
- ✅ Multi-warehouse support

**Performance:**
- ✅ Stock updates in real-time (<1 second)
- ✅ Dashboard loads in <2 seconds
- ✅ Transfer creation completes in <1 minute

**Security:**
- ✅ Only authorized users can adjust stock
- ✅ Audit trail for all movements
- ✅ Approval workflow for large adjustments

**UX:**
- ✅ Clear stock status indicators
- ✅ Low stock alerts
- ✅ One-click transfers
- ✅ Mobile-friendly for warehouse staff

---

## Design Notes

**Tone:**
- Efficient, accurate (critical operations)
- Clear status indicators (in stock/low/out)
- Real-time updates (no delays)

**UX Principles:**
- Real-time visibility (no refresh needed)
- One-click actions (fast processing)
- Mobile-first (warehouse staff on the go)
- Auto-posting (eliminate manual entry)

**Mobile Consideration:**
- Warehouse operations mobile-friendly
- Dashboard desktop-optimized

---

## Related Scenarios

- **02: Sales Order Flow** - Inventory reduced on delivery
- **06: Purchase Order Flow** - Inventory increased on GRN
- **15: Stock Opname** - Physical count reconciliation

---

## Accurate Feature Parity

**Accurate Inventory includes:**
- Stock tracking
- Inter-warehouse transfers
- Stock adjustments

**AkuBook Enhancement:**
- Real-time updates (Accurate delayed)
- Auto-posted journal entries (Accurate manual)
- Mobile-friendly (Accurate desktop-only)
- Low stock alerts (Accurate limited)

---

**Scenario Status:** ✅ Ready for Implementation  
**Next:** Design wireframes for 6 pages in this flow
