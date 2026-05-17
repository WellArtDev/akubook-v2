# Story 8.2: Sales Quotation

**Epic:** 8 - Customer & Sales Management  
**Story ID:** 8.2  
**Story Key:** 8-2-sales-quotation  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** P0 (Foundation)

---

## User Story

**Sebagai** Sales Staff  
**Saya ingin** create dan manage sales quotation  
**Sehingga** saya dapat provide price quote ke customer sebelum create sales order

---

## Business Context

Sales Quotation adalah dokumen penawaran harga ke customer:
- **Pre-Sales**: Quote sebelum customer commit
- **Price Negotiation**: Track revisions & price changes
- **Validity Period**: Quote expire setelah tanggal tertentu
- **Conversion**: Convert approved quote ke Sales Order

Quotation flow:
1. Sales create quotation dengan items & prices
2. Send ke customer untuk review
3. Customer approve/reject/request revision
4. Approved quotation convert ke Sales Order

---

## Acceptance Criteria

### AC1: Quotation CRUD Operations

**Given** user adalah Sales Staff  
**When** user mengakses Sales Quotation  
**Then** user dapat:
- Create new quotation
- Edit draft quotation
- View quotation details
- Delete draft quotation
- Clone existing quotation

### AC2: Quotation Header Information

**When** user create quotation  
**Then** form harus include:
- Quotation Number (auto-generated, format: QT-YYYY-NNNN)
- Quotation Date (default: today)
- Valid Until Date (default: +30 days)
- Customer (searchable dropdown)
- Customer Contact (dropdown dari customer contacts)
- Reference (customer PO/inquiry number)
- Sales Person (default: current user)
- Payment Terms (dari customer default)
- Delivery Terms (FOB, CIF, etc.)
- Notes (textarea)

### AC3: Quotation Line Items

**When** user add items  
**Then** user dapat:
- Search & select product
- Enter quantity
- Set unit price (default dari product price)
- Apply discount (% atau amount)
- View line total
- Add multiple items
- Reorder items (drag & drop)
- Delete items

**Line item fields:**
- Product (searchable dropdown)
- Description (auto-fill dari product, editable)
- Quantity (required, > 0)
- Unit (dari product)
- Unit Price (required, > 0)
- Discount % (0-100)
- Discount Amount (calculated atau manual)
- Tax (PPN 11%)
- Line Total (calculated)

### AC4: Quotation Calculations

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

### AC5: Quotation Status Workflow

**Quotation status:**
- **Draft**: Baru dibuat, masih bisa edit
- **Sent**: Sudah dikirim ke customer, tidak bisa edit
- **Approved**: Customer approve, bisa convert ke SO
- **Rejected**: Customer reject
- **Expired**: Melewati valid until date
- **Converted**: Sudah jadi Sales Order

**Status transitions:**
- Draft → Sent (action: Send to Customer)
- Sent → Approved (action: Mark as Approved)
- Sent → Rejected (action: Mark as Rejected)
- Sent → Draft (action: Revise - create new revision)
- Approved → Converted (action: Convert to Sales Order)
- Any → Expired (automatic jika valid_until < today)

### AC6: Quotation Revisions

**When** customer request changes  
**Then** user dapat:
- Create revision dari existing quotation
- New revision number (QT-YYYY-NNNN-R01, R02, etc.)
- Copy all data dari original
- Original quotation status → Revised
- New revision status → Draft

**Revision tracking:**
- Show revision history
- Link to original quotation
- Show what changed (diff)

### AC7: Convert to Sales Order

**Given** quotation status = Approved  
**When** user click Convert to Sales Order  
**Then** system:
- Create new Sales Order
- Copy all header data
- Copy all line items
- Link SO to quotation
- Update quotation status → Converted
- Redirect to Sales Order edit page

### AC8: Quotation Expiry

**When** system run daily job  
**Then** system:
- Find quotations where valid_until < today
- Update status → Expired (jika status = Sent)
- Send notification ke sales person

### AC9: Validation Rules

**When** user save quotation  
**Then** system validate:
- Customer required
- Quotation date required
- Valid until date > quotation date
- At least 1 line item
- All line items have quantity > 0
- All line items have unit price > 0
- Grand total > 0

**When** user send quotation  
**Then** system validate:
- All draft validations
- Customer contact email exists
- Cannot send if status != Draft

### AC10: Quotation List & Filters

**When** user view quotation list  
**Then** user dapat:
- See table dengan columns:
  - Quotation Number
  - Date
  - Customer
  - Valid Until
  - Status
  - Grand Total
  - Actions
- Filter by:
  - Status (multi-select)
  - Date range
  - Customer
  - Sales Person
- Search by quotation number atau customer name
- Sort by any column
- Pagination (50 per page)

### AC11: Quotation Print/Export

**When** user view quotation  
**Then** user dapat:
- Print quotation (PDF)
- Export to Excel
- Send via email

**PDF format:**
- Company header
- Quotation details
- Customer details
- Line items table
- Terms & conditions
- Signature section

---

## Technical Specifications

### Database Schema

#### Table: sales_quotations
`sql
CREATE TABLE sales_quotations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    quotation_number VARCHAR(50) UNIQUE NOT NULL,
    quotation_date DATE NOT NULL,
    valid_until DATE NOT NULL,
    customer_id BIGINT NOT NULL,
    customer_contact_id BIGINT,
    reference VARCHAR(100),
    sales_person_id BIGINT NOT NULL,
    payment_terms VARCHAR(50),
    delivery_terms VARCHAR(100),
    notes TEXT,
    status ENUM('draft', 'sent', 'approved', 'rejected', 'expired', 'converted', 'revised') DEFAULT 'draft',
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
    discount_type ENUM('percentage', 'amount') DEFAULT 'percentage',
    discount_value DECIMAL(15,2) DEFAULT 0,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    subtotal_after_discount DECIMAL(15,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    grand_total DECIMAL(15,2) NOT NULL DEFAULT 0,
    original_quotation_id BIGINT NULL,
    revision_number INT DEFAULT 0,
    converted_to_sales_order_id BIGINT NULL,
    sent_at TIMESTAMP NULL,
    approved_at TIMESTAMP NULL,
    rejected_at TIMESTAMP NULL,
    expired_at TIMESTAMP NULL,
    converted_at TIMESTAMP NULL,
    created_by BIGINT NOT NULL,
    updated_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (customer_contact_id) REFERENCES customer_contacts(id),
    FOREIGN KEY (sales_person_id) REFERENCES users(id),
    FOREIGN KEY (original_quotation_id) REFERENCES sales_quotations(id),
    FOREIGN KEY (converted_to_sales_order_id) REFERENCES sales_orders(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    INDEX idx_quotation_number (quotation_number),
    INDEX idx_customer (customer_id),
    INDEX idx_status (status),
    INDEX idx_quotation_date (quotation_date),
    INDEX idx_valid_until (valid_until)
);
`

#### Table: sales_quotation_lines
`sql
CREATE TABLE sales_quotation_lines (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sales_quotation_id BIGINT NOT NULL,
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
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sales_quotation_id) REFERENCES sales_quotations(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_quotation (sales_quotation_id),
    INDEX idx_product (product_id)
);
`

### Models

#### SalesQuotation Model
`php
<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\SoftDeletes;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;

class SalesQuotation extends Model
{
    use HasFactory, SoftDeletes;

    protected \\ = [
        'quotation_number',
        'quotation_date',
        'valid_until',
        'customer_id',
        'customer_contact_id',
        'reference',
        'sales_person_id',
        'payment_terms',
        'delivery_terms',
        'notes',
        'status',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'subtotal_after_discount',
        'tax_amount',
        'grand_total',
        'original_quotation_id',
        'revision_number',
        'converted_to_sales_order_id',
        'sent_at',
        'approved_at',
        'rejected_at',
        'expired_at',
        'converted_at',
        'created_by',
        'updated_by',
    ];

    protected \\ = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal_after_discount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'sent_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'expired_at' => 'datetime',
        'converted_at' => 'datetime',
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

    public function salesPerson()
    {
        return \\->belongsTo(User::class, 'sales_person_id');
    }

    public function lines()
    {
        return \\->hasMany(SalesQuotationLine::class)->orderBy('line_number');
    }

    public function originalQuotation()
    {
        return \\->belongsTo(SalesQuotation::class, 'original_quotation_id');
    }

    public function revisions()
    {
        return \\->hasMany(SalesQuotation::class, 'original_quotation_id');
    }

    public function salesOrder()
    {
        return \\->belongsTo(SalesOrder::class, 'converted_to_sales_order_id');
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

    public function scopeSent(\\)
    {
        return \\->where('status', 'sent');
    }

    public function scopeApproved(\\)
    {
        return \\->where('status', 'approved');
    }

    public function scopeExpired(\\)
    {
        return \\->where('status', 'expired');
    }

    public function scopeActive(\\)
    {
        return \\->whereIn('status', ['draft', 'sent', 'approved']);
    }

    // Accessors
    public function getIsExpiredAttribute()
    {
        return \\->valid_until < now() && \\->status === 'sent';
    }

    public function getCanEditAttribute()
    {
        return in_array(\\->status, ['draft']);
    }

    public function getCanSendAttribute()
    {
        return \\->status === 'draft' && \\->lines()->count() > 0;
    }

    public function getCanConvertAttribute()
    {
        return \\->status === 'approved' && !\\->converted_to_sales_order_id;
    }

    public function getCanReviseAttribute()
    {
        return in_array(\\->status, ['sent', 'rejected']);
    }

    // Methods
    public static function generateQuotationNumber()
    {
        \\ = date('Y');
        \\ = \"QT-{\\}-\";
        
        \\ = self::where('quotation_number', 'LIKE', \"\\%\")
            ->orderBy('quotation_number', 'desc')
            ->first();
        
        if (!\\) {
            return \\ . '0001';
        }
        
        \\ = (int) substr(\\->quotation_number, -4);
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

    public function markAsSent()
    {
        \\->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsApproved()
    {
        \\->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function markAsRejected()
    {
        \\->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);
    }

    public function markAsExpired()
    {
        \\->update([
            'status' => 'expired',
            'expired_at' => now(),
        ]);
    }

    public function createRevision()
    {
        \\ = \\->replicate();
        \\->quotation_number = \\->quotation_number . '-R' . str_pad(\\->revisions()->count() + 1, 2, '0', STR_PAD_LEFT);
        \\->status = 'draft';
        \\->original_quotation_id = \\->id;
        \\->revision_number = \\->revision_number + 1;
        \\->sent_at = null;
        \\->approved_at = null;
        \\->rejected_at = null;
        \\->expired_at = null;
        \\->converted_at = null;
        \\->converted_to_sales_order_id = null;
        \\->save();

        // Copy lines
        foreach (\\->lines as \\) {
            \\ = \\->replicate();
            \\->sales_quotation_id = \\->id;
            \\->save();
        }

        // Mark original as revised
        \\->update(['status' => 'revised']);

        return \\;
    }
}
`

#### SalesQuotationLine Model
`php
<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;

class SalesQuotationLine extends Model
{
    use HasFactory;

    protected \\ = [
        'sales_quotation_id',
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
    ];

    // Relationships
    public function quotation()
    {
        return \\->belongsTo(SalesQuotation::class, 'sales_quotation_id');
    }

    public function product()
    {
        return \\->belongsTo(Product::class);
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

### Controller

`php
<?php

namespace App\\Http\\Controllers;

use App\\Models\\SalesQuotation;
use App\\Models\\Customer;
use App\\Models\\Product;
use App\\Http\\Requests\\StoreSalesQuotationRequest;
use App\\Http\\Requests\\UpdateSalesQuotationRequest;
use Illuminate\\Http\\Request;
use Inertia\\Inertia;

class SalesQuotationController extends Controller
{
    public function index(Request \\)
    {
        \\ = SalesQuotation::with(['customer', 'salesPerson'])
            ->orderBy('quotation_date', 'desc');

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
            \\->where('quotation_date', '>=', \\->date_from);
        }

        if (\\->filled('date_to')) {
            \\->where('quotation_date', '<=', \\->date_to);
        }

        if (\\->filled('search')) {
            \\->where(function(\\) use (\\) {
                \\->where('quotation_number', 'LIKE', \"%{\\->search}%\")
                  ->orWhereHas('customer', function(\\) use (\\) {
                      \\->where('name', 'LIKE', \"%{\\->search}%\");
                  });
            });
        }

        \\ = \\->paginate(50);

        return Inertia::render('SalesQuotations/Index', [
            'quotations' => \\,
            'filters' => \\->only(['status', 'customer_id', 'sales_person_id', 'date_from', 'date_to', 'search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('SalesQuotations/Create', [
            'quotationNumber' => SalesQuotation::generateQuotationNumber(),
            'customers' => Customer::select('id', 'name', 'code')->get(),
            'products' => Product::select('id', 'name', 'code', 'unit', 'selling_price')->get(),
        ]);
    }

    public function store(StoreSalesQuotationRequest \\)
    {
        \\ = SalesQuotation::create([
            ...->validated(),
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

        return redirect()->route('sales-quotations.show', \\)
            ->with('success', 'Sales quotation created successfully.');
    }

    public function show(SalesQuotation \\)
    {
        \\->load([
            'customer.contacts',
            'customerContact',
            'salesPerson',
            'lines.product',
            'originalQuotation',
            'revisions',
            'salesOrder',
        ]);

        return Inertia::render('SalesQuotations/Show', [
            'quotation' => \\,
        ]);
    }

    public function edit(SalesQuotation \\)
    {
        if (!\\->can_edit) {
            return redirect()->route('sales-quotations.show', \\)
                ->with('error', 'Cannot edit quotation in current status.');
        }

        \\->load('lines');

        return Inertia::render('SalesQuotations/Edit', [
            'quotation' => \\,
            'customers' => Customer::select('id', 'name', 'code')->get(),
            'products' => Product::select('id', 'name', 'code', 'unit', 'selling_price')->get(),
        ]);
    }

    public function update(UpdateSalesQuotationRequest \\, SalesQuotation \\)
    {
        if (!\\->can_edit) {
            return redirect()->route('sales-quotations.show', \\)
                ->with('error', 'Cannot edit quotation in current status.');
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

        return redirect()->route('sales-quotations.show', \\)
            ->with('success', 'Sales quotation updated successfully.');
    }

    public function destroy(SalesQuotation \\)
    {
        if (!\\->can_edit) {
            return redirect()->route('sales-quotations.index')
                ->with('error', 'Cannot delete quotation in current status.');
        }

        \\->delete();

        return redirect()->route('sales-quotations.index')
            ->with('success', 'Sales quotation deleted successfully.');
    }

    public function send(SalesQuotation \\)
    {
        if (!\\->can_send) {
            return back()->with('error', 'Cannot send quotation in current status.');
        }

        \\->markAsSent();

        // TODO: Send email to customer

        return back()->with('success', 'Quotation sent to customer.');
    }

    public function approve(SalesQuotation \\)
    {
        if (\\->status !== 'sent') {
            return back()->with('error', 'Can only approve sent quotations.');
        }

        \\->markAsApproved();

        return back()->with('success', 'Quotation approved.');
    }

    public function reject(SalesQuotation \\)
    {
        if (\\->status !== 'sent') {
            return back()->with('error', 'Can only reject sent quotations.');
        }

        \\->markAsRejected();

        return back()->with('success', 'Quotation rejected.');
    }

    public function revise(SalesQuotation \\)
    {
        if (!\\->can_revise) {
            return back()->with('error', 'Cannot revise quotation in current status.');
        }

        \\ = \\->createRevision();

        return redirect()->route('sales-quotations.edit', \\)
            ->with('success', 'Revision created. You can now edit the quotation.');
    }

    public function clone(SalesQuotation \\)
    {
        \\ = \\->replicate();
        \\->quotation_number = SalesQuotation::generateQuotationNumber();
        \\->quotation_date = now();
        \\->valid_until = now()->addDays(30);
        \\->status = 'draft';
        \\->original_quotation_id = null;
        \\->revision_number = 0;
        \\->sent_at = null;
        \\->approved_at = null;
        \\->rejected_at = null;
        \\->expired_at = null;
        \\->converted_at = null;
        \\->converted_to_sales_order_id = null;
        \\->created_by = auth()->id();
        \\->save();

        // Copy lines
        foreach (\\->lines as \\) {
            \\ = \\->replicate();
            \\->sales_quotation_id = \\->id;
            \\->save();
        }

        return redirect()->route('sales-quotations.edit', \\)
            ->with('success', 'Quotation cloned successfully.');
    }

    public function convertToSalesOrder(SalesQuotation \\)
    {
        if (!\\->can_convert) {
            return back()->with('error', 'Cannot convert quotation in current status.');
        }

        // TODO: Implement in Story 8.3
        return back()->with('info', 'Convert to Sales Order will be implemented in Story 8.3.');
    }
}
`

### Form Requests

#### StoreSalesQuotationRequest
`php
<?php

namespace App\\Http\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

class StoreSalesQuotationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'quotation_number' => 'required|string|unique:sales_quotations,quotation_number',
            'quotation_date' => 'required|date',
            'valid_until' => 'required|date|after:quotation_date',
            'customer_id' => 'required|exists:customers,id',
            'customer_contact_id' => 'nullable|exists:customer_contacts,id',
            'reference' => 'nullable|string|max:100',
            'sales_person_id' => 'required|exists:users,id',
            'payment_terms' => 'nullable|string|max:50',
            'delivery_terms' => 'nullable|string|max:100',
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

    public function messages()
    {
        return [
            'valid_until.after' => 'Valid until date must be after quotation date.',
            'lines.required' => 'At least one line item is required.',
            'lines.min' => 'At least one line item is required.',
        ];
    }
}
`

#### UpdateSalesQuotationRequest
`php
<?php

namespace App\\Http\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

class UpdateSalesQuotationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'quotation_date' => 'required|date',
            'valid_until' => 'required|date|after:quotation_date',
            'customer_id' => 'required|exists:customers,id',
            'customer_contact_id' => 'nullable|exists:customer_contacts,id',
            'reference' => 'nullable|string|max:100',
            'sales_person_id' => 'required|exists:users,id',
            'payment_terms' => 'nullable|string|max:50',
            'delivery_terms' => 'nullable|string|max:100',
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
    Route::resource('sales-quotations', SalesQuotationController::class);
    Route::post('sales-quotations/{salesQuotation}/send', [SalesQuotationController::class, 'send'])->name('sales-quotations.send');
    Route::post('sales-quotations/{salesQuotation}/approve', [SalesQuotationController::class, 'approve'])->name('sales-quotations.approve');
    Route::post('sales-quotations/{salesQuotation}/reject', [SalesQuotationController::class, 'reject'])->name('sales-quotations.reject');
    Route::post('sales-quotations/{salesQuotation}/revise', [SalesQuotationController::class, 'revise'])->name('sales-quotations.revise');
    Route::post('sales-quotations/{salesQuotation}/clone', [SalesQuotationController::class, 'clone'])->name('sales-quotations.clone');
    Route::post('sales-quotations/{salesQuotation}/convert-to-sales-order', [SalesQuotationController::class, 'convertToSalesOrder'])->name('sales-quotations.convert-to-sales-order');
});
`

### React Components

#### Index Page
`jsx
// resources/js/Pages/SalesQuotations/Index.jsx
import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ quotations, filters }) {
    const [search, setSearch] = useState(filters.search || '');

    const handleFilter = (key, value) => {
        router.get(route('sales-quotations.index'), {
            ...filters,
            [key]: value,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const getStatusBadge = (status) => {
        const badges = {
            draft: 'bg-gray-100 text-gray-800',
            sent: 'bg-blue-100 text-blue-800',
            approved: 'bg-green-100 text-green-800',
            rejected: 'bg-red-100 text-red-800',
            expired: 'bg-yellow-100 text-yellow-800',
            converted: 'bg-purple-100 text-purple-800',
            revised: 'bg-orange-100 text-orange-800',
        };
        return badges[status] || 'bg-gray-100 text-gray-800';
    };

    return (
        <AuthenticatedLayout>
            <Head title=\"Sales Quotations\" />

            <div className=\"py-12\">
                <div className=\"max-w-7xl mx-auto sm:px-6 lg:px-8\">
                    <div className=\"bg-white overflow-hidden shadow-sm sm:rounded-lg\">
                        <div className=\"p-6\">
                            <div className=\"flex justify-between items-center mb-6\">
                                <h2 className=\"text-2xl font-semibold\">Sales Quotations</h2>
                                <Link
                                    href={route('sales-quotations.create')}
                                    className=\"bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded\"
                                >
                                    Create Quotation
                                </Link>
                            </div>

                            {/* Filters */}
                            <div className=\"mb-4 grid grid-cols-1 md:grid-cols-4 gap-4\">
                                <input
                                    type=\"text\"
                                    placeholder=\"Search...\"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    onKeyPress={(e) => e.key === 'Enter' && handleFilter('search', search)}
                                    className=\"border rounded px-3 py-2\"
                                />
                                {/* Add more filters */}
                            </div>

                            {/* Table */}
                            <div className=\"overflow-x-auto\">
                                <table className=\"min-w-full divide-y divide-gray-200\">
                                    <thead className=\"bg-gray-50\">
                                        <tr>
                                            <th className=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase\">Quotation #</th>
                                            <th className=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase\">Date</th>
                                            <th className=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase\">Customer</th>
                                            <th className=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase\">Valid Until</th>
                                            <th className=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase\">Status</th>
                                            <th className=\"px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase\">Grand Total</th>
                                            <th className=\"px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase\">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody className=\"bg-white divide-y divide-gray-200\">
                                        {quotations.data.map((quotation) => (
                                            <tr key={quotation.id}>
                                                <td className=\"px-6 py-4 whitespace-nowrap\">
                                                    <Link href={route('sales-quotations.show', quotation.id)} className=\"text-blue-600 hover:text-blue-900\">
                                                        {quotation.quotation_number}
                                                    </Link>
                                                </td>
                                                <td className=\"px-6 py-4 whitespace-nowrap\">{quotation.quotation_date}</td>
                                                <td className=\"px-6 py-4 whitespace-nowrap\">{quotation.customer.name}</td>
                                                <td className=\"px-6 py-4 whitespace-nowrap\">{quotation.valid_until}</td>
                                                <td className=\"px-6 py-4 whitespace-nowrap\">
                                                    <span className={px-2 inline-flex text-xs leading-5 font-semibold rounded-full \\}>
                                                        {quotation.status}
                                                    </span>
                                                </td>
                                                <td className=\"px-6 py-4 whitespace-nowrap text-right\">
                                                    Rp {quotation.grand_total.toLocaleString('id-ID')}
                                                </td>
                                                <td className=\"px-6 py-4 whitespace-nowrap text-right text-sm font-medium\">
                                                    <Link href={route('sales-quotations.show', quotation.id)} className=\"text-blue-600 hover:text-blue-900 mr-3\">
                                                        View
                                                    </Link>
                                                    {quotation.can_edit && (
                                                        <Link href={route('sales-quotations.edit', quotation.id)} className=\"text-indigo-600 hover:text-indigo-900\">
                                                            Edit
                                                        </Link>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination */}
                            <div className=\"mt-4\">
                                {/* Add pagination component */}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
`

### Tests

`php
<?php

use App\\Models\\SalesQuotation;
use App\\Models\\Customer;
use App\\Models\\Product;
use App\\Models\\User;

test('can create sales quotation', function() {
    \\ = User::factory()->create();
    \\ = Customer::factory()->create();
    \\ = Product::factory()->create();

    \\->actingAs(\\);

    \\ = [
        'quotation_number' => 'QT-2026-0001',
        'quotation_date' => '2026-05-14',
        'valid_until' => '2026-06-14',
        'customer_id' => \\->id,
        'sales_person_id' => \\->id,
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

    \\ = \\->post(route('sales-quotations.store'), \\);
    
    \\->assertRedirect();
    \\->assertDatabaseHas('sales_quotations', ['quotation_number' => 'QT-2026-0001']);
});

test('can send quotation', function() {
    \\ = SalesQuotation::factory()->create(['status' => 'draft']);

    \\ = \\->post(route('sales-quotations.send', \\));
    
    \\->refresh();
    expect(\\->status)->toBe('sent');
    expect(\\->sent_at)->not->toBeNull();
});

test('can create revision', function() {
    \\ = SalesQuotation::factory()->create(['status' => 'sent']);

    \\ = \\->post(route('sales-quotations.revise', \\));
    
    \\->refresh();
    expect(\\->status)->toBe('revised');
    expect(\\->revisions()->count())->toBe(1);
});
`

---

## Definition of Done

- [ ] Migrations created
- [ ] Models created dengan relationships
- [ ] SalesQuotationController dengan CRUD methods
- [ ] Form Requests dengan validation
- [ ] Routes registered
- [ ] React components (Index, Create, Edit, Show)
- [ ] Quotation number auto-generation
- [ ] Status workflow implemented
- [ ] Revision system working
- [ ] Calculations correct
- [ ] Unit tests (80%+ coverage)
- [ ] Feature tests
- [ ] Manual testing
- [ ] Code review passed
- [ ] Merged to main

---

## Notes

- Quotation number format: QT-YYYY-NNNN (e.g., QT-2026-0001)
- Revision format: QT-YYYY-NNNN-R01, R02, etc.
- Valid until default: +30 days dari quotation date
- Tax default: PPN 11%
- Convert to Sales Order: Implemented in Story 8.3
- Email notification: TODO (need email service)
- PDF export: TODO (need PDF library)
- Expiry job: Run daily via Laravel Scheduler
