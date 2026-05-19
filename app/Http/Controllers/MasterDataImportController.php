<?php

namespace App\Http\Controllers;

use App\Http\Requests\MasterDataImportRequest;
use App\Services\MasterDataImportService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class MasterDataImportController extends Controller
{
    public function __construct(private MasterDataImportService $service)
    {
    }

    public function index()
    {
        return Inertia::render('Migration/MasterDataImport');
    }

    public function preview(MasterDataImportRequest $request): JsonResponse
    {
        return response()->json($this->service->preview($request->validated()));
    }

    public function import(MasterDataImportRequest $request): JsonResponse
    {
        return response()->json($this->service->import($request->validated()));
    }
}
