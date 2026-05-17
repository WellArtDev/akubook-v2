# 07: Warehouse Staff's Inventory Movement

**Project:** AkuBook  
**Created:** 2026-05-12  
**Method:** Whiteport Design Studio (WDS)

---

## Transaction (Q1)

**What this scenario covers:**
Create surat jalan for delivery → stock movement auto-recorded → journal entry auto-posted

---

## Business Goal (Q2)

**Goal:** 🚀 SECONDARY: Real-Time Inventory Accuracy  
**Objective:** Stock movements auto-recorded, journal entries auto-posted, inventory always accurate

---

## User & Situation (Q3)

**Persona:** Warehouse Staff (SECONDARY)  
**Situation:** Gudang Pusat Jakarta. Sales Order ready for delivery to customer. Need to generate surat jalan, update stock, and dispatch driver.

---

## Driving Forces (Q4)

**Hope:** Fast surat jalan generation (< 5 minutes), stock auto-updated, journal entry auto-posted, driver departs on time.

**Worry:** Manual surat jalan tedious, stock discrepancies, inventory reconciliation nightmare at month-end, delivery delays.

---

## Device & Starting Point (Q5 + Q6)

**Device:** Desktop (warehouse workstation)  
**Entry:** Daily workflow — multiple deliveries per day, triggered by Sales Order ready for dispatch.

---

## Best Outcome (Q7)

**User Success:**
Surat jalan generated in < 5 minutes, stock movement recorded automatically, inventory accurate. Driver departs on time with proper documentation.

**Business Success:**
Real-time inventory accuracy (no month-end surprises), journal entries auto-posted (COGS recorded), Finance Admin sees accurate inventory valuation. Delivery on time, customer satisfaction.

---

## Shortest Path (Q8)

1. **Dashboard (Inventory)** — Warehouse Staff sees "SO Ready for Delivery" notification, clicks to create Delivery Order (DO)
2. **Delivery Order (Create)** — Staff selects SO, system auto-fills items/quantities, creates DO and generates surat jalan PDF
3. **Stock Movement & COGS (Auto-Posted)** — System immediately:
   - Records stock movement (Gudang Pusat → Customer)
   - Auto-posts COGS journal entry (DR: COGS, CR: Inventory)
   - Updates inventory balance in real-time
4. **Journal Entry (Verify)** — Finance Admin (Sari) reviews auto-posted COGS entry, marks as reviewed ✓

---

## Trigger Map Connections

**Persona:** Warehouse Staff (SECONDARY)

**Driving Forces Addressed:**
- ✅ **Want:** Fast surat jalan generation, stock auto-updated, digital inventory count
- ❌ **Fear:** Manual surat jalan tedious, stock discrepancies, reconciliation nightmare

**Business Goal:** 🚀 SECONDARY: Real-time inventory accuracy → auto-posting → Finance Admin confidence

---

## Scenario Steps

| Step | Folder | Purpose | Exit Action |
|------|--------|---------|-------------|
| 07.1 | `07.1-inventory-dashboard/` | See delivery notification | Click "Create Delivery Order" |
| 07.2 | `07.2-delivery-order-create/` | Create DO (auto-posts COGS immediately) | Click "Save & Print Surat Jalan" |
| 07.3 | `07.3-journal-entry-verify/` | Finance Admin verifies COGS auto-posted | Mark as "Reviewed" ✓ |

## Auto-Posting Triggers

**Delivery Order Created:**
- DR: Cost of Goods Sold (COGS)
- CR: Inventory
- **Trigger**: When DO status = "Saved" (immediate, not after delivery)
- **COGS Method**: FIFO or Average (configured in Company Settings)

**Note:** COGS auto-posts at DO creation, NOT after physical delivery. This ensures real-time inventory valuation.

---

_Scenario 07: Warehouse Staff's Inventory Movement_
