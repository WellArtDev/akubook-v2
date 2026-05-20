<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $startedAt = microtime(true);

        try {
            DB::select('SELECT 1');

            return response()->json([
                'status' => 'ok',
                'app' => 'ok',
                'database' => 'ok',
                'timestamp' => now()->toIso8601String(),
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'status' => 'degraded',
                'app' => 'ok',
                'database' => 'error',
                'timestamp' => now()->toIso8601String(),
                'error' => $exception->getMessage(),
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ], 503);
        }
    }
}
