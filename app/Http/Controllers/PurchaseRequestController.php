<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseRequest::with(['department', 'createdBy', 'approvedBy'])
            ->orderBy('pr_date', 'desc');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('pr_number', 'like', "%{$search}%")
                  ->orWhere('justification', 'like', "%{$search}%");
            });
        }

        $purchaseRequests = $query->paginate(15);

        return Inertia::render('PurchaseRequests/Index', [
            'purchaseRequests' => $purchaseRequests,
            'filters' => $request->only(['status', 'department_id', 'search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('PurchaseRequests/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pr_date' => 'required|date',
            'department_id' => 'required|exists:departments,id',
            'required_date' => 'required|date|after_or_equal:pr_date',
            'justification' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_code' => 'nullable|string|max:50',
            'lines.*.product_name' => 'required|string|max:255',
            'lines.*.description' => 'nullable|string',
            'lines.*.quantity' => 'required|numeric|min:0.001',
            'lines.*.unit' => 'required|string|max:20',
            'lines.*.estimated_price' => 'required|numeric|min:0',
            'lines.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $pr = PurchaseRequest::create([
                'pr_number' => PurchaseRequest::generatePRNumber(),
                'pr_date' => $validated['pr_date'],
                'department_id' => $validated['department_id'],
                'required_date' => $validated['required_date'],
                'justification' => $validated['justification'] ?? null,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['lines'] as $index => $lineData) {
                PurchaseRequestLine::create([
                    'purchase_request_id' => $pr->id,
                    'line_number' => $index + 1,
                    'product_code' => $lineData['product_code'] ?? null,
                    'product_name' => $lineData['product_name'],
                    'description' => $lineData['description'] ?? null,
                    'quantity' => $lineData['quantity'],
                    'unit' => $lineData['unit'],
                    'estimated_price' => $lineData['estimated_price'],
                    'notes' => $lineData['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('purchase-requests.show', $pr->id)
                ->with('success', 'Purchase Request created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create Purchase Request: ' . $e->getMessage()]);
        }
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->load(['department', 'lines', 'createdBy', 'approvedBy', 'updatedBy']);

        return Inertia::render('PurchaseRequests/Show', [
            'purchaseRequest' => $purchaseRequest,
        ]);
    }

    public function edit(PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft PRs can be edited']);
        }

        $purchaseRequest->load('lines');

        return Inertia::render('PurchaseRequests/Edit', [
            'purchaseRequest' => $purchaseRequest,
        ]);
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft PRs can be updated']);
        }

        $validated = $request->validate([
            'pr_date' => 'required|date',
            'department_id' => 'required|exists:departments,id',
            'required_date' => 'required|date|after_or_equal:pr_date',
            'justification' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_code' => 'nullable|string|max:50',
            'lines.*.product_name' => 'required|string|max:255',
            'lines.*.description' => 'nullable|string',
            'lines.*.quantity' => 'required|numeric|min:0.001',
            'lines.*.unit' => 'required|string|max:20',
            'lines.*.estimated_price' => 'required|numeric|min:0',
            'lines.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $purchaseRequest->update([
                'pr_date' => $validated['pr_date'],
                'department_id' => $validated['department_id'],
                'required_date' => $validated['required_date'],
                'justification' => $validated['justification'] ?? null,
                'updated_by' => auth()->id(),
            ]);

            // Delete existing lines and recreate
            $purchaseRequest->lines()->delete();

            foreach ($validated['lines'] as $index => $lineData) {
                PurchaseRequestLine::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'line_number' => $index + 1,
                    'product_code' => $lineData['product_code'] ?? null,
                    'product_name' => $lineData['product_name'],
                    'description' => $lineData['description'] ?? null,
                    'quantity' => $lineData['quantity'],
                    'unit' => $lineData['unit'],
                    'estimated_price' => $lineData['estimated_price'],
                    'notes' => $lineData['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('purchase-requests.show', $purchaseRequest->id)
                ->with('success', 'Purchase Request updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update Purchase Request: ' . $e->getMessage()]);
        }
    }

    public function destroy(PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft PRs can be deleted']);
        }

        $purchaseRequest->delete();

        return redirect()->route('purchase-requests.index')
            ->with('success', 'Purchase Request deleted successfully');
    }

    public function submit(PurchaseRequest $purchaseRequest)
    {
        try {
            $purchaseRequest->submit();

            return back()->with('success', 'Purchase Request submitted for approval');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function approve(PurchaseRequest $purchaseRequest)
    {
        try {
            $purchaseRequest->approve(auth()->id());

            return back()->with('success', 'Purchase Request approved');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(PurchaseRequest $purchaseRequest)
    {
        try {
            $purchaseRequest->reject(auth()->id());

            return back()->with('success', 'Purchase Request rejected');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancel(PurchaseRequest $purchaseRequest)
    {
        try {
            $purchaseRequest->cancel();

            return back()->with('success', 'Purchase Request cancelled');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
