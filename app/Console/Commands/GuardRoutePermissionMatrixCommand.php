<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class GuardRoutePermissionMatrixCommand extends Command
{
    protected $signature = 'app:guard-route-permissions {--simulate-missing-map : Simulate missing matrix mapping for test}';

    protected $description = 'Verify critical route permission matrix is complete and middleware matches expectation';

    public function handle(): int
    {
        $matrix = config('route_permission_matrix.critical_routes', []);

        if ($this->option('simulate-missing-map') && ! empty($matrix)) {
            array_pop($matrix);
        }

        $issues = [];

        foreach ($this->requiredCriticalRouteNames() as $routeName) {
            if (! array_key_exists($routeName, $matrix)) {
                $issues[] = "Missing matrix mapping: {$routeName}";
            }
        }

        foreach ($matrix as $routeName => $expectedMiddlewares) {
            $route = Route::getRoutes()->getByName($routeName);

            if ($route === null) {
                $issues[] = "Missing route: {$routeName}";
                continue;
            }

            $actualMiddlewares = collect($route->gatherMiddleware())
                ->map(fn ($middleware) => trim((string) $middleware))
                ->values();

            foreach ($expectedMiddlewares as $expectedMiddleware) {
                if (! $actualMiddlewares->contains($expectedMiddleware)) {
                    $issues[] = "Route {$routeName} missing middleware {$expectedMiddleware}";
                }
            }
        }

        if (! empty($issues)) {
            $this->error('Route permission matrix guardrail failed:');
            foreach ($issues as $issue) {
                $this->line('- '.$issue);
            }

            return self::FAILURE;
        }

        $this->info('Route permission matrix guardrail passed.');

        return self::SUCCESS;
    }

    private function requiredCriticalRouteNames(): array
    {
        return [
            'dashboard',
            'role-dashboard.index',
            'governance-dashboard-v2.index',
            'customers.index',
            'suppliers.index',
            'sales-orders.index',
            'purchase-orders.index',
            'customer-payments.index',
            'supplier-payments.index',
            'sensitive-alerts.index',
            'compliance-export-packs.index',
            'fiscal-periods.index',
            'journal-entries.index',
        ];
    }
}
