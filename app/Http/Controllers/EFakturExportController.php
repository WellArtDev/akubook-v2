<?php

namespace App\Http\Controllers;

use App\Models\EFakturExport;
use App\Models\FakturPajak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EFakturExportController extends Controller
{
    public function index(Request $request)
    {
        $query = EFakturExport::query()->with('creator')->latest('period_end');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('period_start', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('period_end', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $request->search) . '%';
            $query->where('export_number', 'like', $search);
        }

        return Inertia::render('EFakturExports/Index', [
            'exports' => $query->paginate(50)->withQueryString(),
            'filters' => $request->only(['status', 'date_from', 'date_to', 'search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('EFakturExports/Create', [
            'availableCount' => FakturPajak::query()->where('status', 'issued')->count(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
        ]);

        $export = DB::transaction(function () use ($validated) {
            $fakturs = FakturPajak::query()
                ->with(['customer', 'salesInvoice'])
                ->where('status', 'issued')
                ->whereBetween('faktur_date', [$validated['period_start'], $validated['period_end']])
                ->orderBy('faktur_date')
                ->orderBy('faktur_number')
                ->get();

            $export = EFakturExport::query()->create([
                'export_number' => EFakturExport::generateNumber(),
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end'],
                'status' => 'generated',
                'row_count' => $fakturs->count(),
                'metadata' => [
                    'format' => 'csv',
                    'source_status' => 'issued',
                ],
                'created_by' => Auth::id(),
                'generated_at' => now(),
            ]);

            $rows = [[
                'faktur_number',
                'faktur_date',
                'invoice_number',
                'customer_name',
                'customer_tax_id',
                'dpp',
                'ppn_amount',
                'grand_total',
            ]];

            foreach ($fakturs as $index => $faktur) {
                $line = $export->lines()->create([
                    'faktur_pajak_id' => $faktur->id,
                    'line_number' => $index + 1,
                    'faktur_number' => $faktur->faktur_number,
                    'faktur_date' => $faktur->faktur_date,
                    'customer_name' => $faktur->customer?->name ?? '-',
                    'customer_tax_id' => $faktur->customer?->tax_id,
                    'dpp' => $faktur->dpp,
                    'ppn_amount' => $faktur->ppn_amount,
                    'grand_total' => $faktur->grand_total,
                ]);

                $rows[] = [
                    $line->faktur_number,
                    $line->faktur_date->toDateString(),
                    $faktur->salesInvoice?->invoice_number ?? '',
                    $line->customer_name,
                    $line->customer_tax_id ?? '',
                    number_format((float) $line->dpp, 2, '.', ''),
                    number_format((float) $line->ppn_amount, 2, '.', ''),
                    number_format((float) $line->grand_total, 2, '.', ''),
                ];
            }

            $export->update(['csv_content' => $this->toCsv($rows)]);

            return $export;
        });

        return redirect()->route('e-faktur-exports.show', $export)->with('success', 'E-Faktur export generated.');
    }

    public function show(EFakturExport $eFakturExport)
    {
        return Inertia::render('EFakturExports/Show', [
            'exportBatch' => $eFakturExport->load(['lines.fakturPajak', 'creator']),
        ]);
    }

    public function download(EFakturExport $eFakturExport): StreamedResponse
    {
        return response()->streamDownload(function () use ($eFakturExport) {
            echo $eFakturExport->csv_content ?? '';
        }, $eFakturExport->export_number . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function toCsv(array $rows): string
    {
        $handle = fopen('php://temp', 'r+');

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
