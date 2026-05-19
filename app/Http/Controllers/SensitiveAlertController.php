<?php

namespace App\Http\Controllers;

use App\Models\SensitiveAlert;
use App\Services\SensitiveAlertService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SensitiveAlertController extends Controller
{
    public function __construct(private readonly SensitiveAlertService $sensitiveAlertService)
    {
    }

    public function index()
    {
        $alerts = SensitiveAlert::query()
            ->with('generator:id,name')
            ->latest('generated_at')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('SensitiveAlerts/Index', [
            'alerts' => $alerts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'threshold' => ['nullable', 'integer', 'min:1'],
            'window_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
        ]);

        $alert = $this->sensitiveAlertService->generate(
            $request,
            $validated['threshold'] ?? 3,
            $validated['window_minutes'] ?? 60
        );

        return redirect()->route('sensitive-alerts.index')->with(
            'success',
            $alert ? 'Sensitive alert generated.' : 'No sensitive alert threshold reached.'
        );
    }
}
