<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CashAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CashAccountController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $cashAccounts = CashAccount::with('account')
            ->when($search !== '', function ($q) use ($search) {
                $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
                $q->where(function ($sub) use ($escaped) {
                    $sub->where('code', 'like', "%{$escaped}%")
                        ->orWhere('name', 'like', "%{$escaped}%")
                        ->orWhereHas('account', fn ($account) => $account->where('name', 'like', "%{$escaped}%"));
                });
            })
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('code')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('CashAccounts/Index', [
            'cashAccounts' => $cashAccounts,
            'filters' => [
                'search' => $search,
                'is_active' => $request->input('is_active', ''),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('CashAccounts/Create', [
            'accounts' => $this->availableAccounts(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateCashAccount($request);
        $data['created_by'] = Auth::id();

        $cashAccount = CashAccount::create($data);

        return redirect()->route('cash-accounts.show', $cashAccount)->with('success', 'Cash account created.');
    }

    public function show(CashAccount $cashAccount)
    {
        $cashAccount->load('account', 'creator', 'updater');

        return Inertia::render('CashAccounts/Show', [
            'cashAccount' => $cashAccount,
        ]);
    }

    public function edit(CashAccount $cashAccount)
    {
        return Inertia::render('CashAccounts/Edit', [
            'cashAccount' => $cashAccount->load('account'),
            'accounts' => $this->availableAccounts(),
        ]);
    }

    public function update(Request $request, CashAccount $cashAccount)
    {
        $data = $this->validateCashAccount($request, $cashAccount);
        $data['updated_by'] = Auth::id();

        $cashAccount->update($data);

        return redirect()->route('cash-accounts.show', $cashAccount)->with('success', 'Cash account updated.');
    }

    public function destroy(CashAccount $cashAccount)
    {
        $cashAccount->delete();

        return redirect()->route('cash-accounts.index')->with('success', 'Cash account deleted.');
    }

    private function validateCashAccount(Request $request, ?CashAccount $cashAccount = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('cash_accounts', 'code')->ignore($cashAccount?->id)],
            'name' => ['required', 'string', 'max:255'],
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
