# Accurate Online - Inventory (Persediaan) Module

## Overview

Inventory (Persediaan) module in Accurate Online provides comprehensive multi-warehouse stock management capabilities critical for distributors and businesses with multiple locations. The module handles 26+ features covering warehouse operations, stock movements, valuation methods, and inventory control.

**Module Priority**: HIGH - Core module for AkuBook MVP
**Total Features**: 26 documented features
**Key Capability**: Multi-warehouse inventory tracking with inter-branch transfers

---

## Feature Categories

### 1. Warehouse Management (Gudang)
Multi-location inventory control with automatic stock allocation

**Features (4 items)**:
- Multi-warehouse setup and configuration
- Damaged goods warehouse (Gudang Barang Rusak)
- Inter-warehouse stock tracking
- Automatic warehouse selection rules

**Key Capabilities**:
- Manage multiple warehouses automatically
- Track stock per location
- Separate damaged/defective inventory
- Auto-select warehouse based on transaction history

**Documentation**: https://help.accurate.id/product/gudang

---

### 2. Stock Movements & Transfers (Pemindahan Barang)
Inter-warehouse transfers and stock allocation

**Features (3 items)**:
- Inter-warehouse stock transfers
- Bulk import of stock transfers (Excel/CSV)
- Damaged goods transfer to quarantine warehouse

**Key Capabilities**:
- Record stock movements between warehouses
- Handle damaged goods during transit
- Import transfer transactions in bulk
- Track transfer history

**Documentation**: https://help.accurate.id/product/pemindahan-barang

---

### 3. Stock Adjustments (Penyesuaian Persediaan)
Inventory value and quantity corrections

**Features (2 items)**:
- Adjust inventory value without changing quantity
- Stock quantity adjustments

**Key Capabilities**:
- Correct inventory valuation errors
- Adjust stock for physical count discrepancies
- Handle samples, damages, losses
- Maintain audit trail

**Use Cases**:
- Price corrections
- Physical count adjustments
- Sample distribution
- Shrinkage recording

**Documentation**: https://help.accurate.id/product/penyesuaian-persediaan

---

### 4. Stock Opname (Physical Count)
Physical inventory counting and reconciliation

**Features (4 items)**:
- Stock opname recording and processing
- Delete stock opname transactions
- Import stock count results (Excel)
- Reconcile count discrepancies

**Key Capabilities**:
- Record physical inventory counts
- Auto-generate adjustment entries
- Import count results from Excel
- Identify and resolve variances

**Workflow**:
1. Create stock opname command (Perintah Stok Opname)
2. Perform physical count
3. Input results (manual or import)
4. System generates adjustment entries
5. Review and approve adjustments

**Documentation**: https://help.accurate.id/product/stok-opname

---

### 5. Items & Services Master Data (Barang dan Jasa)
Product catalog and item configuration

**Features (10 items)**:
- Item master data management
- Multi-unit of measure (UOM) support
- Default selling price per UOM
- Item search with multiple keywords
- Import items with opening balance per warehouse
- Serial/lot number tracking
- Item groups (Barang Grup)
- Item variants
- Substitute items
- Barcode support

**Key Capabilities**:
- Centralized product catalog
- Multi-UOM with conversion
- Serial/lot tracking for traceability
- Item variants (size, color, etc.)
- Substitute item suggestions
- Import items in bulk

**Documentation**: https://help.accurate.id/product/barang-dan-jasa

---

### 6. Reorder Point Management (Barang Stok Minimum)
Automated replenishment triggers

**Features (3 items)**:
- Set minimum stock levels per item/warehouse
- Auto-generate purchase orders from reorder points
- Auto-generate item requests from reorder points

**Key Capabilities**:
- Define reorder points per warehouse
- Automatic PO creation when stock hits minimum
- Automatic item request generation
- Prevent stockouts

**Workflow**:
1. Set minimum stock level per item/warehouse
2. System monitors stock levels
3. When stock ≤ minimum, system flags item
4. Generate PO or item request automatically
5. Track replenishment status

**Documentation**: https://help.accurate.id/product/Barang-stok-minimum

---

### 7. Item Requests (Permintaan Barang)
Inter-warehouse stock requisitions

**Features (5 items)**:
- Create item requests between warehouses
- Request with estimated pricing
- Process item requests
- Bulk delete item requests
- Track request fulfillment

**Key Capabilities**:
- Request stock from other warehouses
- Include estimated costs
- Track request status
- Convert to transfers or POs

**Documentation**: https://help.accurate.id/product/permintaan-barang

---

### 8. COGS Calculation Methods
Inventory valuation and cost flow assumptions

**Supported Methods**:
1. **FIFO (First In First Out)**
   - Oldest inventory sold first
   - Better matches physical flow
   - Higher profits in rising prices

2. **Average Cost (Weighted Average)**
   - Recalculates average after each purchase
   - Smooths price fluctuations
   - Simpler to understand

**Key Concepts**:
- COGS = Cost of Goods Sold (Harga Pokok Penjualan)
- Method affects profit and inventory valuation
- Choose method at item level
- Cannot change after transactions exist

**Example Calculation** (from documentation):

**Scenario**: Item A transactions in March 2026
- 01 Mar: Opening 100 pcs @ Rp 50,000
- 10 Mar: Purchase 200 pcs @ Rp 55,000
- 15 Mar: Sale 150 pcs
- 25 Mar: Purchase 100 pcs @ Rp 58,000
- 28 Mar: Sale 180 pcs

**FIFO Calculation**:
- Sale 15 Mar (150 pcs):
  - 100 pcs @ Rp 50,000 = Rp 5,000,000
  - 50 pcs @ Rp 55,000 = Rp 2,750,000
  - **COGS = Rp 7,750,000**

- Sale 28 Mar (180 pcs):
  - 150 pcs @ Rp 55,000 = Rp 8,250,000
  - 30 pcs @ Rp 58,000 = Rp 1,740,000
  - **COGS = Rp 9,990,000**

**Average Cost Calculation**:
- After purchases (01 & 10 Mar):
  - Total cost: (100 × 50,000) + (200 × 55,000) = Rp 16,000,000
  - Total units: 300
  - **Average = Rp 53,333/unit**

- Sale 15 Mar (150 pcs):
  - **COGS = 150 × Rp 53,333 = Rp 8,000,000**

**Documentation**: https://help.accurate.id/product/accurate-online/fitur-aol/persediaan/menghitung-fifo-average

---

### 9. Serial/Lot Number Tracking (Pengisian Nomor Seri)
Item-level traceability

**Features (1 item)**:
- Serial/production number entry
- Track individual units
- Trace item history

**Key Capabilities**:
- Assign serial numbers to items
- Track warranty periods
- Trace defective units
- Regulatory compliance

**Use Cases**:
- Electronics (IMEI, serial numbers)
- Pharmaceuticals (batch/lot numbers)
- Automotive parts
- Warranty tracking

**Documentation**: https://help.accurate.id/product/pengisian-nomor-seri

---

### 10. Supporting Features

**Item Categories (Kategori Barang)** - 2 items
- Organize items by category
- Filter and reporting

**Item Brands (Merek Barang)** - 2 items
- Brand master data
- Brand-based filtering

**Units of Measure (Satuan Barang)** - 1 item
- UOM master data
- Conversion factors

**Order Fulfillment (Pemenuhan Pesanan)** - 2 items
- Track order fulfillment status
- Partial fulfillment support

**Order Completion (Penyelesaian Pesanan)** - 4 items
- Complete sales orders
- Close fulfilled orders

**Stock Opname Commands (Perintah Stok Opname)** - 4 items
- Create count instructions
- Assign count tasks
- Track count progress

**Items per Warehouse (Barang Per Gudang)** - 1 item
- View stock levels by warehouse
- Multi-location stock report

**Job Orders (Pekerjaan Pesanan)** - 6 items
- Custom production orders
- Service job tracking

---

## Multi-Warehouse Scenarios

### Scenario 1: Inter-Branch Transfer
**Business Case**: Transfer stock from main warehouse to branch

**Steps**:
1. Create Item Request (Permintaan Barang) from branch
2. Main warehouse approves request
3. Create Stock Transfer (Pemindahan Barang)
4. Record goods in transit
5. Receive at destination warehouse
6. System updates stock levels at both locations

**Features Used**:
- Permintaan Barang
- Pemindahan Barang
- Gudang (multi-warehouse)

---

### Scenario 2: Damaged Goods Handling
**Business Case**: Separate damaged inventory from sellable stock

**Steps**:
1. Create "Damaged Goods Warehouse" (Gudang Barang Rusak)
2. Identify damaged items during receiving or count
3. Transfer damaged items to quarantine warehouse
4. Record adjustment to reduce sellable inventory
5. Dispose or repair damaged goods
6. Update inventory accordingly

**Features Used**:
- Gudang (damaged goods warehouse)
- Pemindahan Barang (transfer to damaged warehouse)
- Penyesuaian Persediaan (write-off adjustments)

---

### Scenario 3: Reorder Point Automation
**Business Case**: Auto-replenish fast-moving items

**Steps**:
1. Set minimum stock level per item/warehouse
2. System monitors stock levels daily
3. When stock ≤ minimum, system flags item
4. Generate Purchase Order automatically
5. Receive goods and update stock
6. Repeat cycle

**Features Used**:
- Barang Stok Minimum (reorder points)
- Auto-generate PO from minimum stock

---

### Scenario 4: Physical Count Reconciliation
**Business Case**: Quarterly physical inventory count

**Steps**:
1. Create Stock Opname Command (Perintah Stok Opname)
2. Print count sheets or use mobile app
3. Perform physical count
4. Import count results from Excel
5. System compares physical vs. system stock
6. Review variances
7. Approve adjustments
8. System posts adjustment entries

**Features Used**:
- Perintah Stok Opname (count instructions)
- Stok Opname (count results)
- Import hasil stok opname (Excel import)
- Penyesuaian Persediaan (auto-generated adjustments)

---

## Technical Notes

### Warehouse Selection Rules
Accurate Online auto-selects warehouse based on:
1. **New items**: Default warehouse from settings
2. **Existing items**: Last transaction warehouse
3. **Manual override**: User can select different warehouse

**Documentation**: https://help.accurate.id/product/accurate-online/fitur-aol/persediaan/gudang/aturan-pemilihan-gudang

---

### Negative Stock Handling
Accurate Online allows negative stock with Average costing:
- System calculates average cost even with negative quantity
- Useful for backorders and pre-sales
- Requires careful monitoring

**Documentation**: https://help.accurate.id/product/accurate-online/fitur-aol/persediaan/menghitung-persediaan-stok-minus

---

### Import Capabilities
Bulk import supported for:
- Items with opening balance per warehouse (Excel/CSV)
- Items with serial numbers (Excel/CSV)
- Item groups (Excel/CSV)
- Stock transfers (Excel/CSV)
- Stock count results (Excel/CSV)

**Format**: Excel (.xlsx) or CSV
**Validation**: System validates data before import
**Error Handling**: Shows validation errors for correction

---

### Integration Points

**Sales Module**:
- Sales orders reduce inventory
- Delivery orders update stock
- Returns increase inventory

**Purchasing Module**:
- Purchase orders create pending receipts
- Goods receipts increase inventory
- Purchase returns reduce inventory

**Manufacturing Module**:
- Production orders consume raw materials
- Finished goods increase inventory
- Work-in-progress tracking

**Accounting Module**:
- Inventory transactions post to GL
- COGS calculation affects profit
- Inventory valuation on balance sheet

---

## Priority for AkuBook MVP

### Must-Have Features (Phase 1)
1. **Multi-warehouse setup** - Core requirement for distributors
2. **Stock transfers** - Inter-branch movements
3. **Stock adjustments** - Physical count corrections
4. **COGS calculation** - FIFO and Average methods
5. **Reorder points** - Automated replenishment
6. **Item master data** - Product catalog

### Should-Have Features (Phase 2)
7. **Stock opname** - Physical count workflow
8. **Item requests** - Inter-warehouse requisitions
9. **Serial/lot tracking** - Traceability
10. **Damaged goods handling** - Quality control

### Nice-to-Have Features (Phase 3)
11. **Item variants** - Size/color variations
12. **Substitute items** - Alternative products
13. **Barcode scanning** - Mobile operations
14. **Bulk import** - Excel/CSV data loading

---

## Key Differentiators for Distributors

1. **Multi-warehouse native** - Not an add-on, built-in from start
2. **Inter-branch transfers** - Seamless stock movements
3. **Warehouse-level reorder points** - Location-specific replenishment
4. **Allocation rules** - Auto-select warehouse based on rules
5. **Damaged goods workflow** - Separate quarantine inventory
6. **COGS per warehouse** - Accurate costing by location

---

## Common Use Cases

### Distributor with 5 Branches
- Main warehouse + 4 branch warehouses
- Daily inter-branch transfers
- Centralized purchasing, distributed sales
- Reorder points per branch
- Monthly physical counts

### Retail Chain with Central DC
- Central distribution center
- 10+ retail locations
- Push replenishment from DC
- Store-level stock opname
- Damaged goods return to DC

### Manufacturer with Raw Materials & Finished Goods
- Raw materials warehouse
- Work-in-progress area
- Finished goods warehouse
- Production consumes raw materials
- Finished goods to sales warehouse

---

## Related Modules

- **Sales (Penjualan)**: Reduces inventory on delivery
- **Purchasing (Pembelian)**: Increases inventory on receipt
- **Manufacturing (Manufaktur)**: Transforms raw materials to finished goods
- **Accounting (Buku Besar)**: Posts inventory transactions to GL

---

## Documentation Sources

- Main Inventory Page: https://help.accurate.id/product/persediaan
- Warehouse Management: https://help.accurate.id/product/gudang
- Stock Transfers: https://help.accurate.id/product/pemindahan-barang
- Stock Adjustments: https://help.accurate.id/product/penyesuaian-persediaan
- Stock Opname: https://help.accurate.id/product/stok-opname
- Items & Services: https://help.accurate.id/product/barang-dan-jasa
- Reorder Points: https://help.accurate.id/product/Barang-stok-minimum
- COGS Calculation: https://help.accurate.id/product/accurate-online/fitur-aol/persediaan/menghitung-fifo-average

---

## Summary

Accurate Online Inventory module provides enterprise-grade multi-warehouse inventory management with 26+ features covering:
- Multi-location stock tracking
- Inter-warehouse transfers
- COGS calculation (FIFO/Average)
- Reorder point automation
- Physical count reconciliation
- Serial/lot traceability
- Damaged goods handling

**Critical for AkuBook**: Multi-warehouse capability is essential for distributor use case. Accurate Online handles this natively with robust transfer workflows, warehouse-level reorder points, and location-specific stock tracking.

**Next Steps**: 
1. Map Accurate features to AkuBook requirements
2. Identify gaps (if any)
3. Design AkuBook inventory schema
4. Plan integration with Sales/Purchasing modules
