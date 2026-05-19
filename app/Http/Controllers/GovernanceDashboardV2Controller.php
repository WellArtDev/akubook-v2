<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ComplianceExportPack;
use App\Models\DataRetentionExecution;
use App\Models\SensitiveAlert;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GovernanceDashboardV2Controller extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = $validated['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $validated['date_to'] ?? now()->toDateString();

        $retentionBase = DataRetentionExecution::query()
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        $enforcementBase = AuditLog::query()
            ->where('event_key', 'workflow.enforcement.evaluated')
            ->whereDate('occurred_at', '>=', $dateFrom)
            ->whereDate('occurred_at', '<=', $dateTo);

        $alertBase = SensitiveAlert::query()
            ->whereDate('generated_at', '>=', $dateFrom)
            ->whereDate('generated_at', '<=', $dateTo);

        $exportBase = ComplianceExportPack::query()
            ->whereDate('generated_at', '>=', $dateFrom)
            ->whereDate('generated_at', '<=', $dateTo);

        $kpis = [
            'retention_runs' => (clone $retentionBase)->count(),
            'enforcement_count' => (clone $enforcementBase)->where('action', 'enforced')->count(),
            'sensitive_alerts' => (clone $alertBase)->count(),
            'export_packs' => (clone $exportBase)->count(),
        ];

        return Inertia::render('GovernanceDashboardV2/Index', [
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'kpis' => $kpis,
            'trend' => $this->dailyTrend($dateFrom, $dateTo),
            'latest' => [
                'retention_run' => (clone $retentionBase)->latest()->first(),
                'export_pack' => (clone $exportBase)->latest('generated_at')->first(),
            ],
            'generated_at' => now()->toDateTimeString(),
        ]);
    }

    private function dailyTrend(string $dateFrom, string $dateTo): array
    {
        return [
            'retention_runs' => $this->dailyCounts(DataRetentionExecution::query(), 'created_at', $dateFrom, $dateTo),
            'workflow_enforcements' => $this->dailyCounts(
                AuditLog::query()->where('event_key', 'workflow.enforcement.evaluated')->where('action', 'enforced'),
                'occurred_at',
                $dateFrom,
                $dateTo
            ),
            'sensitive_alerts' => $this->dailyCounts(SensitiveAlert::query(), 'generated_at', $dateFrom, $dateTo),
            'export_packs' => $this->dailyCounts(ComplianceExportPack::query(), 'generated_at', $dateFrom, $dateTo),
        ];
    }

    private function dailyCounts($query, string $column, string $dateFrom, string $dateTo): array
    {
        return $query
            ->whereDate($column, '>=', $dateFrom)
            ->whereDate($column, '<=', $dateTo)
            ->selectRaw("DATE({$column}) as date, COUNT(*) as total")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'total' => (int) $row->total,
            ])
            ->all();
    }
}
