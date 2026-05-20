<?php

return [
    'results_path' => env('SLO_RESULTS_PATH', 'test-results/slo-smoke-results.json'),
    'artifact_path' => '_bmad-output/implementation-artifacts/performance-baselines/slo-error-budget-latest.json',
    'endpoints' => [
        '/healthz' => ['target_ms' => 1000, 'warning_ms' => 750],
        '/dashboard' => ['target_ms' => 1500, 'warning_ms' => 1000],
        '/role-dashboard' => ['target_ms' => 1500, 'warning_ms' => 1000],
        '/governance-dashboard-v2' => ['target_ms' => 1500, 'warning_ms' => 1000],
    ],
];
