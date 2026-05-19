<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankReconciliationLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BankReconciliationController extends Controller
{
    public function index(Request $request)
    {
        $reconciliations = BankReconciliation::with('bankAccount')
            ->when($request->filled('bank_account_id'), fn ($q) => $q->where('bank_account_id', $request->integer('bank_account_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('statement_start_date', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('statement_end_date', '<=', $request->date('date_to')))
            ->orderByDesc('statement_end_date')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('BankReconciliations/Index', [
            'reconciliations' => $reconciliations,
            'bankAccounts' => $this->bankAccounts(),
            'filters' => $request->only('bank_account_id', 'status', 'date_from', 'date_to'),
        ]);
    }

    public function create()
    {
        return Inertia::render('BankReconciliations/Create', [
            'bankAccounts' => $this->bankAccounts(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateReconciliation($request);

        $reconciliation = DB::transaction(function () use ($data) {
            $lines = $data['lines'];
            unset($data['lines']);

            $reconciliation = BankReconciliation::create($data + [
                'reconciliation_number' => BankReconciliation::generateNumber(),
                'created_by' => Auth::id(),
                'status' => 'draft',
            ]);

            $this->syncLines($reconciliation, $lines);
            $reconciliation->load('lines');
            $reconciliation->recalculate();
            $reconciliation->save();

            return $reconciliation;
        });

        return redirect()->route('bank-reconciliations.show', $reconciliation)->with('success', 'Bank reconciliation created.');
    }

    public function show(BankReconciliation $bankReconciliation)
    {
        $bankReconciliation->load('bankAccount', 'lines.matcher', 'creator', 'reconciler');
        $bankReconciliation->recalculate();
        $bankReconciliation->save();

        return Inertia::render('BankReconciliations/Show', [
            'reconciliation' => $bankReconciliation,
        ]);
    }

    public function matchLine(Request $request, BankReconciliationLine $bankReconciliationLine)
    {
        $data = $request->validate([
            'matched_reference_type' => ['required', 'string', 'max:100'],
            'matched_reference_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $bankReconciliationLine->update($data + [
            'is_matched' => true,
            'matched_at' => now(),
            'matched_by' => Auth::id(),
        ]);

        $reconciliation = $bankReconciliationLine->reconciliation;
        $reconciliation->load('lines');
        $reconciliation->recalculate();
        $reconciliation->save();

        return back()->with('success', 'Statement line matched.');
    }

    public function unmatchLine(BankReconciliationLine $bankReconciliationLine)
    {
        $bankReconciliationLine->update([
            'is_matched' => false,
            'matched_reference_type' => null,
            'matched_reference_id' => null,
            'matched_at' => null,
            'matched_by' => null,
        ]);

        $reconciliation = $bankReconciliationLine->reconciliation;
        $reconciliation->load('lines');
        $reconciliation->recalculate();
        $reconciliation->save();

        return back()->with('success', 'Statement line unmatched.');
    }

    public function reconcile(BankReconciliation $bankReconciliation)
    {
        if ($bankReconciliation->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft reconciliations can be reconciled.']);
        }

        $bankReconciliation->load('lines');
        $bankReconciliation->recalculate();
        $bankReconciliation->update([
            'status' => 'reconciled',
            'reconciled_by' => Auth::id(),
            'reconciled_at' => now(),
            'matched_debit_total' => $bankReconciliation->matched_debit_total,
            'matched_credit_total' => $bankReconciliation->matched_credit_total,
            'system_balance' => $bankReconciliation->system_balance,
            'difference' => $bankReconciliation->difference,
        ]);

        return redirect()->route('bank-reconciliations.show', $bankReconciliation)->with('success', 'Bank reconciliation completed.');
    }

    private function validateReconciliation(Request $request): array
    {
        return $request->validate([
            'bank_account_id' => ['required', 'exists:bank_accounts,id'],
            'statement_start_date' => ['required', 'date'],
            'statement_end_date' => ['required', 'date', 'after_or_equal:statement_start_date'],
            'reconciliation_date' => ['required', 'date'],
            'statement_opening_balance' => ['required', 'numeric'],
            'statement_closing_balance' => ['required', 'numeric'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.transaction_date' => ['required', 'date'],
            'lines.*.description' => ['required', 'string', 'max:255'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.reference_number' => ['nullable', 'string', 'max:100'],
            'lines.*.notes' => ['nullable', 'string'],
        ]);
    }

    private function syncLines(BankReconciliation $reconciliation, array $lines): void
    {
        foreach ($lines as $index => $line) {
            $reconciliation->lines()->create([
                'line_number' => $index + 1,
                'transaction_date' => $line['transaction_date'],
                'description' => $line['description'],
                'debit' => $line['debit'] ?? 0,
                'credit' => $line['credit'] ?? 0,
                'reference_number' => $line['reference_number'] ?? null,
                'notes' => $line['notes'] ?? null,
            ]);
        }
    }

    private function bankAccounts()
    {
        return BankAccount::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'bank_name', 'account_number', 'opening_balance']);
    }
}
