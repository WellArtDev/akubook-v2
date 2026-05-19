<?php

namespace App\Http\Controllers;

use App\Models\AssetDepreciation;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AssetDepreciationJournalController extends Controller
{
    public function index(Request $request): Response
    {
        $period = (string) $request->input('period', now()->format('Y-m'));

        $rows = AssetDepreciation::query()
            ->with(['fixedAsset', 'journalEntry'])
            ->where('period', $period)
            ->orderBy('id')
            ->get()
            ->map(fn (AssetDepreciation $row) => [
                'id' => $row->id,
                'asset_code' => $row->fixedAsset?->asset_code,
                'asset_name' => $row->fixedAsset?->name,
                'monthly_depreciation' => $row->monthly_depreciation,
                'journal_entry_id' => $row->journal_entry_id,
                'journal_number' => $row->journalEntry?->journal_number,
                'journal_posted_at' => optional($row->journal_posted_at)->toDateTimeString(),
            ]);

        return Inertia::render('AssetDepreciationJournals/Index', [
            'period' => $period,
            'rows' => $rows,
            'summary' => [
                'asset_count' => $rows->count(),
                'total_depreciation' => $rows->sum('monthly_depreciation'),
                'posted_count' => $rows->whereNotNull('journal_entry_id')->count(),
            ],
        ]);
    }

    public function run(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period' => ['required', 'regex:/^\\d{4}-\\d{2}$/'],
        ]);

        $period = $validated['period'];

        $rows = AssetDepreciation::query()
            ->with('fixedAsset')
            ->where('period', $period)
            ->whereNull('journal_entry_id')
            ->get();

        if ($rows->isEmpty()) {
            return back()->with('success', 'Tidak ada depresiasi baru untuk diposting.');
        }

        $missingAccounts = $rows->first(function (AssetDepreciation $row) {
            return ! $row->fixedAsset?->depreciation_expense_account_id || ! $row->fixedAsset?->accumulated_depreciation_account_id;
        });

        if ($missingAccounts) {
            return back()->withErrors(['period' => 'Mapping akun depresiasi aset belum lengkap.']);
        }

        $fiscalPeriod = FiscalPeriod::query()
            ->whereDate('start_date', '<=', "{$period}-01")
            ->whereDate('end_date', '>=', "{$period}-01")
            ->where('status', 'open')
            ->first();

        if (! $fiscalPeriod) {
            return back()->withErrors(['period' => 'Fiscal period open tidak ditemukan untuk period ini.']);
        }

        DB::transaction(function () use ($rows, $period, $fiscalPeriod) {
            $total = (float) $rows->sum('monthly_depreciation');
            $journalNumber = $this->generateJournalNumber();

            $journal = JournalEntry::create([
                'journal_number' => $journalNumber,
                'journal_date' => "{$period}-01",
                'fiscal_period_id' => $fiscalPeriod->id,
                'type' => 'manual',
                'reference_type' => 'asset_depreciation',
                'reference_id' => null,
                'description' => "Depreciation Journal {$period}",
                'total_debit' => $total,
                'total_credit' => $total,
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => Auth::id(),
                'created_by' => Auth::id(),
            ]);

            foreach ($rows as $row) {
                $asset = $row->fixedAsset;
                if (! $asset) {
                    continue;
                }

                if (! $asset->depreciation_expense_account_id || ! $asset->accumulated_depreciation_account_id) {
                    continue;
                }

                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $asset->depreciation_expense_account_id,
                    'description' => "Depreciation expense {$asset->asset_code}",
                    'debit' => $row->monthly_depreciation,
                    'credit' => 0,
                ]);

                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $asset->accumulated_depreciation_account_id,
                    'description' => "Accumulated depreciation {$asset->asset_code}",
                    'debit' => 0,
                    'credit' => $row->monthly_depreciation,
                ]);

                $row->update([
                    'journal_entry_id' => $journal->id,
                    'journal_posted_at' => now(),
                ]);
            }
        });

        return redirect()->route('asset-depreciation-journals.index', ['period' => $period])
            ->with('success', 'Jurnal depresiasi berhasil diposting.');
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
