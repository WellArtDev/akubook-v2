<?php

namespace App\Http\Controllers;

use App\Models\ComplianceExportPack;
use App\Services\ComplianceExportPackService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComplianceExportPackController extends Controller
{
    public function __construct(private readonly ComplianceExportPackService $complianceExportPackService)
    {
    }

    public function index()
    {
        $packs = ComplianceExportPack::query()
            ->with('generator:id,name')
            ->latest('generated_at')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('ComplianceExportPacks/Index', [
            'packs' => $packs,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
        ]);

        $pack = $this->complianceExportPackService->generate(
            $validated['period_start'],
            $validated['period_end'],
            $request
        );

        return redirect()->route('compliance-export-packs.show', $pack)->with('success', 'Compliance export pack generated.');
    }

    public function show(ComplianceExportPack $complianceExportPack)
    {
        return Inertia::render('ComplianceExportPacks/Show', [
            'pack' => $complianceExportPack->load('generator:id,name'),
        ]);
    }

    public function download(ComplianceExportPack $complianceExportPack): StreamedResponse
    {
        return response()->streamDownload(function () use ($complianceExportPack) {
            echo $complianceExportPack->payload_json;
        }, $complianceExportPack->pack_number . '.json', [
            'Content-Type' => 'application/json',
        ]);
    }
}
