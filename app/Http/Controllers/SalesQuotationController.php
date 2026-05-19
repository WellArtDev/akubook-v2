<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Item;
use App\Models\SalesOrder;
use App\Models\SalesQuotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SalesQuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesQuotation::with(['customer', 'salesPerson']);

        if ($request->filled('status')) {
            $statuses = is_array($request->status) ? $request->status : explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('sales_person_id')) {
            $query->where('sales_person_id', $request->sales_person_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('quotation_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('quotation_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('quotation_number', 'ilike', '%' . $request->search . '%')
                    ->orWhereHas('customer', function ($sq) use ($request) {
                        $sq->where('name', 'ilike', '%' . $request->search . '%');
                    });
            });
        }

        $sort = in_array($request->get('sort'), ['quotation_number', 'quotation_date', 'valid_until', 'status', 'grand_total'], true)
            ? $request->get('sort')
            : 'quotation_date';
        $direction = $request->get('direction') === 'asc' ? 'asc' : 'desc';

        return Inertia::render('SalesQuotations/Index', [
            'quotations' => $query->orderBy($sort, $direction)->paginate(50)->withQueryString(),
            'filters' => $request->only(['status', 'customer_id', 'sales_person_id', 'date_from', 'date_to', 'search', 'sort', 'direction']),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function create()
    {
        return Inertia::render('SalesQuotations/Create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateQuotation($request);

        return DB::transaction(function () use ($validated) {
            $quotation = SalesQuotation::create([
                ...$this->headerPayload($validated),
                'quotation_number' => SalesQuotation::generateQuotationNumber(),
                'sales_person_id' => Auth::id(),
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            $this->syncLines($quotation, $validated['lines']);
            $quotation->calculateTotals();
            $quotation->save();

            return redirect()->route('sales-quotations.show', $quotation)->with('success', 'Sales quotation created successfully');
        });
    }

    public function show(SalesQuotation $salesQuotation)
    {
        $salesQuotation->load(['customer', 'customerContact', 'salesPerson', 'lines.item', 'originalQuotation', 'revisions', 'convertedSalesOrder', 'createdBy']);

        return Inertia::render('SalesQuotations/Show', [
            'quotation' => $salesQuotation,
        ]);
    }

    public function edit(SalesQuotation $salesQuotation)
    {
        if (!$salesQuotation->can_edit) {
            return back()->withErrors(['error' => 'Only draft quotations can be edited']);
        }

        $salesQuotation->load('lines.item');

        return Inertia::render('SalesQuotations/Edit', [
            ...$this->formData(),
            'quotation' => $salesQuotation,
        ]);
    }

    public function update(Request $request, SalesQuotation $salesQuotation)
    {
        if (!$salesQuotation->can_edit) {
            return back()->withErrors(['error' => 'Only draft quotations can be updated']);
        }

        $validated = $this->validateQuotation($request);

        return DB::transaction(function () use ($salesQuotation, $validated) {
            $salesQuotation->update([
                ...$this->headerPayload($validated),
                'updated_by' => Auth::id(),
            ]);
            $salesQuotation->lines()->delete();
            $this->syncLines($salesQuotation, $validated['lines']);
            $salesQuotation->calculateTotals();
            $salesQuotation->save();

            return redirect()->route('sales-quotations.show', $salesQuotation)->with('success', 'Sales quotation updated successfully');
        });
    }

    public function destroy(SalesQuotation $salesQuotation)
    {
        if (!$salesQuotation->can_edit) {
            return back()->withErrors(['error' => 'Only draft quotations can be deleted']);
        }

        $salesQuotation->delete();

        return redirect()->route('sales-quotations.index')->with('success', 'Sales quotation deleted successfully');
    }

    public function send(SalesQuotation $salesQuotation)
    {
        if (!$salesQuotation->can_send) {
            return back()->withErrors(['error' => 'Only draft quotations with lines can be sent']);
        }

        if (!$salesQuotation->customerContact?->email) {
            return back()->withErrors(['error' => 'Customer contact email is required']);
        }

        $salesQuotation->update(['status' => 'sent', 'sent_at' => now(), 'updated_by' => Auth::id()]);

        return back()->with('success', 'Sales quotation sent successfully');
    }

    public function approve(SalesQuotation $salesQuotation)
    {
        if ($salesQuotation->status !== 'sent') {
            return back()->withErrors(['error' => 'Only sent quotations can be approved']);
        }

        $salesQuotation->update(['status' => 'approved', 'approved_at' => now(), 'updated_by' => Auth::id()]);

        return back()->with('success', 'Sales quotation approved successfully');
    }

    public function reject(SalesQuotation $salesQuotation)
    {
        if ($salesQuotation->status !== 'sent') {
            return back()->withErrors(['error' => 'Only sent quotations can be rejected']);
        }

        $salesQuotation->update(['status' => 'rejected', 'rejected_at' => now(), 'updated_by' => Auth::id()]);

        return back()->with('success', 'Sales quotation rejected successfully');
    }

    public function revise(SalesQuotation $salesQuotation)
    {
        if (!$salesQuotation->can_revise) {
            return back()->withErrors(['error' => 'Only sent or rejected quotations can be revised']);
        }

        return DB::transaction(function () use ($salesQuotation) {
            $salesQuotation->load('lines');
            $baseQuotation = $salesQuotation->originalQuotation ?: $salesQuotation;
            $revisionNumber = $baseQuotation->revisions()->max('revision_number') + 1;
            $revision = $salesQuotation->replicate(['quotation_number', 'status', 'sent_at', 'approved_at', 'rejected_at', 'expired_at', 'converted_at', 'converted_to_sales_order_id']);
            $revision->quotation_number = $baseQuotation->quotation_number . '-R' . str_pad((string) $revisionNumber, 2, '0', STR_PAD_LEFT);
            $revision->status = 'draft';
            $revision->original_quotation_id = $baseQuotation->id;
            $revision->revision_number = $revisionNumber;
            $revision->created_by = Auth::id();
            $revision->updated_by = null;
            $revision->save();

            foreach ($salesQuotation->lines as $line) {
                $revision->lines()->create($line->only(['line_number', 'item_id', 'description', 'quantity', 'unit', 'unit_price', 'discount_percentage', 'discount_amount', 'tax_percentage', 'tax_amount', 'line_total', 'notes']));
            }

            $salesQuotation->update(['status' => 'revised', 'updated_by' => Auth::id()]);

            return redirect()->route('sales-quotations.edit', $revision)->with('success', 'Sales quotation revision created successfully');
        });
    }

    public function duplicate(SalesQuotation $salesQuotation)
    {
        return DB::transaction(function () use ($salesQuotation) {
            $salesQuotation->load('lines');
            $clone = $salesQuotation->replicate(['quotation_number', 'status', 'sent_at', 'approved_at', 'rejected_at', 'expired_at', 'converted_at', 'converted_to_sales_order_id']);
            $clone->quotation_number = SalesQuotation::generateQuotationNumber();
            $clone->quotation_date = now()->toDateString();
            $clone->valid_until = now()->addDays(30)->toDateString();
            $clone->status = 'draft';
            $clone->created_by = Auth::id();
            $clone->updated_by = null;
            $clone->save();

            foreach ($salesQuotation->lines as $line) {
                $clone->lines()->create($line->only(['line_number', 'item_id', 'description', 'quantity', 'unit', 'unit_price', 'discount_percentage', 'discount_amount', 'tax_percentage', 'tax_amount', 'line_total', 'notes']));
            }

            return redirect()->route('sales-quotations.edit', $clone)->with('success', 'Sales quotation cloned successfully');
        });
    }

    public function convert(SalesQuotation $salesQuotation)
    {
        if (!$salesQuotation->can_convert) {
            return back()->withErrors(['error' => 'Only approved quotations can be converted']);
        }

        return DB::transaction(function () use ($salesQuotation) {
            $salesQuotation->load('lines');
            $salesOrder = SalesOrder::create([
                'sales_quotation_id' => $salesQuotation->id,
                'so_number' => $this->generateSalesOrderNumber(),
                'so_date' => now()->toDateString(),
                'customer_id' => $salesQuotation->customer_id,
                'customer_po_number' => $salesQuotation->reference,
                'sales_person_id' => $salesQuotation->sales_person_id,
                'payment_terms' => $salesQuotation->payment_terms,
                'delivery_terms' => $salesQuotation->delivery_terms,
                'notes' => $salesQuotation->notes,
                'status' => 'draft',
                'subtotal' => $salesQuotation->subtotal,
                'discount_amount' => $salesQuotation->discount_amount,
                'tax_amount' => $salesQuotation->tax_amount,
                'grand_total' => $salesQuotation->grand_total,
                'total_amount' => $salesQuotation->grand_total,
                'created_by' => Auth::id(),
            ]);

            foreach ($salesQuotation->lines as $line) {
                $salesOrder->lines()->create([
                    'line_number' => $line->line_number,
                    'item_id' => $line->item_id,
                    'description' => $line->description,
                    'quantity' => $line->quantity,
                    'unit' => $line->unit,
                    'unit_price' => $line->unit_price,
                    'discount_percent' => $line->discount_percentage,
                    'discount_amount' => $line->discount_amount,
                    'tax_amount' => $line->tax_amount,
                    'line_total' => $line->line_total,
                ]);
            }

            $salesQuotation->update([
                'status' => 'converted',
                'converted_to_sales_order_id' => $salesOrder->id,
                'converted_at' => now(),
                'updated_by' => Auth::id(),
            ]);

            return redirect()->route('sales-orders.edit', $salesOrder)->with('success', 'Sales quotation converted to sales order successfully');
        });
    }

    private function validateQuotation(Request $request): array
    {
        return $request->validate([
            'quotation_date' => 'required|date',
            'valid_until' => 'required|date|after:quotation_date',
            'customer_id' => 'required|exists:customers,id',
            'customer_contact_id' => 'nullable|exists:customer_contacts,id',
            'reference' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string|max:50',
            'delivery_terms' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'discount_type' => 'required|in:percentage,amount',
            'discount_value' => 'nullable|numeric|min:0',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.description' => 'nullable|string',
            'lines.*.quantity' => 'required|numeric|min:0.001',
            'lines.*.unit' => 'required|string|max:20',
            'lines.*.unit_price' => 'required|numeric|min:0.01',
            'lines.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'lines.*.discount_amount' => 'nullable|numeric|min:0',
            'lines.*.tax_percentage' => 'nullable|numeric|min:0',
            'lines.*.notes' => 'nullable|string',
        ]);
    }

    private function headerPayload(array $validated): array
    {
        return collect($validated)->only(['quotation_date', 'valid_until', 'customer_id', 'customer_contact_id', 'reference', 'payment_terms', 'delivery_terms', 'notes', 'discount_type', 'discount_value'])->toArray();
    }

    private function syncLines(SalesQuotation $quotation, array $lines): void
    {
        foreach ($lines as $index => $lineData) {
            $line = $quotation->lines()->make([
                'line_number' => $index + 1,
                'item_id' => $lineData['item_id'],
                'description' => $lineData['description'] ?? null,
                'quantity' => $lineData['quantity'],
                'unit' => $lineData['unit'],
                'unit_price' => $lineData['unit_price'],
                'discount_percentage' => $lineData['discount_percentage'] ?? 0,
                'discount_amount' => $lineData['discount_amount'] ?? 0,
                'tax_percentage' => $lineData['tax_percentage'] ?? 11,
                'notes' => $lineData['notes'] ?? null,
            ]);
            $line->calculateLineTotal();
            $line->save();
        }
    }

    private function formData(): array
    {
        return [
            'customers' => Customer::with('contacts')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'code', 'payment_terms']),
            'items' => Item::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code', 'description', 'unit', 'selling_price']),
        ];
    }

    private function generateSalesOrderNumber(): string
    {
        $year = date('Y');
        $lastSO = SalesOrder::where('so_number', 'like', "SO-{$year}-%")->orderBy('so_number', 'desc')->first();
        $nextNumber = $lastSO ? ((int) substr($lastSO->so_number, -4)) + 1 : 1;

        return sprintf('SO-%s-%04d', $year, $nextNumber);
    }
}
