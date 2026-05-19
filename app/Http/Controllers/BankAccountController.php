<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BankAccountController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $bankAccounts = BankAccount::with('account')
            ->when($search !== '', function ($q) use ($search) {
                $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
                $q->where(function ($sub) use ($escaped) {
                    $sub->where('code', 'like', "%{$escaped}%")
                        ->orWhere('name', 'like', "%{$escaped}%")
                        ->orWhere('bank_name', 'like', "%{$escaped}%")
                        ->orWhere('account_number', 'like', "%{$escaped}%");
                });
            })
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('code')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('BankAccounts/Index', [
            'bankAccounts' => $bankAccounts,
            'filters' => [
                'search' => $search,
                'is_active' => $request->input('is_active', ''),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('BankAccounts/Create', [
            'accounts' => $this->availableAccounts(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateBankAccount($request);
        $data['created_by'] = Auth::id();

        $bankAccount = BankAccount::create($data);

        return redirect()->route('bank-accounts.show', $bankAccount)->with('success', 'Bank account created.');
    }

    public function show(BankAccount $bankAccount)
    {
        $bankAccount->load('account', 'creator', 'updater');

        return Inertia::render('BankAccounts/Show', [
            'bankAccount' => $bankAccount,
        ]);
    }

    public function edit(BankAccount $bankAccount)
    {
        return Inertia::render('BankAccounts/Edit', [
            'bankAccount' => $bankAccount->load('account'),
            'accounts' => $this->availableAccounts(),
        ]);
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $data = $this->validateBankAccount($request, $bankAccount);
        $data['updated_by'] = Auth::id();

        $bankAccount->update($data);

        return redirect()->route('bank-accounts.show', $bankAccount)->with('success', 'Bank account updated.');
    }

    public function destroy(BankAccount $bankAccount)
    {
        $bankAccount->delete();

        return redirect()->route('bank-accounts.index')->with('success', 'Bank account deleted.');
    }

    private function validateBankAccount(Request $request, ?BankAccount $bankAccount = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('bank_accounts', 'code')->ignore($bankAccount?->id)],
            'name' => ['required', 'string', 'max:255'],
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:100', Rule::unique('bank_accounts', 'account_number')->ignore($bankAccount?->id)],
            'account_holder' => ['required', 'string', 'max:255'],
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where(fn ($q) => $q->where('type', 'asset')->where('category', 'current_asset')->where('is_header', false)),
            ],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);
    }

    private function availableAccounts()
    {
        return Account::query()
            ->where('type', 'asset')
            ->where('category', 'current_asset')
            ->where('is_header', false)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);
    }
}
