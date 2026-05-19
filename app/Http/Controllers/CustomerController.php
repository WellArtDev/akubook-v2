<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with(['contacts', 'addresses'])->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('credit_status')) {
            match ($request->credit_status) {
                'exceeded' => $query->whereRaw('outstanding_balance > credit_limit'),
                'warning' => $query->whereRaw('outstanding_balance <= credit_limit AND (credit_limit - outstanding_balance) < (credit_limit * 0.2)'),
                'good' => $query->whereRaw('(credit_limit = 0 AND outstanding_balance = 0) OR (credit_limit - outstanding_balance) >= (credit_limit * 0.2)'),
                default => null,
            };
        }

        if ($request->filled('sort')) {
            match ($request->sort) {
                'code', 'name', 'outstanding_balance' => $query->orderBy($request->sort),
                default => null,
            };
        }

        return Inertia::render('Customers/Index', [
            'customers' => $query->paginate(50)->withQueryString(),
            'filters' => $request->only(['search', 'category', 'credit_status', 'sort']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Customers/Create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $validated = $request->validated();
        $contacts = $validated['contacts'];
        $addresses = $validated['addresses'];
        unset($validated['contacts'], $validated['addresses']);

        DB::transaction(function () use ($validated, $contacts, $addresses) {
            $customer = Customer::create([
                ...$validated,
                'created_by' => auth()->id(),
            ]);

            foreach ($contacts as $contact) {
                $customer->contacts()->create($contact);
            }

            foreach ($addresses as $address) {
                $customer->addresses()->create($address);
            }
        });

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'contacts',
            'addresses',
            'salesOrders' => fn ($q) => $q->latest()->limit(10),
            'salesInvoices' => fn ($q) => $q->latest()->limit(10),
            'payments' => fn ($q) => $q->latest()->limit(10),
        ]);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
        ]);
    }

    public function edit(Customer $customer)
    {
        $customer->load(['contacts', 'addresses']);

        return Inertia::render('Customers/Create', [
            'customer' => $customer,
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $validated = $request->validated();
        $contacts = $validated['contacts'];
        $addresses = $validated['addresses'];
        unset($validated['contacts'], $validated['addresses']);

        DB::transaction(function () use ($customer, $validated, $contacts, $addresses) {
            $customer->update([
                ...$validated,
                'updated_by' => auth()->id(),
            ]);

            $contactIds = [];
            foreach ($contacts as $contactData) {
                $contact = isset($contactData['id'])
                    ? $customer->contacts()->findOrFail($contactData['id'])
                    : $customer->contacts()->make();
                $contact->fill($contactData)->save();
                $contactIds[] = $contact->id;
            }
            $customer->contacts()->whereNotIn('id', $contactIds)->delete();

            $addressIds = [];
            foreach ($addresses as $addressData) {
                $address = isset($addressData['id'])
                    ? $customer->addresses()->findOrFail($addressData['id'])
                    : $customer->addresses()->make();
                $address->fill($addressData)->save();
                $addressIds[] = $address->id;
            }
            $customer->addresses()->whereNotIn('id', $addressIds)->delete();
        });

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->salesOrders()->exists() || $customer->salesInvoices()->exists()) {
            return back()->with('error', 'Cannot delete customer with existing transactions.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
