<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AssetDepreciation;
use App\Models\AssetDisposal;
use App\Models\FiscalPeriod;
use App\Models\FixedAsset;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AssetDisposalController extends Controller
{
    public function index(Request $request): Response
    {
        $query = AssetDisposal::query()->with(['fixedAsset', 'journalEntry']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('date_from')) {
            $query->whereDate('disposal_date', '>=', $request->string('date_from')->toString());
        }

        if ($request->filled('date_to')) {
            $query->whereDate('disposal_date', '<=', $request->string('date_to')->toString());
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(function ($q) use ($search) {
                $q->where('disposal_number', 'like', "%{$search}%")
                    ->orWhereHas('fixedAsset', function ($assetQuery) use ($search) {
                        $assetQuery->where('asset_code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        return Inertia::render('AssetDisposals/Index', [
            'disposals' => $query->latest('disposal_date')->paginate(50)->withQueryString(),
            'filters' => $request->only(['status', 'date_from', 'date_to', 'search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('AssetDisposals/Create', [
            'assets' => FixedAsset::query()
                ->where('status', 'active')
                ->orderBy('asset_code')
                ->get(['id', 'asset_code', 'name', 'acquisition_cost']),
            'accounts' => $this->availableAccounts(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fixed_asset_id' => ['required', 'exists:fixed_assets,id'],
            'disposal_date' => ['required', 'date'],
            'proceeds_amount' => ['required', 'numeric', 'min:0'],
            'proceeds_account_id' => ['nullable', 'exists:accounts,id'],
            'gain_loss_account_id' => ['required', 'exists:accounts,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $asset = FixedAsset::query()->where('status', 'active')->findOrFail((int) $validated['fixed_asset_id']);

        if ((float) $validated['proceeds_amount'] > 0 && empty($validated['proceeds_account_id'])) {
            return back()->withErrors(['proceeds_account_id' => 'Akun kas/bank wajib jika nilai jual > 0.']);
        }

        $latestDep = AssetDepreciation::query()
            ->where('fixed_asset_id', $asset->id)
            ->orderByDesc('period')
            ->first();

        $accumulated = (float) ($latestDep?->accumulated_depreciation ?? 0);
        $bookValue = max((float) $asset->acquisition_cost - $accumulated, 0);

        AssetDisposal::create([
            'disposal_number' => AssetDisposal::generateNumber(),
            'disposal_date' => $validated['disposal_date'],
            'fixed_asset_id' => $asset->id,
            'acquisition_cost' => $asset->acquisition_cost,
            'accumulated_depreciation' => $accumulated,
            'book_value' => $bookValue,
            'proceeds_amount' => $validated['proceeds_amount'],
            'proceeds_account_id' => $validated['proceeds_account_id'] ?? null,
            'gain_loss_account_id' => $validated['gain_loss_account_id'],
            'status' => 'draft',
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('asset-disposals.index')->with('success', 'Asset disposal berhasil dibuat.');
    }

    public function show(AssetDisposal $assetDisposal): Response
    {
        $assetDisposal->load(['fixedAsset', 'proceedsAccount', 'gainLossAccount', 'journalEntry.lines.account']);

        $gainLoss = (float) $assetDisposal->proceeds_amount - (float) $assetDisposal->book_value;

        return Inertia::render('AssetDisposals/Show', [
            'disposal' => $assetDisposal,
            'gainLoss' => $gainLoss,
        ]);
    }

    public function post(AssetDisposal $assetDisposal): RedirectResponse
    {
        if ($assetDisposal->status !== 'draft') {
            return back()->withErrors(['status' => 'Disposal sudah diposting.']);
        }

        $fiscalPeriod = FiscalPeriod::query()
            ->whereDate('start_date', '<=', $assetDisposal->disposal_date)
            ->whereDate('end_date', '>=', $assetDisposal->disposal_date)
            ->where('status', 'open')
            ->first();

        if (! $fiscalPeriod) {
            return back()->withErrors(['status' => 'Fiscal period open tidak ditemukan.']);
        }

        $assetDisposal->load('fixedAsset');
        $asset = $assetDisposal->fixedAsset;

        if (! $asset?->asset_account_id || ! $asset->accumulated_depreciation_account_id) {
            return back()->withErrors(['status' => 'Mapping akun aset belum lengkap.']);
        }

        DB::transaction(function () use ($assetDisposal, $fiscalPeriod, $asset) {
            $proceeds = (float) $assetDisposal->proceeds_amount;
            $bookValue = (float) $assetDisposal->book_value;
            $acquisitionCost = (float) $assetDisposal->acquisition_cost;
            $accDep = (float) $assetDisposal->accumulated_depreciation;
            $gainLoss = $proceeds - $bookValue;

            $journal = JournalEntry::create([
                'journal_number' => $this->generateJournalNumber(),
                'journal_date' => $assetDisposal->disposal_date,
                'fiscal_period_id' => $fiscalPeriod->id,
                'type' => 'manual',
                'reference_type' => 'asset_disposal',
                'reference_id' => $assetDisposal->id,
                'description' => 'Asset Disposal ' . $assetDisposal->disposal_number,
                'total_debit' => $acquisitionCost,
                'total_credit' => $acquisitionCost,
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => Auth::id(),
                'created_by' => Auth::id(),
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $asset->accumulated_depreciation_account_id,
                'description' => 'Accumulated depreciation disposal ' . $asset->asset_code,
                'debit' => $accDep,
                'credit' => 0,
            ]);

            if ($proceeds > 0 && $assetDisposal->proceeds_account_id) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $assetDisposal->proceeds_account_id,
                    'description' => 'Proceeds disposal ' . $asset->asset_code,
                    'debit' => $proceeds,
                    'credit' => 0,
                ]);
            }

            if ($gainLoss < 0) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $assetDisposal->gain_loss_account_id,
                    'description' => 'Loss disposal ' . $asset->asset_code,
                    'debit' => abs($gainLoss),
                    'credit' => 0,
                ]);
            }

            JournalEntryLine::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $asset->asset_account_id,
                'description' => 'Asset disposal ' . $asset->asset_code,
                'debit' => 0,
                'credit' => $acquisitionCost,
            ]);

            if ($gainLoss > 0) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $assetDisposal->gain_loss_account_id,
                    'description' => 'Gain disposal ' . $asset->asset_code,
                    'debit' => 0,
                    'credit' => $gainLoss,
                ]);
            }

            $assetDisposal->update([
                'status' => 'posted',
                'journal_entry_id' => $journal->id,
                'posted_by' => Auth::id(),
                'posted_at' => now(),
            ]);

            $asset->update(['status' => 'disposed']);
        });

        return redirect()->route('asset-disposals.show', $assetDisposal)->with('success', 'Disposal berhasil diposting.');
    }

    private function availableAccounts()
    {
        return Account::query()
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);
    }

    private function generateJournalNumber(): string
    {
        $prefix = now()->format('Ymd');

        $last = JournalEntry::query()
            ->where('journal_number', 'like', "JV-{$prefix}-%")
            ->orderByDesc('journal_number')
            ->first();

        $next = $last ? ((int) substr($last->journal_number, -4)) + 1 : 1;

        return 'JV-' . $prefix . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
