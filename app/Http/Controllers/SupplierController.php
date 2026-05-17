<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers
     */
    public function index(Request $request)
    {
        $query = Supplier::with(['contacts', 'addresses'])
            ->orderBy('created_at', 'desc');

        // Search filter
        if ($request->has('search')) {
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('supplier_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Tax type filter
        if ($request->has('tax_type') && $request->tax_type) {
            $query->where('tax_type', $request->tax_type);
        }

        $suppliers = $query->paginate(15)->withQueryString();

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters' => $request->only(['search', 'category', 'tax_type']),
        ]);
    }

    /**
     * Show the form for creating a new supplier
     */
    public function create()
    {
        return Inertia::render('Suppliers/Create');
    }

    /**
     * Store a newly created supplier
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
            'tax_type' => 'required|in:pkp,non_pkp',
            'payment_terms' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.position' => 'nullable|string|max:100',
            'contacts.*.phone' => 'required|string|max:50',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.is_primary' => 'boolean',
            'addresses' => 'nullable|array',
            'addresses.*.address_type' => 'required|in:billing,shipping,both',
            'addresses.*.street_address' => 'required|string',
            'addresses.*.city' => 'required|string|max:100',
            'addresses.*.province' => 'required|string|max:100',
            'addresses.*.postal_code' => 'nullable|string|max:20',
            'addresses.*.country' => 'nullable|string|max:100',
            'addresses.*.is_default' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $supplier = Supplier::create([
                ...$validated,
                'created_by' => auth()->id(),
            ]);

            // Create contacts
            if (!empty($validated['contacts'])) {
                foreach ($validated['contacts'] as $contact) {
                    $supplier->contacts()->create($contact);
                }
            }

            // Create addresses
            if (!empty($validated['addresses'])) {
                foreach ($validated['addresses'] as $address) {
                    $supplier->addresses()->create($address);
                }
            }

            DB::commit();

            return redirect()->route('suppliers.index')
                ->with('success', 'Supplier created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create supplier: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified supplier
     */
    public function show(Supplier $supplier)
    {
        $supplier->load(['contacts', 'addresses', 'creator', 'updater']);

        return Inertia::render('Suppliers/Show', [
            'supplier' => $supplier,
        ]);
    }

    /**
     * Show the form for editing the specified supplier
     */
    public function edit(Supplier $supplier)
    {
        $supplier->load(['contacts', 'addresses']);

        return Inertia::render('Suppliers/Edit', [
            'supplier' => $supplier,
        ]);
    }

    /**
     * Update the specified supplier
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
            'tax_type' => 'required|in:pkp,non_pkp',
            'payment_terms' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'contacts' => 'nullable|array',
            'contacts.*.id' => 'nullable|exists:supplier_contacts,id',
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.position' => 'nullable|string|max:100',
            'contacts.*.phone' => 'required|string|max:50',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.is_primary' => 'boolean',
            'addresses' => 'nullable|array',
            'addresses.*.id' => 'nullable|exists:supplier_addresses,id',
            'addresses.*.address_type' => 'required|in:billing,shipping,both',
            'addresses.*.street_address' => 'required|string',
            'addresses.*.city' => 'required|string|max:100',
            'addresses.*.province' => 'required|string|max:100',
            'addresses.*.postal_code' => 'nullable|string|max:20',
            'addresses.*.country' => 'nullable|string|max:100',
            'addresses.*.is_default' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $supplier->update([
                ...$validated,
                'updated_by' => auth()->id(),
            ]);

            // Update contacts
            if (isset($validated['contacts'])) {
                $contactIds = [];
                foreach ($validated['contacts'] as $contactData) {
                    if (isset($contactData['id'])) {
                        $contact = $supplier->contacts()->find($contactData['id']);
                        $contact->update($contactData);
                        $contactIds[] = $contact->id;
                    } else {
                        $contact = $supplier->contacts()->create($contactData);
                        $contactIds[] = $contact->id;
                    }
                }
                // Delete removed contacts
                $supplier->contacts()->whereNotIn('id', $contactIds)->delete();
            }

            // Update addresses
            if (isset($validated['addresses'])) {
                $addressIds = [];
                foreach ($validated['addresses'] as $addressData) {
                    if (isset($addressData['id'])) {
                        $address = $supplier->addresses()->find($addressData['id']);
                        $address->update($addressData);
                        $addressIds[] = $address->id;
                    } else {
                        $address = $supplier->addresses()->create($addressData);
                        $addressIds[] = $address->id;
                    }
                }
                // Delete removed addresses
                $supplier->addresses()->whereNotIn('id', $addressIds)->delete();
            }

            DB::commit();

            return redirect()->route('suppliers.index')
                ->with('success', 'Supplier updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update supplier: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified supplier (soft delete)
     */
    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();

            return redirect()->route('suppliers.index')
                ->with('success', 'Supplier deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete supplier: ' . $e->getMessage()]);
        }
    }
}
