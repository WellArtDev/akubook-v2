# Story 8.3: Sales Order Creation

**Epic:** 8 - Customer & Sales Management  
**Story ID:** 8.3  
**Story Key:** 8-3-sales-order-creation  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** P0 (Foundation)

---

## User Story

**Sebagai** Sales Staff  
**Saya ingin** create dan manage sales order  
**Sehingga** saya dapat process customer orders dengan credit limit check dan inventory reservation

---

## Business Context

Sales Order adalah dokumen konfirmasi order dari customer:
- **Order Confirmation**: Customer commit to buy
- **Credit Check**: Validate customer credit limit
- **Inventory Reservation**: Reserve stock untuk order
- **Approval Workflow**: Order > threshold perlu approval
- **Fulfillment Trigger**: Trigger delivery & invoicing

Sales Order flow:
1. Create SO (manual atau convert dari quotation)
2. Credit limit check
3. Inventory availability check
4. Approval (jika perlu)
5. Approved SO trigger delivery process

---

## Acceptance Criteria

### AC1: Sales Order CRUD Operations

**Given** user adalah Sales Staff  
**When** user mengakses Sales Order  
**Then** user dapat:
- Create new sales order
- Edit draft sales order
- View sales order details
- Cancel sales order (dengan reason)
- Clone existing sales order

### AC2: Sales Order Header Information

**When** user create sales order  
**Then** form harus include:
- SO Number (auto-generated, format: SO-YYYY-NNNN)
- SO Date (default: today)
- Customer (searchable dropdown)
- Customer Contact (dropdown dari customer contacts)
- Customer PO Number (reference)
- Sales Person (default: current user)
- Payment Terms (dari customer default)
- Delivery Terms (FOB, CIF, etc.)
- Delivery Address (dropdown dari customer addresses)
- Requested Delivery Date
- Notes (textarea)

### AC3: Convert from Quotation

**Given** quotation status = Approved  
**When** user convert quotation ke SO  
**Then** system:
- Create new SO dengan SO number
- Copy all header data
- Copy all line items
- Link SO to quotation
- Update quotation status → Converted
- Set SO status → Draft
- Redirect to SO edit page

### AC4: Sales Order Line Items

**When** user add items  
**Then** user dapat:
- Search & select product
- Enter quantity
- Check stock availability (show available qty)
- Set unit price (default dari product price)
- Apply discount (% atau amount)
- View line total
- Add multiple items
- Reorder items
- Delete items

**Line item fields:**
- Product (searchable dropdown)
- Description (auto-fill, editable)
- Quantity (required, > 0)
- Unit (dari product)
- Available Stock (display only)
- Unit Price (required, > 0)
- Discount % (0-100)
- Discount Amount (calculated atau manual)
- Tax (PPN 11%)
- Line Total (calculated)

### AC5: Stock Availability Check

**When** user add/edit line item  
**Then** system:
- Show available stock quantity
- Show warning jika qty > available stock
- Allow override dengan approval (jika user punya permission)
- Calculate reserved stock (pending SO + pending DO)

**Stock calculation:**
`
Available Stock = On Hand - Reserved
Reserved = Sum(Pending SO qty) + Sum(Pending DO qty)
`

### AC6: Credit Limit Check

**When** user save sales order  
**Then** system:
- Calculate customer outstanding balance
- Calculate SO grand total
- Check: outstanding + SO total <= credit limit
- Show warning jika exceed credit limit
- Allow override dengan approval (jika user punya permission)

**Credit check:**
`
Outstanding = Sum(Unpaid Invoices)
New Total = Outstanding + SO Grand Total
Credit Available = Credit Limit - Outstanding
Status:
  - OK: New Total <= Credit Limit
  - Warning: New Total > Credit Limit
`

### AC7: Sales Order Calculations

**When** user add/edit items  
**Then** system calculate:
- Subtotal (sum of line totals before discount)
- Discount (header level, % atau amount)
- Subtotal After Discount
- Tax (PPN 11% dari subtotal after discount)
- Grand Total

**Calculation formula:**
`
Line Total = (Quantity × Unit Price) - Line Discount + Line Tax
Subtotal = Sum of Line Totals
Discount = Header Discount (% atau amount)
Subtotal After Discount = Subtotal - Discount
Tax = Subtotal After Discount × 11%
Grand Total = Subtotal After Discount + Tax
`

### AC8: Sales Order Status Workflow

**SO status:**
- **Draft**: Baru dibuat, masih bisa edit
- **Pending Approval**: Submitted untuk approval
- **Approved**: Approved, ready untuk delivery
- **In Progress**: Sebagian sudah delivered
- **Completed**: Semua sudah delivered & invoiced
- **Cancelled**: Dibatalkan

**Status transitions:**
- Draft → Pending Approval (action: Submit for Approval)
- Draft → Approved (auto, jika tidak perlu approval)
- Pending Approval → Approved (action: Approve)
- Pending Approval → Draft (action: Reject)
- Approved → In Progress (auto, saat create DO)
- In Progress → Completed (auto, saat semua delivered & invoiced)
- Any → Cancelled (action: Cancel, dengan reason)

### AC9: Approval Rules

**When** user submit SO  
**Then** system check approval rules:
- SO total > Rp 10,000,000 → perlu approval
- Credit limit exceeded → perlu approval
- Stock not available → perlu approval

**Approval workflow:**
- Submit → Pending Approval
- Approver dapat Approve atau Reject
- Reject → kembali ke Draft dengan notes
- Approve → status Approved

### AC10: Inventory Reservation

**When** SO status → Approved  
**Then** system:
- Create inventory reservation records
- Update reserved stock quantity
- Lock stock untuk SO ini
- Release reservation jika SO cancelled

**Reservation table:**
`sql
CREATE TABLE inventory_reservations (
    id BIGINT PRIMARY KEY,
    product_id BIGINT,
    quantity DECIMAL(15,3),
    reserved_for_type VARCHAR(50), -- 'sales_order', 'delivery_order'
    reserved_for_id BIGINT,
    reserved_at TIMESTAMP,
    released_at TIMESTAMP NULL
);
`

### AC11: Validation Rules

**When** user save SO  
**Then** system validate:
- Customer required
- SO date required
- At least 1 line item
- All line items have quantity > 0
- All line items have unit price > 0
- Grand total > 0
- Delivery address required

**When** user submit SO  
**Then** system validate:
- All draft validations
- Credit limit check (warning, not blocking)
- Stock availability check (warning, not blocking)

### AC12: Sales Order List & Filters

**When** user view SO list  
**Then** user dapat:
- See table dengan columns:
  - SO Number
  - Date
  - Customer
  - Status
  - Grand Total
  - Delivery Status
  - Actions
- Filter by:
  - Status (multi-select)
  - Date range
  - Customer
  - Sales Person
- Search by SO number atau customer name
- Sort by any column
- Pagination (50 per page)

### AC13: Cancel Sales Order

**When** user cancel SO  
**Then** system:
- Require cancellation reason
- Update status → Cancelled
- Release inventory reservation
- Cannot cancel jika sudah ada DO atau Invoice
- Log cancellation untuk audit

---

## Technical Specifications

### Database Schema

#### Table: sales_orders
`sql
CREATE TABLE sales_orders (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    so_number VARCHAR(50) UNIQUE NOT NULL,
    so_date DATE NOT NULL,
    customer_id BIGINT NOT NULL,
    customer_contact_id BIGINT,
    customer_po_number VARCHAR(100),
    sales_person_id BIGINT NOT NULL,
    payment_terms VARCHAR(50),
    delivery_terms VARCHAR(100),
    delivery_address_id BIGINT,
    requested_delivery_date DATE,
    notes TEXT,
    status ENUM('draft', 'pending_approval', 'approved', 'in_progress', 'completed', 'cancelled') DEFAULT 'draft',
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
    discount_type ENUM('percentage', 'amount') DEFAULT 'percentage',
    discount_value DECIMAL(15,2) DEFAULT 0,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    subtotal_after_discount DECIMAL(15,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    grand_total DECIMAL(15,2) NOT NULL DEFAULT 0,
    quotation_id BIGINT NULL,
    approval_required BOOLEAN DEFAULT FALSE,
    approved_by BIGINT NULL,
    approved_at TIMESTAMP NULL,
    rejected_by BIGINT NULL,
    rejected_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    cancelled_by BIGINT NULL,
    cancelled_at TIMESTAMP NULL,
    cancellation_reason TEXT NULL,
    created_by BIGINT NOT NULL,
    updated_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (customer_contact_id) REFERENCES customer_contacts(id),
    FOREIGN KEY (delivery_address_id) REFERENCES customer_addresses(id),
    FOREIGN KEY (sales_person_id) REFERENCES users(id),
    FOREIGN KEY (quotation_id) REFERENCES sales_quotations(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    FOREIGN KEY (rejected_by) REFERENCES users(id),
    FOREIGN KEY (cancelled_by) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    INDEX idx_so_number (so_number),
    INDEX idx_customer (customer_id),
    INDEX idx_status (status),
    INDEX idx_so_date (so_date)
);
`

#### Table: sales_order_lines
`sql
CREATE TABLE sales_order_lines (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sales_order_id BIGINT NOT NULL,
    line_number INT NOT NULL,
    product_id BIGINT NOT NULL,
    description TEXT,
    quantity DECIMAL(15,3) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    discount_percentage DECIMAL(5,2) DEFAULT 0,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    tax_percentage DECIMAL(5,2) DEFAULT 11.00,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    line_total DECIMAL(15,2) NOT NULL,
    delivered_quantity DECIMAL(15,3) DEFAULT 0,
    invoiced_quantity DECIMAL(15,3) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sales_order_id) REFERENCES sales_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_sales_order (sales_order_id),
    INDEX idx_product (product_id)
);
`

#### Table: inventory_reservations
`sql
CREATE TABLE inventory_reservations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT NOT NULL,
    quantity DECIMAL(15,3) NOT NULL,
    reserved_for_type VARCHAR(50) NOT NULL,
    reserved_for_id BIGINT NOT NULL,
    reserved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    released_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_product (product_id),
    INDEX idx_reserved_for (reserved_for_type, reserved_for_id),
    INDEX idx_released (released_at)
);
`

### Models

#### SalesOrder Model
`php
<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\SoftDeletes;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;

class SalesOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected \\ = [
        'so_number',
        'so_date',
        'customer_id',
        'customer_contact_id',
        'customer_po_number',
        'sales_person_id',
        'payment_terms',
        'delivery_terms',
        'delivery_address_id',
        'requested_delivery_date',
        'notes',
        'status',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'subtotal_after_discount',
        'tax_amount',
        'grand_total',
        'quotation_id',
        'approval_required',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'created_by',
        'updated_by',
    ];

    protected \\ = [
        'so_date' => 'date',
        'requested_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal_after_discount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'approval_required' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Relationships
    public function customer()
    {
        return \\->belongsTo(Customer::class);
    }

    public function customerContact()
    {
        return \\->belongsTo(CustomerContact::class);
    }

    public function deliveryAddress()
    {
        return \\->belongsTo(CustomerAddress::class, 'delivery_address_id');
    }

    public function salesPerson()
    {
        return \\->belongsTo(User::class, 'sales_person_id');
    }

    public function lines()
    {
        return \\->hasMany(SalesOrderLine::class)->orderBy('line_number');
    }

    public function quotation()
    {
        return \\->belongsTo(SalesQuotation::class);
    }

    public function deliveryOrders()
    {
        return \\->hasMany(DeliveryOrder::class);
    }

    public function invoices()
    {
        return \\->hasMany(SalesInvoice::class);
    }

    public function approver()
    {
        return \\->belongsTo(User::class, 'approved_by');
    }

    public function rejecter()
    {
        return \\->belongsTo(User::class, 'rejected_by');
    }

    public function canceller()
    {
        return \\->belongsTo(User::class, 'cancelled_by');
    }

    public function creator()
    {
        return \\->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return \\->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeDraft(\\)
    {
        return \\->where('status', 'draft');
    }

    public function scopePendingApproval(\\)
    {
        return \\->where('status', 'pending_approval');
    }

    public function scopeApproved(\\)
    {
        return \\->where('status', 'approved');
    }

    public function scopeActive(\\)
    {
        return \\->whereIn('status', ['approved', 'in_progress']);
    }

    // Accessors
    public function getCanEditAttribute()
    {
        return in_array(\\->status, ['draft']);
    }

    public function getCanSubmitAttribute()
    {
        return \\->status === 'draft' && \\->lines()->count() > 0;
    }

    public function getCanApproveAttribute()
    {
        return \\->status === 'pending_approval';
    }

    public function getCanCancelAttribute()
    {
        return in_array(\\->status, ['draft', 'pending_approval', 'approved']) 
            && \\->deliveryOrders()->count() === 0 
            && \\->invoices()->count() === 0;
    }

    public function getDeliveryStatusAttribute()
    {
        \\ = \\->lines->sum('quantity');
        \\ = \\->lines->sum('delivered_quantity');
        
        if (\\ == 0) return 'Not Delivered';
        if (\\ < \\) return 'Partially Delivered';
        return 'Fully Delivered';
    }

    public function getInvoiceStatusAttribute()
    {
        \\ = \\->lines->sum('quantity');
        \\ = \\->lines->sum('invoiced_quantity');
        
        if (\\ == 0) return 'Not Invoiced';
        if (\\ < \\) return 'Partially Invoiced';
        return 'Fully Invoiced';
    }

    // Methods
    public static function generateSONumber()
    {
        \\ = date('Y');
        \\ = \"SO-{\\}-\";
        
        \\ = self::where('so_number', 'LIKE', \"\\%\")
            ->orderBy('so_number', 'desc')
            ->first();
        
        if (!\\) {
            return \\ . '0001';
        }
        
        \\ = (int) substr(\\->so_number, -4);
        return \\ . str_pad(\\ + 1, 4, '0', STR_PAD_LEFT);
    }

    public function calculateTotals()
    {
        \\->subtotal = \\->lines->sum('line_total');
        
        if (\\->discount_type === 'percentage') {
            \\->discount_amount = \\->subtotal * (\\->discount_value / 100);
        } else {
            \\->discount_amount = \\->discount_value;
        }
        
        \\->subtotal_after_discount = \\->subtotal - \\->discount_amount;
        \\->tax_amount = \\->subtotal_after_discount * 0.11;
        \\->grand_total = \\->subtotal_after_discount + \\->tax_amount;
        
        \\->save();
    }

    public function checkApprovalRequired()
    {
        // Rule 1: SO total > 10 juta
        if (\\->grand_total > 10000000) {
            return true;
        }

        // Rule 2: Credit limit exceeded
        \\ = \\->customer;
        \\ = \\->outstanding_balance ?? 0;
        if ((\\ + \\->grand_total) > \\->credit_limit) {
            return true;
        }

        // Rule 3: Stock not available
        foreach (\\->lines as \\) {
            \\ = \\->product->getAvailableStock();
            if (\\->quantity > \\) {
                return true;
            }
        }

        return false;
    }

    public function submit()
    {
        \\->approval_required = \\->checkApprovalRequired();
        
        if (\\->approval_required) {
            \\->status = 'pending_approval';
        } else {
            \\->status = 'approved';
            \\->approved_at = now();
            \\->createInventoryReservations();
        }
        
        \\->save();
    }

    public function approve(\\)
    {
        \\->update([
            'status' => 'approved',
            'approved_by' => \\,
            'approved_at' => now(),
        ]);

        \\->createInventoryReservations();
    }

    public function reject(\\, \\)
    {
        \\->update([
            'status' => 'draft',
            'rejected_by' => \\,
            'rejected_at' => now(),
            'rejection_reason' => \\,
        ]);
    }

    public function cancel(\\, \\)
    {
        \\->update([
            'status' => 'cancelled',
            'cancelled_by' => \\,
            'cancelled_at' => now(),
            'cancellation_reason' => \\,
        ]);

        \\->releaseInventoryReservations();
    }

    public function createInventoryReservations()
    {
        foreach (\\->lines as \\) {
            InventoryReservation::create([
                'product_id' => \\->product_id,
                'quantity' => \\->quantity,
                'reserved_for_type' => 'sales_order',
                'reserved_for_id' => \\->id,
            ]);
        }
    }

    public function releaseInventoryReservations()
    {
        InventoryReservation::where('reserved_for_type', 'sales_order')
            ->where('reserved_for_id', \\->id)
            ->whereNull('released_at')
            ->update(['released_at' => now()]);
    }

    public function updateStatus()
    {
        \\ = \\->lines->sum('quantity');
        \\ = \\->lines->sum('delivered_quantity');
        \\ = \\->lines->sum('invoiced_quantity');

        if (\\ > 0 && \\ < \\) {
            \\->status = 'in_progress';
        } elseif (\\ >= \\ && \\ >= \\) {
            \\->status = 'completed';
        }

        \\->save();
    }
}
`

#### SalesOrderLine Model
`php
<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;

class SalesOrderLine extends Model
{
    use HasFactory;

    protected \\ = [
        'sales_order_id',
        'line_number',
        'product_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'line_total',
        'delivered_quantity',
        'invoiced_quantity',
        'notes',
    ];

    protected \\ = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'delivered_quantity' => 'decimal:3',
        'invoiced_quantity' => 'decimal:3',
    ];

    // Relationships
    public function salesOrder()
    {
        return \\->belongsTo(SalesOrder::class);
    }

    public function product()
    {
        return \\->belongsTo(Product::class);
    }

    // Accessors
    public function getRemainingQuantityAttribute()
    {
        return \\->quantity - \\->delivered_quantity;
    }

    public function getUnInvoicedQuantityAttribute()
    {
        return \\->delivered_quantity - \\->invoiced_quantity;
    }

    // Methods
    public function calculateLineTotal()
    {
        \\ = \\->quantity * \\->unit_price;
        \\->discount_amount = \\ * (\\->discount_percentage / 100);
        \\ = \\ - \\->discount_amount;
        \\->tax_amount = \\ * (\\->tax_percentage / 100);
        \\->line_total = \\ + \\->tax_amount;
        \\->save();
    }
}
`

#### InventoryReservation Model
`php
<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;

class InventoryReservation extends Model
{
    use HasFactory;

    protected \\ = [
        'product_id',
        'quantity',
        'reserved_for_type',
        'reserved_for_id',
        'reserved_at',
        'released_at',
    ];

    protected \\ = [
        'quantity' => 'decimal:3',
        'reserved_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    // Relationships
    public function product()
    {
        return \\->belongsTo(Product::class);
    }

    public function reservedFor()
    {
        return \\->morphTo(__FUNCTION__, 'reserved_for_type', 'reserved_for_id');
    }

    // Scopes
    public function scopeActive(\\)
    {
        return \\->whereNull('released_at');
    }

    public function scopeForProduct(\\, \\)
    {
        return \\->where('product_id', \\);
    }
}
`

#### Update Product Model
`php
// Add to Product model

public function getAvailableStock()
{
    \\ = \\->stock_quantity ?? 0;
    \\ = InventoryReservation::active()
        ->forProduct(\\->id)
        ->sum('quantity');
    
    return \\ - \\;
}

public function reservations()
{
    return \\->hasMany(InventoryReservation::class);
}
`

### Controller

`php
<?php

namespace App\\Http\\Controllers;

use App\\Models\\SalesOrder;
use App\\Models\\SalesQuotation;
use App\\Models\\Customer;
use App\\Models\\Product;
use App\\Http\\Requests\\StoreSalesOrderRequest;
use App\\Http\\Requests\\UpdateSalesOrderRequest;
use Illuminate\\Http\\Request;
use Inertia\\Inertia;

class SalesOrderController extends Controller
{
    public function index(Request \\)
    {
        \\ = SalesOrder::with(['customer', 'salesPerson'])
            ->orderBy('so_date', 'desc');

        // Filters
        if (\\->filled('status')) {
            \\->whereIn('status', \\->status);
        }

        if (\\->filled('customer_id')) {
            \\->where('customer_id', \\->customer_id);
        }

        if (\\->filled('sales_person_id')) {
            \\->where('sales_person_id', \\->sales_person_id);
        }

        if (\\->filled('date_from')) {
            \\->where('so_date', '>=', \\->date_from);
        }

        if (\\->filled('date_to')) {
            \\->where('so_date', '<=', \\->date_to);
        }

        if (\\->filled('search')) {
            \\->where(function(\\) use (\\) {
                \\->where('so_number', 'LIKE', \"%{\\->search}%\")
                  ->orWhere('customer_po_number', 'LIKE', \"%{\\->search}%\")
                  ->orWhereHas('customer', function(\\) use (\\) {
                      \\->where('name', 'LIKE', \"%{\\->search}%\");
                  });
            });
        }

        \\ = \\->paginate(50);

        return Inertia::render('SalesOrders/Index', [
            'salesOrders' => \\,
            'filters' => \\->only(['status', 'customer_id', 'sales_person_id', 'date_from', 'date_to', 'search']),
        ]);
    }

    public function create(Request \\)
    {
        \\ = null;
        if (\\->filled('quotation_id')) {
            \\ = SalesQuotation::with('lines')->findOrFail(\\->quotation_id);
        }

        return Inertia::render('SalesOrders/Create', [
            'soNumber' => SalesOrder::generateSONumber(),
            'customers' => Customer::select('id', 'name', 'code', 'credit_limit')->get(),
            'products' => Product::select('id', 'name', 'code', 'unit', 'selling_price', 'stock_quantity')->get(),
            'quotation' => \\,
        ]);
    }

    public function store(StoreSalesOrderRequest \\)
    {
        \\ = SalesOrder::create([
            ...\\->validated(),
            'created_by' => auth()->id(),
        ]);

        // Create lines
        foreach (\\->lines as \\ => \\) {
            \\ = \\->lines()->create([
                ...\\,
                'line_number' => \\ + 1,
            ]);
            \\->calculateLineTotal();
        }

        \\->calculateTotals();

        // If from quotation, update quotation status
        if (\\->quotation_id) {
            \\ = SalesQuotation::find(\\->quotation_id);
            \\->update([
                'status' => 'converted',
                'converted_to_sales_order_id' => \\->id,
                'converted_at' => now(),
            ]);
        }

        return redirect()->route('sales-orders.show', \\)
            ->with('success', 'Sales order created successfully.');
    }

    public function show(SalesOrder \\)
    {
        \\->load([
            'customer.contacts',
            'customerContact',
            'deliveryAddress',
            'salesPerson',
            'lines.product',
            'quotation',
            'deliveryOrders',
            'invoices',
            'approver',
            'rejecter',
            'canceller',
        ]);

        return Inertia::render('SalesOrders/Show', [
            'salesOrder' => \\,
        ]);
    }

    public function edit(SalesOrder \\)
    {
        if (!\\->can_edit) {
            return redirect()->route('sales-orders.show', \\)
                ->with('error', 'Cannot edit sales order in current status.');
        }

        \\->load('lines');

        return Inertia::render('SalesOrders/Edit', [
            'salesOrder' => \\,
            'customers' => Customer::select('id', 'name', 'code', 'credit_limit')->get(),
            'products' => Product::select('id', 'name', 'code', 'unit', 'selling_price', 'stock_quantity')->get(),
        ]);
    }

    public function update(UpdateSalesOrderRequest \\, SalesOrder \\)
    {
        if (!\\->can_edit) {
            return redirect()->route('sales-orders.show', \\)
                ->with('error', 'Cannot edit sales order in current status.');
        }

        \\->update([
            ...\\->validated(),
            'updated_by' => auth()->id(),
        ]);

        // Delete old lines
        \\->lines()->delete();

        // Create new lines
        foreach (\\->lines as \\ => \\) {
            \\ = \\->lines()->create([
                ...\\,
                'line_number' => \\ + 1,
            ]);
            \\->calculateLineTotal();
        }

        \\->calculateTotals();

        return redirect()->route('sales-orders.show', \\)
            ->with('success', 'Sales order updated successfully.');
    }

    public function destroy(SalesOrder \\)
    {
        if (!\\->can_edit) {
            return redirect()->route('sales-orders.index')
                ->with('error', 'Cannot delete sales order in current status.');
        }

        \\->delete();

        return redirect()->route('sales-orders.index')
            ->with('success', 'Sales order deleted successfully.');
    }

    public function submit(SalesOrder \\)
    {
        if (!\\->can_submit) {
            return back()->with('error', 'Cannot submit sales order in current status.');
        }

        \\->submit();

        \\ = \\->approval_required 
            ? 'Sales order submitted for approval.'
            : 'Sales order approved automatically.';

        return back()->with('success', \\);
    }

    public function approve(SalesOrder \\)
    {
        if (!\\->can_approve) {
            return back()->with('error', 'Cannot approve sales order in current status.');
        }

        \\->approve(auth()->id());

        return back()->with('success', 'Sales order approved.');
    }

    public function reject(Request \\, SalesOrder \\)
    {
        if (!\\->can_approve) {
            return back()->with('error', 'Cannot reject sales order in current status.');
        }

        \\->validate([
            'reason' => 'required|string',
        ]);

        \\->reject(auth()->id(), \\->reason);

        return back()->with('success', 'Sales order rejected.');
    }

    public function cancel(Request \\, SalesOrder \\)
    {
        if (!\\->can_cancel) {
            return back()->with('error', 'Cannot cancel sales order in current status.');
        }

        \\->validate([
            'reason' => 'required|string',
        ]);

        \\->cancel(auth()->id(), \\->reason);

        return back()->with('success', 'Sales order cancelled.');
    }

    public function clone(SalesOrder \\)
    {
        \\ = \\->replicate();
        \\->so_number = SalesOrder::generateSONumber();
        \\->so_date = now();
        \\->status = 'draft';
        \\->quotation_id = null;
        \\->approval_required = false;
        \\->approved_by = null;
        \\->approved_at = null;
        \\->rejected_by = null;
        \\->rejected_at = null;
        \\->rejection_reason = null;
        \\->cancelled_by = null;
        \\->cancelled_at = null;
        \\->cancellation_reason = null;
        \\->created_by = auth()->id();
        \\->save();

        // Copy lines
        foreach (\\->lines as \\) {
            \\ = \\->replicate();
            \\->sales_order_id = \\->id;
            \\->delivered_quantity = 0;
            \\->invoiced_quantity = 0;
            \\->save();
        }

        return redirect()->route('sales-orders.edit', \\)
            ->with('success', 'Sales order cloned successfully.');
    }

    public function checkCredit(Request \\)
    {
        \\ = Customer::findOrFail(\\->customer_id);
        \\ = \\->grand_total;

        \\ = \\->outstanding_balance ?? 0;
        \\ = \\->credit_limit;
        \\ = \\ + \\;
        \\ = \\ - \\;

        return response()->json([
            'credit_limit' => \\,
            'outstanding' => \\,
            'available' => \\,
            'new_total' => \\,
            'exceeded' => \\ > \\,
        ]);
    }

    public function checkStock(Request \\)
    {
        \\ = Product::findOrFail(\\->product_id);
        \\ = \\->getAvailableStock();

        return response()->json([
            'on_hand' => \\->stock_quantity,
            'reserved' => \\->stock_quantity - \\,
            'available' => \\,
            'insufficient' => \\->quantity > \\,
        ]);
    }
}
`

### Form Requests

#### StoreSalesOrderRequest
`php
<?php

namespace App\\Http\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

class StoreSalesOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'so_number' => 'required|string|unique:sales_orders,so_number',
            'so_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'customer_contact_id' => 'nullable|exists:customer_contacts,id',
            'customer_po_number' => 'nullable|string|max:100',
            'sales_person_id' => 'required|exists:users,id',
            'payment_terms' => 'nullable|string|max:50',
            'delivery_terms' => 'nullable|string|max:100',
            'delivery_address_id' => 'required|exists:customer_addresses,id',
            'requested_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'discount_type' => 'required|in:percentage,amount',
            'discount_value' => 'required|numeric|min:0',
            'quotation_id' => 'nullable|exists:sales_quotations,id',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.description' => 'nullable|string',
            'lines.*.quantity' => 'required|numeric|min:0.001',
            'lines.*.unit' => 'required|string|max:20',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'lines.*.tax_percentage' => 'required|numeric|min:0|max:100',
            'lines.*.notes' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'delivery_address_id.required' => 'Delivery address is required.',
            'lines.required' => 'At least one line item is required.',
            'lines.min' => 'At least one line item is required.',
        ];
    }
}
`

#### UpdateSalesOrderRequest
`php
<?php

namespace App\\Http\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

class UpdateSalesOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'so_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'customer_contact_id' => 'nullable|exists:customer_contacts,id',
            'customer_po_number' => 'nullable|string|max:100',
            'sales_person_id' => 'required|exists:users,id',
            'payment_terms' => 'nullable|string|max:50',
            'delivery_terms' => 'nullable|string|max:100',
            'delivery_address_id' => 'required|exists:customer_addresses,id',
            'requested_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'discount_type' => 'required|in:percentage,amount',
            'discount_value' => 'required|numeric|min:0',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.description' => 'nullable|string',
            'lines.*.quantity' => 'required|numeric|min:0.001',
            'lines.*.unit' => 'required|string|max:20',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'lines.*.tax_percentage' => 'required|numeric|min:0|max:100',
            'lines.*.notes' => 'nullable|string',
        ];
    }
}
`

### Routes

`php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::resource('sales-orders', SalesOrderController::class);
    Route::post('sales-orders/{salesOrder}/submit', [SalesOrderController::class, 'submit'])->name('sales-orders.submit');
    Route::post('sales-orders/{salesOrder}/approve', [SalesOrderController::class, 'approve'])->name('sales-orders.approve');
    Route::post('sales-orders/{salesOrder}/reject', [SalesOrderController::class, 'reject'])->name('sales-orders.reject');
    Route::post('sales-orders/{salesOrder}/cancel', [SalesOrderController::class, 'cancel'])->name('sales-orders.cancel');
    Route::post('sales-orders/{salesOrder}/clone', [SalesOrderController::class, 'clone'])->name('sales-orders.clone');
    Route::post('sales-orders/check-credit', [SalesOrderController::class, 'checkCredit'])->name('sales-orders.check-credit');
    Route::post('sales-orders/check-stock', [SalesOrderController::class, 'checkStock'])->name('sales-orders.check-stock');
});
`

### Tests

`php
<?php

use App\\Models\\SalesOrder;
use App\\Models\\Customer;
use App\\Models\\Product;
use App\\Models\\User;

test('can create sales order', function() {
    \\ = User::factory()->create();
    \\ = Customer::factory()->create();
    \\ = Product::factory()->create(['stock_quantity' => 100]);

    \\->actingAs(\\);

    \\ = [
        'so_number' => 'SO-2026-0001',
        'so_date' => '2026-05-14',
        'customer_id' => \\->id,
        'sales_person_id' => \\->id,
        'delivery_address_id' => \\->addresses()->first()->id,
        'discount_type' => 'percentage',
        'discount_value' => 0,
        'lines' => [
            [
                'product_id' => \\->id,
                'quantity' => 10,
                'unit' => 'pcs',
                'unit_price' => 100000,
                'discount_percentage' => 0,
                'tax_percentage' => 11,
            ],
        ],
    ];

    \\ = \\->post(route('sales-orders.store'), \\);
    
    \\->assertRedirect();
    \\->assertDatabaseHas('sales_orders', ['so_number' => 'SO-2026-0001']);
});

test('creates inventory reservation when approved', function() {
    \\ = SalesOrder::factory()->create(['status' => 'draft']);
    
    \\->submit();
    
    if (\\->status === 'approved') {
        expect(InventoryReservation::where('reserved_for_id', \\->id)->count())->toBeGreaterThan(0);
    }
});

test('cannot cancel SO with delivery orders', function() {
    \\ = SalesOrder::factory()->create(['status' => 'approved']);
    DeliveryOrder::factory()->create(['sales_order_id' => \\->id]);

    \\ = \\->post(route('sales-orders.cancel', \\), ['reason' => 'Test']);
    
    \\->assertSessionHas('error');
});
`

---

## Definition of Done

- [ ] Migrations created
- [ ] Models created dengan relationships
- [ ] SalesOrderController dengan CRUD methods
- [ ] Form Requests dengan validation
- [ ] Routes registered
- [ ] React components (Index, Create, Edit, Show)
- [ ] SO number auto-generation
- [ ] Convert from quotation working
- [ ] Credit limit check implemented
- [ ] Stock availability check implemented
- [ ] Approval workflow working
- [ ] Inventory reservation working
- [ ] Status transitions correct
- [ ] Unit tests (80%+ coverage)
- [ ] Feature tests
- [ ] Manual testing
- [ ] Code review passed
- [ ] Merged to main

---

## Notes

- SO number format: SO-YYYY-NNNN (e.g., SO-2026-0001)
- Approval threshold: Rp 10,000,000
- Inventory reservation: Created saat SO approved
- Credit check: Warning only, tidak blocking
- Stock check: Warning only, tidak blocking
- Status auto-update: Saat create DO atau Invoice
- Cancellation: Tidak bisa jika sudah ada DO atau Invoice
