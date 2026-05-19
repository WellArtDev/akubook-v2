<?php

namespace App\Http\Controllers;

use App\Http\Requests\OpeningBalancesImportRequest;
use App\Services\OpeningBalancesImportService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class OpeningBalancesImportController extends Controller
{
    public function __construct(private OpeningBalancesImportService $service)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Migration/OpeningBalancesImport');
    }

    public function preview(OpeningBalancesImportRequest $request): JsonResponse
    {
        return response()->json($this->service->preview($request->validated()));
    }

    public function import(OpeningBalancesImportRequest $request): JsonResponse
    {
        return response()->json($this->service->import($request->validated()));
    }
}
