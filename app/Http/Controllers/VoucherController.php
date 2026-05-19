<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\CashAccount;
use App\Models\Voucher;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class VoucherController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function index(Request $request)
    {
        $vouchers = Voucher::query()
            ->with(['counterpartAccount', 'journalEntry'])
            ->when($request->filled('voucher_type'), fn ($query) => $query->where('voucher_type', $request->voucher_type))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('voucher_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('voucher_date', '<=', $request->date_to))
            ->when($request->filled('cash_bank_type'), fn ($query) => $query->where('cash_bank_type', $request->cash_bank_type))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
                $query->where('voucher_number', 'like', "%{$search}%");
            })
            ->latest('voucher_date')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Vouchers/Index', [
            'vouchers' => $vouchers,
            'filters' => $request->only(['voucher_type', 'status', 'date_from', 'date_to', 'cash_bank_type', 'search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Vouchers/Create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateVoucher($request);

        $voucher = Voucher::query()->create($validated + [
            'voucher_number' => Voucher::generateNumber($validated['voucher_type']),
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('vouchers.show', $voucher)->with('success', 'Voucher created.');
    }

    public function show(Voucher $voucher)
    {
        $voucher->load(['counterpartAccount', 'journalEntry.lines.account', 'cashAccount', 'bankAccount']);

        return Inertia::render('Vouchers/Show', [
            'voucher' => $voucher,
            'cashBankAccount' => $voucher->cash_bank_type === 'cash' ? $voucher->cashAccount : $voucher->bankAccount,
        ]);
    }

    public function post(Voucher $voucher)
    {
        if (! $voucher->post()) {
            return back()->withErrors(['error' => 'Only draft vouchers can be posted.']);
        }

        return redirect()->route('vouchers.show', $voucher)->with('success', 'Voucher posted.');
    }

    public function cancel(Request $request, Voucher $voucher)
    {
        if (! in_array($voucher->status, ['draft', 'posted'], true)) {
            return back()->withErrors(['error' => 'Voucher cannot be cancelled.']);
        }

        $oldValues = $voucher->only(['status', 'cancelled_by', 'cancelled_at']);

        $voucher->update([
            'status' => 'cancelled',
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now(),
            'updated_by' => Auth::id(),
        ]);

        $this->auditLogger->log(
            eventKey: 'voucher.cancelled',
            entityType: 'voucher',
            entityId: $voucher->id,
            action: 'cancel',
            actorUserId: Auth::id(),
            oldValues: $oldValues,
            newValues: $voucher->only(['status', 'cancelled_by', 'cancelled_at']),
            metadata: ['voucher_number' => $voucher->voucher_number],
            request: $request,
            isSensitive: true,
            sensitivityLevel: 'high',
            sensitivityReason: 'financial_cancellation'
        );

        return redirect()->route('vouchers.show', $voucher)->with('success', 'Voucher cancelled.');
    }

    public function destroy(Request $request, Voucher $voucher)
    {
        if ($voucher->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft vouchers can be deleted.']);
        }

        $snapshot = $voucher->only(['voucher_number', 'voucher_type', 'voucher_date', 'amount', 'status']);
        $voucher->delete();

        $this->auditLogger->log(
            eventKey: 'voucher.deleted',
            entityType: 'voucher',
            entityId: $voucher->id,
            action: 'delete',
            actorUserId: Auth::id(),
            oldValues: $snapshot,
            newValues: null,
            metadata: ['voucher_number' => $snapshot['voucher_number'] ?? null],
            request: $request,
            isSensitive: true,
            sensitivityLevel: 'high',
            sensitivityReason: 'financial_deletion'
        );

        return redirect()->route('vouchers.index')->with('success', 'Voucher deleted.');
    }

    private function validateVoucher(Request $request): array
    {
        $validated = $request->validate([
            'voucher_type' => ['required', Rule::in(['payment', 'receipt'])],
            'voucher_date' => ['required', 'date'],
            'cash_bank_type' => ['required', Rule::in(['cash', 'bank'])],
            'cash_bank_account_id' => ['required', 'integer'],
            'counterpart_account_id' => ['required', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $accountExists = $validated['cash_bank_type'] === 'cash'
            ? CashAccount::query()->whereKey($validated['cash_bank_account_id'])->where('is_active', true)->exists()
            : BankAccount::query()->whereKey($validated['cash_bank_account_id'])->where('is_active', true)->exists();

        if (! $accountExists) {
            abort(422, 'Invalid cash/bank account.');
        }

        return $validated;
    }

    private function formData(): array
    {
        return [
            'cashAccounts' => CashAccount::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name', 'account_id']),
            'bankAccounts' => BankAccount::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name', 'bank_name', 'account_id']),
            'counterpartAccounts' => Account::query()
                ->where('is_active', true)
                ->where('is_header', false)
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'type', 'category']),
        ];
    }
}
