<?php

namespace App\Http\Controllers;

use App\Http\Requests\HistoricalTransactionsImportRequest;
use App\Services\HistoricalTransactionsImportService;
use Inertia\Inertia;
use Inertia\Response;

class HistoricalTransactionsImportController extends Controller
{
    public function __construct(private readonly HistoricalTransactionsImportService $service) {}

    public function index(): Response
    {
        return Inertia::render('Migration/HistoricalTransactionsImport');
    }

    public function preview(HistoricalTransactionsImportRequest $request)
    {
        return response()->json($this->service->preview($request->validated()));
    }

    public function import(HistoricalTransactionsImportRequest $request)
    {
        return response()->json($this->service->import($request->validated()));
    }
}
