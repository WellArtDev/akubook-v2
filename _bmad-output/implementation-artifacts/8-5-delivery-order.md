# Story 8.5: Delivery Order

**Epic:** 8 - Customer & Sales Management  
**Story ID:** 8.5  
**Story Key:** 8-5-delivery-order  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** P0 (Foundation)

---

## User Story

**Sebagai** Warehouse Staff  
**Saya ingin** create dan manage delivery orders  
**Sehingga** saya dapat fulfill sales orders dan update inventory

---

## Business Context

Delivery Order (DO) adalah dokumen pengiriman barang:
- **Fulfillment**: Execute approved sales orders
- **Partial Delivery**: Support multiple DOs per SO
- **Inventory Update**: Reduce stock saat delivery
- **Proof of Delivery**: Track delivery status & POD
- **Invoice Trigger**: Delivered items ready untuk invoicing

DO flow:
1. Create DO dari approved SO
2. Pick items dari warehouse
3. Pack & ship
4. Customer receive & sign POD
5. Update inventory & SO status

---

## Acceptance Criteria

### AC1: Delivery Order CRUD Operations

**Given** user adalah Warehouse Staff  
**When** user mengakses Delivery Order  
**Then** user dapat:
- Create new DO dari approved SO
- Edit draft DO
- View DO details
- Cancel DO (dengan reason)
- Print DO document

### AC2: Create DO from Sales Order

**Given** SO status = Approved  
**When** user create DO  
**Then** system:
- Show SO details
- Show remaining quantities (SO qty - delivered qty)
- Allow select items untuk delivery
- Allow partial delivery (qty <= remaining)
- Generate DO number (format: DO-YYYY-NNNN)
- Link DO to SO

### AC3: Delivery Order Header Information

**When** user create DO  
**Then** form harus include:
- DO Number (auto-generated)
- DO Date (default: today)
- Sales Order (reference)
- Customer (dari SO)
- Delivery Address (dari SO)
- Delivery Date (planned)
- Driver Name
- Vehicle Number
- Notes (textarea)

### AC4: Delivery Order Line Items

**When** user add items  
**Then** user dapat:
- See SO line items
- See remaining quantity per item
- Enter delivery quantity (0 < qty <= remaining)
- View item details (product, unit, price)
- Cannot exceed remaining quantity

**Line item fields:**
- Product (dari SO)
- Description (dari SO)
- SO Quantity (display only)
- Delivered Quantity (display only, previous DOs)
- Remaining Quantity (display only)
- This Delivery Quantity (input, required)
- Unit (dari SO)
- Notes (optional)

### AC5: Delivery Order Status Workflow

**DO status:**
- **Draft**: Baru dibuat, masih bisa edit
- **Ready to Ship**: Confirmed, ready untuk pickup
- **In Transit**: Sedang dalam perjalanan
- **Delivered**: Sudah diterima customer
- **Cancelled**: Dibatalkan

**Status transitions:**
- Draft → Ready to Ship (action: Confirm)
- Ready to Ship → In Transit (action: Ship)
- In Transit → Delivered (action: Mark as Delivered, require POD)
- Any → Cancelled (action: Cancel, dengan reason)

### AC6: Inventory Update

**When** DO status → Delivered  
**Then** system:
- Reduce product stock quantity
- Create inventory transaction records
- Release inventory reservation
- Update SO line delivered_quantity
- Update SO status (if fully delivered)

**Inventory transaction:**
`sql
CREATE TABLE inventory_transactions (
    id BIGINT PRIMARY KEY,
    product_id BIGINT,
    transaction_type VARCHAR(50), -- 'sales_delivery', 'purchase_receipt', etc.
    transaction_id BIGINT,
    quantity DECIMAL(15,3),
    transaction_date DATE,
    notes TEXT
);
`

### AC7: Proof of Delivery (POD)

**When** user mark DO as Delivered  
**Then** system require:
- Received by (customer name)
- Received date/time
- Signature (image upload atau digital signature)
- Notes (optional)

**POD fields:**
- received_by VARCHAR(100)
- received_at TIMESTAMP
- signature_path VARCHAR(255)
- pod_notes TEXT

### AC8: Partial Delivery Support

**When** SO has multiple DOs  
**Then** system:
- Track delivered quantity per SO line
- Calculate remaining quantity
- Allow create new DO untuk remaining items
- Update SO status:
  - In Progress (partial delivery)
  - Completed (fully delivered)

**SO line tracking:**
`
SO Line Qty: 100
DO #1: 40 (delivered)
DO #2: 30 (delivered)
Remaining: 30
`

### AC9: Validation Rules

**When** user save DO  
**Then** system validate:
- Sales order required
- SO status = Approved atau In Progress
- At least 1 line item
- All delivery quantities > 0
- All delivery quantities <= remaining quantities
- Delivery date >= DO date

**When** user confirm DO  
**Then** system validate:
- All draft validations
- Driver name required
- Vehicle number required
- Stock available untuk all items

### AC10: Delivery Order List & Filters

**When** user view DO list  
**Then** user dapat:
- See table dengan columns:
  - DO Number
  - Date
  - SO Number
  - Customer
  - Status
  - Delivery Date
  - Actions
- Filter by:
  - Status (multi-select)
  - Date range
  - Customer
  - Driver
- Search by DO number atau SO number
- Sort by any column
- Pagination (50 per page)

### AC11: Cancel Delivery Order

**When** user cancel DO  
**Then** system:
- Require cancellation reason
- Update status → Cancelled
- Restore inventory (if already delivered)
- Restore SO line delivered_quantity
- Cannot cancel jika sudah ada invoice
- Log cancellation untuk audit

---

## Technical Specifications

### Database Schema

#### Table: delivery_orders
`sql
CREATE TABLE delivery_orders (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    do_number VARCHAR(50) UNIQUE NOT NULL,
    do_date DATE NOT NULL,
    sales_order_id BIGINT NOT NULL,
    customer_id BIGINT NOT NULL,
    delivery_address_id BIGINT NOT NULL,
    delivery_date DATE,
    driver_name VARCHAR(100),
    vehicle_number VARCHAR(50),
    notes TEXT,
    status ENUM('draft', 'ready_to_ship', 'in_transit', 'delivered', 'cancelled') DEFAULT 'draft',
    received_by VARCHAR(100),
    received_at TIMESTAMP NULL,
    signature_path VARCHAR(255),
    pod_notes TEXT,
    cancelled_by BIGINT NULL,
    cancelled_at TIMESTAMP NULL,
    cancellation_reason TEXT NULL,
    created_by BIGINT NOT NULL,
    updated_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (sales_order_id) REFERENCES sales_orders(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (delivery_address_id) REFERENCES customer_addresses(id),
    FOREIGN KEY (cancelled_by) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    INDEX idx_do_number (do_number),
    INDEX idx_sales_order (sales_order_id),
    INDEX idx_customer (customer_id),
    INDEX idx_status (status),
    INDEX idx_do_date (do_date)
);
`

#### Table: delivery_order_lines
`sql
CREATE TABLE delivery_order_lines (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    delivery_order_id BIGINT NOT NULL,
    sales_order_line_id BIGINT NOT NULL,
    line_number INT NOT NULL,
    product_id BIGINT NOT NULL,
    description TEXT,
    so_quantity DECIMAL(15,3) NOT NULL,
    delivered_quantity DECIMAL(15,3) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (delivery_order_id) REFERENCES delivery_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (sales_order_line_id) REFERENCES sales_order_lines(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_delivery_order (delivery_order_id),
    INDEX idx_sales_order_line (sales_order_line_id),
    INDEX idx_product (product_id)
);
`

#### Table: inventory_transactions
`sql
CREATE TABLE inventory_transactions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT NOT NULL,
    transaction_type VARCHAR(50) NOT NULL,
    transaction_id BIGINT NOT NULL,
    quantity DECIMAL(15,3) NOT NULL,
    transaction_date DATE NOT NULL,
    notes TEXT,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_product (product_id),
    INDEX idx_transaction (transaction_type, transaction_id),
    INDEX idx_transaction_date (transaction_date)
);
`

### Models

#### DeliveryOrder Model
`php
<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\SoftDeletes;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;

class DeliveryOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected \\ = [
        'do_number',
        'do_date',
        'sales_order_id',
        'customer_id',
        'delivery_address_id',
        'delivery_date',
        'driver_name',
        'vehicle_number',
        'notes',
        'status',
        'received_by',
        'received_at',
        'signature_path',
        'pod_notes',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'created_by',
        'updated_by',
    ];

    protected \\ = [
        'do_date' => 'date',
        'delivery_date' => 'date',
        'received_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Relationships
    public function salesOrder()
    {
        return \\->belongsTo(SalesOrder::class);
    }

    public function customer()
    {
        return \\->belongsTo(Customer::class);
    }

    public function deliveryAddress()
    {
        return \\->belongsTo(CustomerAddress::class, 'delivery_address_id');
    }

    public function lines()
    {
        return \\->hasMany(DeliveryOrderLine::class)->orderBy('line_number');
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

    public function scopeReadyToShip(\\)
    {
        return \\->where('status', 'ready_to_ship');
    }

    public function scopeInTransit(\\)
    {
        return \\->where('status', 'in_transit');
    }

    public function scopeDelivered(\\)
    {
        return \\->where('status', 'delivered');
    }

    // Accessors
    public function getCanEditAttribute()
    {
        return \\->status === 'draft';
    }

    public function getCanConfirmAttribute()
    {
        return \\->status === 'draft' && \\->lines()->count() > 0;
    }

    public function getCanShipAttribute()
    {
        return \\->status === 'ready_to_ship';
    }

    public function getCanDeliverAttribute()
    {
        return \\->status === 'in_transit';
    }

    public function getCanCancelAttribute()
    {
        return in_array(\\->status, ['draft', 'ready_to_ship', 'in_transit']);
    }

    // Methods
    public static function generateDONumber()
    {
        \\ = date('Y');
        \\ = \"DO-{\\}-\";
        
        \\ = self::where('do_number', 'LIKE', \"\\%\")
            ->orderBy('do_number', 'desc')
            ->first();
        
        if (!\\) {
            return \\ . '0001';
        }
        
        \\ = (int) substr(\\->do_number, -4);
        return \\ . str_pad(\\ + 1, 4, '0', STR_PAD_LEFT);
    }

    public function confirm()
    {
        \\->update(['status' => 'ready_to_ship']);
    }

    public function ship()
    {
        \\->update(['status' => 'in_transit']);
    }

    public function markAsDelivered(\\, \\ = null, \\ = null)
    {
        \\->update([
            'status' => 'delivered',
            'received_by' => \\,
            'received_at' => now(),
            'signature_path' => \\,
            'pod_notes' => \\,
        ]);

        // Update inventory
        foreach (\\->lines as \\) {
            // Reduce stock
            \\ = \\->product;
            \\->decrement('stock_quantity', \\->delivered_quantity);

            // Create inventory transaction
            InventoryTransaction::create([
                'product_id' => \\->product_id,
                'transaction_type' => 'sales_delivery',
                'transaction_id' => \\->id,
                'quantity' => -\\->delivered_quantity,
                'transaction_date' => \\->do_date,
                'notes' => \"Delivery Order {\\->do_number}\",
                'created_by' => auth()->id(),
            ]);

            // Update SO line delivered quantity
            \\ = \\->salesOrderLine;
            \\->increment('delivered_quantity', \\->delivered_quantity);

            // Release reservation
            InventoryReservation::where('reserved_for_type', 'sales_order')
                ->where('reserved_for_id', \\->sales_order_id)
                ->where('product_id', \\->product_id)
                ->whereNull('released_at')
                ->update(['released_at' => now()]);
        }

        // Update SO status
        \\->salesOrder->updateStatus();
    }

    public function cancel(\\, \\)
    {
        \\->update([
            'status' => 'cancelled',
            'cancelled_by' => \\,
            'cancelled_at' => now(),
            'cancellation_reason' => \\,
        ]);

        // If already delivered, restore inventory
        if (\\->status === 'delivered') {
            foreach (\\->lines as \\) {
                // Restore stock
                \\ = \\->product;
                \\->increment('stock_quantity', \\->delivered_quantity);

                // Create reversal transaction
                InventoryTransaction::create([
                    'product_id' => \\->product_id,
                    'transaction_type' => 'sales_delivery_reversal',
                    'transaction_id' => \\->id,
                    'quantity' => \\->delivered_quantity,
                    'transaction_date' => now()->toDateString(),
                    'notes' => \"Cancelled Delivery Order {\\->do_number}\",
                    'created_by' => \\,
                ]);

                // Restore SO line delivered quantity
                \\ = \\->salesOrderLine;
                \\->decrement('delivered_quantity', \\->delivered_quantity);
            }

            // Update SO status
            \\->salesOrder->updateStatus();
        }
    }
}
`

#### DeliveryOrderLine Model
`php
<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;

class DeliveryOrderLine extends Model
{
    use HasFactory;

    protected \\ = [
        'delivery_order_id',
        'sales_order_line_id',
        'line_number',
        'product_id',
        'description',
        'so_quantity',
        'delivered_quantity',
        'unit',
        'notes',
    ];

    protected \\ = [
        'so_quantity' => 'decimal:3',
        'delivered_quantity' => 'decimal:3',
    ];

    // Relationships
    public function deliveryOrder()
    {
        return \\->belongsTo(DeliveryOrder::class);
    }

    public function salesOrderLine()
    {
        return \\->belongsTo(SalesOrderLine::class);
    }

    public function product()
    {
        return \\->belongsTo(Product::class);
    }
}
`

#### InventoryTransaction Model
`php
<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected \\ = [
        'product_id',
        'transaction_type',
        'transaction_id',
        'quantity',
        'transaction_date',
        'notes',
        'created_by',
    ];

    protected \\ = [
        'quantity' => 'decimal:3',
        'transaction_date' => 'date',
    ];

    // Relationships
    public function product()
    {
        return \\->belongsTo(Product::class);
    }

    public function creator()
    {
        return \\->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeForProduct(\\, \\)
    {
        return \\->where('product_id', \\);
    }

    public function scopeByType(\\, \\)
    {
        return \\->where('transaction_type', \\);
    }
}
`

### Controller

`php
<?php

namespace App\\Http\\Controllers;

use App\\Models\\DeliveryOrder;
use App\\Models\\SalesOrder;
use App\\Http\\Requests\\StoreDeliveryOrderRequest;
use App\\Http\\Requests\\UpdateDeliveryOrderRequest;
use Illuminate\\Http\\Request;
use Inertia\\Inertia;

class DeliveryOrderController extends Controller
{
    public function index(Request \\)
    {
        \\ = DeliveryOrder::with(['salesOrder', 'customer'])
            ->orderBy('do_date', 'desc');

        // Filters
        if (\\->filled('status')) {
            \\->whereIn('status', \\->status);
        }

        if (\\->filled('customer_id')) {
            \\->where('customer_id', \\->customer_id);
        }

        if (\\->filled('date_from')) {
            \\->where('do_date', '>=', \\->date_from);
        }

        if (\\->filled('date_to')) {
            \\->where('do_date', '<=', \\->date_to);
        }

        if (\\->filled('search')) {
            \\->where(function(\\) use (\\) {
                \\->where('do_number', 'LIKE', \"%{\\->search}%\")
                  ->orWhereHas('salesOrder', function(\\) use (\\) {
                      \\->where('so_number', 'LIKE', \"%{\\->search}%\");
                  });
            });
        }

        \\ = \\->paginate(50);

        return Inertia::render('DeliveryOrders/Index', [
            'deliveryOrders' => \\,
            'filters' => \\->only(['status', 'customer_id', 'date_from', 'date_to', 'search']),
        ]);
    }

    public function create(Request \\)
    {
        \\ = null;
        if (\\->filled('sales_order_id')) {
            \\ = SalesOrder::with(['lines.product', 'customer', 'deliveryAddress'])
                ->findOrFail(\\->sales_order_id);

            // Calculate remaining quantities
            foreach (\\->lines as \\) {
                \\->remaining_quantity = \\->quantity - \\->delivered_quantity;
            }
        }

        return Inertia::render('DeliveryOrders/Create', [
            'doNumber' => DeliveryOrder::generateDONumber(),
            'salesOrder' => \\,
        ]);
    }

    public function store(StoreDeliveryOrderRequest \\)
    {
        \\ = DeliveryOrder::create([
            ...\\->validated(),
            'created_by' => auth()->id(),
        ]);

        // Create lines
        foreach (\\->lines as \\ => \\) {
            \\->lines()->create([
                ...\\,
                'line_number' => \\ + 1,
            ]);
        }

        return redirect()->route('delivery-orders.show', \\)
            ->with('success', 'Delivery order created successfully.');
    }

    public function show(DeliveryOrder \\)
    {
        \\->load([
            'salesOrder',
            'customer',
            'deliveryAddress',
            'lines.product',
            'lines.salesOrderLine',
        ]);

        return Inertia::render('DeliveryOrders/Show', [
            'deliveryOrder' => \\,
        ]);
    }

    public function edit(DeliveryOrder \\)
    {
        if (!\\->can_edit) {
            return redirect()->route('delivery-orders.show', \\)
                ->with('error', 'Cannot edit delivery order in current status.');
        }

        \\->load('lines', 'salesOrder.lines.product');

        return Inertia::render('DeliveryOrders/Edit', [
            'deliveryOrder' => \\,
        ]);
    }

    public function update(UpdateDeliveryOrderRequest \\, DeliveryOrder \\)
    {
        if (!\\->can_edit) {
            return redirect()->route('delivery-orders.show', \\)
                ->with('error', 'Cannot edit delivery order in current status.');
        }

        \\->update([
            ...\\->validated(),
            'updated_by' => auth()->id(),
        ]);

        // Delete old lines
        \\->lines()->delete();

        // Create new lines
        foreach (\\->lines as \\ => \\) {
            \\->lines()->create([
                ...\\,
                'line_number' => \\ + 1,
            ]);
        }

        return redirect()->route('delivery-orders.show', \\)
            ->with('success', 'Delivery order updated successfully.');
    }

    public function destroy(DeliveryOrder \\)
    {
        if (!\\->can_edit) {
            return redirect()->route('delivery-orders.index')
                ->with('error', 'Cannot delete delivery order in current status.');
        }

        \\->delete();

        return redirect()->route('delivery-orders.index')
            ->with('success', 'Delivery order deleted successfully.');
    }

    public function confirm(DeliveryOrder \\)
    {
        if (!\\->can_confirm) {
            return back()->with('error', 'Cannot confirm delivery order in current status.');
        }

        \\->confirm();

        return back()->with('success', 'Delivery order confirmed.');
    }

    public function ship(DeliveryOrder \\)
    {
        if (!\\->can_ship) {
            return back()->with('error', 'Cannot ship delivery order in current status.');
        }

        \\->ship();

        return back()->with('success', 'Delivery order marked as in transit.');
    }

    public function deliver(Request \\, DeliveryOrder \\)
    {
        if (!\\->can_deliver) {
            return back()->with('error', 'Cannot deliver in current status.');
        }

        \\->validate([
            'received_by' => 'required|string|max:100',
            'signature' => 'nullable|image|max:2048',
            'pod_notes' => 'nullable|string',
        ]);

        \\ = null;
        if (\\->hasFile('signature')) {
            \\ = \\->file('signature')->store('signatures', 'public');
        }

        \\->markAsDelivered(
            \\->received_by,
            \\,
            \\->pod_notes
        );

        return back()->with('success', 'Delivery order marked as delivered.');
    }

    public function cancel(Request \\, DeliveryOrder \\)
    {
        if (!\\->can_cancel) {
            return back()->with('error', 'Cannot cancel delivery order in current status.');
        }

        \\->validate([
            'reason' => 'required|string',
        ]);

        \\->cancel(auth()->id(), \\->reason);

        return back()->with('success', 'Delivery order cancelled.');
    }
}
`

### Form Requests

#### StoreDeliveryOrderRequest
`php
<?php

namespace App\\Http\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

class StoreDeliveryOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'do_number' => 'required|string|unique:delivery_orders,do_number',
            'do_date' => 'required|date',
            'sales_order_id' => 'required|exists:sales_orders,id',
            'customer_id' => 'required|exists:customers,id',
            'delivery_address_id' => 'required|exists:customer_addresses,id',
            'delivery_date' => 'nullable|date|after_or_equal:do_date',
            'driver_name' => 'nullable|string|max:100',
            'vehicle_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.sales_order_line_id' => 'required|exists:sales_order_lines,id',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.description' => 'nullable|string',
            'lines.*.so_quantity' => 'required|numeric|min:0',
            'lines.*.delivered_quantity' => 'required|numeric|min:0.001',
            'lines.*.unit' => 'required|string|max:20',
            'lines.*.notes' => 'nullable|string',
        ];
    }
}
`

### Routes

`php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::resource('delivery-orders', DeliveryOrderController::class);
    Route::post('delivery-orders/{deliveryOrder}/confirm', [DeliveryOrderController::class, 'confirm'])->name('delivery-orders.confirm');
    Route::post('delivery-orders/{deliveryOrder}/ship', [DeliveryOrderController::class, 'ship'])->name('delivery-orders.ship');
    Route::post('delivery-orders/{deliveryOrder}/deliver', [DeliveryOrderController::class, 'deliver'])->name('delivery-orders.deliver');
    Route::post('delivery-orders/{deliveryOrder}/cancel', [DeliveryOrderController::class, 'cancel'])->name('delivery-orders.cancel');
});
`

### Tests

`php
<?php

use App\\Models\\DeliveryOrder;
use App\\Models\\SalesOrder;
use App\\Models\\Product;
use App\\Models\\User;

test('can create delivery order', function() {
    \\ = User::factory()->create();
    \\ = SalesOrder::factory()->create(['status' => 'approved']);
    \\ = \\->lines()->first();

    \\->actingAs(\\);

    \\ = [
        'do_number' => 'DO-2026-0001',
        'do_date' => '2026-05-14',
        'sales_order_id' => \\->id,
        'customer_id' => \\->customer_id,
        'delivery_address_id' => \\->delivery_address_id,
        'lines' => [
            [
                'sales_order_line_id' => \\->id,
                'product_id' => \\->product_id,
                'so_quantity' => \\->quantity,
                'delivered_quantity' => \\->quantity,
                'unit' => \\->unit,
            ],
        ],
    ];

    \\ = \\->post(route('delivery-orders.store'), \\);
    
    \\->assertRedirect();
    \\->assertDatabaseHas('delivery_orders', ['do_number' => 'DO-2026-0001']);
});

test('updates inventory when delivered', function() {
    \\ = Product::factory()->create(['stock_quantity' => 100]);
    \\ = DeliveryOrder::factory()->create(['status' => 'in_transit']);
    \\ = \\->lines()->create([
        'product_id' => \\->id,
        'delivered_quantity' => 10,
    ]);

    \\->markAsDelivered('John Doe');
    
    \\->refresh();
    expect(\\->stock_quantity)->toBe(90.0);
});

test('cannot exceed remaining quantity', function() {
    \\ = SalesOrder::factory()->create();
    \\ = \\->lines()->first();
    \\->update(['quantity' => 100, 'delivered_quantity' => 60]);

    // Try to deliver 50 (remaining is 40)
    \\ = [
        'lines' => [
            [
                'sales_order_line_id' => \\->id,
                'delivered_quantity' => 50, // exceeds remaining
            ],
        ],
    ];

    \\ = \\->post(route('delivery-orders.store'), \\);
    
    \\->assertSessionHasErrors();
});
`

---

## Definition of Done

- [ ] Migrations created
- [ ] Models created dengan relationships
- [ ] DeliveryOrderController dengan CRUD methods
- [ ] Form Requests dengan validation
- [ ] Routes registered
- [ ] React components (Index, Create, Edit, Show)
- [ ] DO number auto-generation
- [ ] Partial delivery support
- [ ] Inventory update working
- [ ] POD capture working
- [ ] Status workflow correct
- [ ] SO status auto-update
- [ ] Unit tests (80%+ coverage)
- [ ] Feature tests
- [ ] Manual testing
- [ ] Code review passed
- [ ] Merged to main

---

## Notes

- DO number format: DO-YYYY-NNNN (e.g., DO-2026-0001)
- Partial delivery: Multiple DOs per SO allowed
- Inventory update: Saat status → Delivered
- POD: Signature upload optional
- Cancellation: Restore inventory jika already delivered
- Cannot cancel jika sudah ada invoice (implement in Story 8.6)
