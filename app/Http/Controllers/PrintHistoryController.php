<?php

namespace App\Http\Controllers;

use App\Models\PrintHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrintHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = PrintHistory::query()->with(['draft', 'template', 'printer'])->latest('printed_at');

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        if ($request->filled('printed_by')) {
            $query->where('printed_by', $request->integer('printed_by'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('printed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('printed_at', '<=', $request->date_to);
        }

        return Inertia::render('PrintHistories/Index', [
            'histories' => $query->paginate(50)->withQueryString(),
            'filters' => $request->only(['document_type', 'printed_by', 'date_from', 'date_to']),
            'documentTypes' => [
                'sales_invoice',
                'delivery_order',
                'purchase_order',
                'goods_receipt',
            ],
        ]);
    }

    public function show(PrintHistory $printHistory)
    {
        return Inertia::render('PrintHistories/Show', [
            'history' => $printHistory->load(['draft', 'template', 'printer']),
        ]);
    }
}
