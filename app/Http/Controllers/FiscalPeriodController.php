<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFiscalPeriodRequest;
use App\Http\Requests\UpdateFiscalPeriodRequest;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FiscalPeriodController extends Controller
{
    public function index(Request $request)
    {
        $query = FiscalPeriod::query();

        // Filter by fiscal_year
        if ($request->filled('fiscal_year')) {
            $query->where('fiscal_year', $request->fiscal_year);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        $periods = $query->orderBy('start_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Get available fiscal years for filter
        $fiscalYears = FiscalPeriod::select('fiscal_year')
            ->distinct()
            ->orderBy('fiscal_year', 'desc')
            ->pluck('fiscal_year');

        return Inertia::render('FiscalPeriods/Index', [
            'periods' => $periods,
            'fiscalYears' => $fiscalYears,
            'filters' => $request->only(['fiscal_year', 'status', 'search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('FiscalPeriods/Create');
    }

    public function store(StoreFiscalPeriodRequest $request)
    {
        $period = FiscalPeriod::create([
            ...$request->validated(),
            'status' => 'open',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('fiscal-periods.index')
            ->with('success', "Periode fiskal {$period->name} berhasil dibuat.");
    }

    public function edit(FiscalPeriod $fiscalPeriod)
    {
        return Inertia::render('FiscalPeriods/Edit', [
            'period' => $fiscalPeriod,
        ]);
    }

    public function update(UpdateFiscalPeriodRequest $request, FiscalPeriod $fiscalPeriod)
    {
        if ($fiscalPeriod->status === 'closed') {
            return back()->withErrors(['error' => 'Tidak dapat mengubah periode yang sudah ditutup.']);
        }

        $fiscalPeriod->update([
            ...$request->validated(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('fiscal-periods.index')
            ->with('success', "Periode fiskal {$fiscalPeriod->name} berhasil diperbarui.");
    }

    public function destroy(FiscalPeriod $fiscalPeriod)
    {
        // Check if period has transactions
        $hasTransactions = JournalEntry::where('fiscal_period_id', $fiscalPeriod->id)->exists();

        if ($hasTransactions) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus periode yang sudah memiliki transaksi.']);
        }

        $fiscalPeriod->delete();

        return redirect()->route('fiscal-periods.index')
            ->with('success', "Periode fiskal {$fiscalPeriod->name} berhasil dihapus.");
    }

    public function close(FiscalPeriod $fiscalPeriod)
    {
        if ($fiscalPeriod->status === 'closed') {
            return back()->withErrors(['error' => 'Periode sudah ditutup.']);
        }

        // Check for unposted journal entries
        $unpostedCount = JournalEntry::where('fiscal_period_id', $fiscalPeriod->id)
            ->where('status', 'draft')
            ->count();

        if ($unpostedCount > 0) {
            return back()->withErrors([
                'error' => "Tidak dapat menutup periode. Masih ada {$unpostedCount} jurnal yang belum diposting."
            ]);
        }

        $fiscalPeriod->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('fiscal-periods.index')
            ->with('success', "Periode fiskal {$fiscalPeriod->name} berhasil ditutup.");
    }

    public function reopen(FiscalPeriod $fiscalPeriod)
    {
        if ($fiscalPeriod->status === 'open') {
            return back()->withErrors(['error' => 'Periode sudah terbuka.']);
        }

        $fiscalPeriod->update([
            'status' => 'open',
            'closed_at' => null,
            'closed_by' => null,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('fiscal-periods.index')
            ->with('success', "Periode fiskal {$fiscalPeriod->name} berhasil dibuka kembali. Harap dokumentasikan alasan pembukaan kembali.");
    }
}
