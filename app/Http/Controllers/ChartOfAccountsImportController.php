<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChartOfAccountsImportRequest;
use App\Services\ChartOfAccountsImportService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class ChartOfAccountsImportController extends Controller
{
    public function __construct(private ChartOfAccountsImportService $service)
    {
    }

    public function index()
    {
        return Inertia::render('Migration/ChartOfAccountsImport');
    }

    public function preview(ChartOfAccountsImportRequest $request): JsonResponse
    {
        return response()->json($this->service->preview($request->validated()));
    }

    public function import(ChartOfAccountsImportRequest $request): JsonResponse
    {
        return response()->json($this->service->import($request->validated()));
    }
}
